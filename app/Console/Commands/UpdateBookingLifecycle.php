<?php

namespace App\Console\Commands;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateBookingLifecycle extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookings:update-lifecycle {--stale-minutes=30 : Minutes after which pending bookings expire}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically complete finished bookings and cancel stale pending bookings.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $now = Carbon::now();
        $todayStr = $now->toDateString();
        $currentTimeStr = $now->toTimeString();
        $staleMinutes = (int) $this->option('stale-minutes');

        $this->info("Running booking lifecycle checks at {$now->toDateTimeString()}...");

        // 1. Auto-complete confirmed bookings whose slot has finished
        $completedCount = Booking::join('time_slots', 'bookings.time_slot_id', '=', 'time_slots.id')
            ->where('bookings.booking_status', 'confirmed')
            ->where(function ($query) use ($todayStr, $currentTimeStr) {
                $query->where('bookings.booking_date', '<', $todayStr)
                    ->orWhere(function ($q) use ($todayStr, $currentTimeStr) {
                        $q->where('bookings.booking_date', '=', $todayStr)
                            ->where('time_slots.end_time', '<=', $currentTimeStr);
                    });
            })
            ->update([
                'bookings.booking_status' => 'completed',
                'bookings.updated_at' => $now,
            ]);

        $this->info("✓ Completed {$completedCount} finished bookings.");

        // 2. Auto-cancel expired pending reservations
        $staleThreshold = Carbon::now()->subMinutes($staleMinutes);

        $expiredPendingCount = Booking::join('time_slots', 'bookings.time_slot_id', '=', 'time_slots.id')
            ->where('bookings.booking_status', 'pending')
            ->where('bookings.payment_status', 'pending')
            ->where(function ($query) use ($now, $staleThreshold, $todayStr, $currentTimeStr) {
                $query->where(function ($exp) use ($now) {
                    $exp->whereNotNull('bookings.expires_at')
                        ->where('bookings.expires_at', '<=', $now);
                })
                ->orWhere(function ($legacy) use ($staleThreshold, $todayStr, $currentTimeStr) {
                    $legacy->whereNull('bookings.expires_at')
                        ->where(function ($sub) use ($staleThreshold, $todayStr, $currentTimeStr) {
                            $sub->where('bookings.created_at', '<=', $staleThreshold)
                                ->orWhere('bookings.booking_date', '<', $todayStr)
                                ->orWhere(function ($today) use ($todayStr, $currentTimeStr) {
                                    $today->where('bookings.booking_date', '=', $todayStr)
                                          ->where('time_slots.start_time', '<=', $currentTimeStr);
                                });
                        });
                });
            })
            ->update([
                'bookings.booking_status' => 'cancelled',
                'bookings.expires_at' => null,
                'bookings.updated_at' => $now,
            ]);

        $this->info("✓ Cancelled {$expiredPendingCount} expired/stale pending bookings.");

        return Command::SUCCESS;
    }
}
