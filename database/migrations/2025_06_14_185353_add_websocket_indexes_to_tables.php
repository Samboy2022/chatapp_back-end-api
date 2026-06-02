<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add indexes for better WebSocket performance safely using try-catch blocks
        try { DB::statement("ALTER TABLE users ADD INDEX users_is_online_last_seen_at_index (is_online, last_seen_at)"); } catch (\Exception $e) {}
        try { DB::statement("ALTER TABLE users ADD INDEX users_last_seen_at_index (last_seen_at)"); } catch (\Exception $e) {}
        try { DB::statement("ALTER TABLE messages ADD INDEX messages_chat_id_created_at_index (chat_id, created_at)"); } catch (\Exception $e) {}
        try { DB::statement("ALTER TABLE messages ADD INDEX messages_sender_id_created_at_index (sender_id, created_at)"); } catch (\Exception $e) {}
        try { DB::statement("ALTER TABLE messages ADD INDEX messages_read_at_index (read_at)"); } catch (\Exception $e) {}
        try { DB::statement("ALTER TABLE messages ADD INDEX messages_delivered_at_index (delivered_at)"); } catch (\Exception $e) {}
        try { DB::statement("ALTER TABLE chat_participants ADD INDEX chat_participants_user_id_left_at_index (user_id, left_at)"); } catch (\Exception $e) {}
        try { DB::statement("ALTER TABLE chat_participants ADD INDEX chat_participants_last_read_message_id_index (last_read_message_id)"); } catch (\Exception $e) {}
        try { DB::statement("ALTER TABLE chats ADD INDEX chats_is_active_updated_at_index (is_active, updated_at)"); } catch (\Exception $e) {}
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove WebSocket performance indexes using raw SQL

        // Drop indexes if they exist
        $indexes = [
            'users' => ['users_is_online_last_seen_at_index', 'users_last_seen_at_index'],
            'messages' => ['messages_chat_id_created_at_index', 'messages_sender_id_created_at_index', 'messages_read_at_index', 'messages_delivered_at_index'],
            'chat_participants' => ['chat_participants_user_id_left_at_index', 'chat_participants_last_read_message_id_index'],
            'chats' => ['chats_is_active_updated_at_index']
        ];

        foreach ($indexes as $table => $tableIndexes) {
            foreach ($tableIndexes as $indexName) {
                $existing = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = '{$indexName}'");
                if (!empty($existing)) {
                    DB::statement("ALTER TABLE {$table} DROP INDEX {$indexName}");
                }
            }
        }
    }
};
