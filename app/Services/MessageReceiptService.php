<?php

namespace App\Services;

use App\Events\MessageRead;
use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * WhatsApp-style delivery receipts.
 *
 *   sent      — stored on the server (one grey tick)
 *   delivered — the recipient's app has fetched it (two grey ticks)
 *   read      — the recipient opened the chat (two blue ticks). In a group,
 *               only once every other member has read it.
 */
class MessageReceiptService
{
    /**
     * Mark every message waiting for [$user] as delivered. Called whenever the
     * user's app fetches its chat list or a conversation, which is the moment
     * the message has reached their device.
     */
    public function markDelivered(User $user, ?int $chatId = null): int
    {
        $query = Message::query()
            ->where('sender_id', '!=', $user->id)
            ->where('status', 'sent');

        if ($chatId !== null) {
            $query->where('chat_id', $chatId);
        } else {
            $query->whereIn('chat_id', function ($q) use ($user) {
                $q->select('chat_id')
                    ->from('chat_participants')
                    ->where('user_id', $user->id);
            });
        }

        return $query->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);
    }

    /**
     * Record that [$reader] has read [$chat] up to its newest message, and
     * flip the messages that are now fully read to `read`.
     *
     * @return array<int> ids of messages that changed to `read`
     */
    public function markChatRead(Chat $chat, User $reader): array
    {
        $latestId = $chat->messages()->max('id');
        if (!$latestId) {
            return [];
        }

        $chat->participants()->updateExistingPivot($reader->id, [
            'last_read_message_id' => $latestId,
        ]);

        // Anything still unread that someone else sent.
        $pending = $chat->messages()
            ->where('sender_id', '!=', $reader->id)
            ->where('status', '!=', 'read')
            ->where('id', '<=', $latestId)
            ->get(['id', 'sender_id', 'delivered_at']);

        if ($pending->isEmpty()) {
            return [];
        }

        $readIds = $chat->isGroup()
            ? $this->fullyReadInGroup($chat, $pending)
            : $pending->pluck('id')->all();

        // Reading implies delivery, even when the delivered step was skipped.
        $partial = $pending->pluck('id')->diff($readIds)->all();
        if ($partial) {
            Message::whereIn('id', $partial)->where('status', 'sent')->update([
                'status' => 'delivered',
                'delivered_at' => now(),
            ]);
        }

        if (!$readIds) {
            return [];
        }

        // Reading implies delivery; keep an existing delivery time.
        Message::whereIn('id', $readIds)->whereNull('delivered_at')
            ->update(['delivered_at' => now()]);
        Message::whereIn('id', $readIds)->update([
            'status' => 'read',
            'read_at' => now(),
        ]);

        $this->broadcast($chat, $reader, $readIds);

        return $readIds;
    }

    /**
     * Of [$pending], the messages every member other than their sender has
     * read past.
     */
    private function fullyReadInGroup(Chat $chat, $pending): array
    {
        $readUpTo = DB::table('chat_participants')
            ->where('chat_id', $chat->id)
            ->whereNull('left_at')
            ->pluck('last_read_message_id', 'user_id');

        return $pending->filter(function ($message) use ($readUpTo) {
            foreach ($readUpTo as $userId => $lastRead) {
                if ((int) $userId === (int) $message->sender_id) {
                    continue;
                }
                if (!$lastRead || (int) $lastRead < (int) $message->id) {
                    return false;
                }
            }
            return true;
        })->pluck('id')->all();
    }

    private function broadcast(Chat $chat, User $reader, array $readIds): void
    {
        try {
            $latest = Message::find(max($readIds));
            if ($latest) {
                broadcast(new MessageRead($latest, $reader, $readIds));
            }
        } catch (\Throwable $e) {
            // Receipts still reach the sender through polling.
            Log::warning('Read receipt broadcast failed: ' . $e->getMessage());
        }
    }
}
