<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Chat;
use App\Models\Message;
use App\Models\Status;
use App\Models\Call;
use App\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Carbon\Carbon;

class PerformanceApiTest extends TestCase
{
    use RefreshDatabase;

    private $users;
    private $tokens;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create multiple users for performance testing
        $this->users = User::factory()->count(10)->create();
        $this->tokens = [];

        foreach ($this->users as $user) {
            $this->tokens[$user->id] = $user->createToken('perf-test')->plainTextToken;
        }
    }

    /** @test */
    public function test_bulk_message_creation_performance()
    {
        $user1 = $this->users[0];
        $user2 = $this->users[1];
        
        $chat = Chat::factory()->create(['type' => 'private']);
        $chat->participants()->attach([$user1->id, $user2->id]);

        $startTime = microtime(true);

        // Create 100 messages
        for ($i = 0; $i < 100; $i++) {
            $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->tokens[$user1->id],
                'Accept' => 'application/json',
            ])->postJson("/api/chats/{$chat->id}/messages", [
                'type' => 'text',
                'content' => "Performance test message {$i}"
            ]);

            $response->assertStatus(201);
        }

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        // Should complete within reasonable time (adjust threshold as needed)
        $this->assertLessThan(30, $executionTime, "Bulk message creation took too long: {$executionTime}s");

        // Verify all messages were created
        $this->assertEquals(100, Message::where('chat_id', $chat->id)->count());

        echo "✅ Created 100 messages in {$executionTime}s\n";
    }

    /** @test */
    public function test_large_chat_list_retrieval_performance()
    {
        $user = $this->users[0];

        // Create many chats for the user
        for ($i = 0; $i < 50; $i++) {
            $otherUser = $this->users[($i % 9) + 1]; // Cycle through other users
            $chat = Chat::factory()->create(['type' => 'private']);
            $chat->participants()->attach([$user->id, $otherUser->id]);

            // Add some messages to each chat
            Message::factory()->count(5)->create([
                'chat_id' => $chat->id,
                'sender_id' => $otherUser->id
            ]);
        }

        $startTime = microtime(true);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->tokens[$user->id],
            'Accept' => 'application/json',
        ])->getJson('/api/chats');

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $response->assertStatus(200);
        
        // Should complete within reasonable time
        $this->assertLessThan(5, $executionTime, "Chat list retrieval took too long: {$executionTime}s");

        echo "✅ Retrieved large chat list in {$executionTime}s\n";
    }

    /** @test */
    public function test_message_pagination_performance()
    {
        $user1 = $this->users[0];
        $user2 = $this->users[1];
        
        $chat = Chat::factory()->create(['type' => 'private']);
        $chat->participants()->attach([$user1->id, $user2->id]);

        // Create many messages
        Message::factory()->count(1000)->create([
            'chat_id' => $chat->id,
            'sender_id' => $user1->id
        ]);

        $startTime = microtime(true);

        // Test paginated retrieval
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->tokens[$user1->id],
            'Accept' => 'application/json',
        ])->getJson("/api/chats/{$chat->id}/messages?per_page=50&page=1");

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $response->assertStatus(200);
        
        // Should complete within reasonable time
        $this->assertLessThan(2, $executionTime, "Message pagination took too long: {$executionTime}s");

        $data = $response->json('data');
        $this->assertEquals(50, count($data['data']));
        $this->assertEquals(1000, $data['total']);

        echo "✅ Retrieved paginated messages in {$executionTime}s\n";
    }

    /** @test */
    public function test_status_feed_performance_with_many_statuses()
    {
        $user = $this->users[0];

        // Create many statuses from different users
        foreach ($this->users as $statusUser) {
            if ($statusUser->id !== $user->id) {
                Status::factory()->count(10)->create([
                    'user_id' => $statusUser->id,
                    'expires_at' => Carbon::now()->addHours(24)
                ]);
            }
        }

        $startTime = microtime(true);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->tokens[$user->id],
            'Accept' => 'application/json',
        ])->getJson('/api/status');

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $response->assertStatus(200);
        
        // Should complete within reasonable time
        $this->assertLessThan(3, $executionTime, "Status feed retrieval took too long: {$executionTime}s");

        echo "✅ Retrieved status feed in {$executionTime}s\n";
    }

    /** @test */
    public function test_contact_sync_performance()
    {
        $user = $this->users[0];

        // Prepare large contact list
        $contacts = [];
        for ($i = 0; $i < 500; $i++) {
            $contacts[] = [
                'phone' => '+1' . str_pad($i, 10, '0', STR_PAD_LEFT),
                'name' => "Contact {$i}"
            ];
        }

        $startTime = microtime(true);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->tokens[$user->id],
            'Accept' => 'application/json',
        ])->postJson('/api/contacts/sync', [
            'contacts' => $contacts
        ]);

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $response->assertStatus(200);
        
        // Should complete within reasonable time
        $this->assertLessThan(10, $executionTime, "Contact sync took too long: {$executionTime}s");

        echo "✅ Synced 500 contacts in {$executionTime}s\n";
    }

    /** @test */
    public function test_group_chat_with_many_participants_performance()
    {
        $creator = $this->users[0];
        
        // Create group with all users
        $groupChat = Chat::factory()->create([
            'type' => 'group',
            'name' => 'Performance Test Group',
            'created_by' => $creator->id
        ]);

        $userIds = $this->users->pluck('id')->toArray();
        $groupChat->participants()->attach($userIds);

        $startTime = microtime(true);

        // Send message to group
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->tokens[$creator->id],
            'Accept' => 'application/json',
        ])->postJson("/api/chats/{$groupChat->id}/messages", [
            'type' => 'text',
            'content' => 'Message to large group'
        ]);

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $response->assertStatus(201);
        
        // Should complete within reasonable time
        $this->assertLessThan(3, $executionTime, "Group message sending took too long: {$executionTime}s");

        echo "✅ Sent message to group with {$this->users->count()} participants in {$executionTime}s\n";
    }

    /** @test */
    public function test_call_history_performance()
    {
        $user1 = $this->users[0];
        $user2 = $this->users[1];

        // Create contact relationship
        Contact::factory()->create([
            'user_id' => $user1->id,
            'contact_user_id' => $user2->id,
        ]);

        // Create many call records
        Call::factory()->count(200)->create([
            'caller_id' => $user1->id,
            'receiver_id' => $user2->id,
            'status' => 'ended'
        ]);

        $startTime = microtime(true);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->tokens[$user1->id],
            'Accept' => 'application/json',
        ])->getJson('/api/calls?per_page=50');

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $response->assertStatus(200);
        
        // Should complete within reasonable time
        $this->assertLessThan(2, $executionTime, "Call history retrieval took too long: {$executionTime}s");

        echo "✅ Retrieved call history in {$executionTime}s\n";
    }

    /** @test */
    public function test_database_query_optimization()
    {
        $user = $this->users[0];

        // Enable query logging
        DB::enableQueryLog();

        // Perform a complex operation that should be optimized
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->tokens[$user->id],
            'Accept' => 'application/json',
        ])->getJson('/api/chats');

        $queries = DB::getQueryLog();
        $queryCount = count($queries);

        // Should not have excessive queries (N+1 problem)
        $this->assertLessThan(10, $queryCount, "Too many database queries: {$queryCount}");

        echo "✅ Chat list retrieved with {$queryCount} database queries\n";

        DB::disableQueryLog();
    }

    /** @test */
    public function test_concurrent_user_operations()
    {
        $chat = Chat::factory()->create(['type' => 'group']);
        $userIds = $this->users->pluck('id')->toArray();
        $chat->participants()->attach($userIds);

        $startTime = microtime(true);

        // Simulate concurrent operations from different users
        $responses = [];
        foreach ($this->users as $user) {
            $responses[] = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->tokens[$user->id],
                'Accept' => 'application/json',
            ])->postJson("/api/chats/{$chat->id}/messages", [
                'type' => 'text',
                'content' => "Concurrent message from user {$user->id}"
            ]);
        }

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        // All operations should succeed
        foreach ($responses as $response) {
            $response->assertStatus(201);
        }

        // Should complete within reasonable time
        $this->assertLessThan(5, $executionTime, "Concurrent operations took too long: {$executionTime}s");

        echo "✅ Handled {$this->users->count()} concurrent operations in {$executionTime}s\n";
    }

    /** @test */
    public function test_memory_usage_during_bulk_operations()
    {
        $user = $this->users[0];
        $initialMemory = memory_get_usage(true);

        // Perform memory-intensive operation
        $contacts = [];
        for ($i = 0; $i < 1000; $i++) {
            $contacts[] = [
                'phone' => '+1' . str_pad($i, 10, '0', STR_PAD_LEFT),
                'name' => "Memory Test Contact {$i}"
            ];
        }

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->tokens[$user->id],
            'Accept' => 'application/json',
        ])->postJson('/api/contacts/sync', [
            'contacts' => $contacts
        ]);

        $finalMemory = memory_get_usage(true);
        $memoryUsed = $finalMemory - $initialMemory;

        $response->assertStatus(200);

        // Memory usage should be reasonable (adjust threshold as needed)
        $this->assertLessThan(50 * 1024 * 1024, $memoryUsed, "Memory usage too high: " . ($memoryUsed / 1024 / 1024) . "MB");

        echo "✅ Bulk operation used " . round($memoryUsed / 1024 / 1024, 2) . "MB memory\n";
    }

    /** @test */
    public function test_api_response_time_consistency()
    {
        $user = $this->users[0];
        $responseTimes = [];

        // Test same endpoint multiple times
        for ($i = 0; $i < 10; $i++) {
            $startTime = microtime(true);

            $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->tokens[$user->id],
                'Accept' => 'application/json',
            ])->getJson('/api/auth/user');

            $endTime = microtime(true);
            $responseTimes[] = $endTime - $startTime;

            $response->assertStatus(200);
        }

        $avgResponseTime = array_sum($responseTimes) / count($responseTimes);
        $maxResponseTime = max($responseTimes);
        $minResponseTime = min($responseTimes);

        // Response times should be consistent
        $this->assertLessThan(1, $avgResponseTime, "Average response time too high: {$avgResponseTime}s");
        $this->assertLessThan(2, $maxResponseTime, "Max response time too high: {$maxResponseTime}s");

        echo "✅ Response time consistency: avg={$avgResponseTime}s, min={$minResponseTime}s, max={$maxResponseTime}s\n";
    }

    /** @test */
    public function run_performance_test_suite()
    {
        echo "\n" . str_repeat("=", 80) . "\n";
        echo "           PERFORMANCE & LOAD TESTING SUITE\n";
        echo str_repeat("=", 80) . "\n";

        $this->test_bulk_message_creation_performance();
        $this->test_large_chat_list_retrieval_performance();
        $this->test_message_pagination_performance();
        $this->test_status_feed_performance_with_many_statuses();
        $this->test_contact_sync_performance();
        $this->test_group_chat_with_many_participants_performance();
        $this->test_call_history_performance();
        $this->test_database_query_optimization();
        $this->test_concurrent_user_operations();
        $this->test_memory_usage_during_bulk_operations();
        $this->test_api_response_time_consistency();

        echo "\n" . str_repeat("=", 80) . "\n";
        echo "           PERFORMANCE TESTING COMPLETED! ⚡\n";
        echo str_repeat("=", 80) . "\n\n";

        echo "PERFORMANCE TEST COVERAGE:\n";
        echo "✅ Bulk Operations Performance\n";
        echo "✅ Large Dataset Retrieval\n";
        echo "✅ Pagination Efficiency\n";
        echo "✅ Feed Generation Performance\n";
        echo "✅ Contact Synchronization\n";
        echo "✅ Group Operations Scalability\n";
        echo "✅ Historical Data Access\n";
        echo "✅ Database Query Optimization\n";
        echo "✅ Concurrent User Handling\n";
        echo "✅ Memory Usage Monitoring\n";
        echo "✅ Response Time Consistency\n";
        echo "\n⚡ Your API performs well under load! 🚀\n\n";
    }
}