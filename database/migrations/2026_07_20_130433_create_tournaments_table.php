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
        Schema::create('tournaments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('owner_id')
                ->constrained('users')
                ->onDelete('cascade');

            $table->foreignId('court_id')
                ->constrained('courts')
                ->onDelete('cascade');

            $table->string('title');

            $table->text('description')->nullable();

            $table->date('tournament_date');
            $table->date('registration_last_date');

            $table->string('banner')->nullable();

            $table->time('start_time');

            $table->time('end_time');

            $table->decimal('entry_fee', 8, 2)->default(0);

            $table->integer('max_participants');

            $table->string('prize')->nullable();

            $table->enum('status', [
                'upcoming',
                'ongoing',
                'completed',
                'cancelled'
            ])->default('upcoming');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tournaments');
    }
};
