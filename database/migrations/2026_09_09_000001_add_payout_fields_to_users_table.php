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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('payout_status', ['pending', 'processing', 'settled'])
                ->default('pending')
                ->after('status');
            $table->string('payout_tx_reference')->nullable()->after('payout_status');
            $table->string('payout_method', 100)->nullable()->after('payout_tx_reference');
            $table->text('payout_notes')->nullable()->after('payout_method');
            $table->timestamp('payout_settlement_date')->nullable()->after('payout_notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'payout_status',
                'payout_tx_reference',
                'payout_method',
                'payout_notes',
                'payout_settlement_date',
            ]);
        });
    }
};
