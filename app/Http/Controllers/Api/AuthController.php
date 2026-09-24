<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\ApiToken;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'username' => $validated['username'],
            'name' => $validated['username'],
            'email' => $validated['username'].'@example.invalid',
            'password' => $validated['password'],
        ]);

        return response()->json([
            'message' => 'User registered successfully.',
            'data' => [
                'id' => $user->id,
                'username' => $user->username,
                'name' => $user->name,
            ],
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = User::where('username', $validated['username'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid username or password.',
            ], 401);
        }

        $authenticationToken = Str::random(80);
        $refreshToken = Str::random(80);

        ApiToken::create([
            'user_id' => $user->id,
            'token_hash' => hash('sha256', $authenticationToken),
            'refresh_token_hash' => hash('sha256', $refreshToken),
            'expires_at' => now()->addDay(),
            'refresh_expires_at' => now()->addDays(30),
        ]);

        return response()->json([
            'authentication_token' => $authenticationToken,
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer',
            'expires_at' => now()->addDay()->format('Y-m-d H:i:s'),
        ]);
    }
}
