<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * "Online" needs a recent check-in, so a phone that died without saying
 * "offline" stops showing as online on its own.
 */
class PresenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_recent_check_in_is_online_and_a_stale_one_is_away()
    {
        $fresh = User::factory()->create(['is_online' => true, 'last_seen_at' => now()->subMinute()]);
        $stale = User::factory()->create(['is_online' => true, 'last_seen_at' => now()->subMinutes(10)]);
        $off = User::factory()->create(['is_online' => false, 'last_seen_at' => now()]);

        $this->assertTrue($fresh->fresh()->is_online);
        $this->assertFalse($stale->fresh()->is_online);
        $this->assertFalse($off->fresh()->is_online);
    }

    public function test_heartbeat_keeps_a_user_online()
    {
        $user = User::factory()->create(['is_online' => true, 'last_seen_at' => now()->subMinutes(10)]);
        $this->assertFalse($user->fresh()->is_online);

        Sanctum::actingAs($user);
        $this->postJson('/api/websocket/online-status', ['is_online' => true])->assertOk();

        $this->assertTrue($user->fresh()->is_online);
    }
}
