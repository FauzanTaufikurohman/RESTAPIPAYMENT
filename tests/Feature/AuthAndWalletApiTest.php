<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthAndWalletApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_login(): void
    {
        $payload = [
            'first_name' => 'Alice',
            'last_name' => 'Sample',
            'address' => 'Bandung',
            'email' => 'alice@example.com',
            'phone_number' => '081234567890',
            'pin' => '123456',
            'password' => 'secret123',
        ];

        $registerResponse = $this->postJson('/api/register', $payload);
        $registerResponse->assertStatus(201)
            ->assertJsonPath('user.email', 'alice@example.com')
            ->assertJsonPath('user.phone_number', '081234567890')
            ->assertJsonStructure(['token', 'user']);

        $loginResponse = $this->postJson('/api/login', [
            'phone_number' => '081234567890',
            'pin' => '123456',
        ]);

        $loginResponse->assertStatus(200)
            ->assertJsonStructure(['token', 'user']);
    }

    public function test_authenticated_user_can_top_up_payment_transfer_and_report_transactions(): void
    {
        $this->postJson('/api/register', [
            'first_name' => 'Alice',
            'last_name' => 'Sample',
            'address' => 'Bandung',
            'email' => 'alice@example.com',
            'phone_number' => '081234567890',
            'pin' => '123456',
            'password' => 'secret123',
        ]);

        $this->postJson('/api/register', [
            'first_name' => 'Bob',
            'last_name' => 'Sample',
            'address' => 'Jakarta',
            'email' => 'bob@example.com',
            'phone_number' => '081234567891',
            'pin' => '654321',
            'password' => 'secret123',
        ]);

        $token = $this->postJson('/api/login', [
            'phone_number' => '081234567890',
            'pin' => '123456',
        ])->json('token');

        $this->withHeader('Authorization', $token)
            ->postJson('/api/top-ups', ['amount' => 50000])
            ->assertStatus(201)
            ->assertJsonPath('transaction.type', 'top_up');

        $this->withHeader('Authorization', $token)
            ->postJson('/api/payments', ['amount' => 15000, 'description' => 'Groceries'])
            ->assertStatus(201)
            ->assertJsonPath('transaction.type', 'payment');

        $this->withHeader('Authorization', $token)
            ->postJson('/api/transfers', [
                'receiver_phone' => '081234567891',
                'amount' => 10000,
                'description' => 'Dinner',
            ])
            ->assertStatus(202)
            ->assertJsonPath('status', 'queued');

        $this->withHeader('Authorization', $token)
            ->getJson('/api/transactions')
            ->assertStatus(200)
            ->assertJsonStructure(['data']);

        $this->withHeader('Authorization', $token)
            ->putJson('/api/profile', ['first_name' => 'Alice Updated'])
            ->assertStatus(200)
            ->assertJsonPath('user.first_name', 'Alice Updated');
    }
}
