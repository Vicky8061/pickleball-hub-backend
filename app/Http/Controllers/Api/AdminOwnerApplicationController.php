<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OwnerApplicationResource;
use App\Models\OwnerApplication;
use Carbon\Carbon;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class AdminOwnerApplicationController extends Controller
{
    #[OA\Get(
        path: '/api/admin/owner-applications',
        summary: 'Get owner applications',
        description: 'Returns a paginated list of owner applications for admin users. Supports searching, status filtering, sorting, and pagination.',
        tags: ['Admin - Owner Applications'],

        security: [
            ['sanctum' => []]
        ],

        parameters: [
            new OA\Parameter(
                name: 'search',
                in: 'query',
                required: false,
                description: 'Search by applicant name, email, or business name.',
                schema: new OA\Schema(
                    type: 'string'
                ),
                example: 'Vicky'
            ),

            new OA\Parameter(
                name: 'status',
                in: 'query',
                required: false,
                description: 'Filter applications by status.',
                schema: new OA\Schema(
                    type: 'string',
                    enum: ['pending', 'approved', 'rejected']
                ),
                example: 'pending'
            ),

            new OA\Parameter(
                name: 'sort',
                in: 'query',
                required: false,
                description: 'Sort applications by creation date.',
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
                description: 'Page number for pagination.',
                schema: new OA\Schema(
                    type: 'integer',
                    minimum: 1,
                    default: 1
                ),
                example: 1
            ),
        ],

        responses: [
            new OA\Response(
                response: 200,
                description: 'Owner applications fetched successfully'
            ),

            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            ),

            new OA\Response(
                response: 403,
                description: 'Unauthorized. Admin access required.'
            ),
        ]
    )]
    public function index(Request $request)
    {
        $query = OwnerApplication::with('user');

        //Search by name,email and business name
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('business_name', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($user) use ($search) {
                        $user->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        //filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        //Sorting
        switch ($request->sort) {
            case 'oldest':
                $query->oldest();
                break;
            case 'latest':
            default:
                $query->latest();
                break;
        }
        $applications = $query->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Owner Applications fetched successfully',
            'data' => OwnerApplicationResource::collection($applications),
            'pagination' => [
                'current_page' => $applications->currentPage(),
                'last_page' => $applications->lastPage(),
                'per_page' => $applications->perPage(),
                'total' => $applications->total(),
            ]
        ], 200);
    }
    #[OA\Get(
        path: '/api/admin/owner-applications/{ownerApplication}',
        summary: 'Get owner application details',
        description: 'Returns the details of a specific owner application for admin review.',
        tags: ['Admin - Owner Applications'],

        security: [
            ['sanctum' => []]
        ],

        parameters: [
            new OA\Parameter(
                name: 'ownerApplication',
                in: 'path',
                required: true,
                description: 'Owner application ID.',
                schema: new OA\Schema(
                    type: 'integer'
                ),
                example: 1
            ),
        ],

        responses: [
            new OA\Response(
                response: 200,
                description: 'Owner application fetched successfully'
            ),

            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            ),

            new OA\Response(
                response: 403,
                description: 'Unauthorized. Admin access required.'
            ),

            new OA\Response(
                response: 404,
                description: 'Owner application not found'
            ),
        ]
    )]
    public function show(OwnerApplication $ownerApplication)
    {
        $ownerApplication->load('user');

        return response()->json([
            'success' => true,
            'message' => 'Owner Application fetched successfully',
            'data' => new OwnerApplicationResource($ownerApplication)
        ], 200);
    }
    #[OA\Patch(
        path: '/api/admin/owner-applications/{ownerApplication}/approve',
        summary: 'Approve owner application',
        description: 'Approves an owner application and promotes the associated user to owner status.',
        tags: ['Admin - Owner Applications'],

        security: [
            ['sanctum' => []]
        ],

        parameters: [
            new OA\Parameter(
                name: 'ownerApplication',
                in: 'path',
                required: true,
                description: 'Owner application ID.',
                schema: new OA\Schema(
                    type: 'integer'
                ),
                example: 1
            ),
        ],

        requestBody: new OA\RequestBody(
            required: false,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'admin_note',
                        type: 'string',
                        maxLength: 500,
                        nullable: true,
                        example: 'Documents verified. Owner application approved.'
                    ),
                ]
            )
        ),

        responses: [
            new OA\Response(
                response: 200,
                description: 'Owner application approved successfully'
            ),

            new OA\Response(
                response: 400,
                description: 'Application is already approved or has been rejected'
            ),

            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            ),

            new OA\Response(
                response: 403,
                description: 'Unauthorized. Admin access required.'
            ),

            new OA\Response(
                response: 404,
                description: 'Owner application not found'
            ),
        ]
    )]
    public function approve(Request $request, OwnerApplication $ownerApplication)
    {
        if ($ownerApplication->status == 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Owner Application is already approved',
            ], 400);
        }

        if ($ownerApplication->status == 'rejected') {
            return response()->json([
                'success' => false,
                'message' => 'Rejected application cannot be approved.',
            ], 400);
        }

        // Update application
        $ownerApplication->update([
            'status' => 'approved',
            'admin_note' => $request->admin_note,
            'reviewed_at' => Carbon::now(),
        ]);

        // Promote user
        $ownerApplication->user()->update([
            'role' => 'owner',
            'status' => 'active',
        ]);

        $ownerApplication->load('user');

        return response()->json([
            'success' => true,
            'message' => 'Owner Application approved successfully',
            'data' => new OwnerApplicationResource($ownerApplication),
        ], 200);
    }

    #[OA\Patch(
        path: '/api/admin/owner-applications/{ownerApplication}/reject',
        summary: 'Reject owner application',
        description: 'Rejects an owner application. An admin rejection reason is required.',
        tags: ['Admin - Owner Applications'],

        security: [
            ['sanctum' => []]
        ],

        parameters: [
            new OA\Parameter(
                name: 'ownerApplication',
                in: 'path',
                required: true,
                description: 'Owner application ID.',
                schema: new OA\Schema(
                    type: 'integer'
                ),
                example: 1
            ),
        ],

        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['admin_note'],
                properties: [
                    new OA\Property(
                        property: 'admin_note',
                        type: 'string',
                        maxLength: 500,
                        example: 'Verification document is not valid.'
                    ),
                ]
            )
        ),

        responses: [
            new OA\Response(
                response: 200,
                description: 'Owner application rejected successfully'
            ),

            new OA\Response(
                response: 400,
                description: 'Application is already rejected, already approved, or can no longer be rejected'
            ),

            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            ),

            new OA\Response(
                response: 403,
                description: 'Unauthorized. Admin access required.'
            ),

            new OA\Response(
                response: 404,
                description: 'Owner application not found'
            ),

            new OA\Response(
                response: 422,
                description: 'Validation error. Rejection reason is required.'
            ),
        ]
    )]
    public function reject(Request $request, OwnerApplication $ownerApplication)
    {
        //Validate rejection reason
        $request->validate([
            'admin_note' => 'required|string|max:500'
        ]);

        //Already Rejected
        if ($ownerApplication->status == 'rejected') {
            return response()->json([
                'success' => false,
                'message' => 'Application is already rejected'
            ], 400);
        }
        //Already approved

        if ($ownerApplication->status == 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Approved application cannot be rejected'
            ], 400);
        }
        if ($ownerApplication->user->role !== 'user') {
            return response()->json([
                'success' => false,
                'message' => 'This application can no longer be rejected.'
            ], 400);
        }

        //Reject Application
        $ownerApplication->update([
            'status' => 'rejected',
            'admin_note' => $request->admin_note,
            'reviewed_at' => Carbon::now(),

        ]);
        $ownerApplication->load('user');

        return response()->json([
            'success' => true,
            'message' => 'Owner Application rejected successfully',
            'data' => new OwnerApplicationResource($ownerApplication)
        ], 200);
    }
}
