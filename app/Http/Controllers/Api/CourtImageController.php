<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Court;
use App\Models\CourtImage;
use App\Http\Requests\StoreCourtImageRequest;
use Illuminate\Support\Facades\Storage;

class CourtImageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Court $court)
    {
        return response()->json([
            'success'=> true,
            'message'=> 'Court images fetched successfuly',
            'data'=> $court->images
        ],200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCourtImageRequest $request)
    {
        $court = Court::findOrFail($request->court_id);

        if($court->owner_id != $request->user()->id){
            return response()->json([
                'success'=>false,
                'message'=>'You are not authorized to upload images for this court ',
            ],403);
        }

        $uploadedImages = [];

        foreach ($request->file('images') as $index => $image){

            $path = $image->store('court_images','public');

            $courtImage = CourtImage::create([
                'court_id' => $court->id,
                'image' => $path,
                'is_primary'=> $request->is_primary && $index == 0,
            ]);

            $uploadedImages[] = $courtImage;
        }
        return response()->json([
            'success'=>true,
            'message'=> 'Images uploaded successfuly',
            'data'=> $uploadedImages
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
    public function destroy(CourtImage $courtImage, Request $request)
    {
        if($courtImage->court->owner_id != $request->user()->id){
            return response()->json([
                'success'=>false,
                'message'=>'Unauthorized',
            ],403);
        }

        if(Storage::disk('public')->exists($courtImage->image)){
            Storage::disk('public')->delete($courtImage->image);
        }

        $courtImage->delete();

        return response()->json([
            'success'=>true,
            'message'=>'Image deleted successfuly.',
        ]);
    }
}
