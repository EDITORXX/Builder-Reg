<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChannelPartner;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();
        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages(['email' => ['The provided credentials are incorrect.']]);
        }
        if (! $user->is_active) {
            throw ValidationException::withMessages(['email' => ['Account is deactivated.']]);
        }

        $user->tokens()->where('name', 'api')->delete();
        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'data' => [
                'user' => $user->load('builderFirm', 'channelPartner'),
                'token' => $token,
                'token_type' => 'Bearer',
            ],
            'message' => 'Success',
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Success']);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('builderFirm', 'channelPartner');
        return response()->json(['data' => $user, 'message' => 'Success']);
    }

    public function registerCp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => ['required', 'confirmed', Password::defaults()],
            'firm_name' => 'nullable|string|max:255',
            'rera_number' => 'nullable|string|max:100',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => User::ROLE_CHANNEL_PARTNER,
            'builder_firm_id' => null,
            'is_active' => true,
        ]);

        ChannelPartner::create([
            'user_id' => $user->id,
            'firm_name' => $validated['firm_name'] ?? null,
            'rera_number' => $validated['rera_number'] ?? null,
        ]);

        $token = $user->createToken('api')->plainTextToken;
        return response()->json([
            'data' => [
                'user' => $user->load('channelPartner'),
                'token' => $token,
                'token_type' => 'Bearer',
            ],
            'message' => 'Success',
        ], 201);
    }
}
