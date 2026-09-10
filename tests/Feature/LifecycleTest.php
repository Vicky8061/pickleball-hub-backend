<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Court;
use App\Models\TimeSlot;
use App\Models\Tournament;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_past_confirmed_bookings_are_marked_completed(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $player = User::factory()->create(['role' => 'user']);

        $court = Court::create([
            'owner_id' => $owner->id,
            'name' => 'Center Court',
            'address' => '123 Pickleball Way',
            'price_per_hour' => 500,
            'court_type' => 'Indoor',
            'opening_time' => '06:00:00',
            'closing_time' => '22:00:00',
            'status' => 'active',
        ]);

        $slot = TimeSlot::create([
            'court_id' => $court->id,
            'start_time' => '08:00:00',
            'end_time' => '09:00:00',
            'status' => 'active',
        ]);

        $pastBooking = Booking::create([
            'user_id' => $player->id,
            'court_id' => $court->id,
            'time_slot_id' => $slot->id,
            'booking_date' => Carbon::yesterday()->toDateString(),
            'court_price' => 500,
            'platform_fee' => 20,
            'admin_commission_rate' => 10,
            'admin_commission_amount' => 50,
            'owner_payout_amount' => 450,
            'total_amount' => 520,
            'payment_status' => 'paid',
            'booking_status' => 'confirmed',
        ]);

        $this->artisan('bookings:update-lifecycle')
            ->expectsOutputToContain('Completed 1 finished bookings.')
            ->assertSuccessful();

        $this->assertDatabaseHas('bookings', [
            'id' => $pastBooking->id,
            'booking_status' => 'completed',
        ]);
    }

    public function test_stale_pending_bookings_are_cancelled(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $player = User::factory()->create(['role' => 'user']);

        $court = Court::create([
            'owner_id' => $owner->id,
            'name' => 'Court Two',
            'address' => '456 Pickleball Blvd',
            'price_per_hour' => 400,
            'court_type' => 'Outdoor',
            'opening_time' => '06:00:00',
            'closing_time' => '22:00:00',
            'status' => 'active',
        ]);

        $slot = TimeSlot::create([
            'court_id' => $court->id,
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'status' => 'active',
        ]);

        $staleBooking = Booking::create([
            'user_id' => $player->id,
            'court_id' => $court->id,
            'time_slot_id' => $slot->id,
            'booking_date' => Carbon::tomorrow()->toDateString(),
            'court_price' => 400,
            'platform_fee' => 20,
            'admin_commission_rate' => 10,
            'admin_commission_amount' => 40,
            'owner_payout_amount' => 360,
            'total_amount' => 420,
            'payment_status' => 'pending',
            'booking_status' => 'pending',
        ]);

        \Illuminate\Support\Facades\DB::table('bookings')
            ->where('id', $staleBooking->id)
            ->update(['created_at' => Carbon::now()->subHours(2)]);

        $this->artisan('bookings:update-lifecycle', ['--stale-minutes' => 30])
            ->expectsOutputToContain('Cancelled 1 expired/stale pending bookings.')
            ->assertSuccessful();

        $this->assertDatabaseHas('bookings', [
            'id' => $staleBooking->id,
            'booking_status' => 'cancelled',
        ]);
    }

    public function test_past_tournaments_are_marked_completed(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);

        $court = Court::create([
            'owner_id' => $owner->id,
            'name' => 'Tournament Court',
            'address' => '789 Tournament Dr',
            'price_per_hour' => 600,
            'court_type' => 'Indoor',
            'opening_time' => '06:00:00',
            'closing_time' => '22:00:00',
            'status' => 'active',
        ]);

        $tournament = Tournament::create([
            'owner_id' => $owner->id,
            'court_id' => $court->id,
            'title' => 'Pickleball Summer Open',
            'tournament_date' => Carbon::yesterday()->toDateString(),
            'registration_last_date' => Carbon::yesterday()->subDays(2)->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
            'entry_fee' => 100,
            'max_participants' => 32,
            'status' => 'upcoming',
        ]);

        $this->artisan('tournaments:update-lifecycle')
            ->expectsOutputToContain('Marked 1 tournaments as completed.')
            ->assertSuccessful();

        $this->assertDatabaseHas('tournaments', [
            'id' => $tournament->id,
            'status' => 'completed',
        ]);
    }

    public function test_expired_hold_immediately_allows_another_user_to_book(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $player1 = User::factory()->create(['role' => 'user']);
        $player2 = User::factory()->create(['role' => 'user']);

        $court = Court::create([
            'owner_id' => $owner->id,
            'name' => 'Hold Test Court',
            'address' => '999 Hold Ave',
            'price_per_hour' => 500,
            'court_type' => 'Indoor',
            'opening_time' => '06:00:00',
            'closing_time' => '22:00:00',
            'status' => 'active',
        ]);

        $slot = TimeSlot::create([
            'court_id' => $court->id,
            'start_time' => '14:00:00',
            'end_time' => '15:00:00',
            'status' => 'active',
        ]);

        // Player 1 had a hold that expired 2 minutes ago
        Booking::create([
            'user_id' => $player1->id,
            'court_id' => $court->id,
            'time_slot_id' => $slot->id,
            'booking_date' => Carbon::tomorrow()->toDateString(),
            'court_price' => 500,
            'platform_fee' => 50,
            'admin_commission_rate' => 10,
            'admin_commission_amount' => 50,
            'owner_payout_amount' => 450,
            'total_amount' => 550,
            'payment_status' => 'pending',
            'booking_status' => 'pending',
            'expires_at' => Carbon::now()->subMinutes(2),
        ]);

        // Player 2 books the same slot - should SUCCEED immediately because hold has expired!
        $response = $this->actingAs($player2)->postJson('/api/bookings', [
            'court_id' => $court->id,
            'time_slot_id' => $slot->id,
            'booking_date' => Carbon::tomorrow()->toDateString(),
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('success', true);
        $this->assertNotNull($response->json('data.expires_at'));
    }

    public function test_active_hold_blocks_other_players(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $player1 = User::factory()->create(['role' => 'user']);
        $player2 = User::factory()->create(['role' => 'user']);

        $court = Court::create([
            'owner_id' => $owner->id,
            'name' => 'Active Hold Court',
            'address' => '888 Active Ave',
            'price_per_hour' => 500,
            'court_type' => 'Indoor',
            'opening_time' => '06:00:00',
            'closing_time' => '22:00:00',
            'status' => 'active',
        ]);

        $slot = TimeSlot::create([
            'court_id' => $court->id,
            'start_time' => '16:00:00',
            'end_time' => '17:00:00',
            'status' => 'active',
        ]);

        // Player 1 currently holds the slot for the next 8 minutes
        Booking::create([
            'user_id' => $player1->id,
            'court_id' => $court->id,
            'time_slot_id' => $slot->id,
            'booking_date' => Carbon::tomorrow()->toDateString(),
            'court_price' => 500,
            'platform_fee' => 50,
            'admin_commission_rate' => 10,
            'admin_commission_amount' => 50,
            'owner_payout_amount' => 450,
            'total_amount' => 550,
            'payment_status' => 'pending',
            'booking_status' => 'pending',
            'expires_at' => Carbon::now()->addMinutes(8),
        ]);

        // Player 2 attempts to book - should be BLOCKED with 400
        $response = $this->actingAs($player2)->postJson('/api/bookings', [
            'court_id' => $court->id,
            'time_slot_id' => $slot->id,
            'booking_date' => Carbon::tomorrow()->toDateString(),
        ]);

        $response->assertStatus(400);
        $response->assertJsonPath('success', false);
    }

    public function test_user_can_pay_and_confirm_booking_during_hold_window(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $player = User::factory()->create(['role' => 'user']);

        $court = Court::create([
            'owner_id' => $owner->id,
            'name' => 'Pay Test Court',
            'address' => '555 Payment Way',
            'price_per_hour' => 600,
            'court_type' => 'Outdoor',
            'opening_time' => '06:00:00',
            'closing_time' => '22:00:00',
            'status' => 'active',
        ]);

        $slot = TimeSlot::create([
            'court_id' => $court->id,
            'start_time' => '18:00:00',
            'end_time' => '19:00:00',
            'status' => 'active',
        ]);

        $booking = Booking::create([
            'user_id' => $player->id,
            'court_id' => $court->id,
            'time_slot_id' => $slot->id,
            'booking_date' => Carbon::tomorrow()->toDateString(),
            'court_price' => 600,
            'platform_fee' => 50,
            'admin_commission_rate' => 10,
            'admin_commission_amount' => 60,
            'owner_payout_amount' => 540,
            'total_amount' => 650,
            'payment_status' => 'pending',
            'booking_status' => 'pending',
            'expires_at' => Carbon::now()->addMinutes(10),
        ]);

        $response = $this->actingAs($player)->postJson("/api/bookings/{$booking->id}/pay");

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.booking_status', 'confirmed');
        $response->assertJsonPath('data.payment_status', 'paid');
        $this->assertNull($response->json('data.expires_at'));

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'booking_status' => 'confirmed',
            'payment_status' => 'paid',
            'expires_at' => null,
        ]);
    }

    public function test_user_cannot_pay_for_expired_hold(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $player = User::factory()->create(['role' => 'user']);

        $court = Court::create([
            'owner_id' => $owner->id,
            'name' => 'Expired Pay Court',
            'address' => '777 Timeout St',
            'price_per_hour' => 600,
            'court_type' => 'Outdoor',
            'opening_time' => '06:00:00',
            'closing_time' => '22:00:00',
            'status' => 'active',
        ]);

        $slot = TimeSlot::create([
            'court_id' => $court->id,
            'start_time' => '18:00:00',
            'end_time' => '19:00:00',
            'status' => 'active',
        ]);

        $booking = Booking::create([
            'user_id' => $player->id,
            'court_id' => $court->id,
            'time_slot_id' => $slot->id,
            'booking_date' => Carbon::tomorrow()->toDateString(),
            'court_price' => 600,
            'platform_fee' => 50,
            'admin_commission_rate' => 10,
            'admin_commission_amount' => 60,
            'owner_payout_amount' => 540,
            'total_amount' => 650,
            'payment_status' => 'pending',
            'booking_status' => 'pending',
            'expires_at' => Carbon::now()->subMinutes(1),
        ]);

        $response = $this->actingAs($player)->postJson("/api/bookings/{$booking->id}/pay");

        $response->assertStatus(400);
        $response->assertJsonPath('success', false);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'booking_status' => 'cancelled',
            'payment_status' => 'failed',
            'expires_at' => null,
        ]);
    }
}

