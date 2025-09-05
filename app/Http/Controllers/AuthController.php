<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\OtpCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Mail\OtpMail;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'nullable|email|unique:users',
            'phone' => 'nullable|string|unique:users',
            'password' => 'required|min:6',
            'role' => 'required|in:rider,driver,admin',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => $request->role,
            'password' => Hash::make($request->password),
        ]);

        return response()->json(['message' => 'User registered successfully. Please verify via OTP.', 'user' => $user], 201);
    }

    public function login(Request $request)
    {
        $field = filter_var($request->input('email'), FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        $user = User::where($field, $request->input('email') ?? $request->input('phone'))->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['token' => $token, 'user' => $user]);
    }


    public function sendOtp(Request $request)
    {
        $request->validate([
            'type' => 'required|in:phone,email',
            'recipient' => 'required|string'
        ]);

        $code = rand(100000, 999999);

        $expiresAt = Carbon::now()->addMinutes(5);

        $otp = OtpCode::create([
            'recipient' => $request->recipient,
            'code' => $code,
            'type' => $request->type,
            'expires_at' => $expiresAt,
            'is_used' => false,
        ]);

        if ($request->type === 'email') {
            Mail::to($request->recipient)->send(new OtpMail($code));
        }

        return response()->json([
            'message' => 'OTP sent',
            'expires_at' => $expiresAt->toDateTimeString()
        ]);
    }


    public function verifyOtp(Request $request)
    {
        $request->validate([
            'recipient' => 'required|string',
            'code' => 'required|string'
        ]);

        $otp = OtpCode::where('recipient', $request->recipient)
            ->where('code', $request->code)
            ->where('is_used', false)
            ->latest()
            ->first();

        if (!$otp) {
            return response()->json(['message' => 'Invalid OTP'], 400);
        }

        if (Carbon::now()->gt($otp->expires_at)) {
            return response()->json(['message' => 'OTP expired'], 400);
        }

        $otp->update(['is_used' => true]);

        $user = User::where('email', $request->recipient)
            ->orWhere('phone', $request->recipient)
            ->first();

        if ($user) {
            $user->update(['is_verified' => true]);
        }

        return response()->json(['message' => 'OTP verified', 'user' => $user]);
    }

    public function checkAuth(Request $request)
    {
        return response()->json([
            'authenticated' => true,
            'user' => $request->user(),
        ]);
    }
}
