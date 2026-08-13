<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTimeSlotRequest;
use App\Http\Requests\UpdateTimeSlotRequest;
use App\Models\Court;
use App\Models\TimeSlot;
use App\Http\Resources\TimeSlotResource;
use Illuminate\Http\Request;

class TimeSlotController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $timeSlots = TimeSlot::with('court')
            ->latest()
            ->get();
        return response()->json([
            'success' => true,
            'message' => 'Time slots fetched successfuly',
            'date' => TimeSlotResource::collection($timeSlots),
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTimeSlotRequest $request)
    {
        if ($request->user()->role !== 'owner') {
            return response()->json([
                'success' => false,
                'message' => 'Only owners can manage time slots.'
            ], 403);
        }
        $data = $request->validated();

        $court = Court::findOrFail($data['court_id']);

        // check owner

        if ($court->owner_id != $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to add time slots for this court',
            ], 403);
        }

        //court must be active
        if ($court->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Inactive courts cannot have time slots.'
            ], 400);
        }

        //Duplicate Slot
        $exists = TimeSlot::where('court_id', $court->id)
            ->where('start_time', $data['start_time'])
            ->where('end_time', $data['end_time'])
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'This time slot already exists.'
            ], 400);
        }

        //overlapping
        $overlap = TimeSlot::where('court_id', $court->id)
            ->where(function ($query) use ($data) {
                $query
                    ->whereBetween('start_time', [$data['start_time'], $data['end_time']])
                    ->orWhereBetween('end_time', [$data['start_time'], $data['end_time']])
                    ->orWhere(function ($q) use ($data) {
                        $q->where('start_time', '<=', $data['start_time'])
                            ->where('end_time', '>=', $data['end_time']);
                    });
            })
            ->exists();

        if ($overlap) {
            return response()->json([
                'success' => false,
                'message' => 'This time slot overlaps with an existing slot.'
            ], 400);
        }

        //create time slot
        $timeSlot = TimeSlot::create([
            'court_id' => $request->court_id,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'status' => 'active',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Time Slot created Successfuly',
            'data' => new TimeSlotResource(
                $timeSlot->load('court')
            ),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(TimeSlot $timeSlot)
    {
        $timeSlot->load('court');

        return response()->json([
            'success' => true,
            'message' => 'Time Slot fetched successfuly.',
            'data' => new TimeSlotResource(
                $timeSlot->load('court')
            ),
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTimeSlotRequest $request, TimeSlot $timeSlot)
    {
        if ($request->user()->role !== 'owner') {
            return response()->json([
                'success' => false,
                'message' => 'Only owners can manage time slots.'
            ], 403);
        }
        //check ownership

        if ($timeSlot->court->owner_id != $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to update this time slot.',
            ], 403);
        }

        $data = $request->validated();

        // Prevent duplicate slot
        $exists = TimeSlot::where('court_id', $timeSlot->court_id)
            ->where('start_time', $data['start_time'])
            ->where('end_time', $data['end_time'])
            ->where('id', '!=', $timeSlot->id)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'This time slot already exists.'
            ], 400);
        }

        $timeSlot->update([
            'start_time' => $data['start_time'],
            'end_time'   => $data['end_time'],
            'status'     => $data['status'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Time slot updated successfully.',
            'data'    => new TimeSlotResource($timeSlot->load('court')),
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TimeSlot $timeSlot, Request $request)
    {
        if ($request->user()->role !== 'owner') {
            return response()->json([
                'success' => false,
                'message' => 'Only owners can manage time slots.'
            ], 403);
        }
        //check ownership
        if ($timeSlot->court->owner_id != $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to delete this time slot',
            ], 403);
        }

        if ($timeSlot->bookings()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete a booked time slot.'
            ], 400);
        }

        $timeSlot->delete();

        return response()->json([
            'success' => true,
            'message' => 'Time Slot deleted successfuly.',
        ], 200);
    }
}
