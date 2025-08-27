<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Company;
use App\Models\CompanyProfile;
use App\Models\Country;
use App\Models\State;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use libphonenumber\PhoneNumberUtil;
use Propaganistas\LaravelPhone\Exceptions\NumberParseException;

class CompanyController extends Controller
{
    public function create()
    {
        // Get all countries
        $countries = Country::all();

        return Inertia::render('Company/Create', props: [
            'countries' => $countries
        ]);
    }

    public function getStates(Request $request)
    {
    
        // Filter states by country_id
        $states = State::where('country_id', $request->country_id)->get();
    
        if ($states->isEmpty()) {
            return response()->json(['error' => 'No states found for this country'], 404);
        }
    
        return response()->json($states);
    }

    public function getCities($country, $state)
    {
        // Filter cities by both country_id and state_code
        $cities = City::where('state_code', $state)
                     ->where('country_id', $country)
                     ->get();
    
        if ($cities->isEmpty()) {
            return response()->json(['error' => 'No cities found for this state and country'], 404);
        }
    
        return response()->json($cities);
    }
    
    public function store(Request $request)
    {
        $request->validate([
                'name' => 'required|string|max:255',
                Rule::unique('companies')->where(function ($query) use ($request) {
                    return $query->where('user_id', auth()->id());
                }),
                'email' => 'required|string|email|unique:company_profiles,email',
                'street_address' => 'required|string|max:255',
                'city' => 'required|string|max:255',
                'state' => 'required|string|max:255',
                'postal_code' => 'nullable|string|max:10',
                'country' => 'required|string|max:255',
                'phone_number' => [
                    'required',
                    function ($attribute, $value, $fail) {
                        $phoneUtil = PhoneNumberUtil::getInstance();
                        try {
                            $number = $phoneUtil->parse($value, null);
                            if (!$phoneUtil->isValidNumber($number)) {
                                $fail("The phone number is invalid.");
                            }
                        } catch (NumberParseException $e) {
                            $fail("Invalid phone number format.");
                        }
                    },
                ],
            ]);

            $personalCompany = $request->has('personal_company') ? $request->personal_company : false;

            $existingCompany = Company::where('name', $request->name)->first();

            if ($existingCompany) {
                return redirect()->back()->with('error', 'Company already exists');
            }

            try {
                $company = Company::create([
                    'user_id' => auth()->user()->id,
                    'name' => $request->name,
                    'personal_company' => $personalCompany,
                 ]);
    
                 $profile = CompanyProfile::create([
                    'company_id' => $company->id,
                    'name' => $request->name,
                    'email' => $request->email,
                    'phone_number' => $request->phone_number,
                    'street_address' => $request->street_address,
                    'city' => $request->city,
                    'state' => $request->state,
                    'postal_code' => $request->postal_code,
                    'country' => $request->country,
                    'created_by' => auth()->id(),
                ]);
    
                // Set the newly created company as the current company
                $user = auth()->user();
                $user->current_company_id = $company->id;
                $user->save();
    
                $adminPanel = Filament::getUrl();
                return Inertia::location($adminPanel);
            } catch (\Exception $e) {
                return back()->withErrors(['error' => 'Something went wrong. Please try again.'])->withInput();
            }
    }
}
