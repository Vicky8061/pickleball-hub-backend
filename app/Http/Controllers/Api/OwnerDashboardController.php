<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Court;
use App\Models\Booking;
use App\Models\Review;
use App\Models\Tournament;
use Carbon\Carbon;
use OpenApi\Attributes as OA;

class OwnerDashboardController extends Controller
{
    /**
     * Display owner dashboard statistics.
     */
    #[OA\Get(
        path: '/api/owner/dashboard',
        summary: 'Get owner dashboard',
        description: 'Fetch dashboard statistics for the authenticated owner including courts, bookings, revenue, tournaments, and reviews.',
        tags: ['Owner Dashboard'],

        security: [
            ['sanctum' => []]
        ],

        responses: [
            new OA\Response(
                response: 200,
                description: 'Dashboard data fetched successfully.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'success',
                            type: 'boolean',
                            example: true
                        ),

                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Dashboard data fetched successfully.'
                        ),

                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [

                                // Courts
                                new OA\Property(
                                    property: 'total_courts',
                                    type: 'integer',
                                    example: 10
                                ),
                                new OA\Property(
                                    property: 'active_courts',
                                    type: 'integer',
                                    example: 8
                                ),
                                new OA\Property(
                                    property: 'inactive_courts',
                                    type: 'integer',
                                    example: 2
                                ),

                                // Bookings
                                new OA\Property(
                                    property: 'total_bookings',
                                    type: 'integer',
                                    example: 150
                                ),
                                new OA\Property(
                                    property: 'today_bookings',
                                    type: 'integer',
                                    example: 8
                                ),
                                new OA\Property(
                                    property: 'pending_bookings',
                                    type: 'integer',
                                    example: 12
                                ),
                                new OA\Property(
                                    property: 'confirmed_bookings',
                                    type: 'integer',
                                    example: 20
                                ),
                                new OA\Property(
                                    property: 'completed_bookings',
                                    type: 'integer',
                                    example: 100
                                ),
                                new OA\Property(
                                    property: 'cancelled_bookings',
                                    type: 'integer',
                                    example: 18
                                ),

                                // Revenue
                                new OA\Property(
                                    property: 'total_revenue',
                                    type: 'number',
                                    format: 'float',
                                    example: 25000.00
                                ),

                                // Tournaments
                                new OA\Property(
                                    property: 'total_tournaments',
                                    type: 'integer',
                                    example: 10
                                ),
                                new OA\Property(
                                    property: 'upcoming_tournaments',
                                    type: 'integer',
                                    example: 3
                                ),
                                new OA\Property(
                                    property: 'ongoing_tournaments',
                                    type: 'integer',
                                    example: 1
                                ),
                                new OA\Property(
                                    property: 'completed_tournaments',
                                    type: 'integer',
                                    example: 5
                                ),
                                new OA\Property(
                                    property: 'cancelled_tournaments',
                                    type: 'integer',
                                    example: 1
                                ),

                                // Reviews
                                new OA\Property(
                                    property: 'total_reviews',
                                    type: 'integer',
                                    example: 75
                                ),
                                new OA\Property(
                                    property: 'average_rating',
                                    type: 'number',
                                    format: 'float',
                                    example: 4.5
                                ),
                            ]
                        ),
                    ]
                )
            ),

            new OA\Response(
                response: 401,
                description: 'Unauthenticated.'
            ),

            new OA\Response(
                response: 403,
                description: 'Only owners can access the dashboard.'
            ),
        ]
    )]

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
            ->sum('owner_payout_amount');

        if (!$totalRevenue) {
            $totalRevenue = (clone $ownerBookings)
                ->where('payment_status', 'paid')
                ->sum('total_amount') * 0.90;
        }


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

    /**
     * Display owner earnings & payout reports.
     */
    public function earnings(Request $request)
    {
        if ($request->user()->role !== 'owner') {
            return response()->json([
                'success' => false,
                'message' => 'Only owners can access earnings reports.',
            ], 403);
        }

        $ownerId = $request->user()->id;

        $bookings = Booking::with(['court', 'user', 'timeSlot'])
            ->whereHas('court', function ($q) use ($ownerId) {
                $q->where('owner_id', $ownerId);
            })
            ->latest()
            ->get();

        $grossVolume = $bookings->whereIn('booking_status', ['confirmed', 'completed'])->sum('total_amount');
        $netPayout = $bookings->whereIn('booking_status', ['confirmed', 'completed'])->sum('owner_payout_amount');
        $adminCommission = $bookings->whereIn('booking_status', ['confirmed', 'completed'])->sum('admin_commission_amount');
        $completedPayout = $bookings->where('booking_status', 'completed')->sum('owner_payout_amount');
        $pendingSettlements = $bookings->whereIn('booking_status', ['pending', 'confirmed'])->sum('owner_payout_amount');

        // Monthly Breakdown (Last 6 Months)
        $monthlyBreakdown = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthStart = Carbon::now()->subMonths($i)->startOfMonth();
            $monthEnd = Carbon::now()->subMonths($i)->endOfMonth();

            $monthBookings = $bookings->filter(function ($b) use ($monthStart, $monthEnd) {
                $date = Carbon::parse($b->booking_date);
                return $date->gte($monthStart) && $date->lte($monthEnd) && in_array($b->booking_status, ['confirmed', 'completed']);
            });

            $monthlyBreakdown[] = [
                'month' => $monthStart->format('M Y'),
                'net_payout' => round($monthBookings->sum('owner_payout_amount'), 2),
                'booking_count' => $monthBookings->count(),
            ];
        }

        // Court Breakdown
        $courtBreakdown = Court::where('owner_id', $ownerId)
            ->get()
            ->map(function ($court) use ($bookings) {
                $courtBookings = $bookings->where('court_id', $court->id)->whereIn('booking_status', ['confirmed', 'completed']);
                return [
                    'court_id' => $court->id,
                    'court_name' => $court->name,
                    'court_type' => $court->court_type,
                    'total_bookings' => $courtBookings->count(),
                    'gross_volume' => round($courtBookings->sum('total_amount'), 2),
                    'net_payout' => round($courtBookings->sum('owner_payout_amount'), 2),
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Owner earnings report fetched successfully.',
            'data' => [
                'summary' => [
                    'gross_volume' => round($grossVolume, 2),
                    'net_payout' => round($netPayout, 2),
                    'admin_commission' => round($adminCommission, 2),
                    'completed_payout' => round($completedPayout, 2),
                    'pending_settlements' => round($pendingSettlements, 2),
                    'total_bookings' => $bookings->count(),
                ],
                'monthly_breakdown' => $monthlyBreakdown,
                'court_breakdown' => $courtBreakdown,
                'recent_transactions' => \App\Http\Resources\BookingResource::collection($bookings->take(15)),
            ],
        ], 200);
    }
}
