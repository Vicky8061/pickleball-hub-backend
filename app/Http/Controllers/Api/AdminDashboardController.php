<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Court;
use App\Models\Booking;
use App\Models\Review;
use App\Models\Tournament;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
{
    $totalUsers = User::where('role', 'user')->count();

    $totalOwners = User::where('role', 'owner')->count();

    $totalAdmins = User::where('role', 'admin')->count();

    $totalCourts = Court::count();

    $activeCourts = Court::where('status', 'active')->count();

    $inactiveCourts = Court::where('status', 'inactive')->count();

    $totalBookings = Booking::count();

    $pendingBookings = Booking::where('booking_status', 'pending')->count();

    $completedBookings = Booking::where('booking_status', 'completed')->count();

    $cancelledBookings = Booking::where('booking_status', 'cancelled')->count();

    $totalRevenue = Booking::where('payment_status', 'paid')
        ->sum('total_amount');

    $totalReviews = Review::count();

    $averageRating = Review::avg('rating');

    $totalTournaments = Tournament::count();

    $activeTournaments = Tournament::where('status', 'ongoing')->count();

    $completedTournaments = Tournament::where('status', 'completed')->count();

    $cancelledTournaments = Tournament::where('status', 'cancelled')->count();

    return response()->json([
        'success' => true,
        'message' => 'Admin dashboard fetched successfully.',
        'data' => [

            'users' => [
                'total_users' => $totalUsers,
                'total_owners' => $totalOwners,
                'total_admins' => $totalAdmins,
            ],

            'courts' => [
                'total_courts' => $totalCourts,
                'active_courts' => $activeCourts,
                'inactive_courts' => $inactiveCourts,
            ],

            'bookings' => [
                'total_bookings' => $totalBookings,
                'pending_bookings' => $pendingBookings,
                'completed_bookings' => $completedBookings,
                'cancelled_bookings' => $cancelledBookings,
            ],

            'revenue' => [
                'total_revenue' => $totalRevenue,
            ],

            'reviews' => [
                'total_reviews' => $totalReviews,
                'average_rating' => round($averageRating ?? 0, 1),
            ],

            'tournaments' => [
                'total_tournaments' => $totalTournaments,
                'active_tournaments' => $activeTournaments,
                'completed_tournaments' => $completedTournaments,
                'cancelled_tournaments' => $cancelledTournaments,
            ],
        ]
    ], 200);
}
}
