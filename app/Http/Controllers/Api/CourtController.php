<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCourtRequest;
use App\Http\Requests\UpdateCourtRequest;
use App\Http\Resources\CourtResource;
use Illuminate\Http\Request;
use App\Models\Court;

class CourtController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Court::with([
            'owner',
            'images',
        ]);

        //Search by court name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        //filter by court type
        if ($request->filled('court_type')) {
            $query->where('court_type', $request->court_type);
        }



        if ($request->user()->role !== 'admin') {
            $query->where('status', 'active');
        } elseif ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        //Filter by city/address
        if ($request->filled('city')) {
            $query->where('address', 'like', '%' . $request->city . '%');
        }

        //price range
        if ($request->filled('price_min')) {
            $query->where('price_per_hour', '>=', $request->price_min);
        }
        if ($request->filled('price_max')) {
            $query->where('price_per_hour', '<=', $request->price_max);
        }

        //Sorting
        switch ($request->sort) {
            case 'price_low':
                $query->orderBy('price_per_hour');
                break;
            case 'price_high':
                $query->orderByDesc('price_per_hour');
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

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCourtRequest $request)
    {
        if ($request->user()->role !== 'owner') {
            return response()->json([
                'success' => false,
                'message' => 'Only owners can create courts.'
            ], 403);
        }
        $court = Court::create([
            'owner_id'       => $request->user()->id,
            'name'           => $request->name,
            'description'    => $request->description,
            'address'        => $request->address,
            'latitude'       => $request->latitude,
            'longitude'      => $request->longitude,
            'price_per_hour' => $request->price_per_hour,
            'court_type'     => $request->court_type,
            'opening_time'   => $request->opening_time,
            'closing_time'   => $request->closing_time,
            'status'         => 'active',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Court created successfully.',
            'data' => new CourtResource($court->load('owner', 'images')),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Court $court, Request $request)
    {
        $court->load([
            'owner',
            'images',
            'timeSlots',
            'reviews',
            'wishlists',
            'tournaments',
        ]);
        if (
            $court->status === 'inactive' &&
            $request->user()->role !== 'admin'
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Court not found.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Court details fetchec successfuly',
            'data' => new CourtResource($court),
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCourtRequest $request, Court $court)
    {
        if ($court->owner_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to update this court.'
            ], 403);
        }

        $court->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Court Updated Successfuly',
            'data' => new CourtResource($court->load('owner', 'images')),
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Court $court, Request $request)
    {
        if ($court->owner_id != $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to delete this court.'
            ], 403);
        }


        $court->delete();

        if ($court->bookings()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete court because bookings exist.'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Court deleted Successfuly',
        ], 200);
    }
}
