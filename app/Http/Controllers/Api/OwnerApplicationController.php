<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOwnerApplicationRequest;
use App\Models\OwnerApplication;
use Illuminate\Http\Request;

class OwnerApplicationController extends Controller
{
    /**
     * Apply to become an owner.
     */
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
