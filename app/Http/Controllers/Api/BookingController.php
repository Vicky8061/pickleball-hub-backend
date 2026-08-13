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

class BookingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
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
            'court',
            'timeSlot',
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

        // Create booking
        $booking = Booking::create([
            'user_id' => $request->user()->id,
            'court_id' => $court->id,
            'time_slot_id' => $timeSlot->id,
            'booking_date' => $data['booking_date'],
            'total_amount' => $court->price_per_hour,
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

        $booking->update([
            'booking_status' => $data['booking_status'],
        ]);

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
    public function ownerBookings(Request $request)
    {
        // Only owners
        if ($request->user()->role !== 'owner') {
            return response()->json([
                'success' => false,
                'message' => 'Only owners can access owner bookings.',
            ], 403);
        }

        $bookings = Booking::with([
            'user',
            'court',
            'timeSlot',
        ])
            ->whereHas('court', function ($query) use ($request) {
                $query->where('owner_id', $request->user()->id);
            })
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Owner bookings fetched successfully.',
            'data' => BookingResource::collection($bookings),
        ], 200);
    }
}
