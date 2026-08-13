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

class ReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
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

        // User must have completed booking
        $hasCompletedBooking = Booking::where('user_id', $request->user()->id)
            ->where('court_id', $court->id)
            ->where('booking_status', 'completed')
            ->exists();

        if (!$hasCompletedBooking) {
            return response()->json([
                'success' => false,
                'message' => 'You can only review courts after completing a booking.'
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
