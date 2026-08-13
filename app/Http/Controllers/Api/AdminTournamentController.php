<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateTournamentRequest;
use App\Http\Resources\TournamentResource;
use App\Models\Tournament;
use Illuminate\Http\Request;

class AdminTournamentController extends Controller
{
    /**
     * Display a listing of all tournaments.
     */
    public function index(Request $request)
    {
        $query = Tournament::with([
            'owner',
            'court',
            'court.images',
        ]);

        // Search by tournament title or court name
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->where('title', 'like', "%{$search}%")

                    ->orWhereHas('court', function ($court) use ($search) {
                        $court->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
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

        $tournaments = $query->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Tournaments fetched successfully.',
            'data' => TournamentResource::collection($tournaments),
            'pagination' => [
                'current_page' => $tournaments->currentPage(),
                'last_page' => $tournaments->lastPage(),
                'per_page' => $tournaments->perPage(),
                'total' => $tournaments->total(),
            ],
        ], 200);
    }

    /**
     * Display a specific tournament.
     */
    public function show(Tournament $tournament)
    {
        $tournament->load([
            'owner',
            'court',
            'court.images',
            'participants.user',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tournament fetched successfully.',
            'data' => new TournamentResource($tournament),
        ], 200);
    }

    /**
     * Update a tournament.
     */
    public function update(
        UpdateTournamentRequest $request,
        Tournament $tournament
    ) {
        $tournament->update($request->validated());

        $tournament->load([
            'owner',
            'court',
            'court.images',
            'participants.user',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tournament updated successfully.',
            'data' => new TournamentResource($tournament),
        ], 200);
    }

    /**
     * Cancel a tournament.
     */
    public function destroy(Tournament $tournament)
    {
        if ($tournament->status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Tournament is already cancelled.',
            ], 400);
        }

        $tournament->update([
            'status' => 'cancelled',
        ]);

        $tournament->load([
            'owner',
            'court',
            'court.images',
            'participants.user',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tournament cancelled successfully.',
            'data' => new TournamentResource($tournament),
        ], 200);
    }
}
