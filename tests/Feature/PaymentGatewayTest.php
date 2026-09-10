<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Court;
use App\Models\TimeSlot;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentGatewayTest extends TestCase
{
    use RefreshDatabase;

    protected function createCourtAndSlot(): array
    {
        $owner = User::factory()->create(['role' => 'owner', 'status' => 'active']);
        $player = User::factory()->create(['role' => 'user', 'status' => 'active']);

        $court = Court::create([
            'owner_id' => $owner->id,
            'name' => 'Gateway Arena',
            'address' => '100 Gateway Blvd',
            'price_per_hour' => 500,
            'court_type' => 'Indoor',
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

        return [$owner, $player, $court, $slot];
    }

    public function test_can_create_payment_order_for_active_hold(): void
    {
        [$owner, $player, $court, $slot] = $this->createCourtAndSlot();

        $booking = Booking::create([
            'user_id' => $player->id,
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
            'expires_at' => Carbon::now()->addMinutes(10),
        ]);

        $response = $this->actingAs($player)->postJson("/api/bookings/{$booking->id}/create-order");

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $this->assertNotEmpty($response->json('data.order_id'));
        $this->assertEquals(55000, $response->json('data.amount')); // in paise
        $this->assertEquals('INR', $response->json('data.currency'));

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'razorpay_order_id' => $response->json('data.order_id'),
        ]);
    }

    public function test_cannot_create_order_for_expired_hold(): void
    {
        [$owner, $player, $court, $slot] = $this->createCourtAndSlot();

        $booking = Booking::create([
            'user_id' => $player->id,
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
            'expires_at' => Carbon::now()->subMinutes(1),
        ]);

        $response = $this->actingAs($player)->postJson("/api/bookings/{$booking->id}/create-order");

        $response->assertStatus(400);
        $response->assertJsonPath('success', false);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'booking_status' => 'cancelled',
            'payment_status' => 'failed',
        ]);
    }

    public function test_valid_payment_signature_confirms_booking(): void
    {
        [$owner, $player, $court, $slot] = $this->createCourtAndSlot();

        $booking = Booking::create([
            'user_id' => $player->id,
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
            'expires_at' => Carbon::now()->addMinutes(10),
        ]);

        $orderId = 'order_sim_test12345';
        $paymentId = 'pay_sim_test67890';
        $signature = hash_hmac('sha256', $orderId . '|' . $paymentId, 'pickleball_hub_simulator');

        $booking->update(['razorpay_order_id' => $orderId]);

        $response = $this->actingAs($player)->postJson("/api/bookings/{$booking->id}/verify-payment", [
            'razorpay_order_id' => $orderId,
            'razorpay_payment_id' => $paymentId,
            'razorpay_signature' => $signature,
            'payment_method' => 'upi',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.booking_status', 'confirmed');
        $response->assertJsonPath('data.payment_status', 'paid');
        $response->assertJsonPath('data.payment_method', 'upi');
        $this->assertNull($response->json('data.expires_at'));

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'booking_status' => 'confirmed',
            'payment_status' => 'paid',
            'payment_method' => 'upi',
            'razorpay_order_id' => $orderId,
            'razorpay_payment_id' => $paymentId,
            'expires_at' => null,
        ]);
    }

    public function test_tampered_payment_signature_is_rejected(): void
    {
        [$owner, $player, $court, $slot] = $this->createCourtAndSlot();

        $booking = Booking::create([
            'user_id' => $player->id,
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
            'expires_at' => Carbon::now()->addMinutes(10),
            'razorpay_order_id' => 'order_sim_test12345',
        ]);

        $response = $this->actingAs($player)->postJson("/api/bookings/{$booking->id}/verify-payment", [
            'razorpay_order_id' => 'order_sim_test12345',
            'razorpay_payment_id' => 'pay_sim_test67890',
            'razorpay_signature' => 'fake_tampered_signature_xyz',
            'payment_method' => 'card',
        ]);

        $response->assertStatus(400);
        $response->assertJsonPath('success', false);

        // Booking remains pending and unconfirmed
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'booking_status' => 'pending',
            'payment_status' => 'pending',
        ]);
    }

    public function test_unauthorized_user_cannot_verify_payment(): void
    {
        [$owner, $player1, $court, $slot] = $this->createCourtAndSlot();
        $player2 = User::factory()->create(['role' => 'user', 'status' => 'active']);

        $booking = Booking::create([
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
            'expires_at' => Carbon::now()->addMinutes(10),
            'razorpay_order_id' => 'order_sim_test12345',
        ]);

        $response = $this->actingAs($player2)->postJson("/api/bookings/{$booking->id}/verify-payment", [
            'razorpay_order_id' => 'order_sim_test12345',
            'razorpay_payment_id' => 'pay_sim_test67890',
            'razorpay_signature' => 'sim_signature_success',
        ]);

        $response->assertStatus(403);
    }
}
