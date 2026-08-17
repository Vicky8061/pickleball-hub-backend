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

    #[OA\Post(
        path: '/api/owner/court-images',
        summary: 'Upload court images',
        description: 'Allows an authenticated owner to upload up to 5 images for their court.',
        tags: ['Owner Court Images'],

        security: [
            ['sanctum' => []]
        ],

        requestBody: new OA\RequestBody(
            required: true,

            content: new OA\MediaType(
                mediaType: 'multipart/form-data',

                schema: new OA\Schema(
                    type: 'object',

                    required: [
                        'court_id',
                        'images[]'
                    ],

                    properties: [

                        new OA\Property(
                            property: 'court_id',
                            type: 'integer',
                            example: 6,
                            description: 'ID of the court.'
                        ),

                        new OA\Property(
                            property: 'images[]',
                            type: 'array',
                            description: 'Court images. Upload between 1 and 5 images.',
                            items: new OA\Items(
                                type: 'string',
                                format: 'binary'
                            ),
                            minItems: 1,
                            maxItems: 5
                        ),
                        new OA\Property(
                            property: 'is_primary',
                            type: 'boolean',
                            nullable: true,
                            example: true,
                            description: 'If true, the first uploaded image will become the primary image.'
                        ),
                    ]
                )
            )
        ),

        responses: [

            new OA\Response(
                response: 201,
                description: 'Images uploaded successfully.'
            ),

            new OA\Response(
                response: 400,
                description: 'Maximum 5 images are allowed for this court.'
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
    public function store(StoreCourtImageRequest $request)
    {
        // -----------------------------------------
        // 1. Only owners can upload images
        // -----------------------------------------

        if ($request->user()->role !== 'owner') {
            return response()->json([
                'success' => false,
                'message' => 'Only owners can upload court images.',
            ], 403);
        }


        // -----------------------------------------
        // 2. Find court
        // -----------------------------------------

        $court = Court::findOrFail($request->court_id);


        // -----------------------------------------
        // 3. Check court ownership
        // -----------------------------------------

        if ($court->owner_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to upload images for this court.',
            ], 403);
        }


        // -----------------------------------------
        // 4. Get uploaded images
        // -----------------------------------------

        $images = $request->file('images');


        // -----------------------------------------
        // 5. Safety check
        // -----------------------------------------

        if (!$images || !is_array($images)) {
            return response()->json([
                'success' => false,
                'message' => 'Please upload at least one image.',
            ], 422);
        }


        // -----------------------------------------
        // 6. Maximum 5 images per court
        // -----------------------------------------

        $existingImages = $court->images()->count();
        $newImages = count($images);

        if (($existingImages + $newImages) > 5) {
            return response()->json([
                'success' => false,
                'message' => 'Maximum 5 images are allowed for this court.',
            ], 400);
        }


        // -----------------------------------------
        // 7. Primary image
        // -----------------------------------------

        $isPrimary = $request->boolean('is_primary');


        // If new primary image is selected,
        // remove primary status from existing images.

        if ($isPrimary) {
            $court->images()->update([
                'is_primary' => false,
            ]);
        }


        // -----------------------------------------
        // 8. Upload images
        // -----------------------------------------

        $uploadedImages = [];

        foreach ($images as $index => $image) {

            $path = $image->store(
                'court_images',
                'public'
            );


            // -----------------------------------------
            // 9. Create database record
            // -----------------------------------------

            $courtImage = CourtImage::create([
                'court_id' => $court->id,
                'image' => $path,

                // Only first image becomes primary
                'is_primary' => $isPrimary && $index === 0,
            ]);


            $uploadedImages[] = $courtImage;
        }


        // -----------------------------------------
        // 10. Response
        // -----------------------------------------

        return response()->json([
            'success' => true,
            'message' => 'Images uploaded successfully.',
            'data' => $uploadedImages,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(CourtImage $courtImage)
    {
        return response()->json([
            'success' => true,
            'message' => 'Court image fetched successfully.',
            'data' => $courtImage,
        ], 200);
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
    #[OA\Delete(
        path: '/api/owner/court-images/{courtImage}',
        summary: 'Delete a court image',
        description: 'Allows an authenticated owner to delete an image belonging to their court. If the deleted image is primary, another image is automatically assigned as primary.',
        tags: ['Owner Court Images'],
        security: [
            ['sanctum' => []]
        ],

        parameters: [
            new OA\Parameter(
                name: 'courtImage',
                in: 'path',
                required: true,
                description: 'Court image ID',
                schema: new OA\Schema(type: 'integer'),
                example: 1
            ),
        ],

        responses: [
            new OA\Response(
                response: 200,
                description: 'Image deleted successfully.'
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
                description: 'Court image not found.'
            ),
        ]
    )]

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
