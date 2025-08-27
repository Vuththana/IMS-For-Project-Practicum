<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\Partner\Deliverer; // 1. Import the Deliverer model
use Illuminate\Http\Request;

class DelivererController extends Controller
{
    /**
     * Store a newly created deliverer in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // 2. Validate the incoming data from the form
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:Internal,External', // Ensure type is one of the allowed options
            'phone_number' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255|unique:deliverers,email',
        ]);

        // 3. Get the current authenticated user's company ID
        $validatedData['company_id'] = auth()->user()->current_company_id;
        $validatedData['created_by'] = auth()->id(); // Optionally track who created the record

        // 4. Create the new deliverer with the validated data
        Deliverer::create($validatedData);

        // 5. Redirect back to the previous page (the cash register)
        // Inertia will automatically update the `deliverers` prop on the frontend.
        return back()->with('success', 'Deliverer created successfully!');
    }
}