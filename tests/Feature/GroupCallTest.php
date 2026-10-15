<?php

namespace Tests\Feature;

use App\Models\Call;
use App\Models\CallParticipant;
use App\Models\Chat;
use App\Models\User;
use App\Services\OneSignalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;
use Mockery;
use Tests\TestCase;

/**
 * Group calls: everyone in the group is rung with their own token, joining
 * and leaving only affect that person, and the call ends when it's empty.
 */
class GroupCallTest extends TestCase
{
    use RefreshDatabase;

    private User $alice;
    private User $bob;
    private User $carol;
    private User $outsider;
    private Chat $group;
    private $push;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();

        config([
            'services.agora.app_id' => str_repeat('a', 32),
            'services.agora.app_certificate' => str_repeat('b', 32),
        ]);

        [$this->alice, $this->bob, $this->carol, $this->outsider] = User::factory()->count(4)->create()->all();

        $this->group = Chat::create(['type' => 'group', 'name' => 'Ward 4', 'created_by' => $this->alice->id]);
        $this->group->participants()->attach([
            $this->alice->id => ['joined_at' => now()],
            $this->bob->id => ['joined_at' => now()],
            $this->carol->id => ['joined_at' => now()],
        ]);

        $this->push = Mockery::mock(OneSignalService::class);
        $this->app->instance(OneSignalService::class, $this->push);
    }

    private function start(User $as, string $type = 'video')
    {
        Sanctum::actingAs($as);
        return $this->postJson('/api/calls/group', ['chat_id' => $this->group->id, 'type' => $type]);
    }

    public function test_starting_rings_every_other_member_with_their_own_token()
    {
        $rung = [];
        $this->push->shouldReceive('sendCallNotification')->twice()
            ->andReturnUsing(function ($to, $data) use (&$rung) {
                $rung[$to] = $data;
                return true;
            });

        $response = $this->start($this->alice)->assertStatus(201);

        $this->assertEqualsCanonicalizing([$this->bob->id, $this->carol->id], array_map('intval', array_keys($rung)));
        foreach ($rung as $to => $data) {
            $this->assertSame('incoming_call', $data['type']);
            $this->assertSame('1', $data['is_group']);
            $this->assertSame('Ward 4', $data['caller_name']);
            $this->assertSame((string) $to, $data['receiver_id']);
            $this->assertStringStartsWith('006', $data['receiver_token']);
        }
        $this->assertNotSame($rung[(string) $this->bob->id]['receiver_token'], $rung[(string) $this->carol->id]['receiver_token']);

        $this->assertTrue($response->json('data.is_group'));
        $this->assertNull($response->json('data.receiver_id'));
        $this->assertSame((int) $this->alice->id, $response->json('data.agora_tokens.caller_uid'));
        $this->assertCount(3, $response->json('data.participants'));
    }

    public function test_starting_again_joins_the_running_call_instead_of_ringing_twice()
    {
        $this->push->shouldReceive('sendCallNotification')->twice()->andReturn(true);
        $first = $this->start($this->alice)->assertStatus(201)->json('data.id');

        $second = $this->start($this->bob)->assertStatus(200);

        $this->assertSame($first, $second->json('data.id'));
        $this->assertTrue($second->json('data.joined_existing'));
        $this->assertSame('joined', CallParticipant::where('call_id', $first)->where('user_id', $this->bob->id)->value('status'));
    }

    public function test_outsiders_and_private_chats_cannot_start_a_group_call()
    {
        $this->push->shouldNotReceive('sendCallNotification');
        $this->start($this->outsider)->assertStatus(403);

        $private = Chat::create(['type' => 'private', 'created_by' => $this->alice->id]);
        $private->participants()->attach([$this->alice->id => ['joined_at' => now()]]);
        Sanctum::actingAs($this->alice);
        $this->postJson('/api/calls/group', ['chat_id' => $private->id, 'type' => 'audio'])->assertStatus(400);
    }

    public function test_members_get_their_own_token_and_outsiders_none()
    {
        $this->push->shouldReceive('sendCallNotification')->andReturn(true);
        $callId = $this->start($this->alice)->json('data.id');

        Sanctum::actingAs($this->bob);
        $tokens = $this->getJson("/api/calls/$callId/agora-tokens")->assertOk()->json('data');
        $this->assertSame((int) $this->bob->id, $tokens['receiver_uid']);
        $this->assertSame($tokens['token'], $tokens['receiver_token']);

        Sanctum::actingAs($this->outsider);
        $this->getJson("/api/calls/$callId/agora-tokens")->assertStatus(403);
    }

    public function test_call_lives_until_the_last_person_leaves_and_non_joiners_missed_it()
    {
        $this->push->shouldReceive('sendCallNotification')->andReturn(true);
        $callId = $this->start($this->alice)->json('data.id');

        Sanctum::actingAs($this->bob);
        $this->postJson("/api/calls/$callId/accept")->assertOk();

        // The starter leaving doesn't end it for Bob.
        Sanctum::actingAs($this->alice);
        $this->postJson("/api/calls/$callId/end")->assertOk();
        $this->assertSame('answered', Call::find($callId)->status);

        Sanctum::actingAs($this->bob);
        $this->postJson("/api/calls/$callId/end")->assertOk();
        $this->assertSame('ended', Call::find($callId)->status);

        // Carol never picked up.
        Sanctum::actingAs($this->carol);
        $history = $this->getJson('/api/calls')->assertOk()->json('data.data');
        $this->assertSame('missed', $history[0]['status']);
        $this->assertTrue($history[0]['is_group']);
        $this->assertSame('Ward 4', $history[0]['callee']['name']);
    }

    public function test_declining_stops_only_your_own_ring()
    {
        $this->push->shouldReceive('sendCallNotification')->andReturn(true);
        $callId = $this->start($this->alice)->json('data.id');

        Sanctum::actingAs($this->carol);
        $this->postJson("/api/calls/$callId/decline")->assertOk();

        $this->assertSame('answered', Call::find($callId)->status);
        $this->assertSame('declined', CallParticipant::where('call_id', $callId)->where('user_id', $this->carol->id)->value('status'));
    }

    public function test_invited_members_see_the_call_ringing_in_active_calls()
    {
        $this->push->shouldReceive('sendCallNotification')->andReturn(true);
        $callId = $this->start($this->alice)->json('data.id');

        Sanctum::actingAs($this->bob);
        $active = $this->getJson('/api/calls/active')->assertOk()->json('data');

        $this->assertSame($callId, $active[0]['id']);
        $this->assertSame('ringing', $active[0]['status']);
        $this->assertSame((int) $this->bob->id, $active[0]['callee_id']);
    }
}
