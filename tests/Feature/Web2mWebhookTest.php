<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class Web2mWebhookTest extends TestCase
{
    use RefreshDatabase;

    private string $secret = 'test_secret_key_12345';

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.web2m.webhook_secret' => $this->secret]);
    }

    /**
     * Test successful Web2m webhook with valid signature
     */
    public function test_web2m_webhook_success_with_valid_signature(): void
    {
        $user = User::factory()->create(['id' => 5, 'xu_balance' => 100]);
        
        $payload = [
            'transaction_code' => 'TXN_TEST_001',
            'amount_vnd' => 100000,
            'description' => 'NAPXU 5',
            'email' => $user->email,
            'status' => 'completed',
        ];

        $payloadJson = json_encode($payload);
        $signature = hash_hmac('sha256', $payloadJson, $this->secret);

        $response = $this->postJson('/webhook/web2m', $payload, [
            'X-Web2m-Signature' => $signature,
            'Content-Type' => 'application/json',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Balance updated',
            'xu_main' => 100,
            'xu_bonus' => 10,
            'total_xu' => 110,
        ]);

        $user->refresh();
        $this->assertEquals(200, $user->xu_balance); // 100 + 100
        $this->assertEquals(10, $user->bonus_xu);

        $this->assertDatabaseHas('transactions', [
            'transaction_code' => 'TXN_TEST_001',
            'user_id' => $user->id,
            'amount_vnd' => 100000,
            'status' => 'completed',
        ]);
    }

    /**
     * Test webhook with invalid signature
     */
    public function test_web2m_webhook_fails_with_invalid_signature(): void
    {
        $payload = [
            'transaction_code' => 'TXN_TEST_002',
            'amount_vnd' => 100000,
            'description' => 'NAPXU 1',
        ];

        $response = $this->postJson('/webhook/web2m', $payload, [
            'X-Web2m-Signature' => 'invalid_signature_here',
        ]);

        $response->assertStatus(403);
        $response->assertJson(['message' => 'Invalid webhook signature']);
    }

    /**
     * Test webhook with missing signature
     */
    public function test_web2m_webhook_fails_with_missing_signature(): void
    {
        $payload = [
            'transaction_code' => 'TXN_TEST_003',
            'amount_vnd' => 100000,
        ];

        $response = $this->postJson('/webhook/web2m', $payload);

        $response->assertStatus(403);
        $response->assertJson(['message' => 'Missing webhook signature']);
    }

    /**
     * Test webhook with invalid payload (missing amount)
     */
    public function test_web2m_webhook_fails_with_invalid_payload(): void
    {
        $payload = [
            'transaction_code' => 'TXN_TEST_004',
            'amount_vnd' => 0, // Invalid amount
        ];

        $payloadJson = json_encode($payload);
        $signature = hash_hmac('sha256', $payloadJson, $this->secret);

        $response = $this->postJson('/webhook/web2m', $payload, [
            'X-Web2m-Signature' => $signature,
        ]);

        $response->assertStatus(422);
    }

    /**
     * Test webhook with different pricing tiers
     */
    public function test_web2m_webhook_calculates_correct_xu_for_different_amounts(): void
    {
        $testCases = [
            500000 => ['xu_main' => 500, 'xu_bonus' => 100],
            200000 => ['xu_main' => 200, 'xu_bonus' => 30],
            100000 => ['xu_main' => 100, 'xu_bonus' => 10],
            20000  => ['xu_main' => 20, 'xu_bonus' => 0],
            10000  => ['xu_main' => 10, 'xu_bonus' => 0], // 10000 / 1000 = 10
        ];

        foreach ($testCases as $amount => $expected) {
            $user = User::factory()->create();
            
            $payload = [
                'transaction_code' => 'TXN_' . $amount,
                'amount_vnd' => $amount,
                'description' => 'NAPXU ' . $user->id,
            ];

            $payloadJson = json_encode($payload);
            $signature = hash_hmac('sha256', $payloadJson, $this->secret);

            $response = $this->postJson('/webhook/web2m', $payload, [
                'X-Web2m-Signature' => $signature,
            ]);

            $response->assertStatus(200);
            $response->assertJson([
                'xu_main' => $expected['xu_main'],
                'xu_bonus' => $expected['xu_bonus'],
            ]);
        }
    }

    /**
     * Test duplicate transaction handling (idempotent)
     */
    public function test_web2m_webhook_handles_duplicate_transaction(): void
    {
        $user = User::factory()->create(['id' => 5]);
        
        $payload = [
            'transaction_code' => 'TXN_DUPLICATE',
            'amount_vnd' => 100000,
            'description' => 'NAPXU 5',
        ];

        $payloadJson = json_encode($payload);
        $signature = hash_hmac('sha256', $payloadJson, $this->secret);

        // First request
        $this->postJson('/webhook/web2m', $payload, [
            'X-Web2m-Signature' => $signature,
        ])->assertStatus(200);

        $initialBalance = $user->fresh()->xu_balance;

        // Duplicate request
        $this->postJson('/webhook/web2m', $payload, [
            'X-Web2m-Signature' => $signature,
        ])->assertStatus(200);

        // Balance should remain the same (no double increment)
        $this->assertEquals($initialBalance, $user->fresh()->xu_balance);
    }

    /**
     * Test user lookup by NAPXU pattern
     */
    public function test_web2m_webhook_finds_user_by_napxu_pattern(): void
    {
        $user = User::factory()->create(['id' => 123]);
        
        $payload = [
            'transaction_code' => 'TXN_NAPXU_TEST',
            'amount_vnd' => 100000,
            'description' => 'NAPXU 123', // Should find user by ID
            'email' => 'wrong@example.com', // Should be ignored
        ];

        $payloadJson = json_encode($payload);
        $signature = hash_hmac('sha256', $payloadJson, $this->secret);

        $response = $this->postJson('/webhook/web2m', $payload, [
            'X-Web2m-Signature' => $signature,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'transaction_code' => 'TXN_NAPXU_TEST',
        ]);
    }
}
