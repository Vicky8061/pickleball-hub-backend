<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateCourtRequest;
use App\Http\Resources\CourtResource;
use App\Models\Court;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class AdminCourtController extends Controller
{
    // --------------------------------------------------
    // GET ALL COURTS
    // --------------------------------------------------

    #[OA\Get(
        path: '/api/admin/courts',
        summary: 'Get all courts',
        description: 'Fetch all courts with owner and images. Supports search, status filtering, sorting and pagination.',
        tags: ['Admin - Courts'],
        security: [['sanctum' => []]],

        parameters: [

            new OA\Parameter(
                name: 'search',
                in: 'query',
                required: false,
                description: 'Search court by name or address.',
                schema: new OA\Schema(
                    type: 'string'
                ),
                example: 'Smash Arena'
            ),

            new OA\Parameter(
                name: 'status',
                in: 'query',
                required: false,
                description: 'Filter courts by status.',
                schema: new OA\Schema(
                    type: 'string',
                    enum: ['active', 'inactive']
                ),
                example: 'active'
            ),

            new OA\Parameter(
                name: 'sort',
                in: 'query',
                required: false,
                description: 'Sort courts by latest or oldest.',
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
                description: 'Courts fetched successfully.'
            ),

            new OA\Response(
                response: 401,
                description: 'Unauthenticated.'
            ),

            new OA\Response(
                response: 403,
                description: 'Only admins can access courts.'
            ),

            new OA\Response(
                response: 422,
                description: 'Invalid status value.'
            )
        ]
    )]
    public function index(Request $request)
    {
        $query = Court::with([
            'owner',
            'images',
        ]);

        // Search by name or address
        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->where(
                    'name',
                    'like',
                    "%{$search}%"
                )->orWhere(
                    'address',
                    'like',
                    "%{$search}%"
                );
            });
        }

        // Filter by status
        if ($request->filled('status')) {

            $request->validate([
                'status' => 'in:active,inactive',
            ]);

            $query->where(
                'status',
                $request->status
            );
        }

        // Sorting
        switch ($request->sort) {

            case 'oldest':
                $query->oldest();
                break;

            case 'latest':
            default:
                $query->latest();
                break;
        }

        // Pagination
        $courts = $query->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Courts fetched successfully.',

            'data' => CourtResource::collection($courts),

            'pagination' => [
                'current_page' => $courts->currentPage(),
                'last_page' => $courts->lastPage(),
                'per_page' => $courts->perPage(),
                'total' => $courts->total(),
            ],
        ], 200);
    }


    // --------------------------------------------------
    // GET SINGLE COURT
    // --------------------------------------------------

    #[OA\Get(
        path: '/api/admin/courts/{court}',
        summary: 'Get court details',
        description: 'Fetch complete details of a specific court including owner, images, time slots and reviews.',
        tags: ['Admin - Courts'],
        security: [['sanctum' => []]],

        parameters: [

            new OA\Parameter(
                name: 'court',
                in: 'path',
                required: true,
                description: 'Court ID.',
                schema: new OA\Schema(
                    type: 'integer'
                ),
                example: 5
            )
        ],

        responses: [

            new OA\Response(
                response: 200,
                description: 'Court fetched successfully.'
            ),

            new OA\Response(
                response: 401,
                description: 'Unauthenticated.'
            ),

            new OA\Response(
                response: 403,
                description: 'Only admins can access courts.'
            ),

            new OA\Response(
                response: 404,
                description: 'Court not found.'
            )
        ]
    )]
    public function show(Court $court)
    {
        $court->load([
            'owner',
            'images',
            'timeSlots',
            'reviews',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Court fetched successfully.',
            'data' => new CourtResource($court),
        ], 200);
    }


    // --------------------------------------------------
    // UPDATE COURT
    // --------------------------------------------------

    #[OA\Put(
        path: '/api/admin/courts/{court}',
        summary: 'Update court',
        description: 'Update details of a specific court.',
        tags: ['Admin - Courts'],
        security: [['sanctum' => []]],

        parameters: [

            new OA\Parameter(
                name: 'court',
                in: 'path',
                required: true,
                description: 'Court ID.',
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
                        example: 'Smash Arena'
                    ),

                    new OA\Property(
                        property: 'address',
                        type: 'string',
                        example: 'Vesu, Surat'
                    ),

                    new OA\Property(
                        property: 'latitude',
                        type: 'number',
                        format: 'double',
                        example: 21.1702
                    ),

                    new OA\Property(
                        property: 'longitude',
                        type: 'number',
                        format: 'double',
                        example: 72.8311
                    ),

                    new OA\Property(
                        property: 'price_per_hour',
                        type: 'number',
                        format: 'float',
                        example: 500
                    ),

                    new OA\Property(
                        property: 'description',
                        type: 'string',
                        example: 'Premium indoor pickleball court.'
                    ),

                    new OA\Property(
                        property: 'court_type',
                        type: 'string',
                        example: 'indoor'
                    ),

                    new OA\Property(
                        property: 'opening_time',
                        type: 'string',
                        format: 'time',
                        example: '06:00'
                    ),

                    new OA\Property(
                        property: 'closing_time',
                        type: 'string',
                        format: 'time',
                        example: '23:00'
                    )
                ]
            )
        ),

        responses: [

            new OA\Response(
                response: 200,
                description: 'Court updated successfully.'
            ),

            new OA\Response(
                response: 401,
                description: 'Unauthenticated.'
            ),

            new OA\Response(
                response: 403,
                description: 'Only admins can update courts.'
            ),

            new OA\Response(
                response: 404,
                description: 'Court not found.'
            ),

            new OA\Response(
                response: 422,
                description: 'Validation error.'
            )
        ]
    )]
    public function update(
        UpdateCourtRequest $request,
        Court $court
    ) {
        $court->update(
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Court updated successfully.',

            'data' => new CourtResource(
                $court->load([
                    'owner',
                    'images',
                ])
            ),
        ], 200);
    }


    // --------------------------------------------------
    // UPDATE COURT STATUS
    // --------------------------------------------------

    #[OA\Patch(
        path: '/api/admin/courts/{court}/status',
        summary: 'Update court status',
        description: 'Activate or deactivate a court.',
        tags: ['Admin - Courts'],
        security: [['sanctum' => []]],

        parameters: [

            new OA\Parameter(
                name: 'court',
                in: 'path',
                required: true,
                description: 'Court ID.',
                schema: new OA\Schema(
                    type: 'integer'
                ),
                example: 5
            )
        ],

        requestBody: new OA\RequestBody(
            required: true,

            content: new OA\JsonContent(
                required: ['status'],

                properties: [

                    new OA\Property(
                        property: 'status',
                        type: 'string',
                        enum: ['active', 'inactive'],
                        example: 'active'
                    )
                ]
            )
        ),

        responses: [

            new OA\Response(
                response: 200,
                description: 'Court status updated successfully.'
            ),

            new OA\Response(
                response: 401,
                description: 'Unauthenticated.'
            ),

            new OA\Response(
                response: 403,
                description: 'Only admins can update court status.'
            ),

            new OA\Response(
                response: 404,
                description: 'Court not found.'
            ),

            new OA\Response(
                response: 422,
                description: 'Validation error.'
            )
        ]
    )]
    public function updateStatus(
        Request $request,
        Court $court
    ) {
        $request->validate([
            'status' => 'required|in:active,inactive',
        ]);

        $court->update([
            'status' => $request->status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Court status updated successfully.',

            'data' => new CourtResource(
                $court->load([
                    'owner',
                    'images',
                ])
            ),
        ], 200);
    }


    // --------------------------------------------------
    // DEACTIVATE COURT
    // --------------------------------------------------

    #[OA\Delete(
        path: '/api/admin/courts/{court}',
        summary: 'Deactivate court',
        description: 'Deactivate a court. This does not permanently delete the court.',
        tags: ['Admin - Courts'],
        security: [['sanctum' => []]],

        parameters: [

            new OA\Parameter(
                name: 'court',
                in: 'path',
                required: true,
                description: 'Court ID.',
                schema: new OA\Schema(
                    type: 'integer'
                ),
                example: 5
            )
        ],

        responses: [

            new OA\Response(
                response: 200,
                description: 'Court deactivated successfully.'
            ),

            new OA\Response(
                response: 400,
                description: 'Court is already inactive.'
            ),

            new OA\Response(
                response: 401,
                description: 'Unauthenticated.'
            ),

            new OA\Response(
                response: 403,
                description: 'Only admins can deactivate courts.'
            ),

            new OA\Response(
                response: 404,
                description: 'Court not found.'
            )
        ]
    )]
    public function destroy(Court $court)
    {
        if ($court->status === 'inactive') {

            return response()->json([
                'success' => false,
                'message' => 'Court is already inactive.',
            ], 400);
        }

        $court->update([
            'status' => 'inactive',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Court deactivated successfully.',

            'data' => new CourtResource(
                $court->load([
                    'owner',
                    'images',
                ])
            ),
        ], 200);
    }
}
