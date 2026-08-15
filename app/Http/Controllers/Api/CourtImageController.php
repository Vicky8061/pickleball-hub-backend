<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Court;
use App\Models\CourtImage;
use App\Http\Requests\StoreCourtImageRequest;
use App\Http\Resources\CourtImageResource;
use Illuminate\Support\Facades\Storage;
use OpenApi\Attributes as OA;

class CourtImageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    #[OA\Get(
        path: '/api/courts/{court}/images',
        summary: 'Get court images',
        description: 'Returns all images associated with a specific court.',
        tags: ['Court Images'],

        security: [
            ['sanctum' => []]
        ],

        parameters: [
            new OA\Parameter(
                name: 'court',
                in: 'path',
                required: true,
                description: 'Court ID.',
                schema: new OA\Schema(
                    type: 'integer'
                ),
                example: 1
            ),
        ],

        responses: [
            new OA\Response(
                response: 200,
                description: 'Court images fetched successfully'
            ),

            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            ),

            new OA\Response(
                response: 404,
                description: 'Court not found'
            ),
        ]
    )]

    public function index(Court $court)
    {
        $court->load('images');
        return response()->json([
            'success' => true,
            'message' => 'Court images fetched successfuly',
            'data' => CourtImageResource::collection($court->images)
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCourtImageRequest $request)
    {
        $court = Court::findOrFail($request->court_id);
        if ($request->user()->role != 'owner') {
            return response()->json([
                'success' => false,
                'message' => 'Only owners can upload court images.'
            ], 403);
        }

        if ($court->owner_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to upload images for this court ',
            ], 403);
        }

        if ($court->images()->count() + count($request->file('images')) > 5) {
            return response()->json([
                'success' => false,
                'message' => 'Maximum 5 images are allowed per court.'
            ], 400);
        }
        if ($request->boolean('is_primary')) {
            $court->images()->update([
                'is_primary' => false
            ]);
        }

        $uploadedImages = [];

        foreach ($request->file('images') as $index => $image) {

            $path = $image->store('court_images', 'public');

            $courtImage = CourtImage::create([
                'court_id' => $court->id,
                'image' => $path,
                'is_primary' => $request->boolean('is_primary') && $index === 0,
            ]);

            $uploadedImages[] = $courtImage;
        }
        return response()->json([
            'success' => true,
            'message' => 'Images uploaded successfully',
            'data' => $uploadedImages
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(CourtImage $courtImage)
    {
        return response()->json([
            'success' => true,
            'data' => $courtImage,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CourtImage $courtImage)
    {
        return response()->json([
            'success' => false,
            'message' => 'Update image feature is not available.',
        ], 405);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CourtImage $courtImage, Request $request)
    {
        if ($request->user()->role != 'owner') {
            return response()->json([
                'success' => false,
                'message' => 'Only owners can delete images.'
            ], 403);
        }

        if ($courtImage->court->owner_id != $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to delete this image',
            ], 403);
        }

        $wasPrimary = $courtImage->is_primary;
        $courtId = $courtImage->court_id;

        // Delete Physical Image
        if (Storage::disk('public')->exists($courtImage->image)) {
            Storage::disk('public')->delete($courtImage->image);
        }

        // Delete Database Record
        $courtImage->delete();

        // Assign new primary image if deleted image was primary
        if ($wasPrimary) {

            $newPrimary = CourtImage::where('court_id', $courtId)
                ->oldest()
                ->first();

            if ($newPrimary) {
                $newPrimary->update([
                    'is_primary' => true
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Image deleted successfully.',
        ], 200);
    }
}
