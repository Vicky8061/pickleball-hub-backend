<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
            'success'=>true,
            'message'=>'Time slots fetched successfuly',
            'date'=> TimeSlotResource::collection($timeSlots),
        ],200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // find court
        $court = Court::findOrFail($request->court_id);

        // check owner

        if($court->owner_id != $request->user()->id){
            return response()->json([
                'success'=>false,
                'message'=>'You are not authorized to add time slots for this court',
            ],403);
        }
        
        //create time slot
        $timeSlot = TimeSlot::create([
            'court_id'=>$request->court_id,
            'start_time'=>$request->start_time,
            'end_time'=>$request->end_time,
            'status'=>'active',
        ]); 

        return response()->json([
            'success'=>true,
            'message'=>'Time Slot created Successfuly',
            'data'=> new TimeSlotResource($timeSlot),
        ]);



    }

    /**
     * Display the specified resource.
     */
    public function show(TimeSlot $timeSlot)
    {
        $timeSlot->load('court');

        return response()->json([
            'success'=>true,
            'message'=>'Time Slot fetched successfuly.',
            'date'=>new TimeSlotResource($timeSlot),
        ],200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTimeSlotRequest $request, TimeSlot $timeSlot)
    {
        //check ownership

        if($timeSlot->court->owner_id != $request->user()->id){
            return response()->json([
                'success'=>false,
                'message'=> 'You are not authorized to update this time slot.',
            ],403);
        }

        $timeSlot -> update($request->validated());

        return response()->json([
            'success'=> true,
            'message'=>'Time Slot updated successfuly',
            'date'=> new TimeSlotResource($timeSlot),
        ],200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TimeSlot $timeSlot,Request $request)
    {
        //check ownership
        if($timeSlot->court->owner_id != $request->user()->id){
            return response()->json([
                'success'=>false,
                'message'=>'You are not authorized to delete this time slot',
            ],403);
        }

        $timeSlot->delete();

        return response()->json([
            'success'=>true,
            'message'=>'Time Slot deleted successfuly.',
        ],200);


    }
}
