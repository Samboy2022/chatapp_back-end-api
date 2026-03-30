<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Illuminate\Pagination\LengthAwarePaginator;

class AiMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'conversation_id',
        'role',
        'content',
        'tokens_used',
        'provider',
        'model',
    ];

    protected $casts = [
        'tokens_used' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns this message
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get messages by conversation
     */
    public function scopeForConversation($query, string $conversationId)
    {
        return $query->where('conversation_id', $conversationId);
    }

    /**
     * Scope to get messages by user
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Generate a new conversation ID
     */
    public static function generateConversationId(): string
    {
        return Str::uuid()->toString();
    }

    /**
     * Create a user message
     */
    public static function createUserMessage(int $userId, string $conversationId, string $content): self
    {
        return self::create([
            'user_id' => $userId,
            'conversation_id' => $conversationId,
            'role' => 'user',
            'content' => $content,
        ]);
    }

    /**
     * Create an assistant message
     */
    public static function createAssistantMessage(
        int $userId, 
        string $conversationId, 
        string $content,
        ?int $tokensUsed = null,
        ?string $provider = null,
        ?string $model = null
    ): self {
        return self::create([
            'user_id' => $userId,
            'conversation_id' => $conversationId,
            'role' => 'assistant',
            'content' => $content,
            'tokens_used' => $tokensUsed,
            'provider' => $provider,
            'model' => $model,
        ]);
    }

    /**
     * Get conversation history formatted for AI API (for context/memory)
     * Includes more messages for better memory
     */
    public static function getConversationHistoryForApi(string $conversationId, int $limit = 20): array
    {
        $messages = self::where('conversation_id', $conversationId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->reverse()
            ->values();

        return $messages->map(function ($message) {
            return [
                'role' => $message->role,
                'content' => $message->content,
            ];
        })->toArray();
    }

    /**
     * Get user's conversation list with pagination (WhatsApp-style)
     */
    public static function getUserConversationsPaginated(int $userId, int $perPage = 20, int $page = 1): array
    {
        $query = self::where('user_id', $userId)
            ->select('conversation_id')
            ->selectRaw('MAX(created_at) as last_message_at')
            ->selectRaw('MIN(created_at) as started_at')
            ->selectRaw('COUNT(*) as message_count')
            ->groupBy('conversation_id')
            ->orderByDesc('last_message_at');

        $total = $query->get()->count();
        $conversations = $query->skip(($page - 1) * $perPage)->take($perPage)->get();

        $items = $conversations->map(function ($conv) use ($userId) {
            // Get last message for preview
            $lastMessage = self::where('conversation_id', $conv->conversation_id)
                ->orderBy('created_at', 'desc')
                ->first();

            // Get first user message as title
            $firstUserMessage = self::where('conversation_id', $conv->conversation_id)
                ->where('role', 'user')
                ->orderBy('created_at', 'asc')
                ->first();

            return [
                'conversation_id' => $conv->conversation_id,
                'title' => $firstUserMessage ? Str::limit($firstUserMessage->content, 50) : 'New Conversation',
                'last_message' => [
                    'role' => $lastMessage->role,
                    'content' => Str::limit($lastMessage->content, 100),
                    'time' => $lastMessage->created_at->diffForHumans(),
                    'timestamp' => $lastMessage->created_at->toISOString(),
                ],
                'message_count' => $conv->message_count,
                'started_at' => $conv->started_at,
                'last_message_at' => $conv->last_message_at,
            ];
        })->toArray();

        return [
            'conversations' => $items,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => ceil($total / $perPage),
                'has_more' => $page < ceil($total / $perPage),
            ],
        ];
    }

    /**
     * Get messages from a specific conversation with pagination
     */
    public static function getConversationMessagesPaginated(
        int $userId, 
        string $conversationId, 
        int $perPage = 50, 
        int $page = 1
    ): array {
        $query = self::where('user_id', $userId)
            ->where('conversation_id', $conversationId)
            ->orderBy('created_at', 'desc');

        $total = $query->count();
        $messages = $query->skip(($page - 1) * $perPage)->take($perPage)->get()->reverse()->values();

        return [
            'conversation_id' => $conversationId,
            'messages' => $messages->map(function ($msg) {
                return [
                    'id' => $msg->id,
                    'role' => $msg->role,
                    'content' => $msg->content,
                    'time' => $msg->created_at->diffForHumans(),
                    'timestamp' => $msg->created_at->toISOString(),
                    'provider' => $msg->provider,
                    'model' => $msg->model,
                ];
            })->toArray(),
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => ceil($total / $perPage),
                'has_more' => $page < ceil($total / $perPage),
            ],
        ];
    }

    /**
     * Get user's last active conversation
     */
    public static function getLastConversation(int $userId): ?string
    {
        $lastMessage = self::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->first();

        return $lastMessage?->conversation_id;
    }

    /**
     * Get context summary from previous conversations for AI memory
     * This helps the AI remember across different conversations
     */
    public static function getUserContextSummary(int $userId, int $recentConversations = 3): string
    {
        $conversations = self::where('user_id', $userId)
            ->select('conversation_id')
            ->selectRaw('MAX(created_at) as last_message_at')
            ->groupBy('conversation_id')
            ->orderByDesc('last_message_at')
            ->limit($recentConversations)
            ->get();

        if ($conversations->isEmpty()) {
            return '';
        }

        $summary = "### Previous Conversation Context:\n";

        foreach ($conversations as $index => $conv) {
            $messages = self::where('conversation_id', $conv->conversation_id)
                ->orderBy('created_at', 'asc')
                ->limit(4) // First few messages of each
                ->get();

            if ($messages->isNotEmpty()) {
                $firstMsg = $messages->first();
                $summary .= "- Conversation " . ($index + 1) . ": ";
                $summary .= Str::limit($firstMsg->content, 100) . "\n";
            }
        }

        return $summary . "\n";
    }

    /**
     * Legacy method - kept for compatibility
     */
    public static function getUserConversations(int $userId, int $limit = 20): array
    {
        $result = self::getUserConversationsPaginated($userId, $limit, 1);
        return $result['conversations'];
    }
}

