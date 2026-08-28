<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Tournament;
use App\Models\Review;
use App\Models\Court;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

class ProfileStatsController extends Controller
{
    /**
     * Get player profile stats, favorite court, badges, and recent activity timeline.
     */
    #[OA\Get(
        path: '/api/user/profile-stats',
        summary: 'Get player profile statistics',
        description: 'Returns player stats, favorite court, unlocked badges, and recent activity timeline.',
        tags: ['User Profile'],
        security: [
            ['sanctum' => []]
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Profile stats retrieved successfully'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            )
        ]
    )]
    public function show(Request $request)
    {
        $user = $request->user();

        // 1. Total & Upcoming Bookings
        $totalBookingsCount = Booking::where('user_id', $user->id)
            ->where('booking_status', '!=', 'cancelled')
            ->count();

        $completedMatchesCount = Booking::where('user_id', $user->id)
            ->where('booking_status', 'completed')
            ->count();

        $upcomingBookingsCount = Booking::where('user_id', $user->id)
            ->whereIn('booking_status', ['confirmed', 'pending'])
            ->where('booking_date', '>=', now()->toDateString())
            ->count();

        // 2. Tournaments Joined
        $tournamentsJoinedCount = \App\Models\TournamentParticipant::where('user_id', $user->id)->count();

        // 3. Reviews Written
        $reviewsCount = Review::where('user_id', $user->id)->count();

        // 4. Most Booked Favorite Court
        $favoriteCourtData = Booking::where('user_id', $user->id)
            ->where('booking_status', '!=', 'cancelled')
            ->select('court_id', DB::raw('COUNT(*) as total_times_booked'))
            ->groupBy('court_id')
            ->orderByDesc('total_times_booked')
            ->first();

        $favoriteCourt = null;
        if ($favoriteCourtData) {
            $court = Court::with(['images', 'owner'])->find($favoriteCourtData->court_id);
            if ($court) {
                $primaryImg = $court->images->first()?->image_url ?? '/assets/images/court-placeholder.jpg';
                $favoriteCourt = [
                    'id' => $court->id,
                    'name' => $court->name,
                    'court_type' => $court->court_type,
                    'address' => $court->address,
                    'price_per_hour' => $court->price_per_hour,
                    'primary_image' => $primaryImg,
                    'times_booked' => $favoriteCourtData->total_times_booked,
                ];
            }
        }

        // 5. Player Achievement Badges
        $badges = [
            [
                'id' => 'first_serve',
                'name' => 'First Serve',
                'description' => 'Made your first court booking',
                'icon' => 'bi-play-circle-fill',
                'color' => '#198754',
                'unlocked' => $totalBookingsCount >= 1,
            ],
            [
                'id' => 'court_veteran',
                'name' => 'Court Veteran',
                'description' => 'Completed 5+ court bookings',
                'icon' => 'bi-fire',
                'color' => '#fd7e14',
                'unlocked' => $totalBookingsCount >= 5,
            ],
            [
                'id' => 'tournament_contender',
                'name' => 'Tournament Contender',
                'description' => 'Registered for a pickleball tournament',
                'icon' => 'bi-trophy-fill',
                'color' => '#0d6efd',
                'unlocked' => $tournamentsJoinedCount >= 1,
            ],
            [
                'id' => 'top_reviewer',
                'name' => 'Community Voice',
                'description' => 'Submitted 2+ court reviews',
                'icon' => 'bi-star-fill',
                'color' => '#ffc107',
                'unlocked' => $reviewsCount >= 2,
            ],
        ];

        // 6. Recent Activity Timeline (Recent 5 Bookings & Tournaments)
        $recentBookings = Booking::with('court')
            ->where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($b) {
                return [
                    'type' => 'booking',
                    'title' => 'Booked ' . ($b->court->name ?? 'Court'),
                    'subtitle' => 'Date: ' . $b->booking_date . ' (' . ucfirst($b->booking_status) . ')',
                    'created_at' => $b->created_at->toIso8601String(),
                    'icon' => 'bi-calendar-check',
                    'badge_class' => $b->booking_status === 'completed' ? 'bg-success' : 'bg-primary',
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Profile stats retrieved successfully',
            'data' => [
                'stats' => [
                    'total_matches' => $totalBookingsCount,
                    'completed_matches' => $completedMatchesCount,
                    'upcoming_matches' => $upcomingBookingsCount,
                    'tournaments_joined' => $tournamentsJoinedCount,
                    'reviews_count' => $reviewsCount,
                ],
                'favorite_court' => $favoriteCourt,
                'badges' => $badges,
                'recent_activity' => $recentBookings,
            ],
        ], 200);
    }
}
