<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateTournamentRequest;
use App\Http\Resources\TournamentResource;
use App\Models\Tournament;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class AdminTournamentController extends Controller
{
    // --------------------------------------------------
    // GET ALL TOURNAMENTS
    // --------------------------------------------------

    #[OA\Get(
        path: '/api/admin/tournaments',
        summary: 'Get all tournaments',
        description: 'Fetch all tournaments with owner, court and court image details. Supports search, status filtering, sorting and pagination.',
        tags: ['Admin - Tournaments'],
        security: [['sanctum' => []]],

        parameters: [

            new OA\Parameter(
                name: 'search',
                in: 'query',
                required: false,
                description: 'Search tournament by title or court name.',
                schema: new OA\Schema(
                    type: 'string'
                ),
                example: 'Summer Cup'
            ),

            new OA\Parameter(
                name: 'status',
                in: 'query',
                required: false,
                description: 'Filter tournaments by status.',
                schema: new OA\Schema(
                    type: 'string',
                    enum: [
                        'upcoming',
                        'ongoing',
                        'completed',
                        'cancelled'
                    ]
                ),
                example: 'upcoming'
            ),

            new OA\Parameter(
                name: 'sort',
                in: 'query',
                required: false,
                description: 'Sort tournaments by latest or oldest.',
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
                description: 'Tournaments fetched successfully.'
            ),

            new OA\Response(
                response: 401,
                description: 'Unauthenticated.'
            ),

            new OA\Response(
                response: 403,
                description: 'Only admins can access tournaments.'
            ),

            new OA\Response(
                response: 422,
                description: 'Invalid filter value.'
            )
        ]
    )]
    public function index(Request $request)
    {
        $query = Tournament::with([
            'owner',
            'court',
            'court.images',
        ]);

        // Search by tournament title or court name
        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->where(
                    'title',
                    'like',
                    "%{$search}%"
                )

                    ->orWhereHas('court', function ($court) use ($search) {

                        $court->where(
                            'name',
                            'like',
                            "%{$search}%"
                        );
                    });
            });
        }

        // Filter by status
        if ($request->filled('status')) {

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
        $tournaments = $query->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Tournaments fetched successfully.',

            'data' => TournamentResource::collection($tournaments),

            'pagination' => [
                'current_page' => $tournaments->currentPage(),
                'last_page' => $tournaments->lastPage(),
                'per_page' => $tournaments->perPage(),
                'total' => $tournaments->total(),
            ],
        ], 200);
    }


    // --------------------------------------------------
    // GET SINGLE TOURNAMENT
    // --------------------------------------------------

    #[OA\Get(
        path: '/api/admin/tournaments/{tournament}',
        summary: 'Get tournament details',
        description: 'Fetch complete details of a specific tournament including owner, court, court images and participants.',
        tags: ['Admin - Tournaments'],
        security: [['sanctum' => []]],

        parameters: [

            new OA\Parameter(
                name: 'tournament',
                in: 'path',
                required: true,
                description: 'Tournament ID.',
                schema: new OA\Schema(
                    type: 'integer'
                ),
                example: 5
            )
        ],

        responses: [

            new OA\Response(
                response: 200,
                description: 'Tournament fetched successfully.'
            ),

            new OA\Response(
                response: 401,
                description: 'Unauthenticated.'
            ),

            new OA\Response(
                response: 403,
                description: 'Only admins can access tournaments.'
            ),

            new OA\Response(
                response: 404,
                description: 'Tournament not found.'
            )
        ]
    )]
    public function show(Tournament $tournament)
    {
        $tournament->load([
            'owner',
            'court',
            'court.images',
            'participants.user',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tournament fetched successfully.',
            'data' => new TournamentResource($tournament),
        ], 200);
    }


    // --------------------------------------------------
    // UPDATE TOURNAMENT
    // --------------------------------------------------

    #[OA\Put(
        path: '/api/admin/tournaments/{tournament}',
        summary: 'Update tournament',
        description: 'Update details of a specific tournament.',
        tags: ['Admin - Tournaments'],
        security: [['sanctum' => []]],

        parameters: [

            new OA\Parameter(
                name: 'tournament',
                in: 'path',
                required: true,
                description: 'Tournament ID.',
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
                        property: 'title',
                        type: 'string',
                        example: 'Summer Pickleball Cup'
                    ),

                    new OA\Property(
                        property: 'description',
                        type: 'string',
                        example: 'Annual pickleball tournament.'
                    ),

                    new OA\Property(
                        property: 'tournament_date',
                        type: 'string',
                        format: 'date',
                        example: '2026-09-15'
                    ),

                    new OA\Property(
                        property: 'start_time',
                        type: 'string',
                        format: 'time',
                        example: '09:00'
                    ),

                    new OA\Property(
                        property: 'end_time',
                        type: 'string',
                        format: 'time',
                        example: '18:00'
                    ),

                    new OA\Property(
                        property: 'entry_fee',
                        type: 'number',
                        format: 'float',
                        example: 500
                    ),

                    new OA\Property(
                        property: 'max_participants',
                        type: 'integer',
                        example: 32
                    ),

                    new OA\Property(
                        property: 'prize',
                        type: 'string',
                        example: '₹25,000'
                    ),

                    new OA\Property(
                        property: 'status',
                        type: 'string',
                        enum: [
                            'upcoming',
                            'ongoing',
                            'completed',
                            'cancelled'
                        ],
                        example: 'upcoming'
                    )
                ]
            )
        ),

        responses: [

            new OA\Response(
                response: 200,
                description: 'Tournament updated successfully.'
            ),

            new OA\Response(
                response: 401,
                description: 'Unauthenticated.'
            ),

            new OA\Response(
                response: 403,
                description: 'Only admins can update tournaments.'
            ),

            new OA\Response(
                response: 404,
                description: 'Tournament not found.'
            ),

            new OA\Response(
                response: 422,
                description: 'Validation error.'
            )
        ]
    )]
    public function update(
        UpdateTournamentRequest $request,
        Tournament $tournament
    ) {
        $tournament->update(
            $request->validated()
        );

        $tournament->load([
            'owner',
            'court',
            'court.images',
            'participants.user',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tournament updated successfully.',
            'data' => new TournamentResource($tournament),
        ], 200);
    }


    // --------------------------------------------------
    // CANCEL TOURNAMENT
    // --------------------------------------------------

    #[OA\Delete(
        path: '/api/admin/tournaments/{tournament}',
        summary: 'Cancel tournament',
        description: 'Cancel a tournament. The tournament is not permanently deleted; its status is changed to cancelled.',
        tags: ['Admin - Tournaments'],
        security: [['sanctum' => []]],

        parameters: [

            new OA\Parameter(
                name: 'tournament',
                in: 'path',
                required: true,
                description: 'Tournament ID.',
                schema: new OA\Schema(
                    type: 'integer'
                ),
                example: 5
            )
        ],

        responses: [

            new OA\Response(
                response: 200,
                description: 'Tournament cancelled successfully.'
            ),

            new OA\Response(
                response: 400,
                description: 'Tournament is already cancelled.'
            ),

            new OA\Response(
                response: 401,
                description: 'Unauthenticated.'
            ),

            new OA\Response(
                response: 403,
                description: 'Only admins can cancel tournaments.'
            ),

            new OA\Response(
                response: 404,
                description: 'Tournament not found.'
            )
        ]
    )]
    public function destroy(Tournament $tournament)
    {
        if ($tournament->status === 'cancelled') {

            return response()->json([
                'success' => false,
                'message' => 'Tournament is already cancelled.',
            ], 400);
        }

        $tournament->update([
            'status' => 'cancelled',
        ]);

        $tournament->load([
            'owner',
            'court',
            'court.images',
            'participants.user',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tournament cancelled successfully.',
            'data' => new TournamentResource($tournament),
        ], 200);
    }
}
