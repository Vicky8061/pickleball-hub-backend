<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReviewResource;
use App\Models\Review;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class AdminReviewController extends Controller
{
    // --------------------------------------------------
    // GET ALL REVIEWS
    // --------------------------------------------------

    #[OA\Get(
        path: '/api/admin/reviews',
        summary: 'Get all reviews',
        description: 'Fetch all reviews with user and court details. Supports search, rating filtering, sorting and pagination.',
        tags: ['Admin - Reviews'],
        security: [['sanctum' => []]],

        parameters: [

            new OA\Parameter(
                name: 'search',
                in: 'query',
                required: false,
                description: 'Search review by review text, user name or court name.',
                schema: new OA\Schema(
                    type: 'string'
                ),
                example: 'excellent court'
            ),

            new OA\Parameter(
                name: 'rating',
                in: 'query',
                required: false,
                description: 'Filter reviews by rating from 1 to 5.',
                schema: new OA\Schema(
                    type: 'integer',
                    minimum: 1,
                    maximum: 5
                ),
                example: 5
            ),

            new OA\Parameter(
                name: 'sort',
                in: 'query',
                required: false,
                description: 'Sort reviews by latest or oldest.',
                schema: new OA\Schema(
                    type: 'string',
                    enum: ['latest', 'oldest'],
                    default: 'latest'
                ),
                example: 'latest'
            ),

            new OA\Parameter(
                name: 'page',
                in: 'query',
                required: false,
                description: 'Page number.',
                schema: new OA\Schema(
                    type: 'integer',
                    default: 1
                ),
                example: 1
            )
        ],

        responses: [

            new OA\Response(
                response: 200,
                description: 'Reviews fetched successfully.'
            ),

            new OA\Response(
                response: 401,
                description: 'Unauthenticated.'
            ),

            new OA\Response(
                response: 403,
                description: 'Only admins can access reviews.'
            ),

            new OA\Response(
                response: 422,
                description: 'Rating must be between 1 and 5.'
            )
        ]
    )]
    public function index(Request $request)
    {
        $query = Review::with([
            'user',
            'court',
        ]);

        // Search by review text, user name or court name
        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->where(
                    'review',
                    'like',
                    "%{$search}%"
                )

                    ->orWhereHas('user', function ($user) use ($search) {

                        $user->where(
                            'name',
                            'like',
                            "%{$search}%"
                        );
                    })

                    ->orWhereHas('court', function ($court) use ($search) {

                        $court->where(
                            'name',
                            'like',
                            "%{$search}%"
                        );
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

            $query->where(
                'rating',
                $rating
            );
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

        // Pagination
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


    // --------------------------------------------------
    // GET SINGLE REVIEW
    // --------------------------------------------------

    #[OA\Get(
        path: '/api/admin/reviews/{review}',
        summary: 'Get review details',
        description: 'Fetch complete details of a specific review including user and court information.',
        tags: ['Admin - Reviews'],
        security: [['sanctum' => []]],

        parameters: [

            new OA\Parameter(
                name: 'review',
                in: 'path',
                required: true,
                description: 'Review ID.',
                schema: new OA\Schema(
                    type: 'integer'
                ),
                example: 5
            )
        ],

        responses: [

            new OA\Response(
                response: 200,
                description: 'Review fetched successfully.'
            ),

            new OA\Response(
                response: 401,
                description: 'Unauthenticated.'
            ),

            new OA\Response(
                response: 403,
                description: 'Only admins can access reviews.'
            ),

            new OA\Response(
                response: 404,
                description: 'Review not found.'
            )
        ]
    )]
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


    // --------------------------------------------------
    // DELETE REVIEW
    // --------------------------------------------------

    #[OA\Delete(
        path: '/api/admin/reviews/{review}',
        summary: 'Delete review',
        description: 'Permanently delete a specific review.',
        tags: ['Admin - Reviews'],
        security: [['sanctum' => []]],

        parameters: [

            new OA\Parameter(
                name: 'review',
                in: 'path',
                required: true,
                description: 'Review ID.',
                schema: new OA\Schema(
                    type: 'integer'
                ),
                example: 5
            )
        ],

        responses: [

            new OA\Response(
                response: 200,
                description: 'Review deleted successfully.'
            ),

            new OA\Response(
                response: 401,
                description: 'Unauthenticated.'
            ),

            new OA\Response(
                response: 403,
                description: 'Only admins can delete reviews.'
            ),

            new OA\Response(
                response: 404,
                description: 'Review not found.'
            )
        ]
    )]
    public function destroy(Review $review)
    {
        $review->delete();

        return response()->json([
            'success' => true,
            'message' => 'Review deleted successfully.',
        ], 200);
    }
}
