<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWishlistRequest;
use Illuminate\Http\Request;
use App\Models\Wishlist;
use App\Models\Court;
use App\Http\Resources\WishlistResource;
use OpenApi\Attributes as OA;


class WishlistController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    #[OA\Get(
        path: '/api/wishlists',
        summary: 'Get user wishlist',
        description: 'Returns all courts added to the authenticated user wishlist.',
        tags: ['Wishlist'],
        security: [
            ['sanctum' => []]
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Wishlists fetched successfully'
            ),
            new OA\Response(
                response: 403,
                description: 'Only users can access wishlist'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            )
        ]
    )]
    public function index(Request $request)
    {
        if ($request->user()->role !== 'user') {
            return response()->json([
                'success' => false,
                'message' => 'Only users can access wishlist.'
            ], 403);
        }
        $wishlists = Wishlist::with([
            'court.owner',
            'court.images',
        ])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();
        return response()->json([
            'success' => true,
            'message' => 'Wishlists fetched successfully',
            'data' => WishlistResource::collection($wishlists)
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    #[OA\Post(
        path: '/api/wishlists',
        summary: 'Add court to wishlist',
        description: 'Adds an active court to the authenticated user wishlist.',
        tags: ['Wishlist'],
        security: [
            ['sanctum' => []]
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['court_id'],
                properties: [
                    new OA\Property(
                        property: 'court_id',
                        type: 'integer',
                        example: 1
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Court added to wishlist successfully'
            ),
            new OA\Response(
                response: 400,
                description: 'Court already in wishlist or inactive court'
            ),
            new OA\Response(
                response: 403,
                description: 'Only users can add courts to wishlist'
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            )
        ]
    )]
    public function store(StoreWishlistRequest $request)
    {
        if ($request->user()->role !== 'user') {
            return response()->json([
                'success' => false,
                'message' => 'Only users can add court to  wishlist.'
            ], 403);
        }
        //Check duplicate wishlist
        $exists = Wishlist::where('user_id', $request->user()->id)
            ->where('court_id', $request->court_id)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Court already in wishlist',
            ], 400);
        }
        $court = Court::findOrFail($request->court_id);

        if ($court->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Inactive courts cannot be added to wishlist.'
            ], 400);
        }

        $wishlist = Wishlist::create([
            'user_id' => $request->user()->id,
            'court_id' => $request->court_id,
        ]);

        $wishlist->load([
            'court.owner',
            'court.images'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Court added to  wishlist',
            'data' => new WishlistResource($wishlist)
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    #[OA\Get(
        path: '/api/wishlists/{wishlist}',
        summary: 'Get wishlist item',
        description: 'Returns a specific wishlist item belonging to the authenticated user.',
        tags: ['Wishlist'],
        security: [
            ['sanctum' => []]
        ],
        parameters: [
            new OA\Parameter(
                name: 'wishlist',
                description: 'Wishlist ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
                example: 1
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Wishlist fetched successfully'
            ),
            new OA\Response(
                response: 403,
                description: 'Unauthorized'
            ),
            new OA\Response(
                response: 404,
                description: 'Wishlist not found'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            )
        ]
    )]
    public function show(Wishlist $wishlist)
    {
        if ($wishlist->user_id !== request()->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.'
            ], 403);
        }

        $wishlist->load([
            'court.owner',
            'court.images',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Wishlist fetched successfully.',
            'data' => new WishlistResource($wishlist)
        ]);
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
    #[OA\Delete(
        path: '/api/wishlists/{court}',
        summary: 'Remove court from wishlist',
        description: 'Removes a court from the authenticated user wishlist.',
        tags: ['Wishlist'],
        security: [
            ['sanctum' => []]
        ],
        parameters: [
            new OA\Parameter(
                name: 'court',
                description: 'Court ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
                example: 1
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Court removed from wishlist successfully'
            ),
            new OA\Response(
                response: 403,
                description: 'Only users can remove courts from wishlist'
            ),
            new OA\Response(
                response: 404,
                description: 'Court not found in wishlist'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            )
        ]
    )]
    public function destroy(Court $court, Request $request,)
    {
        if ($request->user()->role !== 'user') {
            return response()->json([
                'success' => false,
                'message' => 'Only users can remove courts from  wishlist.'
            ], 403);
        }
        $wishlist = Wishlist::where('user_id', $request->user()->id)
            ->where('court_id', $court->id)
            ->first();
        if (!$wishlist) {
            return response()->json([
                'success' => false,
                'message' => 'Court not found in wishlist',
            ], 404);
        }

        $wishlist->delete();

        return response()->json([
            'success' => true,
            'message' =>  'Court removed from wishlist',
        ], 200);
    }
}
