<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReviewResource;
use App\Models\Review;
use Illuminate\Http\Request;

class AdminReviewController extends Controller
{
    /**
     * Display a listing of all reviews.
     */
    public function index(Request $request)
    {
        $query = Review::with([
            'user',
            'court',
        ]);

        // Search by review text, user name, or court name
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('review', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($user) use ($search) {
                        $user->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('court', function ($court) use ($search) {
                        $court->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by rating
        if ($request->filled('rating')) {
            $rating = (int) $request->rating;

            if ($rating < 1 || $rating > 5) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rating must be between 1 and 5.',
                ], 422);
            }

            $query->where('rating', $rating);
        }

        // Sorting
        switch ($request->sort) {
            case 'oldest':
                $query->oldest();
                break;

            case 'latest':
            default:
                $query->latest();
                break;
        }

        $reviews = $query->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Reviews fetched successfully.',
            'data' => ReviewResource::collection($reviews),
            'pagination' => [
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'per_page' => $reviews->perPage(),
                'total' => $reviews->total(),
            ],
        ], 200);
    }

    /**
     * Display the specified review.
     */
    public function show(Review $review)
    {
        $review->load([
            'user',
            'court',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Review fetched successfully.',
            'data' => new ReviewResource($review),
        ], 200);
    }

    /**
     * Remove the specified review.
     */
    public function destroy(Review $review)
    {
        $review->delete();

        return response()->json([
            'success' => true,
            'message' => 'Review deleted successfully.',
        ], 200);
    }
}