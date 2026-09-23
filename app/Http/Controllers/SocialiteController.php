<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    /**
     * Redirect user to Google for authentication.
     */
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle the Google OAuth callback.
     */
    public function callback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('login')->withErrors([
                'email' => 'Unable to authenticate with Google. Please try again.',
            ]);
        }

        // Find or create user by google_id or email
        $user = User::where('google_id', $googleUser->getId())->first();

        if (! $user) {
            $user = User::where('email', $googleUser->getEmail())->first();
        }

        if ($user) {
            // Link Google ID if not already linked
            if (! $user->google_id) {
                $user->update(['google_id' => $googleUser->getId()]);
            }
        } else {
            // Create new user from Google data
            $user = User::create([
                'name' => $googleUser->getName() ?? $googleUser->getNickname() ?? 'Google User',
                'first_name' => $googleUser->getFirstName() ?? $googleUser->getName(),
                'last_name' => $googleUser->getLastName() ?? '',
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'email_verified' => true, // Google accounts are pre-verified
                'status' => 'active',
                'password' => Hash::make(Str::random(32)), // random password, they login via Google
            ]);

            $user->assignRole('user');
        }

        // Check if account is active
        if ($user->status !== 'active') {
            return redirect()->route('login')->withErrors([
                'email' => 'Your account has been deactivated. Please contact support.',
            ]);
        }

        Auth::login($user, true); // true = remember me
        $request = request();
        $request->session()->regenerate();

        // Admins go to admin dashboard, customers to my-account
        if ($user->hasRole('admin')) {
            return redirect()->intended(route('admin.dashboard'));
        }

        return redirect()->intended(route('my-account'))
            ->with('success', 'Welcome back, '.($user->first_name ?: $user->name).'!');
    }
}
