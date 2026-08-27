<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Review;
use App\Models\Court;
use App\Http\Resources\ReviewResource;
use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use App\Models\Booking;
use OpenApi\Attributes as OA;

class ReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    #[OA\Get(
        path: '/api/courts/{court}/reviews',
        summary: 'Get court reviews',
        description: 'Fetch all reviews for a specific court.',
        tags: ['Reviews'],
        security: [
            ['sanctum' => []]
        ],

        parameters: [
            new OA\Parameter(
                name: 'court',
                in: 'path',
                required: true,
                description: 'Court ID',
                schema: new OA\Schema(
                    type: 'integer',
                    example: 1
                )
            ),
        ],

        responses: [
            new OA\Response(
                response: 200,
                description: 'Reviews fetched successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'success',
                            type: 'boolean',
                            example: true
                        ),
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Reviews fetched successfully'
                        ),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                type: 'object',
                                properties: [
                                    new OA\Property(
                                        property: 'id',
                                        type: 'integer',
                                        example: 1
                                    ),
                                    new OA\Property(
                                        property: 'user',
                                        type: 'object',
                                        description: 'User who submitted the review'
                                    ),
                                    new OA\Property(
                                        property: 'court',
                                        type: 'object',
                                        description: 'Court being reviewed'
                                    ),
                                    new OA\Property(
                                        property: 'rating',
                                        type: 'integer',
                                        example: 5
                                    ),
                                    new OA\Property(
                                        property: 'review',
                                        type: 'string',
                                        example: 'Excellent court and very good facilities.'
                                    ),
                                    new OA\Property(
                                        property: 'created_at',
                                        type: 'string',
                                        format: 'date-time'
                                    ),
                                    new OA\Property(
                                        property: 'updated_at',
                                        type: 'string',
                                        format: 'date-time'
                                    ),
                                ]
                            )
                        )
                    ]
                )
            ),

            new OA\Response(
                response: 404,
                description: 'Court not found'
            ),

            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            ),
        ]
    )]

    public function index(Court $court,)
    {
        $reviews = Review::with([
            'user',
            'court.owner',
            'court.images',
        ])->where('court_id', $court->id)->latest()->get();
        return response()->json([
            'success' => true,
            'message' => 'Reviews fetched successfuly',
            'data' => ReviewResource::collection($reviews)
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */

    #[OA\Post(
        path: '/api/reviews',
        summary: 'Create a review',
        description: 'Allows a user to review a court after completing a booking.',
        tags: ['Reviews'],
        security: [
            ['sanctum' => []]
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['court_id', 'rating', 'review'],
                properties: [
                    new OA\Property(
                        property: 'court_id',
                        type: 'integer',
                        example: 1
                    ),
                    new OA\Property(
                        property: 'rating',
                        type: 'integer',
                        minimum: 1,
                        maximum: 5,
                        example: 5
                    ),
                    new OA\Property(
                        property: 'review',
                        type: 'string',
                        example: 'Excellent court and very clean.'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Review created successfully'
            ),
            new OA\Response(
                response: 400,
                description: 'Court inactive or review already exists'
            ),
            new OA\Response(
                response: 403,
                description: 'User is not allowed to submit a review'
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error'
            ),
        ]
    )]
    public function store(StoreReviewRequest $request)

    {
        // Only users can review
        if ($request->user()->role !== 'user') {
            return response()->json([
                'success' => false,
                'message' => 'Only users can submit reviews.'
            ], 403);
        }

        // Court must exist and be active
        $court = Court::findOrFail($request->court_id);

        if ($court->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'This court is currently inactive.'
            ], 400);
        }

        // User must have a booking for this court
        $hasBooking = Booking::where('user_id', $request->user()->id)
            ->where('court_id', $court->id)
            ->where('booking_status', '!=', 'cancelled')
            ->exists();

        if (!$hasBooking) {
            return response()->json([
                'success' => false,
                'message' => 'You can only review courts that you have booked.'
            ], 403);
        }

        // Prevent duplicate reviews
        $exists = Review::where('user_id', $request->user()->id)
            ->where('court_id', $court->id)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'You have already reviewed this court.'
            ], 400);
        }

        $review = Review::create([
            'user_id' => $request->user()->id,
            'court_id' => $court->id,
            'rating' => $request->rating,
            'review' => $request->review,
        ]);

        $review->load([
            'user',
            'court.owner',
            'court.images',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Review created successfully.',
            'data' => new ReviewResource($review),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    #[OA\Get(
        path: '/api/reviews/{review}',
        summary: 'Get a review',
        description: 'Fetch details of a specific review.',
        tags: ['Reviews'],
        security: [
            ['sanctum' => []]
        ],

        parameters: [
            new OA\Parameter(
                name: 'review',
                in: 'path',
                required: true,
                description: 'Review ID',
                schema: new OA\Schema(
                    type: 'integer',
                    example: 1
                )
            ),
        ],

        responses: [
            new OA\Response(
                response: 200,
                description: 'Review fetched successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'success',
                            type: 'boolean',
                            example: true
                        ),
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Review fetched successfully.'
                        ),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(
                                    property: 'id',
                                    type: 'integer',
                                    example: 1
                                ),
                                new OA\Property(
                                    property: 'user',
                                    type: 'object',
                                    description: 'User who submitted the review'
                                ),
                                new OA\Property(
                                    property: 'court',
                                    type: 'object',
                                    description: 'Court being reviewed'
                                ),
                                new OA\Property(
                                    property: 'rating',
                                    type: 'integer',
                                    example: 5
                                ),
                                new OA\Property(
                                    property: 'review',
                                    type: 'string',
                                    example: 'Excellent court and very good facilities.'
                                ),
                                new OA\Property(
                                    property: 'created_at',
                                    type: 'string',
                                    format: 'date-time'
                                ),
                                new OA\Property(
                                    property: 'updated_at',
                                    type: 'string',
                                    format: 'date-time'
                                ),
                            ]
                        ),
                    ]
                )
            ),

            new OA\Response(
                response: 404,
                description: 'Review not found'
            ),

            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            ),
        ]
    )]
    public function show(Review $review)
    {
        $review->load([
            'user',
            'court.owner',
            'court.images'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Review fetched successfully.',
            'data' => new ReviewResource($review)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    #[OA\Put(
        path: '/api/reviews/{review}',
        summary: 'Update a review',
        description: 'Allows the user who created the review to update it.',
        tags: ['Reviews'],
        security: [
            ['sanctum' => []]
        ],
        parameters: [
            new OA\Parameter(
                name: 'review',
                in: 'path',
                required: true,
                description: 'Review ID',
                schema: new OA\Schema(type: 'integer'),
                example: 1
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'rating',
                        type: 'integer',
                        minimum: 1,
                        maximum: 5,
                        example: 4
                    ),
                    new OA\Property(
                        property: 'review',
                        type: 'string',
                        example: 'Updated review. The court was very good.'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Review updated successfully'
            ),
            new OA\Response(
                response: 403,
                description: 'Unauthorized'
            ),
            new OA\Response(
                response: 404,
                description: 'Review not found'
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error'
            ),
        ]
    )]

    public function update(UpdateReviewRequest $request, Review $review)
    {
        if ($review->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to update this review',
            ], 403);
        }
        $review->update($request->validated());
        $review->load([
            'user',
            'court.owner',
            'court.images',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Review updated successfuly',
            'data' => new ReviewResource($review)

        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */

    #[OA\Delete(
        path: '/api/reviews/{review}',
        summary: 'Delete a review',
        description: 'Allows the user who created the review to delete it.',
        tags: ['Reviews'],
        security: [
            ['sanctum' => []]
        ],
        parameters: [
            new OA\Parameter(
                name: 'review',
                in: 'path',
                required: true,
                description: 'Review ID',
                schema: new OA\Schema(type: 'integer'),
                example: 1
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Review deleted successfully'
            ),
            new OA\Response(
                response: 403,
                description: 'Unauthorized'
            ),
            new OA\Response(
                response: 404,
                description: 'Review not found'
            ),
        ]
    )]
    public function destroy(Review $review, Request $request)
    {
        if ($review->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to delete this review',
            ], 403);
        }
        $review->delete();
        return response()->json([
            'success' => true,
            'message' => 'Review deleted successfuly',
        ], 200);
    }
}
