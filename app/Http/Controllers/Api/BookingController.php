<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use Illuminate\Http\Request;
use App\Models\Court;
use App\Models\TimeSlot;
use App\Models\Booking;
use App\Http\Resources\BookingResource;
use App\Http\Requests\UpdateBookingRequest;
use OpenApi\Attributes as OA;

class BookingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    #[OA\Get(
        path: '/api/bookings',
        summary: 'Get my bookings',
        description: 'Returns all bookings belonging to the authenticated user.',
        tags: ['Bookings'],

        security: [
            ['sanctum' => []]
        ],

        responses: [
            new OA\Response(
                response: 200,
                description: 'Bookings fetched successfully'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            ),
            new OA\Response(
                response: 403,
                description: 'Only users can access their bookings'
            ),
        ]
    )]

    public function index(Request $request)
    {
        if ($request->user()->role !== 'user') {
            return response()->json([
                'success' => false,
                'message' => 'Only users can access their bookings.',
            ], 403);
        }

        $bookings = Booking::with([
            'user',
            'court.images',
            'court.owner',
            'court.timeSlots',
            'court.reviews',
            'timeSlot'
        ])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Bookings fetched successfully.',
            'data' => BookingResource::collection($bookings),
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */

    #[OA\Post(
        path: '/api/bookings',
        summary: 'Create a booking',
        description: 'Allows an authenticated user to book an active court and an available time slot.',
        tags: ['Bookings'],

        security: [
            ['sanctum' => []]
        ],

        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: [
                    'court_id',
                    'time_slot_id',
                    'booking_date'
                ],
                properties: [
                    new OA\Property(
                        property: 'court_id',
                        type: 'integer',
                        example: 1
                    ),

                    new OA\Property(
                        property: 'time_slot_id',
                        type: 'integer',
                        example: 3
                    ),

                    new OA\Property(
                        property: 'booking_date',
                        type: 'string',
                        format: 'date',
                        example: '2026-08-20'
                    ),
                ]
            )
        ),

        responses: [
            new OA\Response(
                response: 201,
                description: 'Booking created successfully'
            ),
            new OA\Response(
                response: 400,
                description: 'Court, time slot, or booking is not available'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            ),
            new OA\Response(
                response: 403,
                description: 'Only users can book courts'
            ),
            new OA\Response(
                response: 404,
                description: 'Court or time slot not found'
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error'
            ),
        ]
    )]
    public function store(StoreBookingRequest $request)
    {
        // Only users can book courts
        if ($request->user()->role !== 'user') {
            return response()->json([
                'success' => false,
                'message' => 'Only users can book courts.',
            ], 403);
        }

        $data = $request->validated();

        // Find court
        $court = Court::findOrFail($data['court_id']);

        // Check court status
        if ($court->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Court is not available for booking.',
            ], 400);
        }

        // Find time slot
        $timeSlot = TimeSlot::findOrFail($data['time_slot_id']);

        // Check time slot belongs to selected court
        if ($timeSlot->court_id !== $court->id) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid time slot for the selected court.',
            ], 400);
        }

        // Check time slot status
        if ($timeSlot->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Time slot is inactive.',
            ], 400);
        }

        // Check duplicate booking
        $alreadyBooked = Booking::where('court_id', $court->id)
            ->where('time_slot_id', $timeSlot->id)
            ->where('booking_date', $data['booking_date'])
            ->whereIn('booking_status', ['pending', 'confirmed'])
            ->exists();

        if ($alreadyBooked) {
            return response()->json([
                'success' => false,
                'message' => 'This time slot is already booked for the selected date.',
            ], 400);
        }

        // Calculate financial split (10% admin commission, 90% owner payout + 50 platform fee)
        $courtPrice = (float) $court->price_per_hour;
        $platformFee = 50.00;
        $adminCommissionRate = 10.00; // 10%
        $adminCommissionAmount = round($courtPrice * ($adminCommissionRate / 100), 2);
        $ownerPayoutAmount = round($courtPrice - $adminCommissionAmount, 2);
        $totalAmount = round($courtPrice + $platformFee, 2);

        // Create booking
        $booking = Booking::create([
            'user_id' => $request->user()->id,
            'court_id' => $court->id,
            'time_slot_id' => $timeSlot->id,
            'booking_date' => $data['booking_date'],
            'court_price' => $courtPrice,
            'platform_fee' => $platformFee,
            'admin_commission_rate' => $adminCommissionRate,
            'admin_commission_amount' => $adminCommissionAmount,
            'owner_payout_amount' => $ownerPayoutAmount,
            'total_amount' => $totalAmount,
            'payment_status' => 'pending',
            'booking_status' => 'pending',
        ]);

        $booking->load([
            'user',
            'court',
            'timeSlot',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Booking created successfully.',
            'data' => new BookingResource($booking),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    #[OA\Get(
        path: '/api/bookings/{booking}',
        summary: 'Get booking details',
        description: 'Returns details of a specific booking belonging to the authenticated user.',
        tags: ['Bookings'],

        security: [
            ['sanctum' => []]
        ],

        parameters: [
            new OA\Parameter(
                name: 'booking',
                in: 'path',
                required: true,
                description: 'Booking ID.',
                schema: new OA\Schema(
                    type: 'integer'
                ),
                example: 1
            ),
        ],

        responses: [
            new OA\Response(
                response: 200,
                description: 'Booking fetched successfully'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            ),
            new OA\Response(
                response: 403,
                description: 'You are not authorized to view this booking'
            ),
            new OA\Response(
                response: 404,
                description: 'Booking not found'
            ),
        ]
    )]
    public function show(Booking $booking, Request $request)
    {
        if ($request->user()->role !== 'user') {
            return response()->json([
                'success' => false,
                'message' => 'Only users can access their bookings.',
            ], 403);
        }

        if ($booking->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to view this booking.',
            ], 403);
        }

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

    /**
     * Update the specified resource in storage.
     */
    #[OA\Put(
        path: '/api/owner/bookings/{booking}',
        summary: 'Update booking status',
        description: 'Allows the authenticated owner to update the status of a booking belonging to one of their courts.',
        tags: ['Owner Bookings'],
        security: [
            ['sanctum' => []]
        ],

        parameters: [
            new OA\Parameter(
                name: 'booking',
                in: 'path',
                required: true,
                description: 'Booking ID',
                schema: new OA\Schema(type: 'integer'),
                example: 1
            ),
        ],

        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['booking_status'],
                properties: [
                    new OA\Property(
                        property: 'booking_status',
                        type: 'string',
                        enum: ['confirmed', 'completed'],
                        example: 'confirmed'
                    ),
                ]
            )
        ),

        responses: [
            new OA\Response(
                response: 200,
                description: 'Booking status updated successfully.'
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid booking status transition.'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated.'
            ),
            new OA\Response(
                response: 403,
                description: 'Unauthorized or user is not an owner.'
            ),
            new OA\Response(
                response: 404,
                description: 'Booking not found.'
            ),
        ]
    )]

    public function update(UpdateBookingRequest $request, Booking $booking)
    {
        // Only owners can update booking status
        if ($request->user()->role !== 'owner') {
            return response()->json([
                'success' => false,
                'message' => 'Only owners can update booking status.',
            ], 403);
        }

        // Make sure this booking belongs to owner's court
        if ($booking->court->owner_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to update this booking.',
            ], 403);
        }

        $data = $request->validated();

        // Cancelled booking cannot be updated
        if ($booking->booking_status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Cancelled booking cannot be updated.',
            ], 400);
        }

        // Completed booking cannot be updated
        if ($booking->booking_status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Booking is already completed.',
            ], 400);
        }

        // pending -> confirmed
        if (
            $booking->booking_status === 'pending' &&
            $data['booking_status'] !== 'confirmed'
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Pending booking can only be confirmed.',
            ], 400);
        }

        // confirmed -> completed
        if (
            $booking->booking_status === 'confirmed' &&
            $data['booking_status'] !== 'completed'
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Confirmed booking can only be completed.',
            ], 400);
        }

        $updateData = [
            'booking_status' => $data['booking_status'],
        ];

        if (in_array($data['booking_status'], ['confirmed', 'completed'])) {
            $updateData['payment_status'] = 'paid';
        }

        $booking->update($updateData);

        $booking->load([
            'user',
            'court',
            'timeSlot',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Booking status updated successfully.',
            'data' => new BookingResource($booking),
        ], 200);
    }
    /**
     * Remove the specified resource from storage.
     */
    #[OA\Delete(
        path: '/api/bookings/{booking}',
        summary: 'Cancel a booking',
        description: 'Cancels a booking belonging to the authenticated user. Completed bookings cannot be cancelled.',
        tags: ['Bookings'],

        security: [
            ['sanctum' => []]
        ],

        parameters: [
            new OA\Parameter(
                name: 'booking',
                in: 'path',
                required: true,
                description: 'Booking ID.',
                schema: new OA\Schema(
                    type: 'integer'
                ),
                example: 1
            ),
        ],

        responses: [
            new OA\Response(
                response: 200,
                description: 'Booking cancelled successfully'
            ),
            new OA\Response(
                response: 400,
                description: 'Booking is already cancelled or completed'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            ),
            new OA\Response(
                response: 403,
                description: 'You are not authorized to cancel this booking'
            ),
            new OA\Response(
                response: 404,
                description: 'Booking not found'
            ),
        ]
    )]
    public function destroy(Booking $booking, Request $request)
    {
        // Only users can cancel their bookings
        if ($request->user()->role !== 'user') {
            return response()->json([
                'success' => false,
                'message' => 'Only users can cancel their bookings.',
            ], 403);
        }

        // Check booking owner
        if ($booking->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to cancel this booking.',
            ], 403);
        }

        // Already cancelled
        if ($booking->booking_status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Booking is already cancelled.',
            ], 400);
        }

        // Completed cannot be cancelled
        if ($booking->booking_status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Completed booking cannot be cancelled.',
            ], 400);
        }

        $booking->update([
            'booking_status' => 'cancelled',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Booking cancelled successfully.',
        ], 200);
    }
    #[OA\Get(
        path: '/api/owner/bookings',
        summary: 'Get owner bookings',
        description: 'Fetch all bookings made for courts owned by the authenticated owner.',
        tags: ['Owner Bookings'],
        security: [
            ['sanctum' => []]
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Owner bookings fetched successfully.'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated.'
            ),
            new OA\Response(
                response: 403,
                description: 'Only owners can access owner bookings.'
            ),
        ]
    )]

    public function ownerBookings(Request $request)
    {
        // Only owners
        if ($request->user()->role !== 'owner') {
            return response()->json([
                'success' => false,
                'message' => 'Only owners can access owner bookings.',
            ], 403);
        }

        $courtId = $request->query('court_id');
        $status = $request->query('status');
        $date = $request->query('date');

        $query = Booking::with([
            'user',
            'court',
            'timeSlot',
        ])
            ->whereHas('court', function ($q) use ($request) {
                $q->where('owner_id', $request->user()->id);
            });

        if ($courtId) {
            $query->where('court_id', $courtId);
        }

        if ($status) {
            $query->where('booking_status', $status);
        }

        if ($date) {
            $query->where('booking_date', $date);
        }

        $bookings = $query->latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'Owner bookings fetched successfully.',
            'data' => BookingResource::collection($bookings),
        ], 200);
    }

    /**
     * Cancel a booking by owner.
     */
    public function cancelByOwner(Booking $booking, Request $request)
    {
        if ($request->user()->role !== 'owner') {
            return response()->json([
                'success' => false,
                'message' => 'Only owners can cancel bookings.',
            ], 403);
        }

        if ($booking->court->owner_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to cancel this booking.',
            ], 403);
        }

        if ($booking->booking_status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Completed bookings cannot be cancelled.',
            ], 400);
        }

        if ($booking->booking_status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Booking is already cancelled.',
            ], 400);
        }

        $booking->update([
            'booking_status' => 'cancelled',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Booking cancelled successfully.',
            'data' => new BookingResource($booking->load(['user', 'court', 'timeSlot'])),
        ], 200);
    }
}
