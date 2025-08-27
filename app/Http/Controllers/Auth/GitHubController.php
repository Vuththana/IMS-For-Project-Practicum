<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class GitHubController extends Controller
{
    /**
     * Redirect the user to GitHub's authentication page.
     */
    public function redirect()
    {
        return Socialite::driver('github')->redirect();
    }

    /**
     * Handle GitHub callback and authenticate the user.
     */
    public function callback()
    {
        $githubUser = Socialite::driver('github')->user();

        // Find or create user
        $user = User::updateOrCreate(
            ['github_id' => $githubUser->id],
            [
                'name' => $githubUser->nickname ?? $githubUser->name,
                'email' => $githubUser->email ?? $githubUser->id . '@github.com', // Some GitHub users don't have public emails
                'avatar' => $githubUser->avatar,
                'password' => bcrypt(str()->random(24)), // Random password to satisfy Laravel's password requirement
            ]
        );

        // Check if registered user has admin or not if not assign the account with Admin role
        if(!$user->hasRole('Admin')) {
            $user->assignRole('Admin');
        }

        // Log the user in
        Auth::login($user);

        return redirect('/admin');
    }
}
