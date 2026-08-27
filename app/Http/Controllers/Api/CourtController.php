<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCourtRequest;
use App\Http\Requests\UpdateCourtRequest;
use App\Http\Resources\CourtResource;
use Illuminate\Http\Request;
use App\Models\Court;
use OpenApi\Attributes as OA;

class CourtController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    #[OA\Get(
        path: '/api/courts',
        summary: 'Get courts',
        description: 'Returns a paginated list of courts with search, filtering, price range, and sorting options.',
        tags: ['Courts'],

        security: [
            ['sanctum' => []]
        ],

        parameters: [
            new OA\Parameter(
                name: 'search',
                in: 'query',
                required: false,
                description: 'Search courts by court name.',
                schema: new OA\Schema(type: 'string'),
                example: 'Pickleball Arena'
            ),

            new OA\Parameter(
                name: 'court_type',
                in: 'query',
                required: false,
                description: 'Filter courts by court type.',
                schema: new OA\Schema(type: 'string'),
                example: 'indoor'
            ),

            new OA\Parameter(
                name: 'status',
                in: 'query',
                required: false,
                description: 'Filter courts by status. This filter is intended for admin users.',
                schema: new OA\Schema(
                    type: 'string',
                    enum: ['active', 'inactive']
                ),
                example: 'active'
            ),

            new OA\Parameter(
                name: 'city',
                in: 'query',
                required: false,
                description: 'Filter courts by city/address.',
                schema: new OA\Schema(type: 'string'),
                example: 'Surat'
            ),

            new OA\Parameter(
                name: 'price_min',
                in: 'query',
                required: false,
                description: 'Minimum price per hour.',
                schema: new OA\Schema(
                    type: 'number',
                    format: 'float',
                    minimum: 0
                ),
                example: 200
            ),

            new OA\Parameter(
                name: 'price_max',
                in: 'query',
                required: false,
                description: 'Maximum price per hour.',
                schema: new OA\Schema(
                    type: 'number',
                    format: 'float',
                    minimum: 0
                ),
                example: 1000
            ),

            new OA\Parameter(
                name: 'sort',
                in: 'query',
                required: false,
                description: 'Sort courts by price or latest.',
                schema: new OA\Schema(
                    type: 'string',
                    enum: ['price_low', 'price_high', 'latest'],
                    default: 'latest'
                ),
                example: 'price_low'
            ),

            new OA\Parameter(
                name: 'page',
                in: 'query',
                required: false,
                description: 'Page number for pagination.',
                schema: new OA\Schema(
                    type: 'integer',
                    minimum: 1,
                    default: 1
                ),
                example: 1
            ),
        ],

        responses: [
            new OA\Response(
                response: 200,
                description: 'Courts fetched successfully'
            ),

            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            ),

            new OA\Response(
                response: 403,
                description: 'Unauthorized'
            ),
        ]
    )]

    public function index(Request $request)
    {
        $query = Court::with([
            'owner',
            'images',
        ])->withAvg('reviews', 'rating')->withCount('reviews');

        //Search by court name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        //filter by court type
        if ($request->filled('court_type')) {
            $query->where('court_type', $request->court_type);
        }

        if (!$request->user() || $request->user()->role !== 'admin') {
            $query->where('status', 'active');
        } elseif ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        //Filter by city/address
        if ($request->filled('city')) {
            $query->where('address', 'like', '%' . $request->city . '%');
        }

        //price range
        if ($request->filled('price_min')) {
            $query->where('price_per_hour', '>=', $request->price_min);
        }
        if ($request->filled('price_max')) {
            $query->where('price_per_hour', '<=', $request->price_max);
        }

        //Sorting
        switch ($request->sort) {
            case 'price_low':
                $query->orderBy('price_per_hour');
                break;
            case 'price_high':
                $query->orderByDesc('price_per_hour');
                break;
            case 'top_rated':
            case 'most_rated':
                $query->orderByDesc('reviews_avg_rating')->orderByDesc('reviews_count');
                break;
            case 'latest':
            default:
                $query->latest();
                break;
        }

        $perPage = $request->input('per_page', 10);
        $limit = $request->input('limit');

        if ($limit) {
            $courts = $query->take((int)$limit)->get();
            return response()->json([
                'success' => true,
                'message' => 'Courts fetched successfully',
                'data' => CourtResource::collection($courts),
            ], 200);
        }

        $courts = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Courts fetched successfuly',
            'data' => CourtResource::collection($courts),
            'pagination' => [
                'current_page' => $courts->currentPage(),
                'last_page' => $courts->lastPage(),
                'per_page' => $courts->perPage(),
                'total' => $courts->total(),
            ]
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */

    #[OA\Post(
        path: '/api/owner/courts',
        summary: 'Create a court',
        description: 'Allows an authenticated owner to create a new court.',
        tags: ['Owner Courts'],
        security: [
            ['sanctum' => []]
        ],

        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: [
                    'name',
                    'description',
                    'address',
                    'latitude',
                    'longitude',
                    'price_per_hour',
                    'court_type',
                    'opening_time',
                    'closing_time'
                ],
                properties: [
                    new OA\Property(
                        property: 'name',
                        type: 'string',
                        example: 'Sukhkarta Pickleball Court'
                    ),
                    new OA\Property(
                        property: 'description',
                        type: 'string',
                        example: 'Premium outdoor pickleball court.'
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
                        format: 'double',
                        example: 500
                    ),
                    new OA\Property(
                        property: 'court_type',
                        type: 'string',
                        example: 'Outdoor'
                    ),
                    new OA\Property(
                        property: 'opening_time',
                        type: 'string',
                        example: '06:00'
                    ),
                    new OA\Property(
                        property: 'closing_time',
                        type: 'string',
                        example: '23:00'
                    ),
                ]
            )
        ),

        responses: [
            new OA\Response(
                response: 201,
                description: 'Court created successfully.'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated.'
            ),
            new OA\Response(
                response: 403,
                description: 'Only owners can create courts.'
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error.'
            ),
        ]
    )]

    public function store(StoreCourtRequest $request)
    {
        if ($request->user()->role !== 'owner') {
            return response()->json([
                'success' => false,
                'message' => 'Only owners can create courts.'
            ], 403);
        }
        $court = Court::create([
            'owner_id'       => $request->user()->id,
            'name'           => $request->name,
            'description'    => $request->description,
            'address'        => $request->address,
            'latitude'       => $request->latitude,
            'longitude'      => $request->longitude,
            'price_per_hour' => $request->price_per_hour,
            'court_type'     => $request->court_type,
            'opening_time'   => $request->opening_time,
            'closing_time'   => $request->closing_time,
            'status'         => 'active',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Court created successfully.',
            'data' => new CourtResource($court->load('owner', 'images')),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    #[OA\Get(
        path: '/api/courts/{court}',
        summary: 'Get court details',
        description: 'Returns detailed information about a specific court including owner, images, time slots, reviews, wishlist information, and tournaments.',
        tags: ['Courts'],

        security: [
            ['sanctum' => []]
        ],

        parameters: [
            new OA\Parameter(
                name: 'court',
                in: 'path',
                required: true,
                description: 'Court ID.',
                schema: new OA\Schema(
                    type: 'integer'
                ),
                example: 1
            ),
        ],

        responses: [
            new OA\Response(
                response: 200,
                description: 'Court details fetched successfully'
            ),

            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            ),

            new OA\Response(
                response: 404,
                description: 'Court not found'
            ),

            new OA\Response(
                response: 403,
                description: 'Unauthorized'
            ),
        ]
    )]

    public function show(Court $court, Request $request)
    {
        $court->load([
            'owner',
            'images',
            'timeSlots',
            'reviews',
            'wishlists',
            'tournaments',
        ]);
        if (
            $court->status === 'inactive' &&
            (!$request->user() || $request->user()->role !== 'admin')
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Court not found.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Court details fetchec successfuly',
            'data' => new CourtResource($court),
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    #[OA\Put(
        path: '/api/owner/courts/{court}',
        summary: 'Update a court',
        description: 'Allows the authenticated owner to update their court details.',
        tags: ['Owner Courts'],
        security: [
            ['sanctum' => []]
        ],

        parameters: [
            new OA\Parameter(
                name: 'court',
                in: 'path',
                required: true,
                description: 'Court ID',
                schema: new OA\Schema(type: 'integer'),
                example: 1
            ),
        ],

        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'name',
                        type: 'string',
                        example: 'Updated Pickleball Court'
                    ),
                    new OA\Property(
                        property: 'description',
                        type: 'string',
                        example: 'Updated court description.'
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
                        format: 'double',
                        example: 600
                    ),
                    new OA\Property(
                        property: 'court_type',
                        type: 'string',
                        example: 'Indoor'
                    ),
                    new OA\Property(
                        property: 'opening_time',
                        type: 'string',
                        example: '06:00'
                    ),
                    new OA\Property(
                        property: 'closing_time',
                        type: 'string',
                        example: '22:00'
                    ),
                    new OA\Property(
                        property: 'status',
                        type: 'string',
                        enum: ['active', 'inactive'],
                        example: 'active'
                    ),
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
                description: 'You are not authorized to update this court.'
            ),
            new OA\Response(
                response: 404,
                description: 'Court not found.'
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error.'
            ),
        ]
    )]

    public function update(UpdateCourtRequest $request, Court $court)
    {
        if ($court->owner_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to update this court.'
            ], 403);
        }

        $court->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Court Updated Successfuly',
            'data' => new CourtResource($court->load('owner', 'images')),
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    #[OA\Delete(
        path: '/api/owner/courts/{court}',
        summary: 'Delete a court',
        description: 'Allows the authenticated owner to delete their court if no bookings exist.',
        tags: ['Owner Courts'],
        security: [
            ['sanctum' => []]
        ],

        parameters: [
            new OA\Parameter(
                name: 'court',
                in: 'path',
                required: true,
                description: 'Court ID',
                schema: new OA\Schema(type: 'integer'),
                example: 1
            ),
        ],

        responses: [
            new OA\Response(
                response: 200,
                description: 'Court deleted successfully.'
            ),
            new OA\Response(
                response: 400,
                description: 'Cannot delete court because bookings exist.'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated.'
            ),
            new OA\Response(
                response: 403,
                description: 'You are not authorized to delete this court.'
            ),
            new OA\Response(
                response: 404,
                description: 'Court not found.'
            ),
        ]
    )]

    public function destroy(Court $court, Request $request)
    {
        if ($court->owner_id != $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to delete this court.'
            ], 403);
        }
        
        if ($court->bookings()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete court because bookings exist.'
            ], 400);
        }


        $court->delete();



        return response()->json([
            'success' => true,
            'message' => 'Court deleted Successfuly',
        ], 200);
    }
}
