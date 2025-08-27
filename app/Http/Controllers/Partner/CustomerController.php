<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\Partner\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string',
            'email' => 'nullable|email|unique:customers,email',
            'address' => 'nullable|string',
        ]);

        $validatedData['company_id'] = auth()->user()->current_company_id;

        Customer::create($validatedData);

        return back()->with('success', 'Customer created successfully.');
    }
}