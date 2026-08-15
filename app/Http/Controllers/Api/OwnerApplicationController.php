<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOwnerApplicationRequest;
use App\Models\OwnerApplication;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class OwnerApplicationController extends Controller
{
    /**
     * Apply to become an owner.
     */
    #[OA\Post(
        path: '/api/owner/apply',
        summary: 'Apply to become an owner',
        description: 'Allows an authenticated normal user to submit an application to become an owner. A verification document is required.',
        tags: ['Owner Application'],

        security: [
            ['sanctum' => []]
        ],

        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: [
                        'business_name',
                        'phone',
                        'address',
                        'city',
                        'state',
                        'pincode',
                        'document'
                    ],
                    properties: [
                        new OA\Property(
                            property: 'business_name',
                            type: 'string',
                            example: 'Vicky Pickleball Club'
                        ),

                        new OA\Property(
                            property: 'phone',
                            type: 'string',
                            example: '9876543210'
                        ),

                        new OA\Property(
                            property: 'address',
                            type: 'string',
                            example: '123 Main Road, Adajan'
                        ),

                        new OA\Property(
                            property: 'city',
                            type: 'string',
                            example: 'Surat'
                        ),

                        new OA\Property(
                            property: 'state',
                            type: 'string',
                            example: 'Gujarat'
                        ),

                        new OA\Property(
                            property: 'pincode',
                            type: 'string',
                            example: '395009'
                        ),

                        new OA\Property(
                            property: 'experience',
                            type: 'string',
                            nullable: true,
                            example: '5 years of experience in sports management.'
                        ),

                        new OA\Property(
                            property: 'description',
                            type: 'string',
                            nullable: true,
                            example: 'I want to start and manage a professional pickleball facility.'
                        ),

                        new OA\Property(
                            property: 'document',
                            type: 'string',
                            format: 'binary',
                            description: 'Verification document. Allowed formats: PDF, JPG, JPEG, PNG. Maximum size: 5 MB.'
                        ),
                    ]
                )
            )
        ),

        responses: [
            new OA\Response(
                response: 201,
                description: 'Owner application submitted successfully'
            ),

            new OA\Response(
                response: 400,
                description: 'User already has a pending owner application'
            ),

            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            ),

            new OA\Response(
                response: 403,
                description: 'Only normal users can apply'
            ),

            new OA\Response(
                response: 422,
                description: 'Validation error'
            ),
        ]
    )]
    public function applyForOwner(StoreOwnerApplicationRequest $request)
    {
        $user = $request->user();

        // Only normal users can apply
        if ($user->role !== 'user') {
            return response()->json([
                'success' => false,
                'message' => 'Only normal users can apply to become an owner.',
            ], 403);
        }

        // Check for an existing pending application
        $pendingApplication = OwnerApplication::where('user_id', $user->id)
            ->where('status', 'pending')
            ->exists();

        if ($pendingApplication) {
            return response()->json([
                'success' => false,
                'message' => 'You already have a pending owner application.',
            ], 400);
        }

        // Store verification document
        $documentPath = $request->file('document')
            ->store('owner_documents', 'public');

        // Create application
        $application = OwnerApplication::create([
            'user_id' => $user->id,
            'business_name' => $request->business_name,
            'phone' => $request->phone,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'pincode' => $request->pincode,
            'experience' => $request->experience,
            'description' => $request->description,
            'document' => $documentPath,
            'status' => 'pending',
        ]);

        $application->load('user');

        return response()->json([
            'success' => true,
            'message' => 'Owner application submitted successfully. Please wait for admin approval.',
            'data' => $application,
        ], 201);
    }
    /**
     * Get my owner application.
     */
    #[OA\Get(
        path: '/api/owner/application',
        summary: 'Get my owner application',
        description: 'Returns the authenticated user\'s latest owner application and its current status.',
        tags: ['Owner Application'],

        security: [
            ['sanctum' => []]
        ],

        responses: [
            new OA\Response(
                response: 200,
                description: 'Owner application fetched successfully or no application has been submitted yet'
            ),

            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            ),

            new OA\Response(
                response: 403,
                description: 'Only normal users can access owner applications'
            ),
        ]
    )]


    public function myApplication(Request $request)
    {
        $user = $request->user();

        // Only normal users can check owner application
        if ($user->role !== 'user') {
            return response()->json([
                'success' => false,
                'message' => 'Only normal users can access owner applications.',
            ], 403);
        }

        $application = OwnerApplication::where('user_id', $user->id)
            ->latest()
            ->first();

        // No application found
        if (!$application) {
            return response()->json([
                'success' => true,
                'message' => 'You have not submitted an owner application yet.',
                'data' => null,
            ], 200);
        }

        return response()->json([
            'success' => true,
            'message' => 'Owner application fetched successfully.',
            'data' => $application,
        ], 200);
    }
}
