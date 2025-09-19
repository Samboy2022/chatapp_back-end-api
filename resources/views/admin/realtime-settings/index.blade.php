@extends('layouts.admin')

@section('title', 'Realtime Settings')
@section('page-title', 'Realtime Settings')

@section('toolbar-buttons')
    <a href="{{ route('admin.realtime-settings.test') }}"
       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors duration-200 inline-flex items-center font-medium">
        <i class="fas fa-wifi mr-2"></i> Test Connection
    </a>
    <a href="{{ route('admin.realtime-settings.reset') }}"
       class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg transition-colors duration-200 inline-flex items-center font-medium ml-3">
        <i class="fas fa-undo mr-2"></i> Reset to Defaults
    </a>
@endsection

@section('content')
    <!-- System Status Overview -->
    <div class="bg-white rounded-xl shadow-lg whatsapp-shadow p-6 mb-6">
        <div class="flex items-center space-x-3 mb-6">
            <div class="bg-whatsapp-light p-2 rounded-lg">
                <i class="fas fa-broadcast-tower text-whatsapp-primary"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900">Realtime Broadcasting Settings</h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="text-center">
                <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-3 {{ $connectionStatus['enabled'] ? 'bg-green-100' : 'bg-red-100' }}">
                    <i class="fas fa-{{ $connectionStatus['enabled'] ? 'check-circle' : 'times-circle' }} text-2xl {{ $connectionStatus['enabled'] ? 'text-green-600' : 'text-red-600' }}"></i>
                </div>
                <div class="text-xl font-bold text-gray-900 mb-1">{{ $connectionStatus['enabled'] ? 'ENABLED' : 'DISABLED' }}</div>
                <div class="text-sm text-whatsapp-text">Broadcasting Status</div>
            </div>

            <div class="text-center">
                <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-3 {{ $connectionStatus['connected'] ? 'bg-blue-100' : 'bg-gray-100' }}">
                    <i class="fas fa-{{ $connectionStatus['connected'] ? 'wifi' : 'wifi-slash' }} text-2xl {{ $connectionStatus['connected'] ? 'text-blue-600' : 'text-gray-600' }}"></i>
                </div>
                <div class="text-xl font-bold text-gray-900 mb-1">{{ $connectionStatus['connected'] ? 'CONNECTED' : 'DISCONNECTED' }}</div>
                <div class="text-sm text-whatsapp-text">Connection Status</div>
            </div>

            <div class="text-center">
                <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-3 bg-purple-100">
                    <i class="fas fa-server text-2xl text-purple-600"></i>
                </div>
                <div class="text-xl font-bold text-gray-900 mb-1">{{ ucfirst($connectionStatus['driver']) }}</div>
                <div class="text-sm text-whatsapp-text">Current Driver</div>
            </div>
        </div>

        <div class="bg-gray-50 rounded-lg p-4">
            <div class="text-sm text-gray-600">
                <strong>Status Message:</strong> {{ $connectionStatus['message'] }}
            </div>
        </div>
    </div>

    <!-- Broadcasting Configuration -->
    <div class="bg-white rounded-xl shadow-lg whatsapp-shadow p-6 mb-6">
        <form id="realtime-settings-form" method="POST" action="{{ route('admin.realtime-settings.update') }}">
            @csrf

            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4 flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4 flex items-center">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4">
                    <div class="flex items-center mb-2">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <strong>Validation Error:</strong>
                    </div>
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- General Settings -->
            <div class="border border-gray-200 rounded-lg p-4 mb-6">
                <div class="flex items-center space-x-3 mb-4">
                    <div class="bg-whatsapp-primary p-2 rounded-lg">
                        <i class="fas fa-cog text-white"></i>
                    </div>
                    <div>
                        <h4 class="text-lg font-medium text-gray-900">General Settings</h4>
                        <p class="text-sm text-whatsapp-text">Enable/disable broadcasting and select driver</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Broadcasting Status -->
                    <div class="space-y-2">
                        <label for="status" class="block text-sm font-medium text-gray-700">
                            Broadcasting Status
                        </label>
                        <div class="flex items-center">
                            <input type="checkbox"
                                   id="status"
                                   name="status"
                                   value="enabled"
                                   {{ $settings->status === 'enabled' ? 'checked' : '' }}
                                   onchange="toggleBroadcasting()"
                                   class="mr-3">
                            <span class="text-sm text-gray-600">Enable real-time broadcasting</span>
                        </div>
                        <p class="text-xs text-gray-500 flex items-center">
                            <i class="fas fa-info-circle mr-1"></i>
                            When disabled, all broadcasting will use log driver
                        </p>
                    </div>

                    <!-- Driver Selection -->
                    <div class="space-y-2" id="driver-field">
                        <label for="driver" class="block text-sm font-medium text-gray-700">
                            Broadcasting Driver
                        </label>
                        <select id="driver"
                                name="driver"
                                onchange="toggleDriverSettings()"
                                {{ $settings->status === 'disabled' ? 'disabled' : '' }}
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-primary focus:border-whatsapp-primary transition-colors bg-white">
                            <option value="pusher" {{ $settings->driver === 'pusher' ? 'selected' : '' }}>
                                Pusher (Cloud Service)
                            </option>
                            <option value="reverb" {{ $settings->driver === 'reverb' ? 'selected' : '' }}>
                                Laravel Reverb (Self-hosted)
                            </option>
                        </select>
                        <p class="text-xs text-gray-500 flex items-center">
                            <i class="fas fa-info-circle mr-1"></i>
                            Choose between Pusher Cloud or self-hosted Laravel Reverb
                        </p>
                    </div>
                </div>
            </div>

            <!-- Pusher Settings -->
            <div class="border border-gray-200 rounded-lg p-4 mb-6" id="pusher-settings" style="{{ $settings->driver !== 'pusher' || $settings->status === 'disabled' ? 'display: none;' : '' }}">
                <div class="flex items-center space-x-3 mb-4">
                    <div class="bg-green-100 p-2 rounded-lg">
                        <i class="fas fa-cloud text-green-600"></i>
                    </div>
                    <div>
                        <h4 class="text-lg font-medium text-gray-900">Pusher Cloud Settings</h4>
                        <p class="text-sm text-whatsapp-text">Configure Pusher Cloud credentials</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label for="pusher_app_id" class="block text-sm font-medium text-gray-700">App ID</label>
                        <input type="text"
                               id="pusher_app_id"
                               name="pusher_app_id"
                               value="{{ old('pusher_app_id', $settings->pusher_app_id) }}"
                               placeholder="Enter Pusher App ID"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-primary focus:border-whatsapp-primary transition-colors">
                    </div>

                    <div class="space-y-2">
                        <label for="pusher_key" class="block text-sm font-medium text-gray-700">App Key</label>
                        <input type="text"
                               id="pusher_key"
                               name="pusher_key"
                               value="{{ old('pusher_key', $settings->pusher_key) }}"
                               placeholder="Enter Pusher App Key"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-primary focus:border-whatsapp-primary transition-colors">
                    </div>

                    <div class="space-y-2">
                        <label for="pusher_secret" class="block text-sm font-medium text-gray-700">App Secret</label>
                        <input type="password"
                               id="pusher_secret"
                               name="pusher_secret"
                               value="{{ old('pusher_secret', $settings->pusher_secret) }}"
                               placeholder="Enter Pusher App Secret"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-primary focus:border-whatsapp-primary transition-colors">
                    </div>

                    <div class="space-y-2">
                        <label for="pusher_cluster" class="block text-sm font-medium text-gray-700">Cluster</label>
                        <input type="text"
                               id="pusher_cluster"
                               name="pusher_cluster"
                               value="{{ old('pusher_cluster', $settings->pusher_cluster) }}"
                               placeholder="e.g., mt1, us2, eu"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-primary focus:border-whatsapp-primary transition-colors">
                    </div>
                </div>
            </div>

            <!-- Reverb Settings -->
            <div class="border border-gray-200 rounded-lg p-4 mb-6" id="reverb-settings" style="{{ $settings->driver !== 'reverb' || $settings->status === 'disabled' ? 'display: none;' : '' }}">
                <div class="flex items-center space-x-3 mb-4">
                    <div class="bg-purple-100 p-2 rounded-lg">
                        <i class="fas fa-server text-purple-600"></i>
                    </div>
                    <div>
                        <h4 class="text-lg font-medium text-gray-900">Laravel Reverb Settings</h4>
                        <p class="text-sm text-whatsapp-text">Configure self-hosted Reverb server</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label for="reverb_app_id" class="block text-sm font-medium text-gray-700">App ID</label>
                        <input type="text"
                               id="reverb_app_id"
                               name="reverb_app_id"
                               value="{{ old('reverb_app_id', $settings->reverb_app_id) }}"
                               placeholder="Enter Reverb App ID"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-primary focus:border-whatsapp-primary transition-colors">
                    </div>

                    <div class="space-y-2">
                        <label for="reverb_key" class="block text-sm font-medium text-gray-700">App Key</label>
                        <input type="text"
                               id="reverb_key"
                               name="reverb_key"
                               value="{{ old('reverb_key', $settings->reverb_key) }}"
                               placeholder="Enter Reverb App Key"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-primary focus:border-whatsapp-primary transition-colors">
                    </div>

                    <div class="space-y-2">
                        <label for="reverb_secret" class="block text-sm font-medium text-gray-700">App Secret</label>
                        <input type="password"
                               id="reverb_secret"
                               name="reverb_secret"
                               value="{{ old('reverb_secret', $settings->reverb_secret) }}"
                               placeholder="Enter Reverb App Secret"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-primary focus:border-whatsapp-primary transition-colors">
                    </div>

                    <div class="space-y-2">
                        <label for="reverb_cluster" class="block text-sm font-medium text-gray-700">Cluster</label>
                        <input type="text"
                               id="reverb_cluster"
                               name="reverb_cluster"
                               value="{{ old('reverb_cluster', $settings->reverb_cluster ?? 'local') }}"
                               placeholder="e.g., local, production"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-primary focus:border-whatsapp-primary transition-colors">
                    </div>

                    <div class="space-y-2">
                        <label for="reverb_host" class="block text-sm font-medium text-gray-700">Host</label>
                        <input type="text"
                               id="reverb_host"
                               name="reverb_host"
                               value="{{ old('reverb_host', $settings->reverb_host) }}"
                               placeholder="127.0.0.1"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-primary focus:border-whatsapp-primary transition-colors">
                    </div>

                    <div class="space-y-2">
                        <label for="reverb_port" class="block text-sm font-medium text-gray-700">Port</label>
                        <input type="number"
                               id="reverb_port"
                               name="reverb_port"
                               value="{{ old('reverb_port', $settings->reverb_port) }}"
                               placeholder="8080"
                               min="1"
                               max="65535"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-primary focus:border-whatsapp-primary transition-colors">
                    </div>

                    <div class="space-y-2">
                        <label for="reverb_scheme" class="block text-sm font-medium text-gray-700">Scheme</label>
                        <select id="reverb_scheme"
                                name="reverb_scheme"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-primary focus:border-whatsapp-primary transition-colors bg-white">
                            <option value="http" {{ $settings->reverb_scheme === 'http' ? 'selected' : '' }}>HTTP</option>
                            <option value="https" {{ $settings->reverb_scheme === 'https' ? 'selected' : '' }}>HTTPS</option>
                        </select>
                    </div>
                </div>
            </div>

        <!-- Form Actions -->
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center text-sm text-gray-600">
                    <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                    <span>Changes will be applied immediately and affect all connected clients</span>
                </div>
                <div class="flex items-center space-x-3">
                    <button type="button" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg transition-colors duration-200 inline-flex items-center font-medium" onclick="resetToDefaults()">
                        <i class="fas fa-undo mr-2"></i>Reset to Defaults
                    </button>
                    <button type="button" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors duration-200 inline-flex items-center font-medium" onclick="window.location.reload()">
                        <i class="fas fa-times mr-2"></i>Cancel
                    </button>
                    <button type="submit" class="bg-whatsapp-primary hover:bg-whatsapp-dark text-white px-4 py-2 rounded-lg transition-colors duration-200 inline-flex items-center font-medium">
                        <i class="fas fa-save mr-2"></i>Save Settings
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🎛️ Realtime Settings page loaded');

    // Initialize form handling
    initializeFormHandling();

    // Initialize status updates
    initializeStatusUpdates();

    console.log('✅ All realtime settings features initialized');
});

/**
 * Toggle broadcasting on/off
 */
function toggleBroadcasting() {
    const statusCheckbox = document.getElementById('status');
    const driverField = document.getElementById('driver-field');
    const driverSelect = document.getElementById('driver');
    const pusherSettings = document.getElementById('pusher-settings');
    const reverbSettings = document.getElementById('reverb-settings');

    const isEnabled = statusCheckbox.checked;

    // Enable/disable driver selection
    driverSelect.disabled = !isEnabled;

    if (isEnabled) {
        driverField.style.opacity = '1';
        toggleDriverSettings();
    } else {
        driverField.style.opacity = '0.6';
        pusherSettings.style.display = 'none';
        reverbSettings.style.display = 'none';
    }

    console.log('Broadcasting toggled:', isEnabled ? 'enabled' : 'disabled');
}

/**
 * Toggle driver-specific settings
 */
function toggleDriverSettings() {
    const statusCheckbox = document.getElementById('status');
    const driverSelect = document.getElementById('driver');
    const pusherSettings = document.getElementById('pusher-settings');
    const reverbSettings = document.getElementById('reverb-settings');

    const isEnabled = statusCheckbox.checked;
    const selectedDriver = driverSelect.value;

    if (!isEnabled) {
        pusherSettings.style.display = 'none';
        reverbSettings.style.display = 'none';
        return;
    }

    if (selectedDriver === 'pusher') {
        pusherSettings.style.display = 'block';
        reverbSettings.style.display = 'none';
        console.log('Showing Pusher settings');
    } else if (selectedDriver === 'reverb') {
        pusherSettings.style.display = 'none';
        reverbSettings.style.display = 'block';
        console.log('Showing Reverb settings');
    }
}

/**
 * Test connection with current settings
 */
async function testConnection() {
    const button = event.target;
    const originalText = button.innerHTML;

    // Show loading state
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Testing...';
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
            showNotification('success', `✅ Connection successful: ${data.message}`);
        } else {
            showNotification('error', `❌ Connection failed: ${data.message}`);
        }

    } catch (error) {
        console.error('Connection test error:', error);
        showNotification('error', '❌ Connection test failed: ' + error.message);
    } finally {
        // Restore button
        button.innerHTML = originalText;
        button.disabled = false;
    }
}

/**
 * Reset settings to defaults
 */
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
            showNotification('success', '✅ Settings reset to defaults successfully');
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showNotification('error', '❌ Failed to reset settings: ' + data.message);
        }

    } catch (error) {
        console.error('Reset error:', error);
        showNotification('error', '❌ Reset failed: ' + error.message);
    }
}

/**
 * Initialize form handling
 */
function initializeFormHandling() {
    const form = document.getElementById('realtime-settings-form');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        console.log('📤 Form submission started');

        const submitBtn = form.querySelector('button[type="submit"]');

        if (submitBtn) {
            // Show loading state
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
            submitBtn.disabled = true;
        }

        // Add form validation
        if (!validateForm()) {
            e.preventDefault();
            resetSubmitButton(submitBtn);
            return false;
        }

        console.log('✅ Form validation passed, submitting...');
    });
}

/**
 * Initialize status updates
 */
function initializeStatusUpdates() {
    // Auto-refresh status every 30 seconds
    setInterval(updateConnectionStatus, 30000);
}

/**
 * Update connection status
 */
async function updateConnectionStatus() {
    try {
        const response = await fetch('/admin/realtime-settings/status');
        const data = await response.json();

        if (data.success) {
            updateStatusDisplay(data.status);
        }
    } catch (error) {
        console.error('Failed to update status:', error);
    }
}

/**
 * Update status display
 */
function updateStatusDisplay(status) {
    const enabledBadge = document.querySelector('.status-enabled, .status-disabled');
    const connectedBadge = document.querySelector('.status-connected, .status-disconnected');

    if (enabledBadge) {
        enabledBadge.className = `status-badge ${status.enabled ? 'status-enabled' : 'status-disabled'}`;
        enabledBadge.innerHTML = `<i class="bi bi-${status.enabled ? 'check-circle-fill' : 'x-circle-fill'} me-1"></i>${status.enabled ? 'ENABLED' : 'DISABLED'}`;
    }

    if (connectedBadge) {
        connectedBadge.className = `status-badge ${status.connected ? 'status-connected' : 'status-disconnected'}`;
        connectedBadge.innerHTML = `<i class="bi bi-${status.connected ? 'wifi' : 'wifi-off'} me-1"></i>${status.connected ? 'CONNECTED' : 'DISCONNECTED'}`;
    }
}

/**
 * Validate form before submission
 */
function validateForm() {
    const statusCheckbox = document.getElementById('status');
    const driverSelect = document.getElementById('driver');

    if (!statusCheckbox.checked) {
        // If disabled, no validation needed
        return true;
    }

    const selectedDriver = driverSelect.value;
    let isValid = true;

    if (selectedDriver === 'pusher') {
        const requiredFields = ['pusher_app_id', 'pusher_key', 'pusher_secret'];
        requiredFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (!field.value.trim()) {
                showFieldError(field, 'This field is required for Pusher');
                isValid = false;
            } else {
                clearFieldError(field);
            }
        });
    } else if (selectedDriver === 'reverb') {
        const requiredFields = ['reverb_app_id', 'reverb_key', 'reverb_secret'];
        requiredFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (!field.value.trim()) {
                showFieldError(field, 'This field is required for Reverb');
                isValid = false;
            } else {
                clearFieldError(field);
            }
        });
    }

    return isValid;
}

/**
 * Show field error
 */
function showFieldError(field, message) {
    const settingField = field.closest('.setting-field');

    // Remove existing error
    const existingError = settingField.querySelector('.field-error');
    if (existingError) {
        existingError.remove();
    }

    // Add error class
    field.classList.add('is-invalid');

    // Add error message
    const errorDiv = document.createElement('div');
    errorDiv.className = 'field-error text-danger mt-1';
    errorDiv.innerHTML = `<i class="bi bi-exclamation-circle me-1"></i>${message}`;

    field.parentNode.appendChild(errorDiv);
}

/**
 * Clear field error
 */
function clearFieldError(field) {
    const settingField = field.closest('.setting-field');
    const errorDiv = settingField.querySelector('.field-error');

    if (errorDiv) {
        errorDiv.remove();
    }

    field.classList.remove('is-invalid');
}

/**
 * Reset submit button to normal state
 */
function resetSubmitButton(submitBtn) {
    if (submitBtn) {
        submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Save Settings';
        submitBtn.disabled = false;
    }
}

/**
 * Show notification
 */
function showNotification(type, message) {
    const alertClass = type === 'success' ? 'bg-green-50 border border-green-200 text-green-700' : 'bg-red-50 border border-red-200 text-red-700';
    const icon = type === 'success' ? 'check-circle' : 'exclamation-triangle';

    const notification = document.createElement('div');
    notification.className = `fixed top-5 right-5 z-50 p-4 rounded-lg shadow-lg border ${alertClass} flex items-center space-x-3 min-w-80`;
    notification.innerHTML = `
        <i class="fas fa-${icon}"></i>
        <span>${message}</span>
        <button type="button" class="ml-auto text-gray-400 hover:text-gray-600" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;

    document.body.appendChild(notification);

    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}
</script>

<style>
/* Additional styles for JavaScript interactions */
.field-error {
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

.is-invalid {
    border-color: #dc3545 !important;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
}

.btn-loading {
    position: relative;
    overflow: hidden;
}

.btn-loading::after {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    animation: loading 1.5s infinite;
}

@keyframes loading {
    0% { left: -100%; }
    100% { left: 100%; }
}
</style>
@endpush
