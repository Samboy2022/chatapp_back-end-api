<?php

namespace App\Services;

use App\Models\Call;
use App\Models\CallParticipant;
use App\Models\Chat;
use App\Models\User;
use App\Support\NotificationSettings;
use Illuminate\Support\Facades\Log;

/**
 * Voice and video calls for a whole group chat.
 *
 * A group call is one Call row (is_group = true, no receiver) on the Agora
 * channel `call_{id}`. Everyone in the group gets a call_participants row:
 * the starter as `joined`, the rest `invited`, and each person's row follows
 * them as they join, decline or leave. The call stays live until the last
 * person in it leaves.
 *
 * Statuses on calls: a group call is `answered` (in progress) from the
 * moment it starts — there is nobody in particular to answer it — and
 * `ended` once it's empty.
 */
class GroupCallService
{
    public function __construct(
        private AgoraService $agora,
        private OneSignalService $push,
    ) {}

    /**
     * Start a call in [chat], or join the one already running there.
     *
     * @return array{call: Call, joined_existing: bool}
     */
    public function start(Chat $chat, User $user, string $callType): array
    {
        $existing = Call::where('chat_id', $chat->id)
            ->where('is_group', true)
            ->where('status', 'answered')
            ->latest('id')
            ->first();

        if ($existing) {
            // Nobody left in it (everyone's app died mid-call): close it out
            // and start fresh rather than dropping this person into an empty
            // room.
            if ($this->joinedCount($existing) === 0) {
                $this->finish($existing);
            } else {
                $this->markJoined($existing, $user);
                return ['call' => $existing, 'joined_existing' => true];
            }
        }

        $call = Call::create([
            'chat_id' => $chat->id,
            'caller_id' => $user->id,
            'receiver_id' => null,
            'call_type' => $callType,
            'is_group' => true,
            'status' => 'answered',
            'started_at' => now(),
            'answered_at' => now(),
        ]);

        $members = $chat->participants()->get();
        foreach ($members as $member) {
            CallParticipant::create([
                'call_id' => $call->id,
                'user_id' => $member->id,
                'status' => $member->id === $user->id ? 'joined' : 'invited',
                'joined_at' => $member->id === $user->id ? now() : null,
            ]);
        }

        $this->ringMembers($call, $chat, $user, $members);

        return ['call' => $call, 'joined_existing' => false];
    }

    /** [user] accepted the call (or tapped "Join"). */
    public function markJoined(Call $call, User $user): void
    {
        CallParticipant::updateOrCreate(
            ['call_id' => $call->id, 'user_id' => $user->id],
            ['status' => 'joined', 'joined_at' => now(), 'left_at' => null],
        );
    }

    /** [user] declined the ring. The call carries on for everyone else. */
    public function decline(Call $call, User $user): void
    {
        CallParticipant::where('call_id', $call->id)
            ->where('user_id', $user->id)
            ->where('status', 'invited')
            ->update(['status' => 'declined']);
    }

    /**
     * [user] hung up. Ends the call once nobody is left in it.
     *
     * @return bool whether the call ended
     */
    public function leave(Call $call, User $user): bool
    {
        CallParticipant::where('call_id', $call->id)
            ->where('user_id', $user->id)
            ->update(['status' => 'ended', 'left_at' => now()]);

        if ($call->status !== 'answered' || $this->joinedCount($call) > 0) {
            return false;
        }

        $this->finish($call);
        return true;
    }

    /** Close the call: duration, and anyone who never picked up missed it. */
    public function finish(Call $call): void
    {
        $call->update([
            'status' => 'ended',
            'ended_at' => now(),
            'duration' => $call->answered_at ? $call->answered_at->diffInSeconds(now()) : 0,
        ]);

        $stillRinging = CallParticipant::where('call_id', $call->id)
            ->where('status', 'invited')
            ->pluck('user_id');

        CallParticipant::where('call_id', $call->id)
            ->where('status', 'invited')
            ->update(['status' => 'missed']);

        // Stop the phones that are still ringing.
        foreach ($stillRinging as $userId) {
            $this->push->sendCallNotification((string) $userId, [
                'type' => 'call_ended',
                'call_id' => (string) $call->id,
            ]);
        }
    }

    /**
     * Agora credentials for [user]. Shaped like a private call's so the
     * app's join code works unchanged: both token slots carry this user's
     * own token.
     */
    public function tokensFor(Call $call, User $user): array
    {
        $channel = 'call_' . $call->id;
        $token = $this->agora->generateToken($channel, $user->id);

        return [
            'call_id' => $call->id,
            'token' => $token,
            'uid' => (int) $user->id,
            'caller_token' => $token,
            'receiver_token' => $token,
            'caller_uid' => (int) $user->id,
            'receiver_uid' => (int) $user->id,
            'app_id' => $this->agora->appId(),
            'channel' => $channel,
            'is_group' => true,
            'expires_at' => now()->addHours(1)->toISOString(),
        ];
    }

    /** Everyone in the call, for the call screen's tiles. */
    public function participants(Call $call): array
    {
        return CallParticipant::with('user:id,name,avatar_url')
            ->where('call_id', $call->id)
            ->get()
            ->map(fn (CallParticipant $p) => [
                'user_id' => (int) $p->user_id,
                'name' => $p->user?->name,
                'avatar' => $p->user?->avatar_url,
                'status' => $p->status,
            ])
            ->values()
            ->all();
    }

    /**
     * A group call as it appears in [userId]'s call list. The "other side"
     * is the group itself.
     */
    public function formatFor(Call $call, int $userId, array $extra = []): array
    {
        $chat = $call->chat;
        $mine = CallParticipant::where('call_id', $call->id)->where('user_id', $userId)->first();

        // Someone who never joined a finished call missed it.
        $status = $call->status;
        if ((int) $call->caller_id !== $userId && $call->status === 'ended'
            && $mine && in_array($mine->status, ['invited', 'missed', 'declined'])) {
            $status = $mine->status === 'declined' ? 'declined' : 'missed';
        }

        return array_merge([
            'id' => $call->id,
            'chat_id' => $call->chat_id,
            'caller_id' => $call->caller_id,
            'callee_id' => $userId,
            'type' => $call->call_type,
            'is_group' => true,
            'group_name' => $chat?->name,
            'group_avatar' => $chat?->avatar_url,
            'status' => $status,
            'duration' => $call->duration,
            'started_at' => $call->started_at,
            'answered_at' => $call->answered_at,
            'ended_at' => $call->ended_at,
            'caller' => [
                'id' => $call->caller?->id,
                'name' => $call->caller?->name,
                'avatar' => $call->caller?->avatar_url,
            ],
            'callee' => [
                'id' => $call->chat_id,
                'name' => $chat?->name ?? 'Group call',
                'avatar' => $chat?->avatar_url,
            ],
        ], $extra);
    }

    private function joinedCount(Call $call): int
    {
        return CallParticipant::where('call_id', $call->id)->where('status', 'joined')->count();
    }

    private function ringMembers(Call $call, Chat $chat, User $starter, $members): void
    {
        $channel = 'call_' . $call->id;

        foreach ($members as $member) {
            if ($member->id === $starter->id) {
                continue;
            }
            if (!NotificationSettings::for($member)['call_notifications']) {
                continue;
            }

            try {
                $this->push->sendCallNotification((string) $member->id, [
                    'type' => 'incoming_call',
                    'call_id' => (string) $call->id,
                    'channel' => $channel,
                    // The ring shows the group, like WhatsApp; the starter is
                    // named underneath.
                    'caller_name' => $chat->name ?? 'Group call',
                    'caller_avatar' => $chat->avatar_url ?? '',
                    'call_type' => $call->call_type,
                    'receiver_token' => $this->agora->generateToken($channel, $member->id),
                    'app_id' => $this->agora->appId(),
                    'caller_id' => (string) $starter->id,
                    'receiver_id' => (string) $member->id,
                    'is_group' => '1',
                    'chat_id' => (string) $chat->id,
                    'started_by' => $starter->name,
                ]);
            } catch (\Throwable $e) {
                Log::warning('Group call push failed', ['call_id' => $call->id, 'user_id' => $member->id]);
            }
        }
    }
}
