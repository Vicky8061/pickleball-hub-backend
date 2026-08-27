<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBannerRequest;
use App\Http\Requests\UpdateBannerRequest;
use App\Http\Resources\BannerResource;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use OpenApi\Attributes as OA;

class BannerController extends Controller
{
    /**
     * Display a listing of active banners for user dashboard or all banners for admin.
     */
    #[OA\Get(
        path: '/api/banners',
        summary: 'Get active banners',
        description: 'Returns a list of active banners for the user dashboard carousel.',
        tags: ['Banners'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Banners fetched successfully'
            )
        ]
    )]
    public function index(Request $request)
    {
        $query = Banner::query();

        // If admin is requesting, allow status filter or return all; otherwise default to active only
        if ($request->user() && $request->user()->role === 'admin') {
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
        } else {
            $query->where('status', 'active');
        }

        $banners = $query->latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'Banners fetched successfully',
            'data' => BannerResource::collection($banners),
        ], 200);
    }

    /**
     * Display the specified banner.
     */
    #[OA\Get(
        path: '/api/banners/{banner}',
        summary: 'Get banner details',
        description: 'Returns details of a specific banner.',
        tags: ['Banners'],
        parameters: [
            new OA\Parameter(
                name: 'banner',
                in: 'path',
                required: true,
                description: 'Banner ID',
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Banner details fetched successfully'
            ),
            new OA\Response(
                response: 404,
                description: 'Banner not found'
            )
        ]
    )]
    public function show(Banner $banner)
    {
        return response()->json([
            'success' => true,
            'message' => 'Banner details fetched successfully',
            'data' => new BannerResource($banner),
        ], 200);
    }

    /**
     * Store a newly created banner in storage (Admin only).
     */
    #[OA\Post(
        path: '/api/admin/banners',
        summary: 'Create a new banner',
        description: 'Allows an administrator to upload a new banner for the carousel.',
        tags: ['Admin Banners'],
        security: [
            ['sanctum' => []]
        ],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Banner created successfully'
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error'
            )
        ]
    )]
    public function store(StoreBannerRequest $request)
    {
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('banners', 'public');
        }

        $banner = Banner::create([
            'title' => $request->title,
            'image' => $imagePath,
            'redirect_url' => $request->redirect_url,
            'status' => $request->status ?? 'active',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Banner created successfully',
            'data' => new BannerResource($banner),
        ], 201);
    }

    /**
     * Update the specified banner in storage (Admin only).
     */
    #[OA\Post(
        path: '/api/admin/banners/{banner}',
        summary: 'Update a banner',
        description: 'Allows an administrator to update a banner title, image, URL, or status.',
        tags: ['Admin Banners'],
        security: [
            ['sanctum' => []]
        ],
        parameters: [
            new OA\Parameter(
                name: 'banner',
                in: 'path',
                required: true,
                description: 'Banner ID',
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Banner updated successfully'
            ),
            new OA\Response(
                response: 404,
                description: 'Banner not found'
            )
        ]
    )]
    public function update(UpdateBannerRequest $request, Banner $banner)
    {
        $data = $request->only(['title', 'redirect_url', 'status']);

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($banner->image && Storage::disk('public')->exists($banner->image)) {
                Storage::disk('public')->delete($banner->image);
            }
            $data['image'] = $request->file('image')->store('banners', 'public');
        }

        $banner->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Banner updated successfully',
            'data' => new BannerResource($banner),
        ], 200);
    }

    /**
     * Remove the specified banner from storage (Admin only).
     */
    #[OA\Delete(
        path: '/api/admin/banners/{banner}',
        summary: 'Delete a banner',
        description: 'Allows an administrator to delete a banner.',
        tags: ['Admin Banners'],
        security: [
            ['sanctum' => []]
        ],
        parameters: [
            new OA\Parameter(
                name: 'banner',
                in: 'path',
                required: true,
                description: 'Banner ID',
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Banner deleted successfully'
            ),
            new OA\Response(
                response: 404,
                description: 'Banner not found'
            )
        ]
    )]
    public function destroy(Banner $banner)
    {
        if ($banner->image && Storage::disk('public')->exists($banner->image)) {
            Storage::disk('public')->delete($banner->image);
        }

        $banner->delete();

        return response()->json([
            'success' => true,
            'message' => 'Banner deleted successfully',
        ], 200);
    }
}
