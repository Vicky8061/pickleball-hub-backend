<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class AdminBookingController extends Controller
{
    // --------------------------------------------------
    // GET ALL BOOKINGS
    // --------------------------------------------------

    #[OA\Get(
        path: '/api/admin/bookings',
        summary: 'Get all bookings',
        description: 'Fetch all bookings with user, court and time slot details. Supports search, status filtering, sorting and pagination.',
        tags: ['Admin - Bookings'],
        security: [['sanctum' => []]],

        parameters: [

            new OA\Parameter(
                name: 'search',
                in: 'query',
                required: false,
                description: 'Search booking by user name or court name.',
                schema: new OA\Schema(
                    type: 'string'
                ),
                example: 'Vicky'
            ),

            new OA\Parameter(
                name: 'status',
                in: 'query',
                required: false,
                description: 'Filter bookings by booking status.',
                schema: new OA\Schema(
                    type: 'string'
                ),
                example: 'confirmed'
            ),

            new OA\Parameter(
                name: 'sort',
                in: 'query',
                required: false,
                description: 'Sort bookings by latest or oldest.',
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
                description: 'Bookings fetched successfully.'
            ),

            new OA\Response(
                response: 401,
                description: 'Unauthenticated.'
            ),

            new OA\Response(
                response: 403,
                description: 'Only admins can access bookings.'
            ),

            new OA\Response(
                response: 422,
                description: 'Invalid filter value.'
            )
        ]
    )]
    public function index(Request $request)
    {
        $query = Booking::with([
            'user',
            'court',
            'timeSlot',
        ]);

        // Search by user name or court name
        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->whereHas('user', function ($user) use ($search) {

                    $user->where(
                        'name',
                        'like',
                        "%{$search}%"
                    );
                })->orWhereHas('court', function ($court) use ($search) {

                    $court->where(
                        'name',
                        'like',
                        "%{$search}%"
                    );
                });
            });
        }

        // Filter by booking status
        if ($request->filled('status')) {

            $query->where(
                'booking_status',
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
        $bookings = $query->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Bookings fetched successfully.',

            'data' => BookingResource::collection($bookings),

            'pagination' => [
                'current_page' => $bookings->currentPage(),
                'last_page' => $bookings->lastPage(),
                'per_page' => $bookings->perPage(),
                'total' => $bookings->total(),
            ],
        ], 200);
    }


    // --------------------------------------------------
    // GET SINGLE BOOKING
    // --------------------------------------------------

    #[OA\Get(
        path: '/api/admin/bookings/{booking}',
        summary: 'Get booking details',
        description: 'Fetch complete details of a specific booking including user, court and time slot information.',
        tags: ['Admin - Bookings'],
        security: [['sanctum' => []]],

        parameters: [

            new OA\Parameter(
                name: 'booking',
                in: 'path',
                required: true,
                description: 'Booking ID.',
                schema: new OA\Schema(
                    type: 'integer'
                ),
                example: 5
            )
        ],

        responses: [

            new OA\Response(
                response: 200,
                description: 'Booking fetched successfully.'
            ),

            new OA\Response(
                response: 401,
                description: 'Unauthenticated.'
            ),

            new OA\Response(
                response: 403,
                description: 'Only admins can access bookings.'
            ),

            new OA\Response(
                response: 404,
                description: 'Booking not found.'
            )
        ]
    )]
    public function show(Booking $booking)
    {
        $booking->load([
            'user',
            'court',
            'timeSlot',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Booking fetched successfully.',
            'data' => new BookingResource($booking),
        ], 200);
    }
}
