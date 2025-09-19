<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Call;
use App\Models\Chat;
use App\Models\Contact;
use Laravel\Sanctum\Sanctum;
use Carbon\Carbon;

class CallApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $otherUser;
    protected $call;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test users
        $this->user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone_number' => '1234567890'
        ]);

        $this->otherUser = User::factory()->create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'phone_number' => '0987654321'
        ]);

        // Create a contact relationship
        Contact::factory()->create([
            'user_id' => $this->user->id,
            'contact_user_id' => $this->otherUser->id,
            'is_blocked' => false
        ]);

        Contact::factory()->create([
            'user_id' => $this->otherUser->id,
            'contact_user_id' => $this->user->id,
            'is_blocked' => false
        ]);

        // Authenticate the user
        Sanctum::actingAs($this->user);
    }

    /**
     * Create a call for testing
     */
     protected function createCall($status = 'ringing', $callType = 'audio')
     {
         return Call::create([
             'chat_id' => Chat::factory()->create(['type' => 'private'])->id,
             'caller_id' => $this->user->id,
             'receiver_id' => $this->otherUser->id,
             'type' => $callType,
             'call_type' => $callType,
             'status' => $status,
             'started_at' => $status === 'answered' ? now()->subMinutes(5) : now(),
             'answered_at' => $status === 'answered' ? now()->subMinutes(4) : null,
             'ended_at' => in_array($status, ['ended', 'declined', 'missed']) ? now() : null,
             'duration' => $status === 'ended' ? 240 : 0,
         ]);
     }

   #[Test]
   public function user_can_initiate_call()
    {
        $callData = [
            'receiver_id' => $this->otherUser->id,
            'type' => 'audio'
        ];

        $response = $this->postJson('/api/calls', $callData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Call initiated successfully'
            ]);

        // Just check that we have the basic structure
        $response->assertJsonStructure([
            'success',
            'message',
            'data'
        ]);

        // Verify call was created in database
        $this->assertDatabaseHas('calls', [
            'caller_id' => $this->user->id,
            'receiver_id' => $this->otherUser->id,
            'status' => 'ringing'
        ]);
    }

   #[Test]
   public function user_can_initiate_video_call()
    {
        $callData = [
            'receiver_id' => $this->otherUser->id,
            'type' => 'video'
        ];

        $response = $this->postJson('/api/calls', $callData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Call initiated successfully'
            ]);

        $this->assertDatabaseHas('calls', [
            'caller_id' => $this->user->id,
            'receiver_id' => $this->otherUser->id,
            'call_type' => 'video'
        ]);
    }

    #[Test]
    public function user_cannot_call_self()
    {
        $callData = [
            'receiver_id' => $this->user->id,
            'type' => 'audio'
        ];

        $response = $this->postJson('/api/calls', $callData);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'You cannot call yourself'
            ]);
    }

   #[Test]
   public function user_cannot_call_blocked_contact()
    {
        // Block the contact
        Contact::where('user_id', $this->otherUser->id)
            ->where('contact_user_id', $this->user->id)
            ->update(['is_blocked' => true]);

        $callData = [
            'receiver_id' => $this->otherUser->id,
            'type' => 'audio'
        ];

        $response = $this->postJson('/api/calls', $callData);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Unable to place call'
            ]);
    }

    /** @test */
    public function call_validation_fails_for_invalid_data()
    {
        $callData = [
            'receiver_id' => 'invalid-id',
            'type' => 'voice' // Should be audio or video
        ];

        $response = $this->postJson('/api/calls', $callData);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Validation failed'
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'errors' => [
                    'receiver_id'
                ]
            ]);
    }

   #[Test]
   public function user_cannot_answer_call_open_to_different_user()
    {
        $thirdUser = User::factory()->create();

        $this->actingAs($thirdUser);

        $call = $this->createCall('ringing');

        $response = $this->postJson("/api/calls/{$call->id}/answer");

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'You are not authorized to answer this call'
            ]);
    }

   #[Test]
   public function user_can_view_call_history()
    {
        // Create some calls
        Call::factory()->create([
            'caller_id' => $this->user->id,
            'receiver_id' => $this->otherUser->id,
            'status' => 'ended'
        ]);

        Call::factory()->create([
            'caller_id' => $this->user->id,
            'receiver_id' => $this->otherUser->id,
            'status' => 'answered'
        ]);

        $response = $this->getJson('/api/calls');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Call history retrieved successfully'
            ]);
    }

   #[Test]
   public function user_can_filter_calls_by_type()
    {
        // Create mixed call types
        Call::create([
            'chat_id' => Chat::factory()->create(['type' => 'private'])->id,
            'caller_id' => $this->user->id,
            'receiver_id' => $this->otherUser->id,
            'type' => 'audio',
            'call_type' => 'audio',
            'status' => 'ended'
        ]);

        Call::create([
            'chat_id' => Chat::factory()->create(['type' => 'private'])->id,
            'caller_id' => $this->user->id,
            'receiver_id' => $this->otherUser->id,
            'type' => 'video',
            'call_type' => 'video',
            'status' => 'ended'
        ]);

        $response = $this->getJson('/api/calls?type=audio');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        // Verify only video calls are returned
        $data = $response->json('data.data');
        foreach ($data as $call) {
            $this->assertEquals('audio', $call['type']);
        }
    }

   #[Test]
   public function user_can_view_specific_call_details()
    {
        $call = $this->createCall('ended');

        $response = $this->getJson("/api/calls/{$call->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Call details retrieved successfully'
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'caller_id',
                    'receiver_id',
                    'type',
                    'status',
                    'duration'
                ]
            ]);
    }

   #[Test]
   public function user_cannot_view_call_details_they_are_not_part_of()
    {
        $thirdUser = User::factory()->create();
        $fourthUser = User::factory()->create();

        $call = Call::create([
            'chat_id' => Chat::factory()->create(['type' => 'private'])->id,
            'caller_id' => $thirdUser->id,
            'receiver_id' => $fourthUser->id,
            'type' => 'audio',
            'status' => 'ended'
        ]);

        $response = $this->getJson("/api/calls/{$call->id}");

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'You are not authorized to view this call'
            ]);
    }

   #[Test]
   public function receiver_can_decline_call()
    {
        $call = $this->createCall('ringing');

        // Switch to receiver user
        $this->actingAs($this->otherUser);

        $response = $this->postJson("/api/calls/{$call->id}/decline");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Call declined successfully'
            ]);

        // Verify call status was updated
        $call->refresh();
        $this->assertEquals('declined', $call->status);
        $this->assertNotNull($call->ended_at);
    }

   #[Test]
   public function receiver_can_answer_call()
    {
        $call = $this->createCall('ringing');

        // Switch to receiver user
        $this->actingAs($this->otherUser);

        $response = $this->postJson("/api/calls/{$call->id}/answer");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Call answered successfully'
            ]);

        // Verify call status was updated
        $call->refresh();
        $this->assertEquals('answered', $call->status);
        $this->assertNotNull($call->answered_at);
    }

   #[Test]
   public function only_caller_or_receiver_can_end_call()
    {
        $call = $this->createCall('answered');

        $thirdUser = User::factory()->create();
        $this->actingAs($thirdUser);

        $response = $this->postJson("/api/calls/{$call->id}/end");

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'You are not authorized to end this call'
            ]);
    }

   #[Test]
   public function call_participants_can_end_call()
    {
        $call = $this->createCall('answered');

        $response = $this->postJson("/api/calls/{$call->id}/end");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Call ended successfully'
            ]);

        // Verify call was ended and duration calculated
        $call->refresh();
        $this->assertEquals('ended', $call->status);
        $this->assertNotNull($call->ended_at);
        $this->assertGreaterThan(0, $call->duration);
    }

   #[Test]
   public function user_can_get_call_statistics()
    {
        // Create various call types for testing
        $this->createCall('answered');
        $this->createCall('declined');
        $this->createCall('ended');

        $response = $this->getJson('/api/calls/statistics');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Call statistics retrieved successfully'
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'total_calls',
                    'outgoing_calls',
                    'incoming_calls',
                    'answered_calls',
                    'missed_calls',
                    'total_talk_time',
                    'total_talk_time_formatted',
                    'video_calls',
                    'audio_calls'
                ]
            ]);
    }

   #[Test]
   public function user_can_get_active_calls()
    {
        // Create active calls
        $ringingCall = $this->createCall('ringing');
        $answeredCall = $this->createCall('answered');

        // Create ended call (should not appear in active calls)
        $endedCall = Call::create([
            'chat_id' => Chat::factory()->create(['type' => 'private'])->id,
            'caller_id' => $this->user->id,
            'receiver_id' => $this->otherUser->id,
            'type' => 'audio',
            'status' => 'ended',
        ]);

        $response = $this->getJson('/api/calls/active');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Active calls retrieved successfully'
            ]);

        $data = $response->json('data');
        $this->assertCount(2, $data); // Should have 2 active calls

        // Verify both calls are returned with correct status
        $statuses = collect($data)->pluck('status');
        $this->assertContains('ringing', $statuses);
        $this->assertContains('answered', $statuses);
    }

   #[Test]
   public function user_can_get_missed_calls_count()
    {
        // Create missed calls (received but not answered)
        Call::create([
            'chat_id' => Chat::factory()->create(['type' => 'private'])->id,
            'caller_id' => $this->otherUser->id,
            'receiver_id' => $this->user->id,
            'type' => 'audio',
            'status' => 'declined',
            'duration' => 0
        ]);

        Call::create([
            'chat_id' => Chat::factory()->create(['type' => 'private'])->id,
            'caller_id' => $this->otherUser->id,
            'receiver_id' => $this->user->id,
            'type' => 'audio',
            'status' => 'ended',
            'duration' => 0
        ]);

        // Create answered call (shouldn't count as missed)
        Call::create([
            'chat_id' => Chat::factory()->create(['type' => 'private'])->id,
            'caller_id' => $this->otherUser->id,
            'receiver_id' => $this->user->id,
            'type' => 'audio',
            'status' => 'answered',
            'duration' => 120
        ]);

        $response = $this->getJson('/api/calls/missed-count');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => ['missed_calls_count' => 2]
            ]);
    }

    /** @test */
    public function stale_ringing_calls_cleanup_works()
    {
        // Create stale call (started more than 2 minutes ago)
        $staleCall = Call::create([
            'chat_id' => Chat::factory()->create(['type' => 'private'])->id,
            'caller_id' => $this->user->id,
            'receiver_id' => $this->otherUser->id,
            'type' => 'audio',
            'status' => 'ringing',
            'started_at' => now()->subMinutes(3) // 3 minutes ago
        ]);

        $response = $this->getJson('/api/calls/cleanup-stale');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => ['ended_calls_count' => 1]
            ]);

        // Verify stale call was ended
        $staleCall->refresh();
        $this->assertEquals('ended', $staleCall->status);
        $this->assertNotNull($staleCall->ended_at);
    }

   #[Test]
   public function accept_method_is_alias_for_answer()
    {
        $call = $this->createCall('ringing');

        // Switch to receiver user
        $this->actingAs($this->otherUser);

        $response = $this->postJson("/api/calls/{$call->id}/accept");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Call answered successfully'
            ]);

        $call->refresh();
        $this->assertEquals('answered', $call->status);
    }

   #[Test]
   public function reject_method_is_alias_for_decline()
    {
        $call = $this->createCall('ringing');

        // Switch to receiver user
        $this->actingAs($this->otherUser);

        $response = $this->postJson("/api/calls/{$call->id}/reject");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Call declined successfully'
            ]);

        $call->refresh();
        $this->assertEquals('declined', $call->status);
    }

    #[Test]
    public function pagination_works_for_call_history()
    {
        // Create multiple calls
        for ($i = 0; $i < 10; $i++) {
            $this->createCall('ended');
        }

        $response = $this->getJson('/api/calls?per_page=5');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertCount(5, $data['data']);
        $this->assertEquals(10, $data['total']);
        $this->assertEquals(5, $data['per_page']);
    }
}
