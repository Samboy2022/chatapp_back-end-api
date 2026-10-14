<?php

namespace Tests\Feature;

use App\Models\Call;
use App\Models\User;
use App\Services\OneSignalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;
use Mockery;
use Tests\TestCase;

/**
 * Placing a call: tokens are minted for the exact uids the apps join with,
 * and a call that can't get tokens never rings anyone.
 */
class CallFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $caller;
    private User $receiver;
    private $push;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();

        $this->caller = User::factory()->create();
        $this->receiver = User::factory()->create();

        $this->push = Mockery::mock(OneSignalService::class);
        $this->app->instance(OneSignalService::class, $this->push);
    }

    private function configureAgora(): void
    {
        config([
            'services.agora.app_id' => str_repeat('a', 32),
            'services.agora.app_certificate' => str_repeat('b', 32),
        ]);
    }

    private function placeCall(string $type = 'video')
    {
        Sanctum::actingAs($this->caller);
        return $this->postJson('/api/calls', [
            'receiver_id' => $this->receiver->id,
            'type' => $type,
        ]);
    }

    public function test_call_returns_tokens_for_both_uids_and_rings_the_receiver()
    {
        $this->configureAgora();
        $this->push->shouldReceive('sendCallNotification')->once()
            ->withArgs(fn ($to, $data) => $to === (string) $this->receiver->id
                && $data['type'] === 'incoming_call'
                && $data['receiver_id'] === (string) $this->receiver->id
                && !empty($data['receiver_token']))
            ->andReturn(true);

        $response = $this->placeCall()->assertStatus(201);

        $tokens = $response->json('data.agora_tokens');
        $this->assertSame((int) $this->caller->id, $tokens['caller_uid']);
        $this->assertSame((int) $this->receiver->id, $tokens['receiver_uid']);
        $this->assertSame('call_' . $response->json('data.id'), $tokens['channel']);
        $this->assertSame(str_repeat('a', 32), $tokens['app_id']);
        // AccessToken 006 embeds the App ID right after the version prefix.
        $this->assertStringStartsWith('006' . str_repeat('a', 32), $tokens['caller_token']);
        $this->assertNotSame($tokens['caller_token'], $tokens['receiver_token']);
    }

    public function test_no_agora_config_means_no_call_and_no_ringing()
    {
        config(['services.agora.app_id' => null, 'services.agora.app_certificate' => null]);
        $this->push->shouldNotReceive('sendCallNotification');

        $this->placeCall()->assertStatus(503)->assertJsonPath('success', false);

        $this->assertSame(0, Call::whereIn('status', ['ringing', 'answered'])->count());
    }

    public function test_token_endpoint_gives_each_side_its_own_token()
    {
        $this->configureAgora();
        $this->push->shouldReceive('sendCallNotification')->andReturn(true);
        $callId = $this->placeCall('audio')->json('data.id');

        Sanctum::actingAs($this->receiver);
        $mine = $this->getJson("/api/calls/{$callId}/agora-tokens")->assertOk();

        $this->assertSame((int) $this->receiver->id, $mine->json('data.uid'));
        $this->assertSame($mine->json('data.receiver_token'), $mine->json('data.token'));
    }

    public function test_answering_a_cancelled_call_is_refused()
    {
        $this->configureAgora();
        $this->push->shouldReceive('sendCallNotification')->andReturn(true);
        $callId = $this->placeCall('audio')->json('data.id');

        $this->postJson("/api/calls/{$callId}/end")->assertOk(); // caller hangs up

        Sanctum::actingAs($this->receiver);
        $this->postJson("/api/calls/{$callId}/accept")
            ->assertStatus(410)
            ->assertJsonPath('success', false);
    }
}
