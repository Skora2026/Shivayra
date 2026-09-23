<?php

namespace App\Http\Controllers;

use App\Mail\SendOtpMail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OtpController extends Controller
{
    /**
     * Send OTP to email.
     * POST /otp/send
     */
    public function send(Request $request): JsonResponse
    {
        $request->validate([
            'value' => 'required|email',
        ]);

        $email = $request->value;

        // Rate limit: 3 OTPs per 10 minutes per email
        $rateKey = "otp_rate:email:{$email}";
        $attempts = Cache::get($rateKey, 0);

        if ($attempts >= 3) {
            return response()->json([
                'success' => false,
                'message' => 'Too many OTP requests. Please wait a few minutes.',
            ], 429);
        }

        // Generate 6-digit OTP
        $otp = strval(random_int(100000, 999999));

        // Store OTP with 10-minute expiry
        $cacheKey = "otp:email:{$email}";
        Cache::put($cacheKey, Hash::make($otp), now()->addMinutes(10));

        // Increment rate limit
        Cache::put($rateKey, $attempts + 1, now()->addMinutes(10));

        // Also store in DB for email verification tracking
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            ['token' => Hash::make($otp), 'created_at' => now()]
        );

        try {
            Mail::to($email)->send(new SendOtpMail($otp));
        } catch (\Exception $e) {
            Log::error('Failed to send OTP email: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to send OTP. Please try again.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => "OTP sent to {$email}",
        ]);
    }

    /**
     * Verify OTP for email.
     * POST /otp/verify
     */
    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'value' => 'required|email',
            'otp' => 'required|string|size:6',
        ]);

        $email = $request->value;
        $otp = $request->otp;

        // Brute-force guard: 5 verify attempts per 15 min per email. Every
        // failure burns an attempt; exhausting the budget kills the stored OTP
        // so a fresh send (rate-limited itself) is required to continue.
        $attemptKey = "otp_attempts:email:{$email}";
        $attempts = (int) Cache::get($attemptKey, 0);

        if ($attempts >= 5) {
            Cache::forget("otp:email:{$email}");

            return response()->json([
                'success' => false,
                'message' => 'Too many incorrect attempts. Please request a new OTP.',
            ], 429);
        }

        $cacheKey = "otp:email:{$email}";
        $hashedOtp = Cache::get($cacheKey);

        if (! $hashedOtp || ! Hash::check($otp, $hashedOtp)) {
            Cache::put($attemptKey, $attempts + 1, now()->addMinutes(15));

            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired OTP.',
            ], 422);
        }

        // OTP verified - remove it, clear the attempt budget, and leave a
        // short-lived proof marker the register endpoint can trust (the DB
        // tracking row is deleted above, so it cannot serve as proof).
        Cache::forget($cacheKey);
        Cache::forget($attemptKey);
        Cache::put("otp_verified:email:{$email}", true, now()->addMinutes(30));
        DB::table('password_reset_tokens')->where('email', $email)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully.',
        ]);
    }
}
