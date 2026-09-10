<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Court;
use App\Models\TimeSlot;
use App\Models\Booking;
use App\Http\Resources\BookingResource;
use App\Http\Requests\UpdateBookingRequest;
use App\Services\Payment\RazorpayService;
use App\Services\Invoice\InvoiceService;
use App\Mail\BookingConfirmedMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
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

        return DB::transaction(function () use ($request, $data) {
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

            $now = \Carbon\Carbon::now();
            $slotStartDateTime = \Carbon\Carbon::parse("{$data['booking_date']} {$timeSlot->start_time}");

            // Cannot book a slot that has already started or passed
            if ($slotStartDateTime->isPast()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot book a time slot that has already started or passed.',
                ], 400);
            }

            // Check duplicate booking with pessimistic locking:
            // A slot is taken only if confirmed OR currently on active pending hold
            $alreadyBooked = Booking::where('court_id', $court->id)
                ->where('time_slot_id', $timeSlot->id)
                ->where('booking_date', $data['booking_date'])
                ->where(function ($q) use ($now) {
                    $q->where('booking_status', 'confirmed')
                      ->orWhere(function ($sub) use ($now) {
                          $sub->where('booking_status', 'pending')
                              ->where(function ($hold) use ($now) {
                                  $hold->whereNull('expires_at')
                                       ->orWhere('expires_at', '>', $now);
                              });
                      });
                })
                ->lockForUpdate()
                ->exists();

            if ($alreadyBooked) {
                return response()->json([
                    'success' => false,
                    'message' => 'This time slot is already booked or currently on hold by another player.',
                ], 400);
            }

            // Calculate hold expiry (standard 10-minute hold, capped at match start time)
            $expiresAt = $now->copy()->addMinutes(10);
            if ($expiresAt->gt($slotStartDateTime)) {
                $expiresAt = $slotStartDateTime;
            }

            // Calculate financial split (10% admin commission, 90% owner payout + 50 platform fee)
            $courtPrice = (float) $court->price_per_hour;
            $platformFee = 50.00;
            $adminCommissionRate = 10.00; // 10%
            $adminCommissionAmount = round($courtPrice * ($adminCommissionRate / 100), 2);
            $ownerPayoutAmount = round($courtPrice - $adminCommissionAmount, 2);
            $totalAmount = round($courtPrice + $platformFee, 2);

            // Create booking with temporary reservation hold
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
                'expires_at' => $expiresAt,
            ]);

            $booking->load([
                'user',
                'court.images',
                'court.owner',
                'timeSlot',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Booking created successfully.',
                'data' => new BookingResource($booking),
            ], 201);
        });
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
            $updateData['expires_at'] = null;
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
            'expires_at' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Booking cancelled successfully.',
        ], 200);
    }

    #[OA\Post(
        path: '/api/bookings/{booking}/pay',
        summary: 'Pay and confirm a held booking',
        description: 'Allows an authenticated user to complete payment for their pending court booking hold.',
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
                schema: new OA\Schema(type: 'integer'),
                example: 1
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Payment successful and booking confirmed.'
            ),
            new OA\Response(
                response: 400,
                description: 'Hold expired or booking is not pending.'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated.'
            ),
            new OA\Response(
                response: 403,
                description: 'Unauthorized to pay for this booking.'
            ),
            new OA\Response(
                response: 404,
                description: 'Booking not found.'
            ),
        ]
    )]
    public function pay(Booking $booking, Request $request)
    {
        // Only users can pay for their bookings
        if ($request->user()->role !== 'user') {
            return response()->json([
                'success' => false,
                'message' => 'Only players can pay for their bookings.',
            ], 403);
        }

        // Check booking ownership
        if ($booking->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to complete payment for this booking.',
            ], 403);
        }

        // Check booking status
        if ($booking->booking_status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This booking is already ' . $booking->booking_status . '.',
            ], 400);
        }

        // Check hold expiry
        if ($booking->expires_at && $booking->expires_at->isPast()) {
            $booking->update([
                'booking_status' => 'cancelled',
                'payment_status' => 'failed',
                'expires_at' => null,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Your reservation hold has expired. The court slot has been released for other players.',
            ], 400);
        }

        // Check if match start time has already passed
        $bookingDateStr = is_string($booking->booking_date) ? $booking->booking_date : $booking->booking_date->format('Y-m-d');
        $slotStart = Carbon::parse($bookingDateStr . ' ' . $booking->timeSlot->start_time);
        if ($slotStart->isPast()) {
            $booking->update([
                'booking_status' => 'cancelled',
                'payment_status' => 'failed',
                'expires_at' => null,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'The time slot for this booking has already started or passed.',
            ], 400);
        }

        // Confirm booking and mark paid
        $booking->update([
            'booking_status' => 'confirmed',
            'payment_status' => 'paid',
            'paid_at' => Carbon::now(),
            'expires_at' => null,
        ]);

        $booking->load(['user', 'court', 'timeSlot']);

        // Dispatch confirmation email with attached PDF invoice
        try {
            if ($booking->user && $booking->user->email) {
                Mail::to($booking->user->email)->send(new BookingConfirmedMail($booking));
            }
        } catch (\Throwable $e) {
            Log::error('Failed to send booking confirmation email: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment successful! Your court booking is confirmed.',
            'data' => new BookingResource($booking),
        ], 200);
    }

    #[OA\Post(
        path: '/api/bookings/{booking}/create-order',
        summary: 'Create payment order for booking',
        description: 'Generates a payment order (Razorpay or Sandbox Simulator) for a held court reservation.',
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
                schema: new OA\Schema(type: 'integer'),
                example: 1
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Order created successfully.'),
            new OA\Response(response: 400, description: 'Hold expired or slot started.'),
            new OA\Response(response: 403, description: 'Unauthorized.'),
            new OA\Response(response: 404, description: 'Booking not found.'),
        ]
    )]
    public function createPaymentOrder(Booking $booking, Request $request, RazorpayService $razorpayService)
    {
        if ($request->user()->role !== 'user') {
            return response()->json([
                'success' => false,
                'message' => 'Only players can pay for court bookings.',
            ], 403);
        }

        if ($booking->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to pay for this booking.',
            ], 403);
        }

        if ($booking->booking_status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This booking is already ' . $booking->booking_status . '.',
            ], 400);
        }

        // Check hold expiry
        if ($booking->expires_at && $booking->expires_at->isPast()) {
            $booking->update([
                'booking_status' => 'cancelled',
                'payment_status' => 'failed',
                'expires_at' => null,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Your reservation hold has expired. The court slot has been released.',
            ], 400);
        }

        // Check slot start time
        $bookingDateStr = is_string($booking->booking_date) ? $booking->booking_date : $booking->booking_date->format('Y-m-d');
        $slotStart = Carbon::parse($bookingDateStr . ' ' . $booking->timeSlot->start_time);
        if ($slotStart->isPast()) {
            $booking->update([
                'booking_status' => 'cancelled',
                'payment_status' => 'failed',
                'expires_at' => null,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'The time slot for this booking has already started.',
            ], 400);
        }

        $orderData = $razorpayService->createOrder((float) $booking->total_amount, $booking->id);

        $booking->update([
            'razorpay_order_id' => $orderData['order_id'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment order created successfully.',
            'data' => [
                'order_id' => $orderData['order_id'],
                'amount' => $orderData['amount'],
                'amount_in_rupees' => $orderData['amount_in_rupees'],
                'currency' => $orderData['currency'],
                'key_id' => $orderData['key_id'],
                'is_mock' => $orderData['is_mock'],
                'booking_id' => $booking->id,
                'court_name' => $booking->court->name ?? 'Pickleball Court',
                'customer' => [
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                ],
            ],
        ], 200);
    }

    #[OA\Post(
        path: '/api/bookings/{booking}/verify-payment',
        summary: 'Verify payment signature & confirm booking',
        description: 'Validates HMAC-SHA256 signature and confirms court reservation.',
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
                schema: new OA\Schema(type: 'integer'),
                example: 1
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['razorpay_order_id', 'razorpay_payment_id', 'razorpay_signature'],
                properties: [
                    new OA\Property(property: 'razorpay_order_id', type: 'string'),
                    new OA\Property(property: 'razorpay_payment_id', type: 'string'),
                    new OA\Property(property: 'razorpay_signature', type: 'string'),
                    new OA\Property(property: 'payment_method', type: 'string', example: 'upi'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Payment verified and booking confirmed.'),
            new OA\Response(response: 400, description: 'Invalid signature or expired hold.'),
            new OA\Response(response: 403, description: 'Unauthorized.'),
            new OA\Response(response: 422, description: 'Validation error.'),
        ]
    )]
    public function verifyPayment(Booking $booking, Request $request, RazorpayService $razorpayService)
    {
        if ($request->user()->role !== 'user') {
            return response()->json([
                'success' => false,
                'message' => 'Only players can complete payment.',
            ], 403);
        }

        if ($booking->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to complete this payment.',
            ], 403);
        }

        if ($booking->booking_status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This booking is already ' . $booking->booking_status . '.',
            ], 400);
        }

        // Check hold expiry
        if ($booking->expires_at && $booking->expires_at->isPast()) {
            $booking->update([
                'booking_status' => 'cancelled',
                'payment_status' => 'failed',
                'expires_at' => null,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Your reservation hold has expired. The court slot has been released.',
            ], 400);
        }

        $validated = $request->validate([
            'razorpay_order_id' => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature' => 'required|string',
            'payment_method' => 'nullable|string',
        ]);

        $isValid = $razorpayService->verifySignature(
            $validated['razorpay_order_id'],
            $validated['razorpay_payment_id'],
            $validated['razorpay_signature']
        );

        if (!$isValid) {
            return response()->json([
                'success' => false,
                'message' => 'Payment signature verification failed. Tampered or invalid transaction.',
            ], 400);
        }

        $booking->update([
            'booking_status' => 'confirmed',
            'payment_status' => 'paid',
            'payment_method' => $validated['payment_method'] ?? 'razorpay',
            'razorpay_order_id' => $validated['razorpay_order_id'],
            'razorpay_payment_id' => $validated['razorpay_payment_id'],
            'razorpay_signature' => $validated['razorpay_signature'],
            'paid_at' => Carbon::now(),
            'expires_at' => null,
        ]);

        $booking->load(['user', 'court', 'timeSlot']);

        // Dispatch confirmation email with attached PDF invoice
        try {
            if ($booking->user && $booking->user->email) {
                Mail::to($booking->user->email)->send(new BookingConfirmedMail($booking));
            }
        } catch (\Throwable $e) {
            Log::error('Failed to send booking confirmation email: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment verified successfully! Your court booking is confirmed.',
            'data' => new BookingResource($booking),
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

    #[OA\Get(
        path: '/api/bookings/{booking}/invoice',
        summary: 'Download or view booking invoice PDF',
        description: 'Generates and downloads or streams the official PDF invoice for a confirmed court booking.',
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
                schema: new OA\Schema(type: 'integer'),
                example: 1
            ),
            new OA\Parameter(
                name: 'download',
                in: 'query',
                required: false,
                description: 'Set to 1 to force file download attachment.',
                schema: new OA\Schema(type: 'integer', enum: [0, 1]),
                example: 1
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Invoice PDF generated successfully.', content: new OA\MediaType(mediaType: 'application/pdf')),
            new OA\Response(response: 400, description: 'Booking not paid or confirmed.'),
            new OA\Response(response: 403, description: 'Unauthorized.'),
            new OA\Response(response: 404, description: 'Booking not found.'),
        ]
    )]
    public function downloadInvoice(Booking $booking, Request $request, InvoiceService $invoiceService)
    {
        $user = $request->user();

        // Ensure relations are loaded
        $booking->loadMissing(['user', 'court.owner', 'timeSlot']);

        // Authorization check: Player who booked, Court Owner, or Admin
        $isPlayer = $booking->user_id === $user->id;
        $isAdmin = $user->role === 'admin';
        $isCourtOwner = $user->role === 'owner' && $booking->court && $booking->court->owner_id === $user->id;

        if (!$isPlayer && !$isAdmin && !$isCourtOwner) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to access this invoice.',
            ], 403);
        }

        // Must be paid or confirmed
        if ($booking->payment_status !== 'paid' && $booking->booking_status !== 'confirmed') {
            return response()->json([
                'success' => false,
                'message' => 'Invoice is only available for confirmed and paid court bookings.',
            ], 400);
        }

        if ($request->query('download') === '1') {
            return $invoiceService->download($booking);
        }

        return $invoiceService->stream($booking);
    }
}
