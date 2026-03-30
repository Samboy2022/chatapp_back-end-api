@extends('layouts.admin')

@section('title', 'AI Settings')
@section('page-title', 'AI Farming Assistant Settings')

@section('content')
    <!-- Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div class="flex-1">
            <h2 class="text-lg font-semibold text-gray-900">AI Farming Assistant</h2>
            <p class="text-sm text-gray-500">Configure AI providers for the farming assistant chat feature</p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="openAddModal()" 
                    class="flex items-center gap-2 px-4 py-2.5 bg-green-700 hover:bg-green-800 text-white rounded-xl transition-all text-sm font-medium shadow-lg shadow-green-700/30">
                <i class="ph ph-plus text-lg"></i>
                <span class="hidden sm:inline">Add Provider</span>
            </button>
        </div>
    </div>

    <!-- Status Messages -->
    <div id="statusMessage" class="hidden mb-6"></div>

    <!-- Provider Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8" id="providersGrid">
        @forelse($settings as $setting)
            <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden {{ $setting->is_active ? 'ring-2 ring-green-500' : '' }}" 
                 data-setting-id="{{ $setting->id }}">
                <!-- Card Header -->
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-white border border-gray-200 rounded-lg shadow-sm {{ $setting->is_active ? 'text-green-600' : 'text-gray-600' }}">
                            @if($setting->provider === 'openai')
                                <i class="ph ph-openai-logo text-lg"></i>
                            @elseif($setting->provider === 'gemini')
                                <i class="ph ph-google-logo text-lg"></i>
                            @else
                                <i class="ph ph-robot text-lg"></i>
                            @endif
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900">{{ $providers[$setting->provider] ?? ucfirst($setting->provider) }}</h3>
                            <p class="text-xs text-gray-500">{{ $setting->model }}</p>
                        </div>
                    </div>
                    @if($setting->is_active)
                        <span class="px-2 py-1 bg-green-100 text-green-700 text-xs font-medium rounded-full">Active</span>
                    @endif
                </div>

                <!-- Card Body -->
                <div class="p-6 space-y-4">
                    <!-- API Key Status -->
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">API Key</span>
                        @if($setting->hasApiKey())
                            <span class="flex items-center gap-1 text-green-600 text-sm">
                                <i class="ph ph-check-circle"></i>
                                Configured
                            </span>
                        @else
                            <span class="flex items-center gap-1 text-red-600 text-sm">
                                <i class="ph ph-warning-circle"></i>
                                Not Set
                            </span>
                        @endif
                    </div>

                    <!-- Model -->
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Model</span>
                        <span class="text-sm font-mono text-gray-900">{{ $setting->model }}</span>
                    </div>

                    <!-- Temperature -->
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Temperature</span>
                        <span class="text-sm text-gray-900">{{ $setting->temperature }}</span>
                    </div>

                    <!-- Max Tokens -->
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Max Tokens</span>
                        <span class="text-sm text-gray-900">{{ number_format($setting->max_tokens) }}</span>
                    </div>
                </div>

                <!-- Card Actions -->
                <div class="px-6 py-4 bg-gray-50/50 border-t border-gray-100 flex items-center gap-2">
                    @unless($setting->is_active)
                        <button onclick="activateProvider({{ $setting->id }})" 
                                class="flex-1 flex items-center justify-center gap-2 px-3 py-2 bg-green-700 hover:bg-green-800 text-white rounded-lg transition-all text-sm font-medium">
                            <i class="ph ph-power"></i>
                            Activate
                        </button>
                    @endunless
                    <button onclick="editProvider({{ $setting->id }})" 
                            class="flex items-center justify-center gap-2 px-3 py-2 bg-white border border-gray-200 hover:border-blue-500 hover:text-blue-600 text-gray-700 rounded-lg transition-all text-sm font-medium {{ $setting->is_active ? 'flex-1' : '' }}">
                        <i class="ph ph-pencil-simple"></i>
                        Edit
                    </button>
                    <button onclick="testConnection({{ $setting->id }})" 
                            class="flex items-center justify-center gap-2 px-3 py-2 bg-white border border-gray-200 hover:border-purple-500 hover:text-purple-600 text-gray-700 rounded-lg transition-all text-sm font-medium">
                        <i class="ph ph-plugs-connected"></i>
                    </button>
                    @unless($setting->is_active)
                        <button onclick="deleteProvider({{ $setting->id }})" 
                                class="flex items-center justify-center gap-2 px-3 py-2 bg-white border border-gray-200 hover:border-red-500 hover:text-red-600 text-gray-700 rounded-lg transition-all text-sm font-medium">
                            <i class="ph ph-trash"></i>
                        </button>
                    @endunless
                </div>
            </div>
        @empty
            <!-- Empty State -->
            <div class="col-span-full bg-white border border-gray-100 rounded-2xl shadow-sm p-12 text-center">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="ph ph-robot text-3xl text-gray-400"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">No AI Providers Configured</h3>
                <p class="text-gray-500 mb-6">Add an AI provider to enable the farming assistant feature</p>
                <button onclick="openAddModal()" 
                        class="inline-flex items-center gap-2 px-6 py-3 bg-green-700 hover:bg-green-800 text-white rounded-xl transition-all text-sm font-medium">
                    <i class="ph ph-plus"></i>
                    Add Your First Provider
                </button>
            </div>
        @endforelse
    </div>

    <!-- System Prompt Preview -->
    @if($settings->where('is_active', true)->first())
        <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center gap-3">
                <div class="p-2 bg-white border border-gray-200 rounded-lg shadow-sm text-green-600">
                    <i class="ph ph-text-aa text-lg"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">Active System Prompt</h3>
                    <p class="text-xs text-gray-500">This prompt guides the AI's responses</p>
                </div>
            </div>
            <div class="p-6">
                <pre class="whitespace-pre-wrap text-sm text-gray-700 bg-gray-50 rounded-xl p-4 max-h-64 overflow-y-auto">{{ $settings->where('is_active', true)->first()->getEffectiveSystemPrompt() }}</pre>
            </div>
        </div>

        <!-- AI Chat Testing Section -->
        <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden mt-8">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-white border border-gray-200 rounded-lg shadow-sm text-purple-600">
                        <i class="ph ph-chat-circle-dots text-lg"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">Test AI Chat</h3>
                        <p class="text-xs text-gray-500">Send test messages to the active AI provider</p>
                    </div>
                </div>
                <button onclick="clearChatHistory()" class="text-sm text-gray-500 hover:text-red-600 transition-colors flex items-center gap-1">
                    <i class="ph ph-trash"></i>
                    Clear Chat
                </button>
            </div>
            <div class="flex flex-col h-[500px]">
                <!-- Chat Messages -->
                <div id="chatMessages" class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50/50">
                    <!-- Welcome Message -->
                    <div class="flex justify-center">
                        <div class="bg-white border border-gray-100 rounded-xl px-4 py-2 text-sm text-gray-500 shadow-sm">
                            <i class="ph ph-robot mr-1"></i>
                            Connected to <strong>{{ $settings->where('is_active', true)->first()->provider }}</strong> 
                            using model <strong class="font-mono">{{ $settings->where('is_active', true)->first()->model }}</strong>
                        </div>
                    </div>
                </div>

                <!-- Chat Input -->
                <div class="p-4 border-t border-gray-100 bg-white">
                    <form id="chatForm" onsubmit="sendTestMessage(event)" class="flex gap-3">
                        <input type="text" id="chatInput" 
                               class="flex-1 px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 text-sm"
                               placeholder="Type a message to test the AI..." autocomplete="off">
                        <button type="submit" id="sendButton"
                                class="px-6 py-3 bg-green-700 hover:bg-green-800 text-white rounded-xl transition-all text-sm font-medium flex items-center gap-2 shadow-lg shadow-green-700/30">
                            <span id="sendButtonText">Send</span>
                            <i class="ph ph-paper-plane-tilt"></i>
                        </button>
                    </form>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <span class="text-xs text-gray-400">Quick prompts:</span>
                        <button type="button" onclick="setQuickPrompt('Hello, how can you help me with farming?')" 
                                class="text-xs bg-gray-100 hover:bg-gray-200 px-2 py-1 rounded transition-colors">
                            Greeting
                        </button>
                        <button type="button" onclick="setQuickPrompt('What crops grow best in tropical weather?')" 
                                class="text-xs bg-gray-100 hover:bg-gray-200 px-2 py-1 rounded transition-colors">
                            Crop advice
                        </button>
                        <button type="button" onclick="setQuickPrompt('How do I prevent pests in my tomato garden?')" 
                                class="text-xs bg-gray-100 hover:bg-gray-200 px-2 py-1 rounded transition-colors">
                            Pest control
                        </button>
                        <button type="button" onclick="setQuickPrompt('What is the best time to plant maize in Nigeria?')" 
                                class="text-xs bg-gray-100 hover:bg-gray-200 px-2 py-1 rounded transition-colors">
                            Planting time
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Add/Edit Modal -->
    <div id="providerModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900" id="modalTitle">Add AI Provider</h3>
                <button onclick="closeModal()" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                    <i class="ph ph-x text-gray-500"></i>
                </button>
            </div>
            <form id="providerForm" class="p-6 space-y-4">
                <input type="hidden" name="setting_id" id="settingId">
                
                <!-- Provider Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Provider *</label>
                    <select name="provider" id="providerSelect" required
                            onchange="updateModelOptions()"
                            class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 text-sm">
                        @foreach($providers as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Model Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Model *</label>
                    <!-- Dropdown for OpenAI and Gemini -->
                    <select name="model" id="modelSelect" required
                            class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 text-sm">
                    </select>
                    <!-- Text input for OpenRouter/Gemini (custom model) -->
                    <div id="customModelContainer" class="hidden">
                        <input type="text" name="custom_model" id="customModelInput"
                               class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 text-sm font-mono"
                               placeholder="Enter model name...">
                        <p class="text-xs text-gray-500 mt-1" id="customModelHint">
                            <i class="ph ph-info"></i> 
                            <span id="customModelHintText">Paste the model name</span>
                        </p>
                        <div class="mt-2 flex flex-wrap gap-1" id="customModelExamples">
                            <!-- Examples populated by JS -->
                        </div>
                    </div>
                    <!-- Option to use custom model for providers with dropdown -->
                    <div id="useCustomModelToggle" class="hidden mt-2">
                        <label class="flex items-center gap-2 text-xs text-gray-500 cursor-pointer">
                            <input type="checkbox" id="useCustomModelCheckbox" onchange="toggleCustomModelInput()" class="rounded border-gray-300">
                            <span>Use custom model name</span>
                        </label>
                    </div>
                </div>

                <!-- API Key -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">API Key *</label>
                    <div class="relative">
                        <input type="password" name="api_key" id="apiKeyInput"
                               class="w-full px-3 py-2 pr-10 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 text-sm font-mono"
                               placeholder="sk-...">
                        <button type="button" onclick="toggleApiKeyVisibility()" 
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <i class="ph ph-eye" id="apiKeyToggleIcon"></i>
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 mt-1" id="apiKeyHint">Leave empty to keep existing key when editing</p>
                </div>

                <!-- Advanced Settings Toggle -->
                <button type="button" onclick="toggleAdvancedSettings()" 
                        class="flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900">
                    <i class="ph ph-caret-right transition-transform" id="advancedToggleIcon"></i>
                    Advanced Settings
                </button>

                <!-- Advanced Settings -->
                <div id="advancedSettings" class="hidden space-y-4 pl-4 border-l-2 border-gray-100">
                    <!-- Temperature -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Temperature 
                            <span class="text-gray-400 font-normal">(0.0 - 2.0)</span>
                        </label>
                        <input type="number" name="temperature" id="temperatureInput"
                               step="0.1" min="0" max="2" value="0.7"
                               class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 text-sm">
                        <p class="text-xs text-gray-500 mt-1">Higher values = more creative, lower = more focused</p>
                    </div>

                    <!-- Max Tokens -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Max Tokens</label>
                        <input type="number" name="max_tokens" id="maxTokensInput"
                               min="100" max="8192" value="2048"
                               class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 text-sm">
                    </div>

                    <!-- System Prompt -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            System Prompt
                            <button type="button" onclick="resetSystemPrompt()" 
                                    class="text-green-600 hover:text-green-700 text-xs ml-2">
                                Reset to Default
                            </button>
                        </label>
                        <textarea name="system_prompt" id="systemPromptInput" rows="6"
                                  class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 text-sm"
                                  placeholder="Custom system prompt..."></textarea>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex gap-3 pt-4">
                    <button type="button" onclick="closeModal()" 
                            class="flex-1 px-4 py-2.5 border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 transition-colors font-medium text-sm">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="flex-1 px-4 py-2.5 bg-green-700 hover:bg-green-800 text-white rounded-xl transition-colors font-medium text-sm">
                        Save Provider
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Test Connection Modal -->
    <div id="testModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Testing Connection</h3>
                <button onclick="closeTestModal()" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                    <i class="ph ph-x text-gray-500"></i>
                </button>
            </div>
            <div class="p-6">
                <div id="testLoading" class="flex flex-col items-center py-8">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-700 mb-4"></div>
                    <p class="text-gray-600">Testing connection...</p>
                </div>
                <div id="testResult" class="hidden">
                    <div id="testSuccess" class="hidden text-center py-4">
                        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="ph ph-check text-3xl text-green-600"></i>
                        </div>
                        <h4 class="text-lg font-semibold text-gray-900 mb-2">Connection Successful!</h4>
                        <p class="text-sm text-gray-600" id="testResponse"></p>
                    </div>
                    <div id="testError" class="hidden text-center py-4">
                        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="ph ph-x text-3xl text-red-600"></i>
                        </div>
                        <h4 class="text-lg font-semibold text-gray-900 mb-2">Connection Failed</h4>
                        <p class="text-sm text-red-600" id="testErrorMessage"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
const allModels = @json($models);
const defaultSystemPrompt = @json($defaultSystemPrompt);
let allSettings = @json($settings);

document.addEventListener('DOMContentLoaded', function() {
    updateModelOptions();
    
    document.getElementById('providerForm').addEventListener('submit', function(e) {
        e.preventDefault();
        saveProvider();
    });
});

function updateModelOptions() {
    const provider = document.getElementById('providerSelect').value;
    const modelSelect = document.getElementById('modelSelect');
    const customModelContainer = document.getElementById('customModelContainer');
    const customModelInput = document.getElementById('customModelInput');
    const useCustomModelToggle = document.getElementById('useCustomModelToggle');
    const useCustomModelCheckbox = document.getElementById('useCustomModelCheckbox');
    const customModelExamples = document.getElementById('customModelExamples');
    const customModelHintText = document.getElementById('customModelHintText');
    
    // Always show custom model for OpenRouter
    if (provider === 'openrouter') {
        modelSelect.classList.add('hidden');
        modelSelect.required = false;
        customModelContainer.classList.remove('hidden');
        customModelInput.required = true;
        useCustomModelToggle.classList.add('hidden');
        if (!customModelInput.value) {
            customModelInput.value = 'openai/gpt-4o';
        }
        customModelHintText.innerHTML = 'Paste model from <a href="https://openrouter.ai/models" target="_blank" class="text-green-600 hover:underline">OpenRouter</a>';
        customModelExamples.innerHTML = `
            <span class="text-xs text-gray-400">Examples:</span>
            <button type="button" onclick="setCustomModel('openai/gpt-4o')" class="text-xs bg-gray-100 hover:bg-gray-200 px-2 py-0.5 rounded">openai/gpt-4o</button>
            <button type="button" onclick="setCustomModel('anthropic/claude-3.5-sonnet')" class="text-xs bg-gray-100 hover:bg-gray-200 px-2 py-0.5 rounded">claude-3.5-sonnet</button>
        `;
    } else if (provider === 'gemini') {
        // Show dropdown with toggle for custom model
        modelSelect.classList.remove('hidden');
        modelSelect.required = true;
        customModelContainer.classList.add('hidden');
        customModelInput.required = false;
        useCustomModelToggle.classList.remove('hidden');
        useCustomModelCheckbox.checked = false;
        
        customModelHintText.innerHTML = 'Enter Gemini model name (e.g., gemini-2.5-flash)';
        customModelExamples.innerHTML = `
            <span class="text-xs text-gray-400">Examples:</span>
            <button type="button" onclick="setCustomModel('gemini-2.5-flash')" class="text-xs bg-gray-100 hover:bg-gray-200 px-2 py-0.5 rounded">gemini-2.5-flash</button>
            <button type="button" onclick="setCustomModel('gemini-2.5-pro')" class="text-xs bg-gray-100 hover:bg-gray-200 px-2 py-0.5 rounded">gemini-2.5-pro</button>
            <button type="button" onclick="setCustomModel('gemini-2.0-flash')" class="text-xs bg-gray-100 hover:bg-gray-200 px-2 py-0.5 rounded">gemini-2.0-flash</button>
        `;
        
        const models = allModels[provider] || {};
        modelSelect.innerHTML = '';
        for (const [value, label] of Object.entries(models)) {
            const option = document.createElement('option');
            option.value = value;
            option.textContent = label;
            modelSelect.appendChild(option);
        }
    } else {
        // OpenAI - dropdown only
        modelSelect.classList.remove('hidden');
        modelSelect.required = true;
        customModelContainer.classList.add('hidden');
        customModelInput.required = false;
        useCustomModelToggle.classList.add('hidden');
        
        const models = allModels[provider] || {};
        modelSelect.innerHTML = '';
        for (const [value, label] of Object.entries(models)) {
            const option = document.createElement('option');
            option.value = value;
            option.textContent = label;
            modelSelect.appendChild(option);
        }
    }
}

function toggleCustomModelInput() {
    const checkbox = document.getElementById('useCustomModelCheckbox');
    const modelSelect = document.getElementById('modelSelect');
    const customModelContainer = document.getElementById('customModelContainer');
    const customModelInput = document.getElementById('customModelInput');
    
    if (checkbox.checked) {
        modelSelect.classList.add('hidden');
        modelSelect.required = false;
        customModelContainer.classList.remove('hidden');
        customModelInput.required = true;
    } else {
        modelSelect.classList.remove('hidden');
        modelSelect.required = true;
        customModelContainer.classList.add('hidden');
        customModelInput.required = false;
    }
}

function setCustomModel(model) {
    document.getElementById('customModelInput').value = model;
}

function getSelectedModel() {
    const provider = document.getElementById('providerSelect').value;
    const useCustomCheckbox = document.getElementById('useCustomModelCheckbox');
    
    if (provider === 'openrouter') {
        return document.getElementById('customModelInput').value;
    }
    if (provider === 'gemini' && useCustomCheckbox && useCustomCheckbox.checked) {
        return document.getElementById('customModelInput').value;
    }
    return document.getElementById('modelSelect').value;
}

function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Add AI Provider';
    document.getElementById('settingId').value = '';
    document.getElementById('providerForm').reset();
    document.getElementById('apiKeyHint').classList.add('hidden');
    document.getElementById('apiKeyInput').required = true;
    document.getElementById('customModelInput').value = ''; // Clear custom model
    updateModelOptions();
    
    document.getElementById('providerModal').classList.remove('hidden');
    document.getElementById('providerModal').classList.add('flex');
}

function editProvider(id) {
    const setting = allSettings.find(s => s.id === id);
    if (!setting) return;
    
    document.getElementById('modalTitle').textContent = 'Edit AI Provider';
    document.getElementById('settingId').value = id;
    document.getElementById('providerSelect').value = setting.provider;
    
    // Set the model value before calling updateModelOptions
    if (setting.provider === 'openrouter') {
        document.getElementById('customModelInput').value = setting.model;
    }
    updateModelOptions();
    
    // For non-OpenRouter, set the select value
    if (setting.provider !== 'openrouter') {
        document.getElementById('modelSelect').value = setting.model;
    }
    
    document.getElementById('apiKeyInput').value = '';
    document.getElementById('apiKeyInput').required = false;
    document.getElementById('apiKeyHint').classList.remove('hidden');
    document.getElementById('temperatureInput').value = setting.temperature;
    document.getElementById('maxTokensInput').value = setting.max_tokens;
    document.getElementById('systemPromptInput').value = setting.system_prompt || '';
    
    document.getElementById('providerModal').classList.remove('hidden');
    document.getElementById('providerModal').classList.add('flex');
}

function closeModal() {
    document.getElementById('providerModal').classList.add('hidden');
    document.getElementById('providerModal').classList.remove('flex');
}

async function saveProvider() {
    const form = document.getElementById('providerForm');
    const settingId = document.getElementById('settingId').value;
    const isEdit = !!settingId;
    
    const data = {
        provider: document.getElementById('providerSelect').value,
        model: getSelectedModel(), // Use helper function to get correct model
        api_key: document.getElementById('apiKeyInput').value,
        temperature: parseFloat(document.getElementById('temperatureInput').value),
        max_tokens: parseInt(document.getElementById('maxTokensInput').value),
        system_prompt: document.getElementById('systemPromptInput').value || null,
    };
    
    // Remove empty api_key for edit
    if (isEdit && !data.api_key) {
        delete data.api_key;
    }
    
    try {
        const url = isEdit 
            ? `{{ url('admin/ai-settings') }}/${settingId}`
            : '{{ route('admin.ai-settings.store') }}';
        
        const response = await fetch(url, {
            method: isEdit ? 'PUT' : 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showStatus('success', isEdit ? 'Provider updated successfully' : 'Provider added successfully');
            closeModal();
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showStatus('error', result.message || 'Failed to save provider');
        }
    } catch (error) {
        console.error('Error:', error);
        showStatus('error', 'Failed to save provider');
    }
}

async function activateProvider(id) {
    try {
        const response = await fetch(`{{ url('admin/ai-settings') }}/${id}/activate`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            showStatus('success', result.message);
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showStatus('error', result.message || 'Failed to activate provider');
        }
    } catch (error) {
        showStatus('error', 'Failed to activate provider');
    }
}

async function deleteProvider(id) {
    if (!confirm('Are you sure you want to delete this AI provider?')) return;
    
    try {
        const response = await fetch(`{{ url('admin/ai-settings') }}/${id}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            showStatus('success', 'Provider deleted successfully');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showStatus('error', result.message || 'Failed to delete provider');
        }
    } catch (error) {
        showStatus('error', 'Failed to delete provider');
    }
}

async function testConnection(id) {
    const setting = allSettings.find(s => s.id === id);
    if (!setting) return;
    
    // Show test modal
    document.getElementById('testModal').classList.remove('hidden');
    document.getElementById('testModal').classList.add('flex');
    document.getElementById('testLoading').classList.remove('hidden');
    document.getElementById('testResult').classList.add('hidden');
    
    try {
        const response = await fetch('{{ route('admin.ai-settings.test') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                provider: setting.provider,
                api_key: setting.api_key || 'STORED_KEY', // Will use stored key
                model: setting.model
            })
        });
        
        const result = await response.json();
        
        document.getElementById('testLoading').classList.add('hidden');
        document.getElementById('testResult').classList.remove('hidden');
        
        if (result.success) {
            document.getElementById('testSuccess').classList.remove('hidden');
            document.getElementById('testError').classList.add('hidden');
            document.getElementById('testResponse').textContent = result.response;
        } else {
            document.getElementById('testSuccess').classList.add('hidden');
            document.getElementById('testError').classList.remove('hidden');
            document.getElementById('testErrorMessage').textContent = result.message;
        }
    } catch (error) {
        document.getElementById('testLoading').classList.add('hidden');
        document.getElementById('testResult').classList.remove('hidden');
        document.getElementById('testSuccess').classList.add('hidden');
        document.getElementById('testError').classList.remove('hidden');
        document.getElementById('testErrorMessage').textContent = 'Connection test failed: ' + error.message;
    }
}

function closeTestModal() {
    document.getElementById('testModal').classList.add('hidden');
    document.getElementById('testModal').classList.remove('flex');
}

function toggleAdvancedSettings() {
    const settings = document.getElementById('advancedSettings');
    const icon = document.getElementById('advancedToggleIcon');
    
    settings.classList.toggle('hidden');
    icon.style.transform = settings.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(90deg)';
}

function toggleApiKeyVisibility() {
    const input = document.getElementById('apiKeyInput');
    const icon = document.getElementById('apiKeyToggleIcon');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('ph-eye');
        icon.classList.add('ph-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('ph-eye-slash');
        icon.classList.add('ph-eye');
    }
}

function resetSystemPrompt() {
    document.getElementById('systemPromptInput').value = defaultSystemPrompt;
}

function showStatus(type, message) {
    const container = document.getElementById('statusMessage');
    const bgColor = type === 'success' ? 'bg-green-50 border-green-100' : 'bg-red-50 border-red-100';
    const textColor = type === 'success' ? 'text-green-700' : 'text-red-700';
    const icon = type === 'success' ? 'ph-check-circle' : 'ph-warning-circle';
    
    container.innerHTML = `
        <div class="${bgColor} border rounded-xl p-4 flex items-center gap-3">
            <i class="ph ${icon} ${textColor} text-xl"></i>
            <span class="${textColor} text-sm">${message}</span>
            <button onclick="this.parentElement.parentElement.classList.add('hidden')" class="ml-auto ${textColor} hover:opacity-70">
                <i class="ph ph-x"></i>
            </button>
        </div>
    `;
    container.classList.remove('hidden');
    
    setTimeout(() => container.classList.add('hidden'), 5000);
}

// =====================
// AI Chat Testing Functions
// =====================
let isGenerating = false;

function setQuickPrompt(text) {
    document.getElementById('chatInput').value = text;
    document.getElementById('chatInput').focus();
}

function clearChatHistory() {
    const chatMessages = document.getElementById('chatMessages');
    // Keep only the welcome message
    const welcomeMsg = chatMessages.querySelector('.flex.justify-center');
    chatMessages.innerHTML = '';
    if (welcomeMsg) {
        chatMessages.appendChild(welcomeMsg);
    }
}

function addMessage(role, content) {
    const chatMessages = document.getElementById('chatMessages');
    const messageDiv = document.createElement('div');
    
    if (role === 'user') {
        messageDiv.className = 'flex justify-end';
        messageDiv.innerHTML = `
            <div class="bg-green-700 text-white rounded-2xl rounded-br-md px-4 py-3 max-w-[80%] shadow-md">
                <p class="text-sm whitespace-pre-wrap">${escapeHtml(content)}</p>
            </div>
        `;
    } else {
        messageDiv.className = 'flex justify-start';
        messageDiv.id = role === 'assistant-streaming' ? 'streamingMessage' : '';
        messageDiv.innerHTML = `
            <div class="bg-white border border-gray-200 rounded-2xl rounded-bl-md px-4 py-3 max-w-[80%] shadow-sm">
                <div class="flex items-center gap-2 mb-2 text-xs text-gray-400">
                    <i class="ph ph-robot"></i>
                    <span>AI Assistant</span>
                </div>
                <p class="text-sm text-gray-800 whitespace-pre-wrap ai-response-content">${content}</p>
            </div>
        `;
    }
    
    chatMessages.appendChild(messageDiv);
    chatMessages.scrollTop = chatMessages.scrollHeight;
    return messageDiv;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Simple markdown formatter for AI responses
function formatMarkdown(text) {
    if (!text) return '';
    
    let html = escapeHtml(text);
    
    // Code blocks (```code```)
    html = html.replace(/```(\w*)\n?([\s\S]*?)```/g, function(match, lang, code) {
        return `<pre class="bg-gray-900 text-green-400 p-3 rounded-lg my-2 overflow-x-auto text-xs font-mono"><code>${code.trim()}</code></pre>`;
    });
    
    // Inline code (`code`)
    html = html.replace(/`([^`]+)`/g, '<code class="bg-gray-100 text-pink-600 px-1.5 py-0.5 rounded text-xs font-mono">$1</code>');
    
    // Headers (## Header)
    html = html.replace(/^### (.*$)/gm, '<h4 class="font-semibold text-gray-900 mt-3 mb-1">$1</h4>');
    html = html.replace(/^## (.*$)/gm, '<h3 class="font-bold text-gray-900 mt-3 mb-2">$1</h3>');
    html = html.replace(/^# (.*$)/gm, '<h2 class="font-bold text-gray-900 text-lg mt-3 mb-2">$1</h2>');
    
    // Bold (**text** or __text__)
    html = html.replace(/\*\*([^*]+)\*\*/g, '<strong class="font-semibold">$1</strong>');
    html = html.replace(/__([^_]+)__/g, '<strong class="font-semibold">$1</strong>');
    
    // Italic (*text* or _text_)
    html = html.replace(/\*([^*]+)\*/g, '<em>$1</em>');
    html = html.replace(/_([^_]+)_/g, '<em>$1</em>');
    
    // Unordered lists (- item or * item)
    html = html.replace(/^[\-\*] (.+)$/gm, '<li class="ml-4">• $1</li>');
    
    // Ordered lists (1. item)
    html = html.replace(/^\d+\. (.+)$/gm, '<li class="ml-4 list-decimal list-inside">$1</li>');
    
    // Wrap consecutive list items
    html = html.replace(/(<li[^>]*>.*<\/li>\n?)+/g, '<ul class="my-2 space-y-1">$&</ul>');
    
    // Line breaks (double newline = paragraph)
    html = html.replace(/\n\n/g, '</p><p class="my-2">');
    html = html.replace(/\n/g, '<br>');
    
    // Wrap in paragraph if not already
    if (!html.startsWith('<')) {
        html = '<p class="my-1">' + html + '</p>';
    }
    
    return html;
}

async function sendTestMessage(event) {
    event.preventDefault();
    
    if (isGenerating) return;
    
    const input = document.getElementById('chatInput');
    const message = input.value.trim();
    
    if (!message) return;
    
    // Clear input and show user message
    input.value = '';
    addMessage('user', message);
    
    // Show loading state
    isGenerating = true;
    const sendButton = document.getElementById('sendButton');
    sendButton.disabled = true;
    sendButton.innerHTML = '<span class="animate-spin"><i class="ph ph-spinner"></i></span> Generating...';
    
    // Add streaming message placeholder
    const streamingDiv = addMessage('assistant-streaming', '<span class="text-gray-400 italic">Generating response...</span>');
    const contentElement = streamingDiv.querySelector('.ai-response-content');
    
    try {
        // Use the admin test chat endpoint
        const response = await fetch('{{ route("admin.ai-settings.chat") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'text/event-stream',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ message: message })
        });
        
        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || 'Failed to get AI response');
        }
        
        // Handle SSE streaming
        const reader = response.body.getReader();
        const decoder = new TextDecoder();
        let fullResponse = '';
        
        while (true) {
            const { done, value } = await reader.read();
            if (done) break;
            
            const chunk = decoder.decode(value, { stream: true });
            const lines = chunk.split('\n');
            
            for (const line of lines) {
                if (line.startsWith('event: ')) {
                    // Handle event type
                } else if (line.startsWith('data: ')) {
                    const data = line.substring(6);
                    
                    // Check if it's the done event
                    if (data.startsWith('{')) {
                        try {
                            const jsonData = JSON.parse(data);
                            if (jsonData.total_length !== undefined) {
                                // Done event - apply final formatting
                                contentElement.innerHTML = formatMarkdown(fullResponse);
                                continue;
                            }
                        } catch (e) {
                            // Not JSON, treat as token
                        }
                    }
                    
                    // Append token to response
                    fullResponse += data;
                    // Show plain text during streaming, format on done
                    contentElement.textContent = fullResponse;
                }
            }
            
            // Scroll to bottom
            document.getElementById('chatMessages').scrollTop = document.getElementById('chatMessages').scrollHeight;
        }
        
        // Finalize the message with formatting
        if (fullResponse) {
            contentElement.innerHTML = formatMarkdown(fullResponse);
        } else {
            contentElement.innerHTML = '<span class="text-red-500">No response received</span>';
        }
        
    } catch (error) {
        console.error('Chat error:', error);
        contentElement.innerHTML = `<span class="text-red-500">Error: ${escapeHtml(error.message)}</span>`;
    } finally {
        // Reset button state
        isGenerating = false;
        sendButton.disabled = false;
        sendButton.innerHTML = '<span id="sendButtonText">Send</span><i class="ph ph-paper-plane-tilt"></i>';
        streamingDiv.id = ''; // Remove streaming ID
    }
}
</script>
@endpush
