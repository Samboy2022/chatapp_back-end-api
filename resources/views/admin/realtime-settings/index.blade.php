@extends('layouts.admin')

@section('title', 'Realtime Settings')
@section('page-title', 'Realtime Settings')

@section('content')
    <!-- Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div class="flex-1">
            <h2 class="text-lg font-semibold text-gray-900">Broadcasting Configuration</h2>
            <p class="text-sm text-gray-500">Manage real-time connection settings for Pusher or Reverb</p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="testConnection()" 
                    class="flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-200 rounded-xl hover:border-green-600 hover:text-green-700 transition-all text-sm font-medium text-gray-700">
                <i class="ph ph-wifi-high text-lg"></i>
                <span class="hidden sm:inline">Test Connection</span>
            </button>
            <button onclick="resetToDefaults()" 
                    class="flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-200 rounded-xl hover:border-orange-500 hover:text-orange-500 transition-all text-sm font-medium text-gray-700">
                <i class="ph ph-arrow-counter-clockwise text-lg"></i>
                <span class="hidden sm:inline">Reset Defaults</span>
            </button>
        </div>
    </div>

    <!-- System Status Overview -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Broadcasting Status -->
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex flex-col items-center text-center">
            <div class="w-16 h-16 rounded-full flex items-center justify-center mb-4 {{ $connectionStatus['enabled'] ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600' }}">
                <i class="ph {{ $connectionStatus['enabled'] ? 'ph-check-circle' : 'ph-x-circle' }} text-3xl"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-1">{{ $connectionStatus['enabled'] ? 'ENABLED' : 'DISABLED' }}</h3>
            <p class="text-sm text-gray-500">Broadcasting Status</p>
        </div>

        <!-- Connection Status -->
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex flex-col items-center text-center">
            <div class="w-16 h-16 rounded-full flex items-center justify-center mb-4 {{ $connectionStatus['connected'] ? 'bg-blue-50 text-blue-600' : 'bg-gray-50 text-gray-400' }}">
                <i class="ph {{ $connectionStatus['connected'] ? 'ph-wifi-high' : 'ph-wifi-slash' }} text-3xl"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-1">{{ $connectionStatus['connected'] ? 'CONNECTED' : 'DISCONNECTED' }}</h3>
            <p class="text-sm text-gray-500">Connection Status</p>
        </div>

        <!-- Current Driver -->
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex flex-col items-center text-center">
            <div class="w-16 h-16 rounded-full bg-purple-50 text-purple-600 flex items-center justify-center mb-4">
                <i class="ph ph-hard-drives text-3xl"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-1">{{ ucfirst($connectionStatus['driver']) }}</h3>
            <p class="text-sm text-gray-500">Current Driver</p>
        </div>
    </div>

    @if($connectionStatus['message'])
    <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 mb-8 flex items-start gap-3">
        <i class="ph ph-info text-blue-600 text-xl mt-0.5"></i>
        <div>
            <h4 class="text-sm font-medium text-blue-900">Status Message</h4>
            <p class="text-sm text-blue-700 mt-1">{{ $connectionStatus['message'] }}</p>
        </div>
    </div>
    @endif

    <!-- Configuration Form -->
    <form id="realtime-settings-form" method="POST" action="{{ route('admin.realtime-settings.update') }}" class="space-y-6">
        @csrf

        <!-- General Settings -->
        <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center gap-3">
                <div class="p-2 bg-white border border-gray-200 rounded-lg shadow-sm">
                    <i class="ph ph-gear text-lg text-gray-600"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">General Settings</h3>
                    <p class="text-xs text-gray-500">Enable/disable broadcasting and select driver</p>
                </div>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Status Toggle -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Broadcasting Status</label>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" 
                               id="status" 
                               name="status" 
                               value="enabled" 
                               {{ $settings->status === 'enabled' ? 'checked' : '' }}
                               onchange="toggleBroadcasting()"
                               class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-700"></div>
                        <span class="ml-3 text-sm font-medium text-gray-700">Enable Real-time Broadcasting</span>
                    </label>
                    <p class="mt-2 text-xs text-gray-500">When disabled, the system will fallback to log driver.</p>
                </div>

                <!-- Driver Selection -->
                <div id="driver-field" class="transition-opacity duration-200">
                    <label for="driver" class="block text-sm font-medium text-gray-700 mb-2">Broadcasting Driver</label>
                    <select id="driver" 
                            name="driver" 
                            onchange="toggleDriverSettings()"
                            {{ $settings->status === 'disabled' ? 'disabled' : '' }}
                            class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 transition-all text-sm">
                        <option value="pusher" {{ $settings->driver === 'pusher' ? 'selected' : '' }}>Pusher (Cloud Service)</option>
                        <option value="reverb" {{ $settings->driver === 'reverb' ? 'selected' : '' }}>Laravel Reverb (Self-hosted)</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Pusher Settings -->
        <div id="pusher-settings" class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden" style="{{ $settings->driver !== 'pusher' || $settings->status === 'disabled' ? 'display: none;' : '' }}">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center gap-3">
                <div class="p-2 bg-white border border-gray-200 rounded-lg shadow-sm">
                    <i class="ph ph-cloud text-lg text-green-600"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">Pusher Cloud Settings</h3>
                    <p class="text-xs text-gray-500">Configure your Pusher.com credentials</p>
                </div>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-1 setting-field">
                    <label for="pusher_app_id" class="block text-sm font-medium text-gray-700">App ID</label>
                    <input type="text" id="pusher_app_id" name="pusher_app_id" value="{{ old('pusher_app_id', $settings->pusher_app_id) }}" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 transition-all text-sm">
                </div>
                <div class="space-y-1 setting-field">
                    <label for="pusher_key" class="block text-sm font-medium text-gray-700">App Key</label>
                    <input type="text" id="pusher_key" name="pusher_key" value="{{ old('pusher_key', $settings->pusher_key) }}" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 transition-all text-sm">
                </div>
                <div class="space-y-1 setting-field">
                    <label for="pusher_secret" class="block text-sm font-medium text-gray-700">App Secret</label>
                    <input type="password" id="pusher_secret" name="pusher_secret" value="{{ old('pusher_secret', $settings->pusher_secret) }}" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 transition-all text-sm">
                </div>
                <div class="space-y-1 setting-field">
                    <label for="pusher_cluster" class="block text-sm font-medium text-gray-700">Cluster</label>
                    <input type="text" id="pusher_cluster" name="pusher_cluster" value="{{ old('pusher_cluster', $settings->pusher_cluster) }}" placeholder="e.g. mt1" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 transition-all text-sm">
                </div>
            </div>
        </div>

        <!-- Reverb Settings -->
        <div id="reverb-settings" class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden" style="{{ $settings->driver !== 'reverb' || $settings->status === 'disabled' ? 'display: none;' : '' }}">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center gap-3">
                <div class="p-2 bg-white border border-gray-200 rounded-lg shadow-sm">
                    <i class="ph ph-server text-lg text-purple-600"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">Laravel Reverb Settings</h3>
                    <p class="text-xs text-gray-500">Configure your self-hosted Reverb server</p>
                </div>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-1 setting-field">
                    <label for="reverb_app_id" class="block text-sm font-medium text-gray-700">App ID</label>
                    <input type="text" id="reverb_app_id" name="reverb_app_id" value="{{ old('reverb_app_id', $settings->reverb_app_id) }}" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 transition-all text-sm">
                </div>
                <div class="space-y-1 setting-field">
                    <label for="reverb_key" class="block text-sm font-medium text-gray-700">App Key</label>
                    <input type="text" id="reverb_key" name="reverb_key" value="{{ old('reverb_key', $settings->reverb_key) }}" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 transition-all text-sm">
                </div>
                <div class="space-y-1 setting-field">
                    <label for="reverb_secret" class="block text-sm font-medium text-gray-700">App Secret</label>
                    <input type="password" id="reverb_secret" name="reverb_secret" value="{{ old('reverb_secret', $settings->reverb_secret) }}" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 transition-all text-sm">
                </div>
                <div class="space-y-1 setting-field">
                    <label for="reverb_cluster" class="block text-sm font-medium text-gray-700">Cluster</label>
                    <input type="text" id="reverb_cluster" name="reverb_cluster" value="{{ old('reverb_cluster', $settings->reverb_cluster ?? 'local') }}" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 transition-all text-sm">
                </div>
                <div class="space-y-1 setting-field">
                    <label for="reverb_host" class="block text-sm font-medium text-gray-700">Host</label>
                    <input type="text" id="reverb_host" name="reverb_host" value="{{ old('reverb_host', $settings->reverb_host) }}" placeholder="127.0.0.1" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 transition-all text-sm">
                </div>
                <div class="space-y-1 setting-field">
                    <label for="reverb_port" class="block text-sm font-medium text-gray-700">Port</label>
                    <input type="number" id="reverb_port" name="reverb_port" value="{{ old('reverb_port', $settings->reverb_port) }}" placeholder="8080" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 transition-all text-sm">
                </div>
                <div class="space-y-1 setting-field">
                    <label for="reverb_scheme" class="block text-sm font-medium text-gray-700">Scheme</label>
                    <select id="reverb_scheme" name="reverb_scheme" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 transition-all text-sm">
                        <option value="http" {{ $settings->reverb_scheme === 'http' ? 'selected' : '' }}>HTTP</option>
                        <option value="https" {{ $settings->reverb_scheme === 'https' ? 'selected' : '' }}>HTTPS</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex items-center justify-end gap-3 pt-4">
            <button type="button" onclick="window.location.reload()" class="px-6 py-2.5 bg-white border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 transition-colors font-medium text-sm">
                Cancel
            </button>
            <button type="submit" class="px-6 py-2.5 bg-green-700 text-white rounded-xl hover:bg-green-800 transition-colors font-medium text-sm shadow-lg shadow-green-700/30">
                Save Changes
            </button>
        </div>
    </form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeFormHandling();
    initializeStatusUpdates();
});

function toggleBroadcasting() {
    const statusCheckbox = document.getElementById('status');
    const driverField = document.getElementById('driver-field');
    const driverSelect = document.getElementById('driver');
    const pusherSettings = document.getElementById('pusher-settings');
    const reverbSettings = document.getElementById('reverb-settings');

    const isEnabled = statusCheckbox.checked;
    driverSelect.disabled = !isEnabled;

    if (isEnabled) {
        driverField.style.opacity = '1';
        toggleDriverSettings();
    } else {
        driverField.style.opacity = '0.5';
        pusherSettings.style.display = 'none';
        reverbSettings.style.display = 'none';
    }
}

function toggleDriverSettings() {
    const statusCheckbox = document.getElementById('status');
    const driverSelect = document.getElementById('driver');
    const pusherSettings = document.getElementById('pusher-settings');
    const reverbSettings = document.getElementById('reverb-settings');

    if (!statusCheckbox.checked) {
        pusherSettings.style.display = 'none';
        reverbSettings.style.display = 'none';
        return;
    }

    const selectedDriver = driverSelect.value;
    if (selectedDriver === 'pusher') {
        pusherSettings.style.display = 'block';
        reverbSettings.style.display = 'none';
    } else if (selectedDriver === 'reverb') {
        pusherSettings.style.display = 'none';
        reverbSettings.style.display = 'block';
    }
}

async function testConnection() {
    const button = event.target.closest('button');
    const originalContent = button.innerHTML;

    button.innerHTML = '<i class="ph ph-spinner ph-spin text-lg"></i><span class="hidden sm:inline">Testing...</span>';
    button.disabled = true;

    try {
        const response = await fetch('/admin/realtime-settings/test', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        const data = await response.json();

        if (data.success) {
            showNotification('success', `Connection successful: ${data.message}`);
        } else {
            showNotification('error', `Connection failed: ${data.message}`);
        }
    } catch (error) {
        console.error('Connection test error:', error);
        showNotification('error', 'Connection test failed: ' + error.message);
    } finally {
        button.innerHTML = originalContent;
        button.disabled = false;
    }
}

async function resetToDefaults() {
    if (!confirm('Are you sure you want to reset all settings to defaults? This action cannot be undone.')) {
        return;
    }

    try {
        const response = await fetch('/admin/realtime-settings/reset', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        const data = await response.json();

        if (data.success) {
            showNotification('success', 'Settings reset to defaults successfully');
            setTimeout(() => window.location.reload(), 1500);
        } else {
            showNotification('error', 'Failed to reset settings: ' + data.message);
        }
    } catch (error) {
        console.error('Reset error:', error);
        showNotification('error', 'Reset failed: ' + error.message);
    }
}

function initializeFormHandling() {
    const form = document.getElementById('realtime-settings-form');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.innerHTML = '<i class="ph ph-spinner ph-spin mr-2"></i>Saving...';
            submitBtn.disabled = true;
        }

        if (!validateForm()) {
            e.preventDefault();
            if (submitBtn) {
                submitBtn.innerHTML = 'Save Changes';
                submitBtn.disabled = false;
            }
            return false;
        }
    });
}

function initializeStatusUpdates() {
    setInterval(updateConnectionStatus, 30000);
}

async function updateConnectionStatus() {
    try {
        const response = await fetch('/admin/realtime-settings/status');
        const data = await response.json();

        if (data.success) {
            // Update UI based on status (simplified for brevity, ideally would update DOM elements)
            console.log('Status updated:', data.status);
        }
    } catch (error) {
        console.error('Failed to update status:', error);
    }
}

function validateForm() {
    const statusCheckbox = document.getElementById('status');
    const driverSelect = document.getElementById('driver');

    if (!statusCheckbox.checked) return true;

    const selectedDriver = driverSelect.value;
    let isValid = true;
    let requiredFields = [];

    if (selectedDriver === 'pusher') {
        requiredFields = ['pusher_app_id', 'pusher_key', 'pusher_secret'];
    } else if (selectedDriver === 'reverb') {
        requiredFields = ['reverb_app_id', 'reverb_key', 'reverb_secret'];
    }

    requiredFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (!field.value.trim()) {
            showFieldError(field, 'This field is required');
            isValid = false;
        } else {
            clearFieldError(field);
        }
    });

    return isValid;
}

function showFieldError(field, message) {
    const container = field.closest('.setting-field');
    const existingError = container.querySelector('.text-red-500');
    if (existingError) existingError.remove();

    field.classList.add('border-red-500', 'focus:border-red-500', 'focus:ring-red-200');
    
    const errorMsg = document.createElement('p');
    errorMsg.className = 'text-xs text-red-500 mt-1';
    errorMsg.innerText = message;
    container.appendChild(errorMsg);
}

function clearFieldError(field) {
    const container = field.closest('.setting-field');
    const existingError = container.querySelector('.text-red-500');
    if (existingError) existingError.remove();
    
    field.classList.remove('border-red-500', 'focus:border-red-500', 'focus:ring-red-200');
}

function showNotification(type, message) {
    const notification = document.createElement('div');
    notification.className = `fixed top-5 right-5 z-50 p-4 rounded-xl shadow-lg border flex items-center gap-3 min-w-[300px] animate-fade-in-down ${
        type === 'success' ? 'bg-green-50 border-green-100 text-green-700' : 'bg-red-50 border-red-100 text-red-700'
    }`;
    
    notification.innerHTML = `
        <i class="ph ${type === 'success' ? 'ph-check-circle' : 'ph-warning-circle'} text-xl"></i>
        <span class="font-medium text-sm">${message}</span>
        <button onclick="this.parentElement.remove()" class="ml-auto text-current opacity-50 hover:opacity-100">
            <i class="ph ph-x"></i>
        </button>
    `;

    document.body.appendChild(notification);
    setTimeout(() => notification.remove(), 5000);
}
</script>

<style>
@keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in-down {
    animation: fadeInDown 0.3s ease-out forwards;
}
</style>
@endpush


