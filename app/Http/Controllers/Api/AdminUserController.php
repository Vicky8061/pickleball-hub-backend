<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use App\Models\User;
use OpenApi\Attributes as OA;

class AdminUserController extends Controller
{
    // --------------------------------------------------
    // GET ALL USERS
    // --------------------------------------------------

    #[OA\Get(
        path: '/api/admin/users',
        summary: 'Get all users',
        description: 'Fetch all normal users with optional search and sorting.',
        tags: ['Admin - Users'],
        security: [['sanctum' => []]],

        parameters: [

            new OA\Parameter(
                name: 'search',
                in: 'query',
                required: false,
                description: 'Search user by name or email.',
                schema: new OA\Schema(
                    type: 'string'
                ),
                example: 'vicky'
            ),

            new OA\Parameter(
                name: 'sort',
                in: 'query',
                required: false,
                description: 'Sort users by latest or oldest.',
                schema: new OA\Schema(
                    type: 'string',
                    enum: ['latest', 'oldest'],
                    default: 'latest'
                ),
                example: 'latest'
            ),

            new OA\Parameter(
                name: 'page',
                in: 'query',
                required: false,
                description: 'Page number.',
                schema: new OA\Schema(
                    type: 'integer',
                    default: 1
                ),
                example: 1
            )
        ],

        responses: [

            new OA\Response(
                response: 200,
                description: 'Users fetched successfully.'
            ),

            new OA\Response(
                response: 401,
                description: 'Unauthenticated.'
            ),

            new OA\Response(
                response: 403,
                description: 'Only admins can access users.'
            )
        ]
    )]
    public function index(Request $request)
    {
        // Only admin
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Only admins can access users.',
            ], 403);
        }

        $query = User::where('role', 'user');

        // Search by name/email
        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->where(
                    'name',
                    'like',
                    '%' . $search . '%'
                )->orWhere(
                    'email',
                    'like',
                    '%' . $search . '%'
                );

            });
        }

        // Sort
        switch ($request->sort) {

            case 'oldest':
                $query->oldest();
                break;

            case 'latest':
            default:
                $query->latest();
                break;
        }

        $users = $query->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Users fetched successfully.',
            'data' => UserResource::collection($users),

            'pagination' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ], 200);
    }


    // --------------------------------------------------
    // GET SINGLE USER
    // --------------------------------------------------

    #[OA\Get(
        path: '/api/admin/users/{user}',
        summary: 'Get user details',
        description: 'Fetch details of a normal user.',
        tags: ['Admin - Users'],
        security: [['sanctum' => []]],

        parameters: [

            new OA\Parameter(
                name: 'user',
                in: 'path',
                required: true,
                description: 'User ID.',
                schema: new OA\Schema(
                    type: 'integer'
                ),
                example: 5
            )
        ],

        responses: [

            new OA\Response(
                response: 200,
                description: 'User fetched successfully.'
            ),

            new OA\Response(
                response: 401,
                description: 'Unauthenticated.'
            ),

            new OA\Response(
                response: 403,
                description: 'Only admins can access users.'
            ),

            new OA\Response(
                response: 404,
                description: 'User not found.'
            )
        ]
    )]
    public function show(User $user)
    {
        // Only admin
        if (request()->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Only admins can access users.',
            ], 403);
        }

        // Only normal users
        if ($user->role !== 'user') {

            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'User fetched successfully.',
            'data' => new UserResource($user),
        ], 200);
    }


    // --------------------------------------------------
    // UPDATE USER
    // --------------------------------------------------

    #[OA\Put(
        path: '/api/admin/users/{user}',
        summary: 'Update user',
        description: 'Update details of a normal user.',
        tags: ['Admin - Users'],
        security: [['sanctum' => []]],

        parameters: [

            new OA\Parameter(
                name: 'user',
                in: 'path',
                required: true,
                description: 'User ID.',
                schema: new OA\Schema(
                    type: 'integer'
                ),
                example: 5
            )
        ],

        requestBody: new OA\RequestBody(
            required: true,

            content: new OA\JsonContent(
                required: [],

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
                        property: 'status',
                        type: 'string',
                        enum: ['active', 'blocked'],
                        example: 'active'
                    )
                ]
            )
        ),

        responses: [

            new OA\Response(
                response: 200,
                description: 'User updated successfully.'
            ),

            new OA\Response(
                response: 401,
                description: 'Unauthenticated.'
            ),

            new OA\Response(
                response: 403,
                description: 'Only admins can update users.'
            ),

            new OA\Response(
                response: 404,
                description: 'User not found.'
            ),

            new OA\Response(
                response: 422,
                description: 'Validation error.'
            )
        ]
    )]
    public function update(
        UpdateUserRequest $request,
        User $user
    ) {
        // Only admin
        if ($request->user()->role !== 'admin') {

            return response()->json([
                'success' => false,
                'message' => 'Only admins can update users.',
            ], 403);
        }

        // Only normal users
        if ($user->role !== 'user') {

            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        $user->update(
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully.',
            'data' => new UserResource($user),
        ], 200);
    }


    // --------------------------------------------------
    // BLOCK USER
    // --------------------------------------------------

    #[OA\Patch(
        path: '/api/admin/users/{user}/block',
        summary: 'Block user',
        description: 'Block a normal user account.',
        tags: ['Admin - Users'],
        security: [['sanctum' => []]],

        parameters: [

            new OA\Parameter(
                name: 'user',
                in: 'path',
                required: true,
                description: 'User ID.',
                schema: new OA\Schema(
                    type: 'integer'
                ),
                example: 5
            )
        ],

        responses: [

            new OA\Response(
                response: 200,
                description: 'User blocked successfully.'
            ),

            new OA\Response(
                response: 400,
                description: 'User is already blocked.'
            ),

            new OA\Response(
                response: 401,
                description: 'Unauthenticated.'
            ),

            new OA\Response(
                response: 403,
                description: 'Only admins can block users.'
            ),

            new OA\Response(
                response: 404,
                description: 'User not found.'
            )
        ]
    )]
    public function block(User $user)
    {
        // Only admin
        if (request()->user()->role !== 'admin') {

            return response()->json([
                'success' => false,
                'message' => 'Only admins can block users.',
            ], 403);
        }

        // Only normal users
        if ($user->role !== 'user') {

            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        // Already blocked
        if ($user->status === 'blocked') {

            return response()->json([
                'success' => false,
                'message' => 'User is already blocked.',
            ], 400);
        }

        $user->update([
            'status' => 'blocked',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User blocked successfully.',
            'data' => new UserResource($user),
        ], 200);
    }
}