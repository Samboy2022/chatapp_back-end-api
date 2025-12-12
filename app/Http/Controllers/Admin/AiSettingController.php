<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AiSettingController extends Controller
{
    /**
     * Display the AI settings page
     */
    public function index()
    {
        $settings = AiSetting::all();
        $providers = AiSetting::PROVIDERS;
        $models = AiSetting::DEFAULT_MODELS;
        $defaultSystemPrompt = AiSetting::DEFAULT_SYSTEM_PROMPT;

        return view('admin.ai-settings.index', compact(
            'settings',
            'providers',
            'models',
            'defaultSystemPrompt'
        ));
    }

    /**
     * Store a new AI setting
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'provider' => 'required|string|in:openai,gemini,openrouter',
            'model' => 'required|string|max:100',
            'api_key' => 'required|string|min:10',
            'system_prompt' => 'nullable|string|max:5000',
            'max_tokens' => 'nullable|integer|min:100|max:8192',
            'temperature' => 'nullable|numeric|min:0|max:2',
        ]);

        try {
            $setting = AiSetting::create([
                'provider' => $validated['provider'],
                'model' => $validated['model'],
                'api_key' => $validated['api_key'],
                'system_prompt' => $validated['system_prompt'] ?? null,
                'max_tokens' => $validated['max_tokens'] ?? 2048,
                'temperature' => $validated['temperature'] ?? 0.7,
                'is_active' => false,
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'AI setting created successfully',
                    'data' => $setting,
                ]);
            }

            return redirect()->route('admin.ai-settings.index')
                ->with('success', 'AI setting created successfully');

        } catch (\Exception $e) {
            Log::error('Failed to create AI setting: ' . $e->getMessage());

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create AI setting',
                ], 500);
            }

            return back()->with('error', 'Failed to create AI setting');
        }
    }

    /**
     * Update an AI setting
     */
    public function update(Request $request, $id)
    {
        $setting = AiSetting::findOrFail($id);

        $validated = $request->validate([
            'provider' => 'sometimes|required|string|in:openai,gemini,openrouter',
            'model' => 'sometimes|required|string|max:100',
            'api_key' => 'nullable|string|min:10',
            'system_prompt' => 'nullable|string|max:5000',
            'max_tokens' => 'nullable|integer|min:100|max:8192',
            'temperature' => 'nullable|numeric|min:0|max:2',
        ]);

        try {
            $updateData = array_filter([
                'provider' => $validated['provider'] ?? null,
                'model' => $validated['model'] ?? null,
                'system_prompt' => $validated['system_prompt'] ?? null,
                'max_tokens' => $validated['max_tokens'] ?? null,
                'temperature' => $validated['temperature'] ?? null,
            ], fn($value) => $value !== null);

            // Only update API key if provided
            if (!empty($validated['api_key'])) {
                $updateData['api_key'] = $validated['api_key'];
            }

            $setting->update($updateData);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'AI setting updated successfully',
                    'data' => $setting->fresh(),
                ]);
            }

            return redirect()->route('admin.ai-settings.index')
                ->with('success', 'AI setting updated successfully');

        } catch (\Exception $e) {
            Log::error('Failed to update AI setting: ' . $e->getMessage());

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update AI setting',
                ], 500);
            }

            return back()->with('error', 'Failed to update AI setting');
        }
    }

    /**
     * Delete an AI setting
     */
    public function destroy($id)
    {
        $setting = AiSetting::findOrFail($id);

        try {
            $setting->delete();

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'AI setting deleted successfully',
                ]);
            }

            return redirect()->route('admin.ai-settings.index')
                ->with('success', 'AI setting deleted successfully');

        } catch (\Exception $e) {
            Log::error('Failed to delete AI setting: ' . $e->getMessage());

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete AI setting',
                ], 500);
            }

            return back()->with('error', 'Failed to delete AI setting');
        }
    }

    /**
     * Activate an AI setting (deactivate others)
     */
    public function activate($id)
    {
        $setting = AiSetting::findOrFail($id);

        try {
            $setting->activate();

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => ucfirst($setting->provider) . ' activated as the AI provider',
                    'data' => $setting,
                ]);
            }

            return redirect()->route('admin.ai-settings.index')
                ->with('success', ucfirst($setting->provider) . ' activated as the AI provider');

        } catch (\Exception $e) {
            Log::error('Failed to activate AI setting: ' . $e->getMessage());

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to activate AI setting',
                ], 500);
            }

            return back()->with('error', 'Failed to activate AI setting');
        }
    }

    /**
     * Test AI connection
     */
    public function testConnection(Request $request)
    {
        $validated = $request->validate([
            'provider' => 'required|string|in:openai,gemini,openrouter',
            'api_key' => 'required|string',
            'model' => 'required|string',
        ]);

        try {
            $testMessage = "Hello, this is a connection test. Please respond with 'Connection successful!'";
            
            $response = $this->testProviderConnection(
                $validated['provider'],
                $validated['api_key'],
                $validated['model'],
                $testMessage
            );

            return response()->json([
                'success' => true,
                'message' => 'Connection test successful',
                'response' => $response,
            ]);

        } catch (\Exception $e) {
            Log::error('AI connection test failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Connection test failed: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Test provider connection
     */
    private function testProviderConnection(string $provider, string $apiKey, string $model, string $message): string
    {
        switch ($provider) {
            case 'openai':
                return $this->testOpenAI($apiKey, $model, $message);
            case 'gemini':
                return $this->testGemini($apiKey, $model, $message);
            case 'openrouter':
                return $this->testOpenRouter($apiKey, $model, $message);
            default:
                throw new \Exception('Unknown provider: ' . $provider);
        }
    }

    private function testOpenAI(string $apiKey, string $model, string $message): string
    {
        $client = new \GuzzleHttp\Client();
        
        $response = $client->post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => $model,
                'messages' => [
                    ['role' => 'user', 'content' => $message],
                ],
                'max_tokens' => 50,
            ],
            'timeout' => 30,
        ]);

        $data = json_decode($response->getBody(), true);
        return $data['choices'][0]['message']['content'] ?? 'No response';
    }

    private function testGemini(string $apiKey, string $model, string $message): string
    {
        $client = new \GuzzleHttp\Client();
        
        $response = $client->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent", [
            'headers' => [
                'Content-Type' => 'application/json',
                'x-goog-api-key' => $apiKey,
            ],
            'json' => [
                'contents' => [
                    ['parts' => [['text' => $message]]],
                ],
            ],
            'timeout' => 30,
        ]);

        $data = json_decode($response->getBody(), true);
        return $data['candidates'][0]['content']['parts'][0]['text'] ?? 'No response';
    }

    private function testOpenRouter(string $apiKey, string $model, string $message): string
    {
        $client = new \GuzzleHttp\Client();
        
        $response = $client->post('https://openrouter.ai/api/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
                'HTTP-Referer' => config('app.url'),
            ],
            'json' => [
                'model' => $model,
                'messages' => [
                    ['role' => 'user', 'content' => $message],
                ],
                'max_tokens' => 50,
            ],
            'timeout' => 30,
        ]);

        $data = json_decode($response->getBody(), true);
        return $data['choices'][0]['message']['content'] ?? 'No response';
    }

    /**
     * Get all settings as JSON
     */
    public function getSettings()
    {
        $settings = AiSetting::all()->map(function ($setting) {
            return [
                'id' => $setting->id,
                'provider' => $setting->provider,
                'model' => $setting->model,
                'masked_api_key' => $setting->masked_api_key,
                'has_api_key' => $setting->hasApiKey(),
                'system_prompt' => $setting->system_prompt,
                'is_active' => $setting->is_active,
                'max_tokens' => $setting->max_tokens,
                'temperature' => $setting->temperature,
                'created_at' => $setting->created_at,
                'updated_at' => $setting->updated_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $settings,
        ]);
    }

    /**
     * Chat with AI for testing (SSE streaming)
     */
    public function chat(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $setting = AiSetting::getActive();

        if (!$setting || !$setting->hasApiKey()) {
            return response()->json([
                'success' => false,
                'message' => 'No active AI provider configured or API key missing.',
            ], 503);
        }

        return $this->streamChatResponse($setting, $validated['message']);
    }

    /**
     * Stream chat response via SSE
     */
    private function streamChatResponse(AiSetting $setting, string $message)
    {
        return response()->stream(function () use ($setting, $message) {
            // Disable output buffering
            if (ob_get_level()) {
                ob_end_clean();
            }

            try {
                $fullResponse = '';

                switch ($setting->provider) {
                    case 'openai':
                        $fullResponse = $this->streamOpenAIChat($setting, $message);
                        break;
                    case 'gemini':
                        $fullResponse = $this->streamGeminiChat($setting, $message);
                        break;
                    case 'openrouter':
                        $fullResponse = $this->streamOpenRouterChat($setting, $message);
                        break;
                    default:
                        $this->sendSSE('error', 'Unknown provider: ' . $setting->provider);
                        return;
                }

                // Send done event
                $this->sendSSE('done', json_encode([
                    'total_length' => strlen($fullResponse),
                ]));

            } catch (\Exception $e) {
                Log::error('Admin AI chat error: ' . $e->getMessage());
                $this->sendSSE('error', $e->getMessage());
            }

        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    private function sendSSE(string $event, string $data): void
    {
        echo "event: {$event}\n";
        echo "data: {$data}\n\n";
        
        if (ob_get_level()) {
            ob_flush();
        }
        flush();
    }

    private function streamOpenAIChat(AiSetting $setting, string $message): string
    {
        $messages = [
            ['role' => 'system', 'content' => $setting->getEffectiveSystemPrompt()],
            ['role' => 'user', 'content' => $message],
        ];

        $client = new \GuzzleHttp\Client();

        // Check if using new GPT-5 Responses API or standard Chat Completions
        if (str_starts_with($setting->model, 'gpt-5')) {
            // Use new Responses API for GPT-5 models
            $response = $client->post('https://api.openai.com/v1/responses', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $setting->api_key,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => $setting->model,
                    'input' => $message,
                ],
                'timeout' => 120,
            ]);

            $data = json_decode($response->getBody(), true);
            $content = $data['output'][0]['content'][0]['text'] ?? '';
            
            // Send as a single chunk for non-streaming
            $this->sendSSE('token', $content);
            return $content;
        }

        // Standard Chat Completions API with streaming
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
            $line = $this->readStreamLine($body);

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

    private function streamGeminiChat(AiSetting $setting, string $message): string
    {
        // Use simple format like the user's curl example
        $contents = [
            [
                'parts' => [
                    ['text' => $setting->getEffectiveSystemPrompt() . "\n\nUser: " . $message]
                ]
            ]
        ];

        $client = new \GuzzleHttp\Client();

        try {
            // Use non-streaming generateContent for reliability
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
            
            // Log for debugging
            \Log::info('Gemini response:', ['data' => $data]);
            
            $fullResponse = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
            
            if ($fullResponse) {
                // Send as chunks for a streaming effect
                $chunks = str_split($fullResponse, 20);
                foreach ($chunks as $chunk) {
                    $this->sendSSE('token', $chunk);
                    usleep(10000); // Small delay for visual effect
                }
            }
            
            return $fullResponse;
            
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $errorBody = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : 'Unknown error';
            \Log::error('Gemini API error: ' . $errorBody);
            throw new \Exception('Gemini API error: ' . $e->getMessage());
        }
    }

    private function streamOpenRouterChat(AiSetting $setting, string $message): string
    {
        $messages = [
            ['role' => 'system', 'content' => $setting->getEffectiveSystemPrompt()],
            ['role' => 'user', 'content' => $message],
        ];

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
            $line = $this->readStreamLine($body);

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

    private function readStreamLine($stream): string
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
}
