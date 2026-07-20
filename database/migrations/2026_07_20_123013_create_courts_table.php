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
        Schema::create('courts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')
                ->constrained('users')
                ->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('address');

            $table->decimal('latitude',10, 7)->nullable();
            $table->decimal('longitude',10, 7)->nullable();

            $table->decimal('price_per_hour', 8, 2);

            $table->time('opening_time');
            $table->time('closing_time');

            $table->enum('status', ['active','inactive'])->default('active');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courts');
    }
};
