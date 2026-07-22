<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * Register User / Owner
     */
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role'     => $data['role'],
            'status'   => $data['role'] === 'owner' ? 'pending' : 'active',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registration successful.',
            'data' => [
                'user' => [
                    'id'     => $user->id,
                    'name'   => $user->name,
                    'email'  => $user->email,
                    'role'   => $user->role,
                    'status' => $user->status,
                ],
                'token' => $token,
            ]
        ], 201);
    }

    /**
     * Login
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password.'
            ], 401);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Pending Owner
        if ($user->status === 'pending') {
            Auth::logout();

            return response()->json([
                'success' => false,
                'message' => 'Your account is pending admin approval.'
            ], 403);
        }

        // Blocked User
        if ($user->status === 'blocked') {
            Auth::logout();

            return response()->json([
                'success' => false,
                'message' => 'Your account has been blocked.'
            ], 403);
        }

        // Delete old tokens
        $user->tokens()->delete();

        // Generate new token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'data' => [
                'user' => [
                    'id'     => $user->id,
                    'name'   => $user->name,
                    'email'  => $user->email,
                    'role'   => $user->role,
                    'status' => $user->status,
                ],
                'token' => $token,
            ]
        ], 200);
    }

    public function logout(Request $request){
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success'=> true,
            'message'=>'Logout successful',
        ],200);
    }

    public function profile(Request $request){
        $user = $request->user();

        return response()->json([
            'success'=>true,
            'date'=> [
                'id'=> $user->id,
                'name'=> $user->name,
                'email'=> $user->email,
                'role'=> $user->role,
                'status'=> $user->status,

            ]
        ],200);
    }
}
