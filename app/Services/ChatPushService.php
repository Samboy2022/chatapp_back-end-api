<?php

namespace App\Services;

use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Push notifications for chat messages.
 *
 * Messages were only ever broadcast over the websocket, which reaches people
 * who already have the app open — precisely the ones who least need telling.
 * Anyone with the app closed or backgrounded got nothing at all. This sends
 * the actual push.
 */
class ChatPushService
{
    public function __construct(private OneSignalService $push)
    {
    }

    /**
     * Notify a chat's participants about a new message.
     *
     * Never throws: a failed notification must not roll back or fail the send
     * that triggered it. The message is already saved by this point.
     */
    public function notifyNewMessage(Message $message): void
    {
        try {
            if (!$this->push->isConfigured()) {
                return;
            }

            $chat = $message->chat;

            if (!$chat) {
                return;
            }

            $recipients = $this->recipientsFor($message);

            if ($recipients === []) {
                return;
            }

            $sender = $message->sender;
            $senderName = $sender?->name ?: 'Someone';

            // In a group the chat name is the headline and the sender is
            // prefixed onto the body, matching how every messaging app does it
            // — otherwise you can't tell which group a message came from.
            $isGroup = $chat->isGroup();
            $title = $isGroup ? ($chat->name ?: 'Group message') : $senderName;
            $body = $isGroup
                ? $senderName . ': ' . $this->preview($message)
                : $this->preview($message);

            $this->push->sendToUsers($recipients, $title, $body, [
                'type' => 'new_message',
                'chat_id' => (string) $chat->id,
                'message_id' => (string) $message->id,
                'sender_id' => (string) ($sender?->id ?? ''),
                'sender_name' => $senderName,
                'chat_type' => $chat->type,
                // Lets the app open straight to the right conversation.
                'chat_name' => $isGroup ? ($chat->name ?: '') : $senderName,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Chat push notification failed', [
                'message_id' => $message->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Everyone who should be told about this message.
     *
     * Excludes the sender (they know), anyone who has left the chat, and
     * anyone who muted it — a muted conversation that still buzzes the phone
     * is the fastest way to get an app uninstalled.
     */
    private function recipientsFor(Message $message): array
    {
        return $message->chat
            ->participants()
            ->where('user_id', '!=', $message->sender_id)
            ->whereNull('left_at')
            ->where(function ($query) {
                $query->whereNull('muted_until')
                      ->orWhere('muted_until', '<=', now());
            })
            ->pluck('user_id')
            ->map(fn ($id) => (string) $id)
            ->all();
    }

    /**
     * One line describing the message.
     *
     * Media gets a label rather than a blank line — a notification reading
     * just "Ahmad" with no body tells the user nothing.
     */
    private function preview(Message $message): string
    {
        $content = trim((string) $message->content);

        $label = match ($message->type ?? 'text') {
            'image' => '📷 Photo',
            'video' => '🎥 Video',
            'voice', 'audio' => '🎤 Voice message',
            'file', 'document' => '📄 ' . ($message->file_name ?: 'Document'),
            'location' => '📍 Location',
            'contact' => '👤 Contact',
            default => null,
        };

        if ($label !== null) {
            // A captioned photo shows the caption after the label.
            return $content !== '' ? $label . ' · ' . $this->truncate($content) : $label;
        }

        return $content !== '' ? $this->truncate($content) : 'New message';
    }

    /**
     * Keep the body short. Both platforms truncate anyway, and a long preview
     * pushes the useful part off the lock screen.
     */
    private function truncate(string $text, int $limit = 120): string
    {
        return mb_strlen($text) > $limit
            ? mb_substr($text, 0, $limit - 1) . '…'
            : $text;
    }
}
