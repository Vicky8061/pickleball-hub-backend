<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCourtRequest;
use App\Http\Requests\UpdateCourtRequest;
use Illuminate\Http\Request;
use App\Models\Court;

class CourtController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $courts = Court::with('owner')->latest()->get();

        return response()->json([
            'success'=>true,
            'message'=>'Courts fetched Successfuly',
            'data'=> $courts,
        ],200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCourtRequest $request)
    {
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
            'data' => $court
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Court $court)
    {
        $court->load([
            'owner',
            'images',
            'timeSlots',
            'reviews',
            'wishlists',
            'tournaments',
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'Court details fetchec successfuly',
            'data'=> $court
        ],200);
        
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCourtRequest $request, Court $court)
    {
        if($court->owner_id != $request->user()->id){
            return response()->json([
                'success'=>false,
                'message'=>'You are not authorized to update this court.'
            ],403);
        }

        $court->update($request->validated());

        return response()->json([
            'success'=>true,
            'message'=>'Court Updated Successfuly',
            'data'=> $court
        ],200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Court $court, Request $request)
    {
        if ($court->owner_id != $request->user()->id){
            return response()->json([
                'success'=>false,
                'message'=> 'You are not authorized to delete this court.'
            ],403);
        }


        $court->delete();

        return response()->json([
            'success'=>true,
            'message'=>'Court deleted Successfuly',
        ],200);
        
    }
}
