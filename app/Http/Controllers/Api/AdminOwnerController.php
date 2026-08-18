<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOwnerRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class AdminOwnerController extends Controller
{
    // --------------------------------------------------
    // GET ALL OWNERS
    // --------------------------------------------------

    #[OA\Get(
        path: '/api/admin/owners',
        summary: 'Get all owners',
        description: 'Fetch all owner users with optional search and sorting.',
        tags: ['Admin - Owners'],
        security: [['sanctum' => []]],

        parameters: [

            new OA\Parameter(
                name: 'search',
                in: 'query',
                required: false,
                description: 'Search owner by name or email.',
                schema: new OA\Schema(
                    type: 'string'
                ),
                example: 'vicky'
            ),

            new OA\Parameter(
                name: 'sort',
                in: 'query',
                required: false,
                description: 'Sort owners by latest or oldest.',
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
                description: 'Owners fetched successfully.'
            ),

            new OA\Response(
                response: 401,
                description: 'Unauthenticated.'
            ),

            new OA\Response(
                response: 403,
                description: 'Only admins can access owners.'
            )
        ]
    )]
    public function index(Request $request)
    {
        $query = User::where('role', 'owner');

        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->where(
                    'name',
                    'like',
                    "%{$search}%"
                )->orWhere(
                    'email',
                    'like',
                    "%{$search}%"
                );
            });
        }

        switch ($request->sort) {

            case 'oldest':
                $query->oldest();
                break;

            case 'latest':
            default:
                $query->latest();
                break;
        }

        $owners = $query->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Owner fetched successfully.',
            'data' => UserResource::collection($owners),

            'pagination' => [
                'current_page' => $owners->currentPage(),
                'last_page' => $owners->lastPage(),
                'per_page' => $owners->perPage(),
                'total' => $owners->total(),
            ],
        ], 200);
    }


    // --------------------------------------------------
    // GET SINGLE OWNER
    // --------------------------------------------------

    #[OA\Get(
        path: '/api/admin/owners/{owner}',
        summary: 'Get owner details',
        description: 'Fetch details of a specific owner.',
        tags: ['Admin - Owners'],
        security: [['sanctum' => []]],

        parameters: [

            new OA\Parameter(
                name: 'owner',
                in: 'path',
                required: true,
                description: 'Owner ID.',
                schema: new OA\Schema(
                    type: 'integer'
                ),
                example: 5
            )
        ],

        responses: [

            new OA\Response(
                response: 200,
                description: 'Owner fetched successfully.'
            ),

            new OA\Response(
                response: 401,
                description: 'Unauthenticated.'
            ),

            new OA\Response(
                response: 403,
                description: 'Only admins can access owners.'
            ),

            new OA\Response(
                response: 404,
                description: 'Owner not found.'
            )
        ]
    )]
    public function show(User $owner)
    {
        if ($owner->role !== 'owner') {

            return response()->json([
                'success' => false,
                'message' => 'Owner not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Owner fetched successfully',
            'data' => new UserResource($owner),
        ], 200);
    }


    // --------------------------------------------------
    // UPDATE OWNER
    // --------------------------------------------------

    #[OA\Put(
        path: '/api/admin/owners/{owner}',
        summary: 'Update owner',
        description: 'Update details of a specific owner.',
        tags: ['Admin - Owners'],
        security: [['sanctum' => []]],

        parameters: [

            new OA\Parameter(
                name: 'owner',
                in: 'path',
                required: true,
                description: 'Owner ID.',
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
                description: 'Owner updated successfully.'
            ),

            new OA\Response(
                response: 401,
                description: 'Unauthenticated.'
            ),

            new OA\Response(
                response: 403,
                description: 'Only admins can update owners.'
            ),

            new OA\Response(
                response: 404,
                description: 'Owner not found.'
            ),

            new OA\Response(
                response: 422,
                description: 'Validation error.'
            )
        ]
    )]
    public function update(
        UpdateOwnerRequest $request,
        User $owner
    ) {
        if ($owner->role !== 'owner') {

            return response()->json([
                'success' => false,
                'message' => 'Owner not found',
            ], 404);
        }

        $owner->update(
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Owner updated successfully',
            'data' => new UserResource($owner),
        ]);
    }


    // --------------------------------------------------
    // BLOCK OWNER
    // --------------------------------------------------

    #[OA\Patch(
        path: '/api/admin/owners/{owner}/block',
        summary: 'Block owner',
        description: 'Block a specific owner account.',
        tags: ['Admin - Owners'],
        security: [['sanctum' => []]],

        parameters: [

            new OA\Parameter(
                name: 'owner',
                in: 'path',
                required: true,
                description: 'Owner ID.',
                schema: new OA\Schema(
                    type: 'integer'
                ),
                example: 5
            )
        ],

        responses: [

            new OA\Response(
                response: 200,
                description: 'Owner blocked successfully.'
            ),

            new OA\Response(
                response: 400,
                description: 'Owner is already blocked.'
            ),

            new OA\Response(
                response: 401,
                description: 'Unauthenticated.'
            ),

            new OA\Response(
                response: 403,
                description: 'Only admins can block owners.'
            ),

            new OA\Response(
                response: 404,
                description: 'Owner not found.'
            )
        ]
    )]
    public function block(User $owner)
    {
        if ($owner->role !== 'owner') {

            return response()->json([
                'success' => false,
                'message' => 'Owner not found',
            ], 404);
        }

        if ($owner->status === 'blocked') {

            return response()->json([
                'success' => false,
                'message' => 'Owner is already blocked',
            ], 400);
        }

        $owner->update([
            'status' => 'blocked',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Owner blocked successfully',
            'data' => new UserResource($owner),
        ]);
    }
}
