<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class SumUpService
{
    protected string $apiKey;
    protected string $merchantCode;
    protected string $baseUrl = 'https://api.sumup.com/v0.1';

    public function __construct()
    {
        $this->apiKey       = Setting::get('sumup_api_key',      config('services.sumup.api_key'));
        $this->merchantCode = Setting::get('sumup_merchant_code', config('services.sumup.merchant_code'));
    }

    /**
     * Create a Hosted Checkout for a web order. SumUp returns a URL we
     * redirect the customer to; they pay on SumUp's own page, then we
     * verify status server-side via checkout_reference (don't trust the
     * redirect alone — always re-check via the API or a webhook).
     */
    public function createHostedCheckout(string $checkoutReference, int $amountPence, string $redirectUrl): array
    {
        $response = Http::withToken($this->apiKey)
            ->post("{$this->baseUrl}/checkouts", [
                'checkout_reference' => $checkoutReference,
                'amount' => $amountPence / 100,
                'currency' => 'GBP',
                'merchant_code' => $this->merchantCode,
                'description' => "Order {$checkoutReference}",
                'redirect_url' => $redirectUrl,
                'hosted_checkout' => [
                    'enabled' => true,
                ],
            ]);

        if ($response->failed()) {
            throw new RuntimeException('SumUp checkout creation failed: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Look up a checkout by its SumUp id to confirm final payment status.
     * Call this from your redirect-back handler AND from a webhook —
     * never mark an order paid purely because the browser redirected back.
     */
    public function getCheckout(string $checkoutId): array
    {
        $response = Http::withToken($this->apiKey)
            ->get("{$this->baseUrl}/checkouts/{$checkoutId}");

        if ($response->failed()) {
            throw new RuntimeException('SumUp checkout lookup failed: ' . $response->body());
        }

        return $response->json();
    }

    public static function generateReference(): string
    {
        return 'ORD-' . now()->format('Ymd') . '-' . Str::upper(Str::random(8));
    }
}
