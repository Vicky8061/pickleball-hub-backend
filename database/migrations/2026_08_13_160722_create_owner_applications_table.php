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
        Schema::create('owner_applications', function (Blueprint $table) {
            $table->id();

            // User who is applying to become an owner
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('business_name');
            $table->string('phone');
            $table->text('address');

            $table->string('city');
            $table->string('state');
            $table->string('pincode', 10);

            $table->text('experience')->nullable();
            $table->text('description')->nullable();

            // Optional verification document
            $table->string('document')->nullable();

            // Application status
            $table->enum('status', [
                'pending',
                'approved',
                'rejected'
            ])->default('pending');

            // Admin's reason/note when reviewing
            $table->text('admin_note')->nullable();

            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();

            // Prevent multiple active applications for the same user
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('owner_applications');
    }
};