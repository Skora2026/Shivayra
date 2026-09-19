<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Show the owner login page.
     */
    public function showLogin(): View
    {
        return view('admin.login');
    }

    /**
     * Owner login. Only accounts with the admin role may pass — a customer's
     * valid credentials get the same generic error as a wrong password, so the
     * form never reveals which emails exist.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Brute-force guard, two buckets:
        //   per email (5/5min) — stops hammering one account
        //   per IP (20/15min)  — stops password spraying across many emails
        $throttleKey = 'admin-login:'.strtolower($credentials['email']).'|'.$request->ip();
        $ipKey = 'admin-login-ip:'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5) || RateLimiter::tooManyAttempts($ipKey, 20)) {
            $seconds = max(RateLimiter::availableIn($throttleKey), RateLimiter::availableIn($ipKey));

            return back()->withErrors([
                'email' => "Too many login attempts. Try again in {$seconds} seconds.",
            ])->onlyInput('email');
        }

        if (! Auth::attempt($credentials)) {
            RateLimiter::hit($throttleKey, 300);
            RateLimiter::hit($ipKey, 900);

            return back()->withErrors([
                'email' => 'Invalid credentials.',
            ])->onlyInput('email');
        }

        $user = Auth::user();

        if (! $user || ! $user->hasRole('admin')) {
            Auth::logout();
            $request->session()->invalidate();
            RateLimiter::hit($throttleKey, 300);
            RateLimiter::hit($ipKey, 900);

            return back()->withErrors([
                'email' => 'Invalid credentials.',
            ])->onlyInput('email');
        }

        RateLimiter::clear($throttleKey);
        RateLimiter::clear($ipKey);
        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    /**
     * Owner logout — always lands back on the admin login page.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
