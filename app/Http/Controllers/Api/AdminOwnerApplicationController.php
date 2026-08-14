<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OwnerApplicationResource;
use App\Models\OwnerApplication;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminOwnerApplicationController extends Controller
{
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
            'successs' => true,
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
    public function show(OwnerApplication $ownerApplication)
    {
        $ownerApplication->load('user');

        return response()->json([
            'success' => true,
            'message' => 'Owner Application fetched successfully',
            'data' => new OwnerApplicationResource($ownerApplication)
        ], 200);
    }
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
