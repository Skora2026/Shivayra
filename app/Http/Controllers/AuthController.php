<?php

namespace App\Http\Controllers;

use App\Mail\SendOtpMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    //
    public function loginView()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {

            $request->session()->regenerate();

            $user = Auth::user();

            // Deactivated accounts cannot keep shopping (QA finding #5)
            if ($user && $user->status !== 'active') {
                Auth::logout();
                $request->session()->invalidate();

                return back()->withErrors([
                    'email' => 'Your account has been deactivated. Please contact support.',
                ])->onlyInput('email');
            }

            if ($user && $user->hasRole('admin')) {
                return redirect()->intended(route('admin.dashboard'));
            }

            // Customer dashboard — intended() still wins when the user was
            // heading somewhere (e.g. checkout) before being asked to log in.
            return redirect()->intended(route('my-account'))
                ->with('success', 'Welcome back, '.($user->first_name ?: $user->name).'!');
        }

        return back()
            ->withErrors([
                'email' => 'Invalid email or password.',
            ])
            ->onlyInput('email');
    }

    public function registerView()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
        ]);

        // The register page walks the user through an email OTP before submit,
        // but the UI alone proves nothing — a crafted POST would otherwise
        // create an active account on an unverified (possibly someone else's)
        // email. Server-side proof: OtpController::verify leaves a marker when
        // an OTP for this address is genuinely solved (valid 30 minutes).
        if (! cache()->get("otp_verified:email:{$request->email}")) {
            return back()
                ->withInput($request->only('name', 'email'))
                ->withErrors(['email' => 'Please verify your email with the OTP code before creating your account.']);
        }

        $user = User::create([
            'name' => $request->name,
            'first_name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'status' => 'active',
            'email_verified' => true,
            'email_verified_at' => now(),
        ]);

        $user->assignRole('user');

        // Auto log in the newly registered user
        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->intended(route('my-account'))
            ->with('success', 'Registration Successful. You are now logged in!');
    }

    public function forgotPasswordView()
    {
        return view('auth.forgot-password');
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        // Cryptographically random 6-digit code, stored hashed with a 15-minute window
        $otp = strval(random_int(100000, 999999));

        \DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => Hash::make($otp),
                'created_at' => now(),
            ]
        );

        try {
            \Mail::to($request->email)->send(new SendOtpMail($otp));
        } catch (\Exception $e) {
            \Log::error('Failed to send OTP email: '.$e->getMessage());

            return back()->withErrors(['email' => 'Could not send OTP email. Please verify mail settings.']);
        }

        return redirect()->route('password.reset', ['email' => $request->email])
            ->with('success', 'OTP code sent to your email.');
    }

    public function resetPasswordView()
    {
        return view('auth.reset-password');
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|string|size:6',
            'password' => 'required|min:8|confirmed',
        ]);

        $reset = \DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        $expired = $reset && $reset->created_at && now()->diffInMinutes($reset->created_at) > 15;

        if ($expired) {
            \DB::table('password_reset_tokens')->where('email', $request->email)->delete();

            return back()->withErrors(['otp' => 'Invalid or expired OTP code.'])->withInput();
        }

        // hashed comparison + bounded guesses per code
        $attempts = cache()->get("otp_attempts:{$request->email}", 0);

        if (! $reset || $attempts >= 5 || ! Hash::check($request->otp, $reset->token)) {
            cache()->put("otp_attempts:{$request->email}", $attempts + 1, now()->addMinutes(15));

            return back()->withErrors(['otp' => 'Invalid or expired OTP code.'])->withInput();
        }

        cache()->forget("otp_attempts:{$request->email}");

        // Update password
        $user = User::where('email', $request->email)->first();
        if ($user) {
            $user->password = $request->password;
            $user->save();
        }

        // Delete used token
        \DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')
            ->with('success', 'Password reset successfully. You can now log in.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Send people back where they came from: admin logout -> admin login, storefront -> home
        if ($request->is('admin') || $request->is('admin/*')) {
            return redirect()->route('admin.login');
        }

        return redirect('/')->with('success', 'You have been logged out.');
    }
}
