@extends('layouts.admin')

@section('title', 'Settings')
@section('page-title', 'CMS Settings')

@section('content')
    <!-- Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div class="flex-1">
            <h2 class="text-lg font-semibold text-gray-900">Content Management System</h2>
            <p class="text-sm text-gray-500">Manage all application settings in real-time</p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="openAddSettingModal()" 
                    class="flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-200 rounded-xl hover:border-green-600 hover:text-green-700 transition-all text-sm font-medium text-gray-700">
                <i class="ph ph-plus text-lg"></i>
                <span class="hidden sm:inline">Add Setting</span>
            </button>
            <button onclick="clearCache()" 
                    class="flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-200 rounded-xl hover:border-green-600 hover:text-green-700 transition-all text-sm font-medium text-gray-700">
                <i class="ph ph-arrows-clockwise text-lg"></i>
                <span class="hidden sm:inline">Clear Cache</span>
            </button>
            <button onclick="refreshSettings()" 
                    class="flex items-center gap-2 px-4 py-2.5 bg-green-700 hover:bg-green-800 text-white rounded-xl transition-all text-sm font-medium shadow-lg shadow-green-700/30">
                <i class="ph ph-arrow-clockwise text-lg"></i>
                <span class="hidden sm:inline">Refresh</span>
            </button>
        </div>
    </div>

    <!-- Status Messages -->
    <div id="statusMessage" class="hidden mb-6"></div>

    <!-- Settings Groups Tabs -->
    <div class="bg-white border border-gray-100 rounded-2xl shadow-sm mb-6">
        <div class="border-b border-gray-100 px-4">
            <nav class="flex gap-1 overflow-x-auto" id="groupTabs">
                <button onclick="filterByGroup('all')" data-group="all"
                        class="group-tab active px-4 py-3 text-sm font-medium text-gray-600 hover:text-green-700 border-b-2 border-transparent hover:border-green-700 transition-all whitespace-nowrap">
                    All Settings
                </button>
            </nav>
        </div>
    </div>

    <!-- Logo Upload Section -->
    <div id="profile" class="bg-white border border-gray-100 rounded-2xl shadow-sm mb-6 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center gap-3">
            <div class="p-2 bg-white border border-gray-200 rounded-lg shadow-sm text-green-600">
                <i class="ph ph-image text-lg"></i>
            </div>
            <div>
                <h3 class="font-semibold text-gray-900">Application Logo</h3>
                <p class="text-xs text-gray-500">Upload your application logo (recommended: 512x512px)</p>
            </div>
        </div>
        <div class="p-6">
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6">
                <!-- Logo Preview -->
                <div class="relative">
                    <div id="logoPreview" class="w-24 h-24 rounded-2xl border-2 border-dashed border-gray-300 flex items-center justify-center bg-gray-50 overflow-hidden">
                        @if(isset($appSettings['logo_url']) && $appSettings['logo_url'])
                            <img src="{{ $appSettings['logo_url'] }}" alt="Logo" class="w-full h-full object-cover">
                        @else
                            <div class="text-center">
                                <i class="ph ph-image text-2xl text-gray-400"></i>
                                <p class="text-xs text-gray-400 mt-1">No logo</p>
                            </div>
                        @endif
                    </div>
                </div>
                
                <!-- Upload Controls -->
                <div class="flex-1">
                    <div class="flex flex-wrap gap-3">
                        <label class="flex items-center gap-2 px-4 py-2.5 bg-green-700 hover:bg-green-800 text-white rounded-xl transition-all text-sm font-medium cursor-pointer shadow-lg shadow-green-700/30">
                            <i class="ph ph-upload-simple"></i>
                            Upload Logo
                            <input type="file" id="logoUpload" accept="image/*" class="hidden" onchange="uploadLogo(this)">
                        </label>
                        @if(isset($appSettings['logo_url']) && $appSettings['logo_url'])
                            <button type="button" onclick="removeLogo()" class="flex items-center gap-2 px-4 py-2.5 bg-white border border-red-200 text-red-600 hover:bg-red-50 rounded-xl transition-all text-sm font-medium">
                                <i class="ph ph-trash"></i>
                                Remove
                            </button>
                        @endif
                    </div>
                    <p class="text-xs text-gray-500 mt-3">Supported formats: JPG, PNG, GIF. Max size: 2MB</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Settings Container -->
    <div id="settingsContainer" class="space-y-6">
        <div class="flex items-center justify-center py-12">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-green-700"></div>
            <span class="ml-3 text-gray-500">Loading settings...</span>
        </div>
    </div>

    <!-- Add/Edit Setting Modal -->
    <div id="settingModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900" id="modalTitle">Add New Setting</h3>
                <button onclick="closeModal()" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                    <i class="ph ph-x text-gray-500"></i>
                </button>
            </div>
            <form id="settingForm" class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Key <span class="text-red-500">*</span></label>
                    <input type="text" name="key" id="settingKey" required
                           class="w-full px-3 py-2 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 text-sm"
                           placeholder="e.g., app_name">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Label <span class="text-red-500">*</span></label>
                    <input type="text" name="label" id="settingLabel" required
                           class="w-full px-3 py-2 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 text-sm"
                           placeholder="e.g., Application Name">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Type <span class="text-red-500">*</span></label>
                        <select name="type" id="settingType" required
                                class="w-full px-3 py-2 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 text-sm">
                            <option value="string">String</option>
                            <option value="text">Text (Long)</option>
                            <option value="boolean">Boolean</option>
                            <option value="integer">Integer</option>
                            <option value="float">Float</option>
                            <option value="json">JSON</option>
                            <option value="color">Color</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Group <span class="text-red-500">*</span></label>
                        <select name="group" id="settingGroup" required
                                class="w-full px-3 py-2 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 text-sm">
                            <option value="general">General</option>
                            <option value="system">System</option>
                            <option value="file">File</option>
                            <option value="chat">Chat</option>
                            <option value="user">User</option>
                            <option value="notification">Notification</option>
                            <option value="landing">Landing Page</option>
                            <option value="colors">UI Colors</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Value</label>
                    <input type="text" name="value" id="settingValue"
                           class="w-full px-3 py-2 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 text-sm"
                           placeholder="Setting value">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea name="description" id="settingDescription" rows="2"
                              class="w-full px-3 py-2 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 text-sm"
                              placeholder="Brief description of this setting"></textarea>
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_public" id="settingPublic" 
                           class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500">
                    <label for="settingPublic" class="text-sm text-gray-700">Public (accessible via API)</label>
                </div>
                <div class="flex gap-3 pt-4">
                    <button type="button" onclick="closeModal()" 
                            class="flex-1 px-4 py-2.5 border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 transition-colors font-medium text-sm">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="flex-1 px-4 py-2.5 bg-green-700 hover:bg-green-800 text-white rounded-xl transition-colors font-medium text-sm">
                        Save Setting
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- System Information -->
    <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden mt-8">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center gap-3">
            <div class="p-2 bg-white border border-gray-200 rounded-lg shadow-sm text-blue-600">
                <i class="ph ph-info text-lg"></i>
            </div>
            <div>
                <h3 class="font-semibold text-gray-900">System Information</h3>
                <p class="text-xs text-gray-500">Read-only environment details</p>
            </div>
        </div>
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="flex justify-between items-center py-2 px-3 bg-gray-50 rounded-lg">
                <span class="text-sm text-gray-600">Laravel</span>
                <span class="text-sm font-mono text-gray-900">{{ app()->version() }}</span>
            </div>
            <div class="flex justify-between items-center py-2 px-3 bg-gray-50 rounded-lg">
                <span class="text-sm text-gray-600">PHP</span>
                <span class="text-sm font-mono text-gray-900">{{ phpversion() }}</span>
            </div>
            <div class="flex justify-between items-center py-2 px-3 bg-gray-50 rounded-lg">
                <span class="text-sm text-gray-600">Environment</span>
                <span class="text-sm font-mono text-gray-900">{{ app()->environment() }}</span>
            </div>
            <div class="flex justify-between items-center py-2 px-3 bg-gray-50 rounded-lg">
                <span class="text-sm text-gray-600">Debug</span>
                <span class="text-sm font-medium px-2 py-0.5 rounded {{ config('app.debug') ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                    {{ config('app.debug') ? 'ON' : 'OFF' }}
                </span>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
let allSettings = {};
let currentGroup = 'all';

const groupIcons = {
    general: 'ph-gear',
    system: 'ph-hard-drives',
    file: 'ph-file-cloud',
    chat: 'ph-chat-circle-text',
    user: 'ph-users',
    notification: 'ph-bell'
};

const groupColors = {
    general: 'blue',
    system: 'gray',
    file: 'orange',
    chat: 'green',
    user: 'purple',
    notification: 'red'
};

document.addEventListener('DOMContentLoaded', function() {
    loadSettings();
    
    document.getElementById('settingForm').addEventListener('submit', function(e) {
        e.preventDefault();
        saveSetting();
    });
});

async function loadSettings() {
    try {
        const response = await fetch('{{ route("admin.settings.api.all") }}', {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            allSettings = data.data;
            renderGroupTabs();
            renderSettings();
        } else {
            showStatus('error', 'Failed to load settings');
        }
    } catch (error) {
        console.error('Error loading settings:', error);
        showStatus('error', 'Failed to load settings: ' + error.message);
    }
}

function renderGroupTabs() {
    const tabsContainer = document.getElementById('groupTabs');
    const groups = Object.keys(allSettings);
    
    let tabsHtml = `
        <button onclick="filterByGroup('all')" data-group="all"
                class="group-tab ${currentGroup === 'all' ? 'active' : ''} px-4 py-3 text-sm font-medium text-gray-600 hover:text-green-700 border-b-2 ${currentGroup === 'all' ? 'border-green-700 text-green-700' : 'border-transparent'} transition-all whitespace-nowrap">
            All (${Object.values(allSettings).flat().length})
        </button>
    `;
    
    groups.forEach(group => {
        const count = allSettings[group].length;
        const isActive = currentGroup === group;
        tabsHtml += `
            <button onclick="filterByGroup('${group}')" data-group="${group}"
                    class="group-tab ${isActive ? 'active' : ''} px-4 py-3 text-sm font-medium text-gray-600 hover:text-green-700 border-b-2 ${isActive ? 'border-green-700 text-green-700' : 'border-transparent'} transition-all whitespace-nowrap flex items-center gap-2">
                <i class="ph ${groupIcons[group] || 'ph-gear'}"></i>
                ${group.charAt(0).toUpperCase() + group.slice(1)} (${count})
            </button>
        `;
    });
    
    tabsContainer.innerHTML = tabsHtml;
}

function filterByGroup(group) {
    currentGroup = group;
    renderGroupTabs();
    renderSettings();
}

function renderSettings() {
    const container = document.getElementById('settingsContainer');
    let html = '';
    
    const groupsToRender = currentGroup === 'all' ? Object.keys(allSettings) : [currentGroup];
    
    groupsToRender.forEach(group => {
        if (!allSettings[group]) return;
        
        const color = groupColors[group] || 'blue';
        const icon = groupIcons[group] || 'ph-gear';
        
        html += `
            <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden" data-group="${group}">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-white border border-gray-200 rounded-lg shadow-sm text-${color}-600">
                            <i class="ph ${icon} text-lg"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900">${group.charAt(0).toUpperCase() + group.slice(1)} Settings</h3>
                            <p class="text-xs text-gray-500">${allSettings[group].length} settings</p>
                        </div>
                    </div>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
        `;
        
        allSettings[group].forEach(setting => {
            html += renderSettingField(setting);
        });
        
        html += `
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html || '<div class="text-center py-12 text-gray-500">No settings found</div>';
}

function renderSettingField(setting) {
    let inputHtml = '';
    const value = setting.value || '';
    
    if (setting.type === 'boolean') {
        const isChecked = setting.typed_value ? 'checked' : '';
        inputHtml = `
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" ${isChecked} onchange="updateSetting('${setting.key}', this.checked)"
                       class="sr-only peer">
                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-700"></div>
                <span class="ml-3 text-sm font-medium text-gray-600">${setting.typed_value ? 'Enabled' : 'Disabled'}</span>
            </label>
        `;
    } else if (setting.options && Object.keys(setting.options).length > 0) {
        let optionsHtml = '';
        for (const [optValue, optLabel] of Object.entries(setting.options)) {
            const selected = value == optValue ? 'selected' : '';
            optionsHtml += `<option value="${optValue}" ${selected}>${optLabel}</option>`;
        }
        inputHtml = `
            <select onchange="updateSetting('${setting.key}', this.value)"
                    class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 text-sm">
                ${optionsHtml}
            </select>
        `;
    } else if (setting.type === 'text' || (setting.key && setting.key.includes('description'))) {
        inputHtml = `
            <textarea onchange="updateSetting('${setting.key}', this.value)" rows="3"
                      class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 text-sm">${escapeHtml(value)}</textarea>
        `;
    } else if (setting.type === 'integer') {
        inputHtml = `
            <input type="number" value="${escapeHtml(value)}" onchange="updateSetting('${setting.key}', this.value)"
                   class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 text-sm">
        `;
    } else if (setting.type === 'color') {
        inputHtml = `
            <div class="flex items-center gap-3">
                <input type="color" value="${escapeHtml(value)}" onchange="updateSetting('${setting.key}', this.value)"
                       class="h-10 w-20 border border-gray-200 rounded-lg cursor-pointer">
                <input type="text" value="${escapeHtml(value)}" onchange="updateSetting('${setting.key}', this.value)"
                       class="flex-1 px-3 py-2 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 text-sm font-mono"
                       placeholder="#000000">
            </div>
        `;
    } else {
        const inputType = setting.key && setting.key.includes('email') ? 'email' : 
                         setting.key && setting.key.includes('url') ? 'url' : 'text';
        inputHtml = `
            <input type="${inputType}" value="${escapeHtml(value)}" onchange="updateSetting('${setting.key}', this.value)"
                   class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 text-sm">
        `;
    }
    
    return `
        <div class="space-y-2" data-setting-key="${setting.key}">
            <div class="flex items-center justify-between">
                <label class="block text-sm font-medium text-gray-700 flex items-center gap-2">
                    ${setting.label || setting.key}
                    ${setting.description ? `
                        <div class="group relative">
                            <i class="ph ph-info text-gray-400 hover:text-gray-600 cursor-help"></i>
                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-48 p-2 bg-gray-900 text-white text-xs rounded shadow-lg opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-10">
                                ${escapeHtml(setting.description)}
                            </div>
                        </div>
                    ` : ''}
                </label>
                <button onclick="deleteSetting('${setting.key}')" class="p-1 text-gray-400 hover:text-red-600 transition-colors" title="Delete">
                    <i class="ph ph-trash text-sm"></i>
                </button>
            </div>
            ${inputHtml}
            <div class="flex items-center justify-between">
                <span class="text-xs text-gray-400 font-mono">${setting.key}</span>
                <span class="update-indicator text-xs text-green-600 hidden"><i class="ph ph-check"></i> Saved</span>
            </div>
        </div>
    `;
}

async function updateSetting(key, value) {
    const settingEl = document.querySelector(`[data-setting-key="${key}"]`);
    const indicator = settingEl?.querySelector('.update-indicator');
    
    try {
        const response = await fetch('{{ route("admin.settings.api.update-single") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ key, value })
        });
        
        const data = await response.json();
        
        if (data.success) {
            if (indicator) {
                indicator.classList.remove('hidden');
                setTimeout(() => indicator.classList.add('hidden'), 2000);
            }
            
            // Update local data
            for (const group in allSettings) {
                const idx = allSettings[group].findIndex(s => s.key === key);
                if (idx !== -1) {
                    allSettings[group][idx].value = data.data.value;
                    allSettings[group][idx].typed_value = data.data.typed_value;
                    break;
                }
            }
        } else {
            showStatus('error', data.message || 'Failed to update setting');
        }
    } catch (error) {
        console.error('Error updating setting:', error);
        showStatus('error', 'Failed to update setting');
    }
}

async function deleteSetting(key) {
    if (!confirm(`Are you sure you want to delete the setting "${key}"?`)) return;
    
    try {
        const response = await fetch('{{ route("admin.settings.api.delete") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ key })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showStatus('success', 'Setting deleted successfully');
            loadSettings();
        } else {
            showStatus('error', data.message || 'Failed to delete setting');
        }
    } catch (error) {
        showStatus('error', 'Failed to delete setting');
    }
}

function openAddSettingModal() {
    document.getElementById('modalTitle').textContent = 'Add New Setting';
    document.getElementById('settingForm').reset();
    document.getElementById('settingKey').disabled = false;
    document.getElementById('settingModal').classList.remove('hidden');
    document.getElementById('settingModal').classList.add('flex');
}

function closeModal() {
    document.getElementById('settingModal').classList.add('hidden');
    document.getElementById('settingModal').classList.remove('flex');
}

async function saveSetting() {
    const form = document.getElementById('settingForm');
    const formData = new FormData(form);
    
    const data = {
        key: formData.get('key'),
        label: formData.get('label'),
        type: formData.get('type'),
        group: formData.get('group'),
        value: formData.get('value') || '',
        description: formData.get('description') || '',
        is_public: formData.get('is_public') === 'on'
    };
    
    try {
        const response = await fetch('{{ route("admin.settings.api.create") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showStatus('success', 'Setting created successfully');
            closeModal();
            loadSettings();
        } else {
            showStatus('error', result.message || 'Failed to create setting');
        }
    } catch (error) {
        showStatus('error', 'Failed to create setting');
    }
}

function refreshSettings() {
    loadSettings();
    showStatus('success', 'Settings refreshed');
}

async function clearCache() {
    if (!confirm('Clear all application cache?')) return;
    
    try {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("admin.settings.clear-cache") }}';
        
        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = document.querySelector('meta[name="csrf-token"]').content;
        form.appendChild(csrf);
        
        document.body.appendChild(form);
        form.submit();
    } catch (error) {
        showStatus('error', 'Failed to clear cache');
    }
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

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Logo Upload Functions
async function uploadLogo(input) {
    if (!input.files || !input.files[0]) return;
    
    const file = input.files[0];
    
    // Validate file type
    if (!file.type.startsWith('image/')) {
        showStatus('error', 'Please select an image file');
        return;
    }
    
    // Validate file size (2MB max)
    if (file.size > 2 * 1024 * 1024) {
        showStatus('error', 'File size must be less than 2MB');
        return;
    }
    
    const formData = new FormData();
    formData.append('logo', file);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
    
    try {
        const response = await fetch('{{ route("admin.settings.upload-logo") }}', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Update preview
            const preview = document.getElementById('logoPreview');
            preview.innerHTML = `<img src="${data.url}" alt="Logo" class="w-full h-full object-cover">`;
            
            showStatus('success', 'Logo uploaded successfully');
            
            // Reload page after short delay to update all logo instances
            setTimeout(() => window.location.reload(), 1500);
        } else {
            showStatus('error', data.message || 'Failed to upload logo');
        }
    } catch (error) {
        console.error('Error uploading logo:', error);
        showStatus('error', 'Failed to upload logo');
    }
    
    // Reset input
    input.value = '';
}

async function removeLogo() {
    if (!confirm('Are you sure you want to remove the logo?')) return;
    
    try {
        const response = await fetch('{{ route("admin.settings.remove-logo") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Update preview
            const preview = document.getElementById('logoPreview');
            preview.innerHTML = `
                <div class="text-center">
                    <i class="ph ph-image text-2xl text-gray-400"></i>
                    <p class="text-xs text-gray-400 mt-1">No logo</p>
                </div>
            `;
            
            showStatus('success', 'Logo removed successfully');
            
            // Reload page after short delay
            setTimeout(() => window.location.reload(), 1500);
        } else {
            showStatus('error', data.message || 'Failed to remove logo');
        }
    } catch (error) {
        showStatus('error', 'Failed to remove logo');
    }
}
</script>
@endpush
