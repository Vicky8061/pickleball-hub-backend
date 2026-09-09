<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->index(['court_id', 'time_slot_id', 'booking_date'], 'bookings_slot_date_idx');
            $table->index(['booking_status'], 'bookings_status_idx');
            $table->index(['user_id', 'booking_status'], 'bookings_user_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('bookings_slot_date_idx');
            $table->dropIndex('bookings_status_idx');
            $table->dropIndex('bookings_user_status_idx');
        });
    }
};
