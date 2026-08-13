<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateCourtRequest;
use App\Http\Resources\CourtResource;
use App\Models\Court;
use Illuminate\Http\Request;

class AdminCourtController extends Controller
{
    public function index(Request $request)
    {

        $query = Court::with([
            'owner',
            'images',
        ]);

        //Search
        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        //filter by status
        if ($request->filled('status')) {
            $request->validated([
                'status'=> 'in:active,inactive'
            ]);
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

        $courts = $query->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Courts fetched successfuly',
            'data' => CourtResource::collection($courts),
            'pagination' => [
                'current_page' => $courts->currentPage(),
                'last_page' => $courts->lastPage(),
                'per_page' => $courts->perPage(),
                'total' => $courts->total(),
            ]
        ], 200);
    }

    public function show(Court $court)
    {
        $court->load([
            'owner',
            'images',
            'timeSlots',
            'reviews'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Court fetched successfuly',
            'data' => new CourtResource($court),
        ], 200);
    }
    public function update(UpdateCourtRequest $request, Court $court)
    {
        $court->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Court updated successfully.',
            'data' => new CourtResource(
                $court->load(['owner', 'images'])
            )
        ], 200);
    }
    public function updateStatus(Request $request, Court $court)
    {
        $request->validate([
            'status' => 'required|in:active,inactive',
        ]);

        $court->update([
            'status' => $request->status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Court status updated successfully.',
            'data' => new CourtResource(
                $court->load(['owner', 'images'])
            )
        ], 200);
    }
    public function destroy(Court $court)
    {
        if ($court->status === 'inactive') {

            return response()->json([
                'success' => false,
                'message' => 'Court is already inactive.'
            ], 400);
        }

        $court->update([
            'status' => 'inactive'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Court deactivated successfully.',
            'data' => new CourtResource(
                $court->load(['owner', 'images'])
            )
        ], 200);
    }
}
