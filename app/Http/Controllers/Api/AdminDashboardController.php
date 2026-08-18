<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Court;
use App\Models\Booking;
use App\Models\Review;
use App\Models\Tournament;
use OpenApi\Attributes as OA;


class AdminDashboardController extends Controller
{
    #[OA\Get(
        path: '/api/admin/dashboard',
        summary: 'Get admin dashboard',
        description: 'Fetch dashboard statistics for the authenticated admin.',
        tags: ['Admin Dashboard'],
        security: [
            ['sanctum' => []]
        ],
        responses: [

            new OA\Response(
                response: 200,
                description: 'Admin dashboard fetched successfully.'
            ),

            new OA\Response(
                response: 401,
                description: 'Unauthenticated.'
            ),

            new OA\Response(
                response: 403,
                description: 'Only admins can access the admin dashboard.'
            )
        ]
    )]
    public function index(Request $request)
    {
        // Only admin can access dashboard
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Only admins can access the admin dashboard.',
            ], 403);
        }

        // Users
        $totalUsers = User::where('role', 'user')->count();

        $totalOwners = User::where('role', 'owner')->count();

        $totalAdmins = User::where('role', 'admin')->count();

        // Courts
        $totalCourts = Court::count();

        $activeCourts = Court::where('status', 'active')->count();

        $inactiveCourts = Court::where('status', 'inactive')->count();

        // Bookings
        $totalBookings = Booking::count();

        $pendingBookings = Booking::where(
            'booking_status',
            'pending'
        )->count();

        $completedBookings = Booking::where(
            'booking_status',
            'completed'
        )->count();

        $cancelledBookings = Booking::where(
            'booking_status',
            'cancelled'
        )->count();

        // Revenue
        $totalRevenue = Booking::where(
            'payment_status',
            'paid'
        )->sum('total_amount');

        // Reviews
        $totalReviews = Review::count();

        $averageRating = Review::avg('rating');

        // Tournaments
        $totalTournaments = Tournament::count();

        $activeTournaments = Tournament::where(
            'status',
            'ongoing'
        )->count();

        $completedTournaments = Tournament::where(
            'status',
            'completed'
        )->count();

        $cancelledTournaments = Tournament::where(
            'status',
            'cancelled'
        )->count();

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
                    'average_rating' => round(
                        $averageRating ?? 0,
                        1
                    ),
                ],

                'tournaments' => [
                    'total_tournaments' => $totalTournaments,
                    'active_tournaments' => $activeTournaments,
                    'completed_tournaments' => $completedTournaments,
                    'cancelled_tournaments' => $cancelledTournaments,
                ],
            ],
        ], 200);
    }
}
