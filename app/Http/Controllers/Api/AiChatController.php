<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiMessage;
use App\Models\AiSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AiChatController extends Controller
{
    /**
     * Handle AI chat with SSE streaming
     */
    public function chat(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:2000',
            'conversation_id' => 'nullable|string|max:36',
        ]);

        $user = $request->user();
        $message = $validated['message'];
        $conversationId = $validated['conversation_id'] ?? AiMessage::generateConversationId();

        // Get active AI setting
        $aiSetting = AiSetting::getActive();
        
        if (!$aiSetting) {
            return response()->json([
                'success' => false,
                'message' => 'No AI provider is currently configured. Please contact the administrator.',
            ], 503);
        }

        if (!$aiSetting->hasApiKey()) {
            return response()->json([
                'success' => false,
                'message' => 'AI provider API key is not configured.',
            ], 503);
        }

        // Save user message
        AiMessage::createUserMessage($user->id, $conversationId, $message);

        // Get conversation history for context
        $history = AiMessage::getConversationHistoryForApi($conversationId, 10);

        // Return SSE streaming response
        return $this->streamResponse($aiSetting, $message, $history, $user->id, $conversationId);
    }

    /**
     * Create SSE streaming response
     */
    private function streamResponse(AiSetting $setting, string $message, array $history, int $userId, string $conversationId): StreamedResponse
    {
        return new StreamedResponse(function () use ($setting, $message, $history, $userId, $conversationId) {
            // Disable output buffering
            if (ob_get_level()) {
                ob_end_clean();
            }

            // Set headers for SSE
            header('Content-Type: text/event-stream');
            header('Cache-Control: no-cache');
            header('Connection: keep-alive');
            header('X-Accel-Buffering: no');

            $fullResponse = '';

            try {
                // Send conversation ID first
                $this->sendSSE('conversation_id', $conversationId);

                // Stream based on provider
                switch ($setting->provider) {
                    case 'openai':
                        $fullResponse = $this->streamOpenAI($setting, $message, $history);
                        break;
                    case 'gemini':
                        $fullResponse = $this->streamGemini($setting, $message, $history);
                        break;
                    case 'openrouter':
                        $fullResponse = $this->streamOpenRouter($setting, $message, $history);
                        break;
                    default:
                        $this->sendSSE('error', 'Unknown AI provider');
                        return;
                }

                // Save assistant response
                if (!empty($fullResponse)) {
                    AiMessage::createAssistantMessage(
                        $userId,
                        $conversationId,
                        $fullResponse,
                        null,
                        $setting->provider,
                        $setting->model
                    );
                }

                // Send done event
                $this->sendSSE('done', json_encode([
                    'conversation_id' => $conversationId,
                    'total_length' => strlen($fullResponse),
                ]));

            } catch (\Exception $e) {
                Log::error('AI streaming error: ' . $e->getMessage());
                $this->sendSSE('error', $e->getMessage());
            }

        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /**
     * Send SSE event
     */
    private function sendSSE(string $event, string $data): void
    {
        echo "event: {$event}\n";
        echo "data: {$data}\n\n";
        
        if (ob_get_level()) {
            ob_flush();
        }
        flush();
    }

    /**
     * Stream from OpenAI
     */
    private function streamOpenAI(AiSetting $setting, string $message, array $history): string
    {
        $messages = [
            ['role' => 'system', 'content' => $setting->getEffectiveSystemPrompt()],
        ];

        // Add history
        foreach ($history as $msg) {
            $messages[] = $msg;
        }

        // Add current message if not in history
        if (empty($history) || end($history)['content'] !== $message) {
            $messages[] = ['role' => 'user', 'content' => $message];
        }

        $client = new \GuzzleHttp\Client();
        
        $response = $client->post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $setting->api_key,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => $setting->model,
                'messages' => $messages,
                'max_tokens' => $setting->max_tokens,
                'temperature' => $setting->temperature,
                'stream' => true,
            ],
            'stream' => true,
            'timeout' => 120,
        ]);

        $fullResponse = '';
        $body = $response->getBody();

        while (!$body->eof()) {
            $line = $this->readLine($body);
            
            if (empty($line) || $line === 'data: [DONE]') {
                continue;
            }

            if (strpos($line, 'data: ') === 0) {
                $json = substr($line, 6);
                $data = json_decode($json, true);

                if (isset($data['choices'][0]['delta']['content'])) {
                    $content = $data['choices'][0]['delta']['content'];
                    $fullResponse .= $content;
                    $this->sendSSE('token', $content);
                }
            }
        }

        return $fullResponse;
    }

    /**
     * Stream from Gemini (using generateContent for reliability)
     */
    private function streamGemini(AiSetting $setting, string $message, array $history): string
    {
        // Build conversation with system prompt
        $fullPrompt = $setting->getEffectiveSystemPrompt() . "\n\n";
        
        // Add history
        foreach ($history as $msg) {
            $role = $msg['role'] === 'user' ? 'User' : 'Assistant';
            $fullPrompt .= "{$role}: {$msg['content']}\n\n";
        }
        
        // Add current message
        $fullPrompt .= "User: {$message}\n\nAssistant:";

        $contents = [
            [
                'parts' => [
                    ['text' => $fullPrompt]
                ]
            ]
        ];

        $client = new \GuzzleHttp\Client();

        try {
            // Use generateContent for reliability
            $response = $client->post(
                "https://generativelanguage.googleapis.com/v1beta/models/{$setting->model}:generateContent",
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'x-goog-api-key' => $setting->api_key,
                    ],
                    'json' => [
                        'contents' => $contents,
                        'generationConfig' => [
                            'maxOutputTokens' => $setting->max_tokens,
                            'temperature' => $setting->temperature,
                        ],
                    ],
                    'timeout' => 120,
                ]
            );

            $data = json_decode($response->getBody(), true);
            $fullResponse = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';

            if ($fullResponse) {
                // Send as chunks for streaming effect
                $chunks = str_split($fullResponse, 15);
                foreach ($chunks as $chunk) {
                    $this->sendSSE('token', $chunk);
                    usleep(5000);
                }
            }

            return $fullResponse;

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $errorBody = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : 'Unknown error';
            Log::error('Gemini API error: ' . $errorBody);
            throw new \Exception('Gemini API error: ' . $e->getMessage());
        }
    }

    /**
     * Stream from OpenRouter
     */
    private function streamOpenRouter(AiSetting $setting, string $message, array $history): string
    {
        $messages = [
            ['role' => 'system', 'content' => $setting->getEffectiveSystemPrompt()],
        ];

        // Add history
        foreach ($history as $msg) {
            $messages[] = $msg;
        }

        // Add current message if not in history
        if (empty($history) || end($history)['content'] !== $message) {
            $messages[] = ['role' => 'user', 'content' => $message];
        }

        $client = new \GuzzleHttp\Client();

        $response = $client->post('https://openrouter.ai/api/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $setting->api_key,
                'Content-Type' => 'application/json',
                'HTTP-Referer' => config('app.url'),
                'X-Title' => config('app.name') . ' AI Assistant',
            ],
            'json' => [
                'model' => $setting->model,
                'messages' => $messages,
                'max_tokens' => $setting->max_tokens,
                'temperature' => $setting->temperature,
                'stream' => true,
            ],
            'stream' => true,
            'timeout' => 120,
        ]);

        $fullResponse = '';
        $body = $response->getBody();

        while (!$body->eof()) {
            $line = $this->readLine($body);

            if (empty($line) || $line === 'data: [DONE]') {
                continue;
            }

            if (strpos($line, 'data: ') === 0) {
                $json = substr($line, 6);
                $data = json_decode($json, true);

                if (isset($data['choices'][0]['delta']['content'])) {
                    $content = $data['choices'][0]['delta']['content'];
                    $fullResponse .= $content;
                    $this->sendSSE('token', $content);
                }
            }
        }

        return $fullResponse;
    }

    /**
     * Read a line from stream
     */
    private function readLine($stream): string
    {
        $line = '';
        while (!$stream->eof()) {
            $char = $stream->read(1);
            if ($char === "\n") {
                break;
            }
            $line .= $char;
        }
        return trim($line);
    }

    /**
     * Get conversation history with pagination (WhatsApp-style)
     */
    public function getConversationHistory(Request $request)
    {
        $user = $request->user();
        $conversationId = $request->query('conversation_id');
        $page = (int) $request->query('page', 1);
        $perPage = (int) $request->query('per_page', 50);

        if ($conversationId) {
            // Get specific conversation messages with pagination
            $data = AiMessage::getConversationMessagesPaginated(
                $user->id, 
                $conversationId, 
                $perPage, 
                $page
            );

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        }

        // Get list of conversations with pagination
        $data = AiMessage::getUserConversationsPaginated($user->id, $perPage, $page);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Get user's last conversation (for resuming)
     */
    public function getLastConversation(Request $request)
    {
        $user = $request->user();
        $conversationId = AiMessage::getLastConversation($user->id);

        if (!$conversationId) {
            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'No previous conversations found',
            ]);
        }

        // Get the last conversation with messages
        $data = AiMessage::getConversationMessagesPaginated($user->id, $conversationId, 50, 1);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Clear conversation history
     */
    public function clearHistory(Request $request)
    {
        $user = $request->user();
        $conversationId = $request->query('conversation_id');

        if ($conversationId) {
            // Clear specific conversation
            $deleted = AiMessage::where('user_id', $user->id)
                ->where('conversation_id', $conversationId)
                ->delete();
            
            return response()->json([
                'success' => true,
                'message' => "Conversation deleted ({$deleted} messages)",
            ]);
        } else {
            // Clear all conversations
            $deleted = AiMessage::where('user_id', $user->id)->delete();
            
            return response()->json([
                'success' => true,
                'message' => "All conversations deleted ({$deleted} messages)",
            ]);
        }
    }

    /**
     * Non-streaming chat (fallback)
     */
    public function chatNonStreaming(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:2000',
            'conversation_id' => 'nullable|string|max:36',
        ]);

        $user = $request->user();
        $message = $validated['message'];
        $conversationId = $validated['conversation_id'] ?? AiMessage::generateConversationId();

        $aiSetting = AiSetting::getActive();

        if (!$aiSetting || !$aiSetting->hasApiKey()) {
            return response()->json([
                'success' => false,
                'message' => 'No AI provider is currently configured.',
            ], 503);
        }

        // Save user message
        AiMessage::createUserMessage($user->id, $conversationId, $message);

        // Get conversation history
        $history = AiMessage::getConversationHistoryForApi($conversationId, 10);

        try {
            $response = $this->getNonStreamingResponse($aiSetting, $message, $history);

            // Save assistant response
            $assistantMessage = AiMessage::createAssistantMessage(
                $user->id,
                $conversationId,
                $response,
                null,
                $aiSetting->provider,
                $aiSetting->model
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'conversation_id' => $conversationId,
                    'message' => $assistantMessage,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('AI chat error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to get AI response: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get non-streaming response
     */
    private function getNonStreamingResponse(AiSetting $setting, string $message, array $history): string
    {
        $messages = [
            ['role' => 'system', 'content' => $setting->getEffectiveSystemPrompt()],
        ];

        foreach ($history as $msg) {
            $messages[] = $msg;
        }

        if (empty($history) || end($history)['content'] !== $message) {
            $messages[] = ['role' => 'user', 'content' => $message];
        }

        $client = new \GuzzleHttp\Client();

        switch ($setting->provider) {
            case 'openai':
                $response = $client->post('https://api.openai.com/v1/chat/completions', [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $setting->api_key,
                        'Content-Type' => 'application/json',
                    ],
                    'json' => [
                        'model' => $setting->model,
                        'messages' => $messages,
                        'max_tokens' => $setting->max_tokens,
                        'temperature' => $setting->temperature,
                    ],
                    'timeout' => 60,
                ]);
                $data = json_decode($response->getBody(), true);
                return $data['choices'][0]['message']['content'] ?? '';

            case 'gemini':
                $contents = [];
                foreach ($messages as $msg) {
                    $contents[] = [
                        'role' => $msg['role'] === 'user' ? 'user' : 'model',
                        'parts' => [['text' => $msg['content']]],
                    ];
                }
                $response = $client->post(
                    "https://generativelanguage.googleapis.com/v1beta/models/{$setting->model}:generateContent",
                    [
                        'headers' => [
                            'Content-Type' => 'application/json',
                            'x-goog-api-key' => $setting->api_key,
                        ],
                        'json' => [
                            'contents' => $contents,
                            'generationConfig' => [
                                'maxOutputTokens' => $setting->max_tokens,
                                'temperature' => $setting->temperature,
                            ],
                        ],
                        'timeout' => 60,
                    ]
                );
                $data = json_decode($response->getBody(), true);
                return $data['candidates'][0]['content']['parts'][0]['text'] ?? '';

            case 'openrouter':
                $response = $client->post('https://openrouter.ai/api/v1/chat/completions', [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $setting->api_key,
                        'Content-Type' => 'application/json',
                        'HTTP-Referer' => config('app.url'),
                    ],
                    'json' => [
                        'model' => $setting->model,
                        'messages' => $messages,
                        'max_tokens' => $setting->max_tokens,
                        'temperature' => $setting->temperature,
                    ],
                    'timeout' => 60,
                ]);
                $data = json_decode($response->getBody(), true);
                return $data['choices'][0]['message']['content'] ?? '';

            default:
                throw new \Exception('Unknown provider: ' . $setting->provider);
        }
    }
}
