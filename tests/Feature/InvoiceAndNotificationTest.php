<?php

namespace Tests\Feature;

use App\Mail\BookingConfirmedMail;
use App\Models\Booking;
use App\Models\Court;
use App\Models\TimeSlot;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class InvoiceAndNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $player;
    protected User $otherPlayer;
    protected User $owner;
    protected User $admin;
    protected Court $court;
    protected TimeSlot $timeSlot;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create([
            'role' => 'owner',
            'status' => 'active',
            'password' => Hash::make('password123'),
        ]);

        $this->player = User::factory()->create([
            'role' => 'user',
            'status' => 'active',
            'password' => Hash::make('password123'),
        ]);

        $this->otherPlayer = User::factory()->create([
            'role' => 'user',
            'status' => 'active',
            'password' => Hash::make('password123'),
        ]);

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
            'password' => Hash::make('password123'),
        ]);

        $this->court = Court::create([
            'owner_id' => $this->owner->id,
            'name' => 'Championship Center Court',
            'address' => '42 Pickleball Way, Sports Complex',
            'price_per_hour' => 500.00,
            'court_type' => 'Indoor',
            'opening_time' => '06:00:00',
            'closing_time' => '22:00:00',
            'status' => 'active',
        ]);

        $this->timeSlot = TimeSlot::create([
            'court_id' => $this->court->id,
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'price' => 500.00,
            'status' => 'active',
        ]);
    }

    public function test_authenticated_user_can_download_own_booking_invoice_pdf(): void
    {
        $booking = Booking::create([
            'user_id' => $this->player->id,
            'court_id' => $this->court->id,
            'time_slot_id' => $this->timeSlot->id,
            'booking_date' => Carbon::tomorrow()->toDateString(),
            'total_amount' => 550.00,
            'court_price' => 500.00,
            'platform_fee' => 50.00,
            'booking_status' => 'confirmed',
            'payment_status' => 'paid',
            'payment_method' => 'razorpay',
            'razorpay_order_id' => 'order_test_123',
            'razorpay_payment_id' => 'pay_test_123',
            'paid_at' => Carbon::now(),
        ]);

        $response = $this->actingAs($this->player, 'sanctum')
            ->get("/api/bookings/{$booking->id}/invoice");

        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('content-type'));
        $this->assertNotEmpty($response->getContent());
    }

    public function test_unauthorized_user_cannot_download_another_users_invoice(): void
    {
        $booking = Booking::create([
            'user_id' => $this->player->id,
            'court_id' => $this->court->id,
            'time_slot_id' => $this->timeSlot->id,
            'booking_date' => Carbon::tomorrow()->toDateString(),
            'total_amount' => 550.00,
            'booking_status' => 'confirmed',
            'payment_status' => 'paid',
            'paid_at' => Carbon::now(),
        ]);

        $response = $this->actingAs($this->otherPlayer, 'sanctum')
            ->get("/api/bookings/{$booking->id}/invoice");

        $response->assertStatus(403);
    }

    public function test_cannot_download_invoice_for_unpaid_or_pending_booking(): void
    {
        $booking = Booking::create([
            'user_id' => $this->player->id,
            'court_id' => $this->court->id,
            'time_slot_id' => $this->timeSlot->id,
            'booking_date' => Carbon::tomorrow()->toDateString(),
            'total_amount' => 550.00,
            'booking_status' => 'pending',
            'payment_status' => 'pending',
            'expires_at' => Carbon::now()->addMinutes(10),
        ]);

        $response = $this->actingAs($this->player, 'sanctum')
            ->get("/api/bookings/{$booking->id}/invoice");

        $response->assertStatus(400);
    }

    public function test_court_owner_and_admin_can_download_booking_invoice(): void
    {
        $booking = Booking::create([
            'user_id' => $this->player->id,
            'court_id' => $this->court->id,
            'time_slot_id' => $this->timeSlot->id,
            'booking_date' => Carbon::tomorrow()->toDateString(),
            'total_amount' => 550.00,
            'booking_status' => 'confirmed',
            'payment_status' => 'paid',
            'paid_at' => Carbon::now(),
        ]);

        // Court Owner
        $ownerResp = $this->actingAs($this->owner, 'sanctum')
            ->get("/api/bookings/{$booking->id}/invoice");
        $ownerResp->assertStatus(200);
        $this->assertEquals('application/pdf', $ownerResp->headers->get('content-type'));

        // Admin
        $adminResp = $this->actingAs($this->admin, 'sanctum')
            ->get("/api/bookings/{$booking->id}/invoice");
        $adminResp->assertStatus(200);
        $this->assertEquals('application/pdf', $adminResp->headers->get('content-type'));
    }

    public function test_payment_verification_dispatches_confirmation_email_with_pdf_attachment(): void
    {
        Mail::fake();

        $booking = Booking::create([
            'user_id' => $this->player->id,
            'court_id' => $this->court->id,
            'time_slot_id' => $this->timeSlot->id,
            'booking_date' => Carbon::tomorrow()->toDateString(),
            'total_amount' => 550.00,
            'booking_status' => 'pending',
            'payment_status' => 'pending',
            'razorpay_order_id' => 'order_mock_test',
            'expires_at' => Carbon::now()->addMinutes(10),
        ]);

        $response = $this->actingAs($this->player, 'sanctum')
            ->postJson("/api/bookings/{$booking->id}/verify-payment", [
                'razorpay_order_id' => 'order_mock_test',
                'razorpay_payment_id' => 'pay_mock_123',
                'razorpay_signature' => 'sim_signature_success',
                'payment_method' => 'upi',
            ]);

        $response->assertStatus(200);

        Mail::assertSent(BookingConfirmedMail::class, function ($mail) use ($booking) {
            return $mail->hasTo($this->player->email) &&
                   $mail->booking->id === $booking->id;
        });
    }

    public function test_direct_pay_dispatches_confirmation_email_with_pdf_attachment(): void
    {
        Mail::fake();

        $booking = Booking::create([
            'user_id' => $this->player->id,
            'court_id' => $this->court->id,
            'time_slot_id' => $this->timeSlot->id,
            'booking_date' => Carbon::tomorrow()->toDateString(),
            'total_amount' => 550.00,
            'booking_status' => 'pending',
            'payment_status' => 'pending',
            'expires_at' => Carbon::now()->addMinutes(10),
        ]);

        $response = $this->actingAs($this->player, 'sanctum')
            ->postJson("/api/bookings/{$booking->id}/pay");

        $response->assertStatus(200);

        Mail::assertSent(BookingConfirmedMail::class, function ($mail) use ($booking) {
            return $mail->hasTo($this->player->email) &&
                   $mail->booking->id === $booking->id;
        });
    }
}
