<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    /**
     * Register Normal User
     */
    #[OA\Post(
        path: '/api/register',
        summary: 'Register a new user',
        description: 'Register a normal user account.',
        tags: ['Authentication'],

        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: [
                    'name',
                    'email',
                    'password',
                    'password_confirmation'
                ],
                properties: [
                    new OA\Property(
                        property: 'name',
                        type: 'string',
                        example: 'Vicky'
                    ),

                    new OA\Property(
                        property: 'email',
                        type: 'string',
                        format: 'email',
                        example: 'vicky@gmail.com'
                    ),

                    new OA\Property(
                        property: 'password',
                        type: 'string',
                        format: 'password',
                        minLength: 8,
                        example: 'password123'
                    ),

                    new OA\Property(
                        property: 'password_confirmation',
                        type: 'string',
                        format: 'password',
                        example: 'password123'
                    ),
                ]
            )
        ),

        responses: [
            new OA\Response(
                response: 200,
                description: 'Registration successful',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'success',
                            type: 'boolean',
                            example: true
                        ),
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Registration successful.'
                        ),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(
                                    property: 'user',
                                    type: 'object',
                                    properties: [
                                        new OA\Property(
                                            property: 'id',
                                            type: 'integer',
                                            example: 1
                                        ),
                                        new OA\Property(
                                            property: 'name',
                                            type: 'string',
                                            example: 'Vicky'
                                        ),
                                        new OA\Property(
                                            property: 'email',
                                            type: 'string',
                                            example: 'vicky@gmail.com'
                                        ),
                                        new OA\Property(
                                            property: 'role',
                                            type: 'string',
                                            example: 'user'
                                        ),
                                        new OA\Property(
                                            property: 'status',
                                            type: 'string',
                                            example: 'active'
                                        ),
                                    ]
                                ),
                                new OA\Property(
                                    property: 'token',
                                    type: 'string',
                                    example: '1|abcdefghijklmnopqrstuvwxyz'
                                ),
                            ]
                        ),
                    ]
                )
            ),

            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Password must be at least 8 characters'
                        ),
                        new OA\Property(
                            property: 'errors',
                            type: 'object',
                            properties: [
                                new OA\Property(
                                    property: 'password',
                                    type: 'array',
                                    items: new OA\Items(
                                        type: 'string'
                                    ),
                                    example: [
                                        'Password must be at least 8 characters'
                                    ]
                                ),
                            ]
                        ),
                    ]
                )
            ),
        ]
    )]
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role'     => 'user',
            'status'   => 'active',
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
        ], 200);
    }

    /**
     * Login
     */

    #[OA\Post(
        path: '/api/login',
        summary: 'User login',
        description: 'Login for user, owner, and admin accounts.',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(
                        property: 'email',
                        type: 'string',
                        format: 'email',
                        example: 'user@gmail.com'
                    ),
                    new OA\Property(
                        property: 'password',
                        type: 'string',
                        format: 'password',
                        example: 'password123'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Login successful'
            ),
            new OA\Response(
                response: 401,
                description: 'Invalid email or password'
            ),
            new OA\Response(
                response: 403,
                description: 'Account is pending or blocked'
            ),
        ]
    )]
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

        /*
        |--------------------------------------------------------------------------
        | Check Blocked Account
        |--------------------------------------------------------------------------
        */

        if ($user->status === 'blocked') {

            Auth::logout();

            return response()->json([
                'success' => false,
                'message' => 'Your account has been blocked.'
            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | Delete Old Tokens
        |--------------------------------------------------------------------------
        */

        $user->tokens()->delete();

        /*
        |--------------------------------------------------------------------------
        | Generate New Token
        |--------------------------------------------------------------------------
        */

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


    /**
     * Logout
     */
    #[OA\Post(
        path: '/api/logout',
        summary: 'Logout user',
        description: 'Logout the currently authenticated user and revoke the current Sanctum access token.',
        tags: ['Authentication'],
        security: [
            ['sanctum' => []]
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Logout successful'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            ),
        ]
    )]
    public function logout(Request $request)
    {
        $token = $request->user()->currentAccessToken();

        if ($token) {
            $token->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logout successful.'
        ], 200);
    }


    /**
     * Get Authenticated User Profile
     */
    #[OA\Get(
        path: '/api/profile',
        summary: 'Get authenticated user profile',
        description: 'Returns the profile information of the currently authenticated user.',
        tags: ['Authentication'],
        security: [
            ['sanctum' => []]
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Profile fetched successfully'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            ),
        ]
    )]
    public function profile(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'message' => 'Profile fetched successfully.',
            'data' => [
                'id'     => $user->id,
                'name'   => $user->name,
                'email'  => $user->email,
                'role'   => $user->role,
                'status' => $user->status,
            ]
        ], 200);
    }
}
