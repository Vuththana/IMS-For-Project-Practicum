<?php

namespace App\Http\Middleware;

use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class CheckCurrentCompany
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Exclude the create company route from the middleware check
        if ($user && is_null($user->current_company_id) && $request->route()->getName() !== 'company.create') {
            session()->flash('success', "You don't have a company yet. Please create one.");

            // Redirect to company creation page
            return redirect()->route('company.create');
        }

        if ($user && !is_null($user->current_company_id) && $request->route()->getName() === 'company.create') {
            // Ensure the user is authorized to access the company
            if (!Gate::allows('view', $user->company)) {
                abort(403, 'Unauthorized');
            }

            $adminPanel = Filament::getUrl();
            return Inertia::location($adminPanel);
        }

        return $next($request);
    }
}

