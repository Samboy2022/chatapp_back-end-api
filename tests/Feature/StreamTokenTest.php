<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class StreamTokenTest extends TestCase
{
    // Do not refresh DB here to avoid wiping existing test data in this repo.
    // use RefreshDatabase;

    /**
     * Ensure the protected stream token endpoint returns a token for an authenticated user.
     */
    public function test_stream_token_endpoint_returns_token()
    {
        // Create a fresh user for this test using a factory to avoid depending on repo state
        if (method_exists(User::class, 'factory')) {
            $user = User::factory()->create();
        } else {
            // Fallback: create user directly if factories are not available
            $user = User::create([
                'name' => 'Test User',
                'email' => 'test+' . time() . '@example.com',
                'password' => bcrypt('password')
            ]);
        }

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/stream/token', []);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'token',
                'api_key',
                'user_id',
                'expires_at'
            ],
            'message'
        ]);
    }
}
