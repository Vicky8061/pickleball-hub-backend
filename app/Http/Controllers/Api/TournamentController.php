<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tournament;
use App\Models\Court;
use App\Models\TournamentParticipant;
use App\Http\Resources\TournamentResource;
use App\Http\Resources\TournamentParticipantResource;
use App\Http\Requests\StoreTournamentRequest;
use App\Http\Requests\UpdateTournamentRequest;
use Carbon\Carbon;
use OpenApi\Attributes as OA;

class TournamentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    #[OA\Get(
        path: '/api/tournaments',
        summary: 'Get all tournaments',
        description: 'Fetch all available tournaments with owner, court, and participant information.',
        tags: ['Tournaments'],
        security: [
            ['sanctum' => []]
        ],
        parameters: [
            new OA\Parameter(
                name: 'page',
                in: 'query',
                required: false,
                description: 'Page number',
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Tournaments fetched successfully'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            )
        ]
    )]

    public function index(Request $request)
    {
        $tournaments = Tournament::with([
            'owner',
            'court.owner',
            'court.images',
            'participants.user',
        ])
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Tournaments fetched successfully',
            'data' => TournamentResource::collection($tournaments),
            'pagination' => [
                'current_page' => $tournaments->currentPage(),
                'last_page' => $tournaments->lastPage(),
                'per_page' => $tournaments->perPage(),
                'total' => $tournaments->total(),
            ]
        ]);
    }
    /**
     * Store a newly created resource in storage.
     */

    #[OA\Post(
        path: '/api/owner/tournaments',
        summary: 'Create a tournament',
        description: 'Create a new tournament for a court owned by the authenticated owner.',
        tags: ['Owner - Tournaments'],
        security: [['sanctum' => []]],

        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    type: 'object',
                    required: [
                        'court_id',
                        'title',
                        'description',
                        'tournament_date',
                        'registration_last_date',
                        'start_time',
                        'end_time',
                        'entry_fee',
                        'max_participants',
                        'prize'
                    ],
                    properties: [

                        new OA\Property(
                            property: 'court_id',
                            type: 'integer',
                            example: 6,
                            description: 'ID of the court.'
                        ),

                        new OA\Property(
                            property: 'title',
                            type: 'string',
                            example: 'Surat Pickleball Championship'
                        ),

                        new OA\Property(
                            property: 'description',
                            type: 'string',
                            example: 'Open pickleball tournament for intermediate players.'
                        ),

                        new OA\Property(
                            property: 'banner',
                            type: 'string',
                            format: 'binary',
                            nullable: true,
                            description: 'Tournament banner image. JPG, JPEG, PNG or WEBP.'
                        ),

                        new OA\Property(
                            property: 'tournament_date',
                            type: 'string',
                            format: 'date',
                            example: '2026-09-15'
                        ),

                        new OA\Property(
                            property: 'registration_last_date',
                            type: 'string',
                            format: 'date',
                            example: '2026-09-10'
                        ),

                        new OA\Property(
                            property: 'start_time',
                            type: 'string',
                            example: '09:00'
                        ),

                        new OA\Property(
                            property: 'end_time',
                            type: 'string',
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
                            example: '₹20,000'
                        )
                    ]
                )
            )
        ),

        responses: [

            new OA\Response(
                response: 201,
                description: 'Tournament created successfully'
            ),

            new OA\Response(
                response: 400,
                description: 'Inactive court cannot host tournaments'
            ),

            new OA\Response(
                response: 403,
                description: 'Unauthorized'
            ),

            new OA\Response(
                response: 404,
                description: 'Court not found'
            ),

            new OA\Response(
                response: 422,
                description: 'Validation error'
            )
        ]
    )]
    public function store(StoreTournamentRequest $request)
    {
        // Only owner can create tournament
        if ($request->user()->role !== 'owner') {
            return response()->json([
                'success' => false,
                'message' => 'Only owner can create tournaments.',
            ], 403);
        }

        // Get validated data
        $data = $request->validated();

        // Find court
        $court = Court::findOrFail($data['court_id']);

        // Check court ownership
        if ($court->owner_id != $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to create a tournament for this court.',
            ], 403);
        }

        // Court must be active
        if ($court->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Inactive court cannot host tournaments.',
            ], 400);
        }

        // Store banner
        $bannerPath = null;

        if ($request->hasFile('banner')) {
            $bannerPath = $request->file('banner')->store(
                'tournament_banners',
                'public'
            );
        }

        // Create tournament
        $tournament = Tournament::create([
            'owner_id' => $request->user()->id,
            'court_id' => $data['court_id'],
            'title' => $data['title'],
            'description' => $data['description'],
            'banner' => $bannerPath,
            'tournament_date' => $data['tournament_date'],
            'registration_last_date' => $data['registration_last_date'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'entry_fee' => $data['entry_fee'],
            'max_participants' => $data['max_participants'],
            'prize' => $data['prize'],
            'status' => 'upcoming',
        ]);

        // Load relationships
        $tournament->load([
            'owner',
            'court.owner',
            'court.images',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tournament created successfully.',
            'data' => new TournamentResource($tournament),
        ], 201);
    }
    /**
     * Display the specified resource.
     */
    #[OA\Get(
        path: '/api/tournaments/{tournament}',
        summary: 'Get tournament details',
        description: 'Fetch detailed information about a specific tournament.',
        tags: ['Tournaments'],
        security: [
            ['sanctum' => []]
        ],
        parameters: [
            new OA\Parameter(
                name: 'tournament',
                in: 'path',
                required: true,
                description: 'Tournament ID',
                schema: new OA\Schema(
                    type: 'integer',
                    example: 1
                )
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Tournament fetched successfully'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            ),
            new OA\Response(
                response: 404,
                description: 'Tournament not found'
            )
        ]
    )]

    public function show(Tournament $tournament)
    {
        $tournament->load([
            'owner',
            'court.owner',
            'court.images',
            'participants.user',
        ]);
        return response()->json([
            'success' => true,
            'message' => 'Tournament fetched successfully',
            'data' => new TournamentResource($tournament)
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    #[OA\Put(
        path: '/api/owner/tournaments/{tournament}',
        summary: 'Update a tournament',
        description: 'Update a tournament owned by the authenticated owner.',
        tags: ['Owner - Tournaments'],
        security: [['sanctum' => []]],

        parameters: [
            new OA\Parameter(
                name: 'tournament',
                in: 'path',
                required: true,
                description: 'Tournament ID',
                schema: new OA\Schema(type: 'integer'),
                example: 1
            )
        ],

        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'title',
                        type: 'string',
                        example: 'Surat Pickleball Championship 2026'
                    ),
                    new OA\Property(
                        property: 'description',
                        type: 'string',
                        example: 'Updated tournament description.'
                    ),
                    new OA\Property(
                        property: 'tournament_date',
                        type: 'string',
                        format: 'date',
                        example: '2026-09-20'
                    ),
                    new OA\Property(
                        property: 'registration_last_date',
                        type: 'string',
                        format: 'date',
                        example: '2026-09-15'
                    ),
                    new OA\Property(
                        property: 'start_time',
                        type: 'string',
                        example: '09:00'
                    ),
                    new OA\Property(
                        property: 'end_time',
                        type: 'string',
                        example: '18:00'
                    ),
                    new OA\Property(
                        property: 'entry_fee',
                        type: 'number',
                        format: 'float',
                        example: 600
                    ),
                    new OA\Property(
                        property: 'max_participants',
                        type: 'integer',
                        example: 40
                    ),
                    new OA\Property(
                        property: 'prize',
                        type: 'string',
                        example: '₹25,000'
                    ),
                    new OA\Property(
                        property: 'status',
                        type: 'string',
                        enum: ['upcoming', 'ongoing', 'completed', 'cancelled'],
                        example: 'upcoming'
                    )
                ]
            )
        ),

        responses: [
            new OA\Response(
                response: 200,
                description: 'Tournament updated successfully'
            ),
            new OA\Response(
                response: 400,
                description: 'Completed tournament cannot be updated'
            ),
            new OA\Response(
                response: 403,
                description: 'Unauthorized'
            ),
            new OA\Response(
                response: 404,
                description: 'Tournament not found'
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error'
            )
        ]
    )]
    public function update(UpdateTournamentRequest $request, Tournament $tournament)
    {

        if ($request->user()->role != 'owner') {
            return response()->json([
                'success' => false,
                'message' => 'Only owner can update tournaments'
            ], 403);
        }

        if ($tournament->owner_id != $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to update this tournament',
            ], 403);
        }

        if ($tournament->status == 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Completed Tournament can not be updated'
            ]);
        }
        $tournament->update($request->validated());

        $tournament->load([
            'owner',
            'court.owner',
            'court.images',
            'participants.user',
        ]);
        return response()->json([
            'success' => true,
            'message' => 'Tournament updated successfuly',
            'data' => new TournamentResource($tournament),
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    #[OA\Delete(
        path: '/api/owner/tournaments/{tournament}',
        summary: 'Cancel a tournament',
        description: 'Cancel a tournament owned by the authenticated owner.',
        tags: ['Owner - Tournaments'],
        security: [['sanctum' => []]],

        parameters: [
            new OA\Parameter(
                name: 'tournament',
                in: 'path',
                required: true,
                description: 'Tournament ID',
                schema: new OA\Schema(type: 'integer'),
                example: 1
            )
        ],

        responses: [
            new OA\Response(
                response: 200,
                description: 'Tournament cancelled successfully'
            ),
            new OA\Response(
                response: 400,
                description: 'Tournament is already cancelled'
            ),
            new OA\Response(
                response: 403,
                description: 'Unauthorized'
            ),
            new OA\Response(
                response: 404,
                description: 'Tournament not found'
            )
        ]
    )]
    public function destroy(Tournament $tournament, Request $request)
    {
        if ($request->user()->role != 'owner') {
            return response()->json([
                'success' => false,
                'message' => 'Only owners can cancel tournaments.'
            ], 403);
        }
        if ($tournament->owner_id != $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to delete this tournament',
            ], 403);
        }
        if ($tournament->status == 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Tournament is already cancelled.'
            ], 400);
        }
        $tournament->update(['status' => 'cancelled']);
        return response()->json([
            'success' => true,
            'message' => 'Tournament cancelled successfuly',
        ], 200);
    }

    #[OA\Get(
        path: '/api/owner/tournaments/my',
        summary: 'Get my tournaments',
        description: 'Fetch all tournaments created by the authenticated owner with pagination.',
        tags: ['Owner - Tournaments'],
        security: [['sanctum' => []]],

        parameters: [
            new OA\Parameter(
                name: 'page',
                in: 'query',
                required: false,
                description: 'Page number',
                schema: new OA\Schema(type: 'integer', minimum: 1),
                example: 1
            )
        ],

        responses: [
            new OA\Response(
                response: 200,
                description: 'Owner tournaments fetched successfully'
            ),
            new OA\Response(
                response: 403,
                description: 'Unauthorized'
            )
        ]
    )]

    public function myTournaments(Request $request)
    {
        if ($request->user()->role != 'owner') {
            return response()->json([
                'success' => false,
                'message' => 'Only owners can access tournaments.'
            ], 403);
        }

        $perPage = (int) ($request->query('per_page', 10));

        $tournaments = Tournament::with([
            'owner',
            'court.owner',
            'court.images',
            'participants.user',
        ])->where('owner_id', $request->user()->id)->latest()->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'My tournaments fetched successfuly',
            'data' => TournamentResource::collection($tournaments),
            'pagination' => [
                'current_page' => $tournaments->currentPage(),
                'last_page' => $tournaments->lastPage(),
                'per_page' => $tournaments->perPage(),
                'total' => $tournaments->total(),
            ]
        ], 200);
    }


    #[OA\Post(
        path: '/api/tournaments/{tournament}/join',
        summary: 'Join a tournament',
        description: 'Allows a normal user to join an upcoming tournament.',
        tags: ['Tournaments'],
        security: [
            ['sanctum' => []]
        ],
        parameters: [
            new OA\Parameter(
                name: 'tournament',
                in: 'path',
                required: true,
                description: 'Tournament ID',
                schema: new OA\Schema(
                    type: 'integer',
                    example: 1
                )
            )
        ],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Successfully joined tournament'
            ),
            new OA\Response(
                response: 400,
                description: 'Tournament unavailable, registration closed, already joined, or tournament full'
            ),
            new OA\Response(
                response: 403,
                description: 'Only normal users can join tournaments'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            )
        ]
    )]
    public function joinTournament(
        Tournament $tournament,
        Request $request
    ) {
        // Only normal users can join tournaments
        if ($request->user()->role !== 'user') {
            return response()->json([
                'success' => false,
                'message' => 'Only users can join tournaments.',
            ], 403);
        }

        // Tournament owner cannot join their own tournament
        if ($tournament->owner_id === $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Tournament owner cannot join their own tournament.',
            ], 403);
        }

        // Tournament must be upcoming
        if ($tournament->status !== 'upcoming') {
            return response()->json([
                'success' => false,
                'message' => 'Tournament is not available for registration.',
            ], 400);
        }

        // Registration deadline check
        if (
            Carbon::today()->greaterThan(
                Carbon::parse($tournament->registration_last_date)
            )
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Registration for this tournament is closed.',
            ], 400);
        }

        // Tournament date must not have passed
        if (
            Carbon::today()->greaterThan(
                Carbon::parse($tournament->tournament_date)
            )
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Tournament date has already passed.',
            ], 400);
        }

        // Check if user already joined
        $exists = TournamentParticipant::where('tournament_id', $tournament->id)
            ->where('user_id', $request->user()->id)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'You have already joined this tournament.',
            ], 400);
        }

        // Check tournament capacity
        $participantCount = TournamentParticipant::where(
            'tournament_id',
            $tournament->id
        )->count();

        if ($participantCount >= $tournament->max_participants) {
            return response()->json([
                'success' => false,
                'message' => 'Tournament is full.',
            ], 400);
        }

        // Create participant
        $participant = TournamentParticipant::create([
            'tournament_id' => $tournament->id,
            'user_id' => $request->user()->id,
            'payment_status' => 'pending',
        ]);

        $participant->load([
            'user',
            'tournament',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'You have successfully joined the tournament.',
            'data' => new TournamentParticipantResource($participant),
        ], 201);
    }
    #[OA\Delete(
        path: '/api/tournaments/{tournament}/leave',
        summary: 'Leave a tournament',
        description: 'Allows a user to leave a tournament they have joined.',
        tags: ['Tournaments'],
        security: [
            ['sanctum' => []]
        ],
        parameters: [
            new OA\Parameter(
                name: 'tournament',
                in: 'path',
                required: true,
                description: 'Tournament ID',
                schema: new OA\Schema(
                    type: 'integer',
                    example: 1
                )
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successfully left tournament'
            ),
            new OA\Response(
                response: 400,
                description: 'Tournament cannot be left'
            ),
            new OA\Response(
                response: 403,
                description: 'Only normal users can leave tournaments'
            ),
            new OA\Response(
                response: 404,
                description: 'User is not a participant'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            )
        ]
    )]

    public function leaveTournament(
        Tournament $tournament,
        Request $request
    ) {
        // Only normal users can leave a tournament
        if ($request->user()->role !== 'user') {
            return response()->json([
                'success' => false,
                'message' => 'Only users can leave tournaments.',
            ], 403);
        }

        // Find user's participation
        $participant = TournamentParticipant::where(
            'tournament_id',
            $tournament->id
        )
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$participant) {
            return response()->json([
                'success' => false,
                'message' => 'You are not a participant of this tournament.',
            ], 404);
        }

        // Do not allow leaving completed tournaments
        if ($tournament->status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'You cannot leave a completed tournament.',
            ], 400);
        }

        // Do not allow leaving cancelled tournaments
        if ($tournament->status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Tournament has already been cancelled.',
            ], 400);
        }

        // Delete participation
        $participant->delete();

        return response()->json([
            'success' => true,
            'message' => 'You have successfully left the tournament.',
        ], 200);
    }
    #[OA\Get(
        path: '/api/user/my-tournaments',
        summary: 'Get my joined tournaments',
        description: 'Fetch all tournaments joined by the currently authenticated user.',
        tags: ['Tournaments'],
        security: [
            ['sanctum' => []]
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Joined tournaments fetched successfully'
            ),
            new OA\Response(
                response: 403,
                description: 'Only normal users can access joined tournaments'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            )
        ]
    )]

    public function myJoinedTournaments(Request $request)
    {
        // Only normal users can access joined tournaments
        if ($request->user()->role !== 'user') {
            return response()->json([
                'success' => false,
                'message' => 'Only users can access joined tournaments.',
            ], 403);
        }

        $participants = TournamentParticipant::with([
            'user',
            'tournament.owner',
            'tournament.court.owner',
            'tournament.court.images',
        ])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Joined tournaments fetched successfully.',
            'data' => TournamentParticipantResource::collection($participants),
        ], 200);
    }
}
