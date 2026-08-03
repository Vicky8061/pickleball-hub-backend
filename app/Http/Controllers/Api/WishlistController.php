<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Wishlist;
use App\Models\Court;
use App\Http\Resources\WishlistResource;

class WishlistController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $wishlists = Wishlist::with([
            'court.owner',
            'court.images',
        ])
            ->where('user_id', $request->user()->id)  
            ->latest()
            ->get();
        return response()->json([
            'success'=> true,
            'message'=> 'Wishlists fetched successfuly',
            'data'=> WishlistResource::collection($wishlists)
        ],200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //Check duplicate wishlist
        $exists = Wishlist::where('user_id', $request->user()->id)
            ->where('court_id', $request->court_id)
            ->exists();
        
        if($exists){
            return response()->json([
                'success'=> false,
                'message'=> 'Court already in wishlist',
            ],400);
        }

        $wishlist = Wishlist::create([
            'user_id'=> $request->user()->id,
            'court_id'=> $request->court_id,
        ]);

        $wishlist->load('court');

        return response()->json([
            'success'=> true,
            'message'=> 'Court added to  wishlist',
            'data'=> new WishlistResource($wishlist)
        ],201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Court $court, Request $request,)
    {
        $wishlist = Wishlist::where('user_id', $request->user()->id)
            ->where('court_id', $court->id)
            ->first();
        if(!$wishlist){
            return response()->json([
                'success'=> false,
                'message'=> 'Court not found in wishlist',
            ],404);
        }

        $wishlist->delete();

        return response()->json([
            'success'=> true,
            'message'=>  'Court removed from wishlist',
        ],200);
    }
}
