<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email'=> ['required', 'email'],
            'password' => 'required',
            'remember' => 'boolean'
        ]);
        $remember = $credentials['remember'] ?? false;
        unset($credentials['remember']);
        if (!Auth::attempt($credentials, $remember)) {
            return response()->json([
                'message' => 'Email or password is incorrect'
            ], 422);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->is_admin) {
            Auth::logout();
            return response()->json([
                'message' => 'You don\'t have permission to authenticate as admin'
            ], 403);
        }
        if (!$user->email_verified_at) {
            Auth::logout();
            return response()->json([
                'message' => 'Your email address is not verified'
            ], 403);
        }
        $token = $user->createToken('main')->plainTextToken;
        return response()->json([
            'user' => new UserResource($user),
            'token' => $token
        ]);

    }

    public function logout(): Response
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->currentAccessToken()->delete();

        return response()->noContent();
    }

    public function getUser(Request $request): UserResource
    {
        return new UserResource($request->user());
    }
}
