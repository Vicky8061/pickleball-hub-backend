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
        ]);

        //Search by court name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        //filter by court type
        if ($request->filled('court_type')) {
            $query->where('court_type', $request->court_type);
        }



        if ($request->user()->role !== 'admin') {
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
            case 'latest':
            default:
                $query->latest();
                break;
        }

        $courts = $query->paginate(10);

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
            $request->user()->role !== 'admin'
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
    public function destroy(Court $court, Request $request)
    {
        if ($court->owner_id != $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to delete this court.'
            ], 403);
        }


        $court->delete();

        if ($court->bookings()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete court because bookings exist.'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Court deleted Successfuly',
        ], 200);
    }
}
