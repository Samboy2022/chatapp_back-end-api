<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Fake events and queues for testing
        Event::fake();
        Queue::fake();
        Storage::fake('public');
        
        // Set up test database
        $this->artisan('migrate:fresh');
    }

    /**
     * Create and authenticate a user for testing
     */
    protected function authenticateUser($attributes = []): User
    {
        $user = User::factory()->create($attributes);
        Sanctum::actingAs($user);
        return $user;
    }

    /**
     * Create multiple users for testing
     */
    protected function createUsers(int $count = 3): array
    {
        return User::factory()->count($count)->create()->toArray();
    }

    /**
     * Assert that a broadcast event was dispatched
     */
    protected function assertEventDispatched(string $event, callable $callback = null): void
    {
        Event::assertDispatched($event, $callback);
    }

    /**
     * Assert that a job was dispatched
     */
    protected function assertJobDispatched(string $job, callable $callback = null): void
    {
        Queue::assertPushed($job, $callback);
    }

    /**
     * Create test file for upload testing
     */
    protected function createTestFile(string $name = 'test.jpg', string $content = 'test content'): \Illuminate\Http\UploadedFile
    {
        Storage::fake('public');
        return \Illuminate\Http\UploadedFile::fake()->image($name);
    }
}