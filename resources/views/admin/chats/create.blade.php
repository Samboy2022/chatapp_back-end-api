@extends('layouts.admin')

@section('title', 'Create Chat')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight flex items-center gap-2">
                <i class="ph ph-chat-circle-dots text-green-700"></i>
                Create New Chat
            </h1>
            <p class="text-sm text-gray-500 mt-1">Configure and initialize a new private or group conversation</p>
        </div>
        <a href="{{ route('admin.chats.index') }}" class="inline-flex items-center gap-2 px-4 py-2 border border-gray-200 rounded-xl text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-50 transition duration-150">
            <i class="ph ph-arrow-left"></i>
            Back to Chats
        </a>
    </div>

    <!-- Main Card -->
    <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm overflow-hidden animate-fadeIn">
        <!-- Top accent line -->
        <div class="h-1.5 bg-gradient-to-r from-green-600 to-emerald-500"></div>

        <form action="{{ route('admin.chats.store') }}" method="POST" enctype="multipart/form-data" class="p-6 md:p-8 space-y-6">
            @csrf

            <!-- Form Section: Type selection -->
            <div class="bg-gray-50/50 rounded-xl p-4 border border-gray-100 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="type" class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2">Chat Type *</label>
                    <div class="relative">
                        <select class="w-full rounded-xl border border-gray-300 px-4 py-2.5 bg-white text-gray-700 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200/50 focus:outline-none transition duration-150 @error('type') border-red-500 ring ring-red-200/50 @enderror" 
                                id="type" name="type" required onchange="toggleGroupFields()">
                            <option value="">Select Type</option>
                            <option value="private" {{ old('type') === 'private' ? 'selected' : '' }}>Private Chat (1-on-1)</option>
                            <option value="group" {{ old('type') === 'group' ? 'selected' : '' }}>Group Chat</option>
                        </select>
                    </div>
                    @error('type')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div id="name-field" style="display: none;">
                    <label for="name" class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2">Group Name *</label>
                    <input type="text" 
                           class="w-full rounded-xl border border-gray-300 px-4 py-2.5 bg-white text-gray-700 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200/50 focus:outline-none transition duration-150 @error('name') border-red-500 ring ring-red-200/50 @enderror" 
                           id="name" 
                           name="name" 
                           value="{{ old('name') }}"
                           placeholder="Enter group name">
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Form Section: Group Specific Fields -->
            <div id="group-fields" style="display: none;" class="space-y-6 pt-2">
                <div class="border-t border-gray-100 my-4"></div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="description" class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2">Group Description</label>
                        <textarea class="w-full rounded-xl border border-gray-300 px-4 py-2.5 bg-white text-gray-700 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200/50 focus:outline-none transition duration-150 @error('description') border-red-500 ring ring-red-200/50 @enderror" 
                                  id="description" 
                                  name="description" 
                                  rows="3"
                                  placeholder="Enter group description">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="max_participants" class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2">Max Participants</label>
                        <input type="number" 
                               class="w-full rounded-xl border border-gray-300 px-4 py-2.5 bg-white text-gray-700 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200/50 focus:outline-none transition duration-150 @error('max_participants') border-red-500 ring ring-red-200/50 @enderror" 
                               id="max_participants" 
                               name="max_participants" 
                               value="{{ old('max_participants', 100) }}"
                               min="2" max="500">
                        <p class="text-xs text-gray-400 mt-1">Choose between 2 and 500 participants</p>
                        @error('max_participants')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Form Section: Creator & Avatar -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="created_by" class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2">Creator *</label>
                    <select class="w-full rounded-xl border border-gray-300 px-4 py-2.5 bg-white text-gray-700 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200/50 focus:outline-none transition duration-150 @error('created_by') border-red-500 ring ring-red-200/50 @enderror" 
                            id="created_by" name="created_by" required>
                        <option value="">Select Creator</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('created_by') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                        @endforeach
                    </select>
                    @error('created_by')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="avatar" class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2">Avatar Image</label>
                    <div class="relative flex items-center justify-center border-2 border-dashed border-gray-300 rounded-xl p-4 hover:bg-gray-50/50 hover:border-green-400 transition cursor-pointer">
                        <input type="file" 
                               class="absolute inset-0 opacity-0 cursor-pointer" 
                               id="avatar" 
                               name="avatar" 
                               accept="image/*">
                        <div class="text-center space-y-1 text-gray-500" id="avatar-preview-container">
                            <i class="ph ph-image text-2xl text-gray-400" id="avatar-icon"></i>
                            <div class="text-sm font-medium">Click or drag avatar file here</div>
                            <div class="text-xs">Max 2MB (JPEG, PNG)</div>
                        </div>
                    </div>
                    @error('avatar')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Form Section: Initial Participants -->
            <div>
                <label for="participants" class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2">Initial Participants</label>
                <select class="w-full rounded-xl border border-gray-300 px-4 py-2.5 bg-white text-gray-700 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200/50 focus:outline-none transition duration-150 min-h-[160px] @error('participants') border-red-500 ring ring-red-200/50 @enderror" 
                        id="participants" name="participants[]" multiple>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ in_array($user->id, old('participants', [])) ? 'selected' : '' }} class="p-2 rounded-lg hover:bg-green-50">
                            {{ $user->name }} ({{ $user->email }})
                        </option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-400 mt-1">Hold Ctrl/Cmd key to select multiple users</p>
                @error('participants')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Form Section: Toggle Status -->
            <div class="flex items-center space-x-3 bg-gray-50/50 rounded-xl p-4 border border-gray-100">
                <div class="relative flex items-start">
                    <div class="flex items-center h-5">
                        <input type="checkbox" id="is_active" name="is_active" value="1" 
                               {{ old('is_active', true) ? 'checked' : '' }}
                               class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500 focus:ring-offset-0 focus:outline-none transition duration-150">
                    </div>
                    <div class="ml-3 text-sm">
                        <label for="is_active" class="font-medium text-gray-700 select-none">Active Chat</label>
                        <p class="text-gray-500 text-xs">If unchecked, the chat room will be locked and hidden from non-admin participants.</p>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <a href="{{ route('admin.chats.index') }}" class="px-5 py-2.5 border border-gray-200 rounded-xl text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-50 transition duration-150">
                    Cancel
                </a>
                <button type="submit" class="px-5 py-2.5 bg-green-700 hover:bg-green-800 text-white rounded-xl text-sm font-medium shadow-sm hover:shadow transition duration-150 flex items-center gap-2">
                    <i class="ph ph-check-circle"></i>
                    Create Chat
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleGroupFields() {
    const type = document.getElementById('type').value;
    const nameField = document.getElementById('name-field');
    const groupFields = document.getElementById('group-fields');
    
    if (type === 'group') {
        nameField.style.display = 'block';
        groupFields.style.display = 'block';
        document.getElementById('name').required = true;
    } else {
        nameField.style.display = 'none';
        groupFields.style.display = 'none';
        document.getElementById('name').required = false;
    }
}

// Interactive Avatar Preview
document.addEventListener('DOMContentLoaded', function() {
    toggleGroupFields();
    
    const fileInput = document.getElementById('avatar');
    const container = document.getElementById('avatar-preview-container');
    
    if (fileInput && container) {
        fileInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                container.innerHTML = `
                    <i class="ph ph-file-image text-2xl text-green-600"></i>
                    <div class="text-sm font-medium text-gray-800 truncate max-w-xs">${file.name}</div>
                    <div class="text-xs text-gray-500">${(file.size / 1024 / 1024).toFixed(2)} MB</div>
                `;
            }
        });
    }
});
</script>
@endpush 

