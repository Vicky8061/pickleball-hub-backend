<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->decimal('court_price', 10, 2)->nullable()->after('time_slot_id');
            $table->decimal('platform_fee', 10, 2)->default(50.00)->after('court_price');
            $table->decimal('admin_commission_rate', 5, 2)->default(10.00)->after('platform_fee');
            $table->decimal('admin_commission_amount', 10, 2)->nullable()->after('admin_commission_rate');
            $table->decimal('owner_payout_amount', 10, 2)->nullable()->after('admin_commission_amount');
        });

        // Backfill existing booking records
        DB::table('bookings')->get()->each(function ($booking) {
            $courtPrice = $booking->total_amount ?? 0;
            $adminCommissionRate = 10.00;
            $adminCommissionAmount = round($courtPrice * 0.10, 2);
            $ownerPayoutAmount = round($courtPrice - $adminCommissionAmount, 2);

            DB::table('bookings')->where('id', $booking->id)->update([
                'court_price' => $courtPrice,
                'platform_fee' => 0.00,
                'admin_commission_rate' => $adminCommissionRate,
                'admin_commission_amount' => $adminCommissionAmount,
                'owner_payout_amount' => $ownerPayoutAmount,
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'court_price',
                'platform_fee',
                'admin_commission_rate',
                'admin_commission_amount',
                'owner_payout_amount',
            ]);
        });
    }
};
