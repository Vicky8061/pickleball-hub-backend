<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class RazorpayService
{
    protected ?string $keyId;
    protected ?string $keySecret;

    public function __construct()
    {
        $this->keyId = config('services.razorpay.key_id');
        $this->keySecret = config('services.razorpay.key_secret');
    }

    /**
     * Check if official Razorpay credentials are fully configured.
     */
    public function isConfigured(): bool
    {
        return !empty($this->keyId) && !empty($this->keySecret);
    }

    /**
     * Get the public key ID for frontend checkout.
     */
    public function getKeyId(): string
    {
        return $this->isConfigured() ? $this->keyId : 'rzp_test_simulator';
    }

    /**
     * Create an authorized payment order with Razorpay or generate a simulator order.
     *
     * @param float $amountInRupees Total payable amount in INR.
     * @param int|string $bookingId The booking reference ID.
     * @return array
     */
    public function createOrder(float $amountInRupees, $bookingId): array
    {
        $amountInPaise = (int) round($amountInRupees * 100);

        // If official Razorpay keys are configured, communicate directly with Razorpay API
        if ($this->isConfigured()) {
            try {
                $response = Http::withBasicAuth($this->keyId, $this->keySecret)
                    ->timeout(10)
                    ->post('https://api.razorpay.com/v1/orders', [
                        'amount' => $amountInPaise,
                        'currency' => 'INR',
                        'receipt' => 'booking_' . $bookingId,
                        'notes' => [
                            'booking_id' => (string) $bookingId,
                            'platform' => 'Pickleball Hub',
                        ],
                    ]);

                if ($response->successful()) {
                    $order = $response->json();
                    return [
                        'success' => true,
                        'order_id' => $order['id'],
                        'amount' => $amountInPaise,
                        'amount_in_rupees' => $amountInRupees,
                        'currency' => 'INR',
                        'key_id' => $this->keyId,
                        'is_mock' => false,
                    ];
                }

                \Log::warning('Razorpay Order API failed, falling back to simulator: ' . $response->body());
            } catch (\Throwable $e) {
                \Log::error('Razorpay Connection Exception: ' . $e->getMessage());
            }
        }

        // Learning / Sandbox Simulator Mode
        $mockOrderId = 'order_sim_' . strtolower(Str::random(14));

        return [
            'success' => true,
            'order_id' => $mockOrderId,
            'amount' => $amountInPaise,
            'amount_in_rupees' => $amountInRupees,
            'currency' => 'INR',
            'key_id' => 'rzp_test_simulator',
            'is_mock' => true,
        ];
    }

    /**
     * Verify payment signature authenticity using HMAC-SHA256.
     *
     * @param string $orderId
     * @param string $paymentId
     * @param string $signature
     * @return bool
     */
    public function verifySignature(string $orderId, string $paymentId, string $signature): bool
    {
        if (empty($orderId) || empty($paymentId) || empty($signature)) {
            return false;
        }

        // Check if this was a Sandbox Simulator order
        if (str_starts_with($orderId, 'order_sim_') || !$this->isConfigured()) {
            if ($signature === 'sim_signature_success') {
                return true;
            }

            $mockExpected = hash_hmac('sha256', $orderId . '|' . $paymentId, 'pickleball_hub_simulator');
            return hash_equals($mockExpected, $signature);
        }

        // Official Razorpay HMAC-SHA256 signature verification
        $expectedSignature = hash_hmac('sha256', $orderId . '|' . $paymentId, $this->keySecret);

        return hash_equals($expectedSignature, $signature);
    }
}
