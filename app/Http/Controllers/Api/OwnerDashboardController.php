<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Court;
use App\Models\Booking;
use App\Models\Review;
use App\Models\Tournament;
use Carbon\Carbon;

class OwnerDashboardController extends Controller
{
    public function index(Request $request){
        $ownerId = $request->user()->id;

        //courts

        $totalCourts = Court::where('owner_id', $ownerId)->count();

        $activeCourts = Court::where('owner_id', $ownerId)
            ->where('status', 'active')
            ->count();
        
        $inactiveCourts = Court::where('owner_id', $ownerId)
            ->where('status','inactive')
            ->count();
        
        //bookings

        $totalBookings = Booking::whereHas('court' ,function($query) use ($ownerId){
            $query->where('owner_id', $ownerId);
        })->count();

        $todayBookings = Booking::whereDate('booking_date', Carbon::today())
            ->whereHas('court',function ($query) use ($ownerId){
                $query->where('owner_id',$ownerId);
            })->count();

        $pendingBookings = Booking::where('booking_status','pending')
            ->whereHas('court',function($query) use ($ownerId){
                $query->where('owner_id',$ownerId);
            })->count();

        $completedBookings =Booking::where('booking_status','complete')
            ->whereHas('court',function($query) use ($ownerId){
                $query->where('owner_id',$ownerId);
            })->count();
        
        $cancelledBookings = Booking::where('booking_status','cancel')
            ->whereHas('court',function ($query) use ($ownerId){
                $query->where('owner_id',$ownerId);
            })->count();
        
        //revnue

        $totalRevenue = Booking::where('payment_status','paid')
            ->whereHas('court',function ($query) use ($ownerId){
                $query->where('owner_id',$ownerId);
            })->sum('total_amount');
        
        //Tournaments

        $totalTournaments = Tournament::where('owner_id',$ownerId)->count();

        $activeTournaments = Tournament::where('owner_id',$ownerId)
            ->where('status','ongoing')
            ->count();
        
        $completeTournaments = Tournament::where('owner_id',$ownerId)
            ->where('status','complete')
            ->count();
        
        $cancelledTournaments = Tournament::where('owner_id',$ownerId)
            ->where('status','cancel')
            ->count();
        
        //reviews

        $totalReviews = Review::whereHas('court',function ($query) use ($ownerId){
            $query->where('owner_id',$ownerId);
        })->count();

        $averageRating = Review::whereHas('court',function($query) use ($ownerId){
            $query->where('owner_id',$ownerId);
        })->count();

        return response()->json([
            'success'=> true,
            'message'=> 'Dashboard Data fetched successfuly',
            'data'=> [
                'total_courts'=>$totalCourts,
                'active_courts'=>$activeCourts,
                'inactive_courts'=>$inactiveCourts,
                'total_bookings'=> $totalBookings,
                'today_bookings'=> $todayBookings,
                'pending_bookings'=> $pendingBookings,
                'completed_bookings'=> $completedBookings,
                'cancelled_bookings'=> $cancelledBookings,
                'total_revenue'=> $totalRevenue,
                'total_tournament'=> $totalTournaments,
                'active_tournaments'=> $activeTournaments,
                'completed_tournaments'=> $completeTournaments,
                'cancelled_tournaments'=>$cancelledTournaments,
                'total_reviews'=> $totalReviews,
                'average_rating'=> round($averageRating ?? 0,1),

            ]
        ],200);        

    }
}
