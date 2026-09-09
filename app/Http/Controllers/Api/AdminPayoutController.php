<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Court;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminPayoutController extends Controller
{
    /**
     * Display a listing of owner payouts, commission metrics, and platform totals.
     */
    public function index(Request $request)
    {
        // Only admin access
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Only admins can access payout settlements.',
            ], 403);
        }

        $query = User::where('role', 'owner')->with(['courts']);

        // Search by owner name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $owners = $query->latest()->get();

        $ownerIds = $owners->pluck('id');
        $ownerBookingStats = Booking::join('courts', 'bookings.court_id', '=', 'courts.id')
            ->where('bookings.payment_status', 'paid')
            ->whereIn('courts.owner_id', $ownerIds)
            ->groupBy('courts.owner_id')
            ->selectRaw('courts.owner_id, COUNT(bookings.id) as total_bookings, SUM(COALESCE(bookings.total_amount, bookings.court_price, 0)) as gross_revenue, SUM(COALESCE(bookings.admin_commission_amount, 0)) as platform_commission')
            ->get()
            ->keyBy('owner_id');

        $ownersList = [];
        $totalGrossVolume = 0;
        $totalRetainedCommission = 0;
        $totalSettledPayouts = 0;
        $totalPendingPayouts = 0;

        foreach ($owners as $owner) {
            $courtCount = $owner->courts->count();
            $stats = $ownerBookingStats->get($owner->id);

            $totalBookingsCount = $stats ? (int) $stats->total_bookings : 0;
            $grossRevenue = $stats ? (float) $stats->gross_revenue : 0.0;
            $platformCommission = $stats ? (float) $stats->platform_commission : 0.0;
            if ($platformCommission <= 0 && $grossRevenue > 0) {
                $platformCommission = $grossRevenue * 0.10;
            }
            $netOwnerPayout = max(0, $grossRevenue - $platformCommission);

            // Payout Settlement Status
            $payoutStatus = $owner->payout_status ?? ($netOwnerPayout > 0 ? 'pending' : 'settled');
            
            if ($request->filled('status') && $request->status !== '') {
                if (strtolower($request->status) !== strtolower($payoutStatus)) {
                    continue; // Filter out
                }
            }

            // Accumulate Platform Totals
            $totalGrossVolume += $grossRevenue;
            $totalRetainedCommission += $platformCommission;
            if ($payoutStatus === 'settled') {
                $totalSettledPayouts += $netOwnerPayout;
            } else {
                $totalPendingPayouts += $netOwnerPayout;
            }

            $ownersList[] = [
                'id' => $owner->id,
                'name' => $owner->name,
                'email' => $owner->email,
                'phone' => $owner->phone ?? $owner->phone_number ?? 'N/A',
                'total_venues' => $courtCount,
                'total_bookings' => $totalBookingsCount,
                'gross_revenue' => round($grossRevenue, 2),
                'platform_commission' => round($platformCommission, 2),
                'net_owner_payout' => round($netOwnerPayout, 2),
                'payout_status' => $payoutStatus,
                'tx_reference' => $owner->payout_tx_reference ?? null,
                'payment_method' => $owner->payout_method ?? null,
                'settlement_date' => $owner->payout_settlement_date ?? null,
                'notes' => $owner->payout_notes ?? null,
                'created_at' => $owner->created_at,
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'Payout settlements fetched successfully.',
            'summary' => [
                'total_gross_volume' => round($totalGrossVolume, 2),
                'total_retained_commission' => round($totalRetainedCommission, 2),
                'total_settled_payouts' => round($totalSettledPayouts, 2),
                'total_pending_payouts' => round($totalPendingPayouts, 2),
            ],
            'data' => $ownersList,
        ], 200);
    }

    /**
     * Update payout settlement status and details for an owner.
     */
    public function updateStatus(Request $request, User $owner)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Only admins can update payout settlements.',
            ], 403);
        }

        if ($owner->role !== 'owner') {
            return response()->json([
                'success' => false,
                'message' => 'User is not a court owner.',
            ], 400);
        }

        $request->validate([
            'status' => 'required|in:settled,processing,pending',
            'tx_reference' => 'nullable|string|max:255',
            'payment_method' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
        ]);

        $status = $request->status;

        $owner->payout_status = $status;
        $owner->payout_tx_reference = $request->tx_reference;
        $owner->payout_method = $request->payment_method ?? 'Bank Transfer';
        $owner->payout_notes = $request->notes;
        $owner->payout_settlement_date = $status === 'settled' ? now() : null;
        $owner->save();

        return response()->json([
            'success' => true,
            'message' => "Owner payout status updated to '{$status}' successfully.",
            'data' => [
                'owner_id' => $owner->id,
                'owner_name' => $owner->name,
                'payout_status' => $owner->payout_status,
                'tx_reference' => $owner->payout_tx_reference,
                'payment_method' => $owner->payout_method,
                'settlement_date' => $owner->payout_settlement_date,
            ],
        ], 200);
    }
}
