<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Status;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\Log;

class StatusApiTest extends TestCase
{
    use DatabaseTransactions;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        Log::info('StatusApiTest: setUp started');

        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
        Log::info('StatusApiTest: setUp finished');
    }

    /**
     * Test getting statuses.
     *
     * @return void
     */
    public function test_can_get_statuses()
    {
        Status::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->getJson('/api/status');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'user' => [
                            'id',
                            'name',
                            'phone_number',
                            'avatar_url',
                        ],
                        'statuses' => [
                            '*' => [
                                'id',
                                'type',
                                'content',
                                'media_url',
                                'background_color',
                                'text_color',
                                'font_family',
                                'created_at',
                                'expires_at',
                                'is_viewed',
                                'views_count',
                                'privacy',
                            ]
                        ],
                        'latest_status_at',
                        'has_unviewed',
                    ]
                ],
                'message'
            ]);
    }

    /**
     * Test creating a new status.
     *
     * @return void
     */
    public function test_can_create_status()
    {
        $statusData = [
            'type' => 'text',
            'content' => 'This is a test status.',
            'privacy' => 'everyone',
        ];

        $response = $this->postJson('/api/status', $statusData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Status uploaded successfully',
            ]);
    }
}
