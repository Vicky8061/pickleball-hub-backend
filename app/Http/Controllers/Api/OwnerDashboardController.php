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
    /**
     * Display owner dashboard statistics.
     */
    public function index(Request $request)
    {
        // Only owners can access owner dashboard
        if ($request->user()->role !== 'owner') {
            return response()->json([
                'success' => false,
                'message' => 'Only owners can access the dashboard.',
            ], 403);
        }

        $ownerId = $request->user()->id;

        /*
        |--------------------------------------------------------------------------
        | Courts
        |--------------------------------------------------------------------------
        */

        $totalCourts = Court::where('owner_id', $ownerId)
            ->count();

        $activeCourts = Court::where('owner_id', $ownerId)
            ->where('status', 'active')
            ->count();

        $inactiveCourts = Court::where('owner_id', $ownerId)
            ->where('status', 'inactive')
            ->count();


        /*
        |--------------------------------------------------------------------------
        | Bookings
        |--------------------------------------------------------------------------
        */

        $ownerBookings = Booking::whereHas('court', function ($query) use ($ownerId) {
            $query->where('owner_id', $ownerId);
        });

        $totalBookings = (clone $ownerBookings)->count();

        $todayBookings = (clone $ownerBookings)
            ->whereDate('booking_date', Carbon::today())
            ->count();

        $pendingBookings = (clone $ownerBookings)
            ->where('booking_status', 'pending')
            ->count();

        $confirmedBookings = (clone $ownerBookings)
            ->where('booking_status', 'confirmed')
            ->count();

        $completedBookings = (clone $ownerBookings)
            ->where('booking_status', 'completed')
            ->count();

        $cancelledBookings = (clone $ownerBookings)
            ->where('booking_status', 'cancelled')
            ->count();


        /*
        |--------------------------------------------------------------------------
        | Revenue
        |--------------------------------------------------------------------------
        */

        $totalRevenue = (clone $ownerBookings)
            ->where('payment_status', 'paid')
            ->sum('total_amount');


        /*
        |--------------------------------------------------------------------------
        | Tournaments
        |--------------------------------------------------------------------------
        */

        $totalTournaments = Tournament::where('owner_id', $ownerId)
            ->count();

        $upcomingTournaments = Tournament::where('owner_id', $ownerId)
            ->where('status', 'upcoming')
            ->count();

        $ongoingTournaments = Tournament::where('owner_id', $ownerId)
            ->where('status', 'ongoing')
            ->count();

        $completedTournaments = Tournament::where('owner_id', $ownerId)
            ->where('status', 'completed')
            ->count();

        $cancelledTournaments = Tournament::where('owner_id', $ownerId)
            ->where('status', 'cancelled')
            ->count();


        /*
        |--------------------------------------------------------------------------
        | Reviews
        |--------------------------------------------------------------------------
        */

        $ownerReviews = Review::whereHas('court', function ($query) use ($ownerId) {
            $query->where('owner_id', $ownerId);
        });

        $totalReviews = (clone $ownerReviews)
            ->count();

        $averageRating = (clone $ownerReviews)
            ->avg('rating');


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' => true,
            'message' => 'Dashboard data fetched successfully.',
            'data' => [

                // Courts
                'total_courts' => $totalCourts,
                'active_courts' => $activeCourts,
                'inactive_courts' => $inactiveCourts,

                // Bookings
                'total_bookings' => $totalBookings,
                'today_bookings' => $todayBookings,
                'pending_bookings' => $pendingBookings,
                'confirmed_bookings' => $confirmedBookings,
                'completed_bookings' => $completedBookings,
                'cancelled_bookings' => $cancelledBookings,

                // Revenue
                'total_revenue' => $totalRevenue,

                // Tournaments
                'total_tournaments' => $totalTournaments,
                'upcoming_tournaments' => $upcomingTournaments,
                'ongoing_tournaments' => $ongoingTournaments,
                'completed_tournaments' => $completedTournaments,
                'cancelled_tournaments' => $cancelledTournaments,

                // Reviews
                'total_reviews' => $totalReviews,
                'average_rating' => round($averageRating ?? 0, 1),
            ],
        ], 200);
    }
}
