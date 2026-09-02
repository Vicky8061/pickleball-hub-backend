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

        // Owner Applications
        $pendingOwnerApplications = \App\Models\OwnerApplication::where('status', 'pending')->count();
        $recentOwnerApplications = \App\Models\OwnerApplication::with('user')->latest()->take(5)->get();

        // Platform Commission Revenue
        $paidBookings = Booking::where('payment_status', 'paid')->get();
        $platformCommissionRevenue = $paidBookings->sum('admin_commission_amount');
        if ($platformCommissionRevenue <= 0) {
            $platformCommissionRevenue = $totalRevenue * 0.10;
        }

        // Recent Bookings
        $recentBookings = Booking::with(['court', 'user'])->latest()->take(5)->get();

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
                    'gross_revenue' => round($totalRevenue, 2),
                    'commission_revenue' => round($platformCommissionRevenue, 2),
                ],

                'owner_applications' => [
                    'pending_count' => $pendingOwnerApplications,
                    'total_count' => \App\Models\OwnerApplication::count(),
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

                'recent_owner_applications' => $recentOwnerApplications,
                'recent_bookings' => \App\Http\Resources\BookingResource::collection($recentBookings),
            ],
        ], 200);
    }
}
