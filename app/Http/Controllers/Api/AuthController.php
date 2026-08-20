<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Support\JwtToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone_number' => ['required', 'string', 'max:20', 'unique:users,phone_number'],
            'pin' => ['required', 'string', 'digits:6'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $user = User::create([
            'user_id' => (string) Str::uuid(),
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'address' => $validated['address'] ?? null,
            'email' => $validated['email'],
            'phone_number' => $validated['phone_number'],
            'pin' => Hash::make((string) $validated['pin']),
            'password' => Hash::make($validated['password']),
            'balance' => 0,
        ]);

        return response()->json([
            'token' => JwtToken::encode($user),
            'user' => $user,
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'phone_number' => ['required_without:email', 'string'],
            'email' => ['required_without:phone_number', 'email'],
            'pin' => ['required', 'string', 'digits:6'],
        ]);

        $user = null;

        if (! empty($credentials['phone_number'])) {
            $user = User::where('phone_number', $credentials['phone_number'])->first();
        } elseif (! empty($credentials['email'])) {
            $user = User::where('email', $credentials['email'])->first();
        }

        if (! $user || ! Hash::check((string) $credentials['pin'], $user->pin ?? '')) {
            return response()->json(['message' => 'Invalid phone number or PIN.'], 401);
        }

        return response()->json([
            'access_token' => JwtToken::encode($user),
            'refresh_token' => JwtToken::refresh($user),
            'token_type' => 'bearer',
            'user' => $user,
        ]);
    }
}
