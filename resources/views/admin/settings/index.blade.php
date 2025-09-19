@extends('layouts.admin')

@section('title', 'Settings')
@section('page-title', 'System Settings')

@php
function getGroupColor($group) {
    $colors = [
        'general' => 'primary',
        'file' => 'success',
        'chat' => 'info',
        'user' => 'warning',
        'notification' => 'secondary',
        'system' => 'danger'
    ];
    return $colors[$group] ?? 'primary';
}

function getGroupIcon($group) {
    $icons = [
        'general' => 'gear',
        'file' => 'cloud-upload',
        'chat' => 'chat-left-text',
        'user' => 'people',
        'notification' => 'bell',
        'system' => 'server'
    ];
    return $icons[$group] ?? 'gear';
}
@endphp

@section('toolbar-buttons')
    <button type="submit" form="settingsForm"
            class="bg-whatsapp-primary hover:bg-whatsapp-dark text-white px-4 py-2 rounded-lg transition-colors duration-200 inline-flex items-center font-medium">
        <i class="fas fa-save mr-2"></i> Save All Settings
    </button>
    <a href="{{ route('admin.settings.clear-cache') }}"
       class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors duration-200 inline-flex items-center font-medium ml-3">
        <i class="fas fa-sync-alt mr-2"></i> Clear Cache
    </a>
@endsection

@section('content')
<!-- Settings Overview -->
<div class="bg-white rounded-xl shadow-lg whatsapp-shadow p-6 mb-6">
    <div class="flex items-center space-x-3 mb-6">
        <div class="bg-whatsapp-light p-2 rounded-lg">
            <i class="fas fa-cog text-whatsapp-primary"></i>
        </div>
        <h3 class="text-lg font-semibold text-gray-900">System Settings</h3>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-blue-50 rounded-lg p-4 text-center">
            <div class="text-2xl font-bold text-blue-600 mb-2">{{ $settingsGrouped->get('general', collect())->count() }}</div>
            <div class="text-sm text-blue-800">General Settings</div>
        </div>
        <div class="bg-green-50 rounded-lg p-4 text-center">
            <div class="text-2xl font-bold text-green-600 mb-2">{{ $settingsGrouped->get('system', collect())->count() }}</div>
            <div class="text-sm text-green-800">System Settings</div>
        </div>
        <div class="bg-orange-50 rounded-lg p-4 text-center">
            <div class="text-2xl font-bold text-orange-600 mb-2">{{ $settingsGrouped->get('file', collect())->count() }}</div>
            <div class="text-sm text-orange-800">File Settings</div>
        </div>
    </div>
</div>

<!-- Settings Form -->
<form id="settingsForm" method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
    @csrf
    <!-- Settings Configuration -->
    <div class="bg-white rounded-xl shadow-lg whatsapp-shadow p-6 mb-6">
        <h4 class="text-lg font-semibold text-gray-900 mb-4">Settings Configuration</h4>

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

        <div class="space-y-6">
            @foreach($settingsGrouped as $group => $settings)
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="bg-{{ getGroupColor($group) }}-100 p-2 rounded-lg">
                            <i class="fas fa-{{ getGroupIcon($group) }} text-{{ getGroupColor($group) }}-600"></i>
                        </div>
                        <div>
                            <h5 class="text-md font-medium text-gray-900">{{ ucfirst(str_replace('_', ' ', $group)) }} Settings</h5>
                            <p class="text-sm text-whatsapp-text">Configure {{ $group }} related settings</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($settings as $setting)
                            <div class="space-y-2">
                                <label for="setting_{{ $setting->key }}" class="block text-sm font-medium text-gray-700">
                                    {{ $setting->label }}
                                    @if(isset($setting->description) && !empty($setting->description))
                                        <i class="fas fa-info-circle text-gray-400 ml-1" title="{{ $setting->description }}"></i>
                                    @endif
                                </label>

                                @if($setting->type == 'boolean')
                                    <div class="flex items-center">
                                        <input type="checkbox"
                                               id="setting_{{ $setting->key }}"
                                               name="{{ $setting->key }}"
                                               value="1"
                                               {{ $setting->typed_value ? 'checked' : '' }}
                                               class="mr-3">
                                        <span class="text-sm text-gray-600">{{ $setting->label }}</span>
                                    </div>
                                @elseif($setting->options)
                                    <select id="setting_{{ $setting->key }}"
                                            name="{{ $setting->key }}"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-primary focus:border-whatsapp-primary transition-colors bg-white">
                                        @foreach($setting->options as $value => $label)
                                            <option value="{{ $value }}" {{ $setting->typed_value == $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                @elseif($setting->type == 'integer')
                                    <input type="number"
                                           id="setting_{{ $setting->key }}"
                                           name="{{ $setting->key }}"
                                           value="{{ $setting->typed_value }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-primary focus:border-whatsapp-primary transition-colors">
                                @elseif($setting->type == 'text' || $setting->key == 'app_description')
                                    <textarea id="setting_{{ $setting->key }}"
                                              name="{{ $setting->key }}"
                                              rows="3"
                                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-primary focus:border-whatsapp-primary transition-colors">{{ $setting->value }}</textarea>
                                @else
                                    <input type="{{ $setting->type == 'email' ? 'email' : 'text' }}"
                                           id="setting_{{ $setting->key }}"
                                           name="{{ $setting->key }}"
                                           value="{{ $setting->value }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-primary focus:border-whatsapp-primary transition-colors">
                                @endif

                                @if(isset($setting->description) && !empty($setting->description))
                                    <p class="text-xs text-gray-500">{{ $setting->description }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <!-- System Information -->
        <div class="border border-gray-200 rounded-lg p-4">
            <div class="flex items-center space-x-3 mb-4">
                <div class="bg-blue-100 p-2 rounded-lg">
                    <i class="fas fa-server text-blue-600"></i>
                </div>
                <div>
                    <h5 class="text-lg font-medium text-gray-900">System Information</h5>
                    <p class="text-sm text-whatsapp-text">Read-only system information values</p>
                </div>
            </div>

            <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-lg mb-4 flex items-center">
                <i class="fas fa-info-circle mr-2"></i>
                <strong>Info:</strong> These are read-only system information values.
            </div>

            <div class="space-y-3">
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="font-medium text-gray-700">Laravel Version:</span>
                    <span class="text-gray-600">{{ app()->version() }}</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="font-medium text-gray-700">PHP Version:</span>
                    <span class="text-gray-600">{{ phpversion() }}</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="font-medium text-gray-700">Environment:</span>
                    <span class="text-gray-600">{{ app()->environment() }}</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="font-medium text-gray-700">Debug Mode:</span>
                    <span class="{{ config('app.debug') ? 'text-red-600' : 'text-green-600' }} font-medium">
                        {{ config('app.debug') ? 'Enabled' : 'Disabled' }}
                    </span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="font-medium text-gray-700">Cache Driver:</span>
                    <span class="text-gray-600">{{ config('cache.default') }}</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="font-medium text-gray-700">Session Driver:</span>
                    <span class="text-gray-600">{{ config('session.driver') }}</span>
                </div>
                <div class="flex justify-between items-center py-2">
                    <span class="font-medium text-gray-700">Database:</span>
                    <span class="text-gray-600">{{ config('database.default') }}</span>
                </div>
            </div>
        </div>
        </div>
    </div>
</form>
@endsection


@push('scripts')
<script>
function saveSettings() {
    if (confirm('Save all settings?')) {
        document.getElementById('settingsForm').submit();
    }
}

function clearCache() {
    if (confirm('Are you sure you want to clear all cache?')) {
        // Create a form and submit it
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route('admin.settings.clear-cache') }}';
        form.style.display = 'none';

        // Add CSRF token
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        form.appendChild(csrfToken);

        document.body.appendChild(form);
        form.submit();
    }
}

function optimizeSystem() {
    if (confirm('Are you sure you want to optimize the system?')) {
        // Create a form and submit it
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route('admin.settings.optimize') }}';
        form.style.display = 'none';

        // Add CSRF token
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        form.appendChild(csrfToken);

        document.body.appendChild(form);
        form.submit();
    }
}

function backupDatabase() {
    if (confirm('Are you sure you want to create a database backup?')) {
        // Create a form and submit it
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route('admin.settings.backup') }}';
        form.style.display = 'none';

        // Add CSRF token
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        form.appendChild(csrfToken);

        document.body.appendChild(form);
        form.submit();
    }
}

function testEmail() {
    if (confirm('Send a test email to the administrator email?')) {
        // Create a form and submit it
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route('admin.settings.test-email') }}';
        form.style.display = 'none';

        // Add CSRF token
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        form.appendChild(csrfToken);

        document.body.appendChild(form);
        form.submit();
    }
}

function exportSettings() {
    window.location.href = '{{ route('admin.settings.export') }}';
}

function importSettings(input) {
    if (input.files && input.files[0]) {
        if (confirm('Are you sure you want to import these settings? This will overwrite current settings.')) {
            // Create a form and submit it
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route('admin.settings.import') }}';
            form.enctype = 'multipart/form-data';
            form.style.display = 'none';

            // Add CSRF token
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            form.appendChild(csrfToken);

            // Add the file
            const fileInput = document.createElement('input');
            fileInput.type = 'file';
            fileInput.name = 'settings_file';
            fileInput.files = input.files;
            form.appendChild(fileInput);

            document.body.appendChild(form);
            form.submit();
        }
    }
}

function resetSettings() {
    if (confirm('Are you sure you want to reset all settings to defaults? This action cannot be undone.')) {
        // Reset form to defaults
        document.getElementById('settingsForm').reset();
        alert('Settings reset to defaults successfully!');
    }
}

// Preview logo before upload
document.getElementById('logo').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('logoPreview');
            preview.innerHTML = `<img src="${e.target.result}" alt="Logo Preview" style="max-width: 100%; max-height: 100%; object-fit: contain;">`;
        };
        reader.readAsDataURL(file);
    }
});

// Form validation and interactions
document.addEventListener('DOMContentLoaded', function() {
    // Add real-time form validation
    const form = document.getElementById('settingsForm');
    form.addEventListener('input', function(e) {
        if (e.target.type === 'email') {
            e.target.classList.toggle('is-invalid', !e.target.validity.valid);
            e.target.classList.toggle('is-valid', e.target.validity.valid);
        }
        if (e.target.type === 'url') {
            e.target.classList.toggle('is-invalid', !e.target.validity.valid);
            e.target.classList.toggle('is-valid', e.target.validity.valid);
        }
    });
    
    // Enable/disable related settings
    document.getElementById('enable_file_upload').addEventListener('change', function() {
        const maxFileSize = document.getElementById('max_file_size');
        const allowedTypes = document.getElementById('allowed_file_types');
        maxFileSize.disabled = !this.checked;
        allowedTypes.disabled = !this.checked;
    });
    
    // Show warning for dangerous settings
    document.getElementById('maintenance_mode').addEventListener('change', function() {
        if (this.checked) {
            alert('Warning: Enabling maintenance mode will make your application inaccessible to users!');
        }
    });
    
    document.getElementById('debug_mode').addEventListener('change', function() {
        if (this.checked) {
            alert('Warning: Debug mode should only be enabled in development environments!');
        }
    });
});
</script>
@endpush 