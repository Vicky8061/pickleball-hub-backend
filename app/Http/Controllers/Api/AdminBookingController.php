<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use Illuminate\Http\Request;

class AdminBookingController extends Controller
{
    public function index(Request $request){
        $query = Booking::with([
            'user',
            'court',
            'timeSlot'
        ]);

        //Search by User name or court name
        if($request->filled('search')){
            $search = $request->search;

            $query->where(function ($q) use($search){
                $q->whereHas('user',function ($user) use($search){
                    $user->where('name','like',"%{$search}%");
                });
                $q->OrWhereHas('court',function ($court) use($search){
                    $court->where('name','like',"%{$search}%");
                });

            });
        }

        //filter by booking status
        if($request->filled('status')){
            $query->where('Booking_status',$request->status);
        }

        //Latest / oldest

        switch($request->sort){
            case 'oldest':
                $query->oldest();
                break;
            case 'latest':
            default:
                $query->latest();
                break;
        }

        $bookings = $query->paginate(10);
        return response()->json([
            'success'=>true,
            'message'=>'Bookings fetched successfuly',
            'data'=>BookingResource::collection($bookings),
            'pagination'=>[
                'current_page'=> $bookings->currentPage(),
                'last_page'=> $bookings->lastPage(),
                'per_page'=> $bookings->perPage(),
                'total'=> $bookings->total(),
            ]
        ],200);
    }

    public function show(Booking $booking){
        $booking->load([
            'user',
            'court',
            'timeSlot'
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'Booking fetched successfuly',
            'data'=> new BookingResource($booking),
        ],200);
    }
}
