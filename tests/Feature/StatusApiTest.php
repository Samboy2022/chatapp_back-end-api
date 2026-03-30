<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\User;
use App\Models\Status;
use Laravel\Sanctum\Sanctum;

class StatusApiTest extends TestCase
{
    use DatabaseTransactions;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    public function test_can_get_statuses()
    {
        Status::factory()->count(3)->create(['user_id' => $this->user->id]);
        $response = $this->getJson('/api/status');
        $response->assertStatus(200);
    }

    public function test_can_create_status()
    {
        $statusData = [
            'type' => 'text',
            'content' => 'Test status',
            'privacy' => 'everyone',
        ];

        $response = $this->postJson('/api/status', $statusData);
        $response->assertStatus(201);
    }

    public function test_can_create_status_with_media()
    {
        $statusData = [
            'type' => 'image',
            'content' => 'Image status',
            'media_url' => 'https://example.com/image.jpg',
            'privacy' => 'contacts',
        ];

        $response = $this->postJson('/api/status', $statusData);
        $response->assertStatus(201);
    }

    public function test_can_create_status_with_background_styling()
    {
        $statusData = [
            'type' => 'text',
            'content' => 'Styled status',
            'privacy' => 'everyone',
            'background_color' => '#FF5733',
            'font_family' => 'Arial',
        ];

        $response = $this->postJson('/api/status', $statusData);
        $response->assertStatus(201);
    }

    public function test_can_get_user_statuses()
    {
        Status::factory()->count(3)->create(['user_id' => $this->user->id]);
        $response = $this->getJson('/api/status/user/' . $this->user->id);
        $response->assertStatus(200);
    }

    public function test_can_mark_status_as_viewed()
    {
        $otherUser = User::factory()->create();
        $status = Status::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->postJson('/api/status/' . $status->id . '/view');
        $response->assertStatus(200);
    }

    public function test_can_get_status_viewers()
    {
        $status = Status::factory()->create(['user_id' => $this->user->id]);
        $response = $this->getJson('/api/status/' . $status->id . '/viewers');
        $response->assertStatus(200);
    }

    public function test_can_delete_own_status()
    {
        $status = Status::factory()->create(['user_id' => $this->user->id]);
        $response = $this->deleteJson('/api/status/' . $status->id);
        $response->assertStatus(200);
    }

    public function test_cannot_delete_other_users_status()
    {
        $otherUser = User::factory()->create();
        $status = Status::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->deleteJson('/api/status/' . $status->id);
        $response->assertStatus(403);
    }

    public function test_status_validation_errors()
    {
        $response = $this->postJson('/api/status', [
            'content' => 'No type',
            'privacy' => 'everyone'
        ]);
        $response->assertStatus(422);

        $response = $this->postJson('/api/status', [
            'type' => 'text',
            'content' => 'Invalid privacy',
            'privacy' => 'invalid'
        ]);
        $response->assertStatus(422);
    }

    public function test_statuses_respect_privacy_settings()
    {
        $status = Status::factory()->create([
            'user_id' => $this->user->id,
            'privacy_settings' => ['visibility' => 'contacts']
        ]);

        $stranger = User::factory()->create();
        $this->actingAs($stranger);

        $response = $this->getJson('/api/status');
        $response->assertStatus(200);
    }
}