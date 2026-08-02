<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
            'message' => 'Bookings fetched successfuly',
            'data' => BookingResource::collection($bookings),
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Only User can book
        if ($request->user()->role != 'user') {
            return response()->json([
                'success' => false,
                'message' => 'Only users can book courts.'
            ], 403);
        }
        //Get Court
        $court = Court::findOrFail($request->court_id);

        //check court status
        if ($court->status != 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Court is not available for booking.',
            ], 400);
        }

        // Get the time slot
        $timeSlot  = TimeSlot::findOrFail($request->time_slot_id);

        // check time slot  belongs to the court
        if ($timeSlot->court_id != $court->id) {
            return response()->json([
                'success' => false,
                'message' => 'Invalide time slot for the selected court.',
            ], 400);
        }

        // check time slot status
        if ($timeSlot->status != 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Time slot is inactive.',
            ], 400);
        }



        //check existing booking 

        $alreadyBooked = Booking::where('court_id', $court->id)
            ->where('time_slot_id', $timeSlot->id)
            ->where('booking_date', $request->booking_date)
            ->whereIn('booking_status', ['pending', 'confirmed'])
            ->exists();
        if ($alreadyBooked) {
            return response()->json([
                'success' => false,
                'message' => 'This time slot is already booked for the selected date.',
            ], 400);
        }

        // Create the booking
        $booking = Booking::create([
            'user_id' => $request->user()->id,
            'court_id' => $court->id,
            'time_slot_id' => $timeSlot->id,
            'booking_date' => $request->booking_date,
            'total_amount' => $court->price_per_hour,
            'payment_status' => 'pending',
            'booking_status' => 'pending',
        ]);

        $booking->load(['user', 'court', 'timeSlot']);
        return response()->json([
            'success' => true,
            'message' => 'Booking created successfully.',
            'data' => new BookingResource($booking)
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Booking $booking, Request $request)
    {
        if ($booking->user_id != $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access',
            ], 403);
        }

        $booking->load([
            'user',
            'court',
            'timeSlot',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Booking fetched successfuly',
            'data' => new BookingResource($booking),
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBookingRequest $request, Booking $booking)
    {
        // Only Owner
        if ($request->user()->role != 'owner') {
            return response()->json([
                'success' => false,
                'message' => 'Only owners can update booking status.'
            ], 403);
        }

        // Check Court Owner
        if ($booking->court->owner_id != $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to update this booking.'
            ], 403);
        }

        // Current Status
        if ($booking->booking_status == 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Cancelled booking cannot be updated.'
            ], 400);
        }

        if ($booking->booking_status == 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Booking is already completed.'
            ], 400);
        }

        // Allowed Flow:
        // pending -> confirmed
        // confirmed -> completed

        if (
            $booking->booking_status == 'pending' &&
            $request->booking_status != 'confirmed'
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Pending booking can only be confirmed.'
            ], 400);
        }

        if (
            $booking->booking_status == 'confirmed' &&
            $request->booking_status != 'completed'
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Confirmed booking can only be completed.'
            ], 400);
        }

        $booking->update([
            'booking_status' => $request->booking_status,
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
        if ($booking->user_id != $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access.'
            ], 403);
        }

        if ($booking->booking_status == 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Booking is already cancelled.'
            ], 400);
        }

        if ($booking->booking_status == 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Completed booking cannot be cancelled.'
            ], 400);
        }

        $booking->update([
            'booking_status' => 'cancelled'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Booking cancelled successfully.'
        ], 200);
    }
    public function ownerBookings(Request $request)
    {
        // Only Owner
        if ($request->user()->role != 'owner') {
            return response()->json([
                'success' => false,
                'message' => 'Only owners can access this.'
            ], 403);
        }

        $bookings = Booking::with([
            'user',
            'court',
            'timeSlot'
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
