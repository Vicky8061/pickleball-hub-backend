<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTimeSlotRequest;
use App\Http\Requests\UpdateTimeSlotRequest;
use App\Models\Court;
use App\Models\TimeSlot;
use App\Http\Resources\TimeSlotResource;
use App\Models\Booking;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class TimeSlotController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    #[OA\Get(
        path: '/api/time-slots',
        summary: 'Get all time slots',
        description: 'Returns all time slots along with their associated courts, sorted by latest first.',
        tags: ['Time Slots'],

        security: [
            ['sanctum' => []]
        ],

        responses: [
            new OA\Response(
                response: 200,
                description: 'Time slots fetched successfully'
            ),

            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            ),
        ]
    )]
    public function index()
    {
        $timeSlots = TimeSlot::with('court')
            ->latest()
            ->get();
        return response()->json([
            'success' => true,
            'message' => 'Time slots fetched successfuly',
            'data' => TimeSlotResource::collection($timeSlots),
        ], 200);
    }

    public function availability(Request $request, Court $court)
    {
        $request->validate([
            'date' => 'required|date_format:Y-m-d|after_or_equal:today',
        ]);

        // Court must be active
        if ($court->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Court is not available for booking.',
            ], 400);
        }

        $date = $request->date;

        // Get all active slots of this court
        $timeSlots = TimeSlot::where('court_id', $court->id)
            ->where('status', 'active')
            ->orderBy('start_time')
            ->get();

        // Get booked time slot IDs for selected date
        $bookedSlotIds = Booking::where('court_id', $court->id)
            ->where('booking_date', $date)
            ->whereIn('booking_status', ['pending', 'confirmed'])
            ->pluck('time_slot_id')
            ->toArray();

        $data = $timeSlots->map(function ($slot) use ($bookedSlotIds) {

            return [
                'id' => $slot->id,
                'court_id' => $slot->court_id,
                'start_time' => $slot->start_time,
                'end_time' => $slot->end_time,
                'status' => $slot->status,

                'is_booked' => in_array(
                    $slot->id,
                    $bookedSlotIds
                ),

                'is_available' => !in_array(
                    $slot->id,
                    $bookedSlotIds
                ),
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Court time slot availability fetched successfully.',
            'data' => $data,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    #[OA\Post(
        path: '/api/owner/time-slots',
        summary: 'Create a time slot',
        description: 'Allows an authenticated owner to create a time slot for their court.',
        tags: ['Owner Time Slots'],
        security: [
            ['sanctum' => []]
        ],

        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: [
                    'court_id',
                    'start_time',
                    'end_time'
                ],
                properties: [
                    new OA\Property(
                        property: 'court_id',
                        type: 'integer',
                        example: 1,
                        description: 'Court ID'
                    ),
                    new OA\Property(
                        property: 'start_time',
                        type: 'string',
                        example: '06:00',
                        description: 'Time slot start time'
                    ),
                    new OA\Property(
                        property: 'end_time',
                        type: 'string',
                        example: '07:00',
                        description: 'Time slot end time'
                    ),
                ]
            )
        ),

        responses: [
            new OA\Response(
                response: 201,
                description: 'Time slot created successfully.'
            ),
            new OA\Response(
                response: 400,
                description: 'Duplicate, overlapping, or inactive court.'
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
                description: 'Court not found.'
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error.'
            ),
        ]
    )]

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

        // overlapping (strict interval intersection: start < endB AND end > startB)
        $overlap = TimeSlot::where('court_id', $court->id)
            ->where('start_time', '<', $data['end_time'])
            ->where('end_time', '>', $data['start_time'])
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
    #[OA\Get(
        path: '/api/time-slots/{timeSlot}',
        summary: 'Get time slot details',
        description: 'Returns details of a specific time slot along with its associated court.',
        tags: ['Time Slots'],

        security: [
            ['sanctum' => []]
        ],

        parameters: [
            new OA\Parameter(
                name: 'timeSlot',
                in: 'path',
                required: true,
                description: 'Time slot ID.',
                schema: new OA\Schema(
                    type: 'integer'
                ),
                example: 1
            ),
        ],

        responses: [
            new OA\Response(
                response: 200,
                description: 'Time slot fetched successfully'
            ),

            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            ),

            new OA\Response(
                response: 404,
                description: 'Time slot not found'
            ),
        ]
    )]
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
    #[OA\Put(
        path: '/api/owner/time-slots/{timeSlot}',
        summary: 'Update a time slot',
        description: 'Allows an authenticated owner to update a time slot belonging to their court.',
        tags: ['Owner Time Slots'],
        security: [
            ['sanctum' => []]
        ],

        parameters: [
            new OA\Parameter(
                name: 'timeSlot',
                in: 'path',
                required: true,
                description: 'Time slot ID',
                schema: new OA\Schema(type: 'integer'),
                example: 1
            ),
        ],

        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: [
                    'start_time',
                    'end_time',
                    'status'
                ],
                properties: [
                    new OA\Property(
                        property: 'start_time',
                        type: 'string',
                        example: '07:00',
                        description: 'Updated start time'
                    ),
                    new OA\Property(
                        property: 'end_time',
                        type: 'string',
                        example: '08:00',
                        description: 'Updated end time'
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
                description: 'Time slot updated successfully.'
            ),
            new OA\Response(
                response: 400,
                description: 'Time slot already exists.'
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
                description: 'Time slot not found.'
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error.'
            ),
        ]
    )]

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
    #[OA\Delete(
        path: '/api/owner/time-slots/{timeSlot}',
        summary: 'Delete a time slot',
        description: 'Allows an authenticated owner to delete a time slot belonging to their court. A time slot cannot be deleted if it has bookings.',
        tags: ['Owner Time Slots'],
        security: [
            ['sanctum' => []]
        ],

        parameters: [
            new OA\Parameter(
                name: 'timeSlot',
                in: 'path',
                required: true,
                description: 'Time slot ID',
                schema: new OA\Schema(type: 'integer'),
                example: 1
            ),
        ],

        responses: [
            new OA\Response(
                response: 200,
                description: 'Time slot deleted successfully.'
            ),
            new OA\Response(
                response: 400,
                description: 'Cannot delete a booked time slot.'
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
                description: 'Time slot not found.'
            ),
        ]
    )]

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

    /**
     * Display all time slots belonging to courts owned by the authenticated owner.
     */
    public function ownerTimeSlots(Request $request)
    {
        if ($request->user()->role !== 'owner') {
            return response()->json([
                'success' => false,
                'message' => 'Only owners can access time slots.'
            ], 403);
        }

        $ownerId = $request->user()->id;
        $courtId = $request->query('court_id');

        $query = TimeSlot::whereHas('court', function ($q) use ($ownerId) {
            $q->where('owner_id', $ownerId);
        })->with('court');

        if ($courtId) {
            $query->where('court_id', $courtId);
        }

        $timeSlots = $query->orderBy('court_id')->orderBy('start_time')->get();

        return response()->json([
            'success' => true,
            'message' => 'Owner time slots fetched successfully.',
            'data' => TimeSlotResource::collection($timeSlots),
        ], 200);
    }

    /**
     * Bulk generate time slots for a court.
     */
    public function bulkStore(Request $request)
    {
        if ($request->user()->role !== 'owner') {
            return response()->json([
                'success' => false,
                'message' => 'Only owners can manage time slots.'
            ], 403);
        }

        $request->validate([
            'court_id' => 'required|exists:courts,id',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'slot_duration_minutes' => 'nullable|integer|in:30,60,90,120',
        ]);

        $court = Court::findOrFail($request->court_id);

        if ($court->owner_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to manage time slots for this court.'
            ], 403);
        }

        if ($court->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Inactive courts cannot have time slots.'
            ], 400);
        }

        $duration = (int) ($request->slot_duration_minutes ?? 60);
        $start = \Carbon\Carbon::createFromFormat('H:i', $request->start_time);
        $end = \Carbon\Carbon::createFromFormat('H:i', $request->end_time);

        $createdSlots = [];
        $skippedCount = 0;

        $current = $start->copy();
        while ($current->copy()->addMinutes($duration)->lte($end)) {
            $slotStart = $current->format('H:i:s');
            $slotEnd = $current->copy()->addMinutes($duration)->format('H:i:s');

            // strict interval intersection: start < endB AND end > startB
            $exists = TimeSlot::where('court_id', $court->id)
                ->where('start_time', '<', $slotEnd)
                ->where('end_time', '>', $slotStart)
                ->exists();

            if (!$exists) {
                $timeSlot = TimeSlot::create([
                    'court_id' => $court->id,
                    'start_time' => $slotStart,
                    'end_time' => $slotEnd,
                    'status' => 'active',
                ]);
                $createdSlots[] = new TimeSlotResource($timeSlot->load('court'));
            } else {
                $skippedCount++;
            }

            $current->addMinutes($duration);
        }

        return response()->json([
            'success' => true,
            'message' => count($createdSlots) . " time slots generated successfully. ($skippedCount skipped due to overlap)",
            'created_count' => count($createdSlots),
            'skipped_count' => $skippedCount,
            'data' => $createdSlots,
        ], 201);
    }
}
