@extends('layouts.admin')

@section('title', 'Create Chat')
@section('page-title', 'Create New Chat')

@section('toolbar-buttons')
    <a href="{{ route('admin.chats.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back to Chats
    </a>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-chat-dots"></i> Create New Chat
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.chats.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="type" class="form-label">Chat Type *</label>
                                <select class="form-select @error('type') is-invalid @enderror" 
                                        id="type" name="type" required onchange="toggleGroupFields()">
                                    <option value="">Select Type</option>
                                    <option value="private" {{ old('type') === 'private' ? 'selected' : '' }}>Private Chat</option>
                                    <option value="group" {{ old('type') === 'group' ? 'selected' : '' }}>Group Chat</option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6" id="name-field" style="display: none;">
                            <div class="mb-3">
                                <label for="name" class="form-label">Group Name</label>
                                <input type="text" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       id="name" 
                                       name="name" 
                                       value="{{ old('name') }}"
                                       placeholder="Enter group name">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row" id="group-fields" style="display: none;">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" 
                                          name="description" 
                                          rows="3"
                                          placeholder="Enter group description">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="max_participants" class="form-label">Max Participants</label>
                                <input type="number" 
                                       class="form-control @error('max_participants') is-invalid @enderror" 
                                       id="max_participants" 
                                       name="max_participants" 
                                       value="{{ old('max_participants', 100) }}"
                                       min="2" max="500">
                                @error('max_participants')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Between 2 and 500 participants</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="created_by" class="form-label">Creator *</label>
                                <select class="form-select @error('created_by') is-invalid @enderror" 
                                        id="created_by" name="created_by" required>
                                    <option value="">Select Creator</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ old('created_by') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }} ({{ $user->email }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('created_by')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="avatar" class="form-label">Avatar</label>
                                <input type="file" 
                                       class="form-control @error('avatar') is-invalid @enderror" 
                                       id="avatar" 
                                       name="avatar" 
                                       accept="image/*">
                                @error('avatar')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Maximum 2MB (JPEG, PNG)</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="participants" class="form-label">Initial Participants</label>
                        <select class="form-select @error('participants') is-invalid @enderror" 
                                id="participants" name="participants[]" multiple>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ in_array($user->id, old('participants', [])) ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>
                        @error('participants')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Hold Ctrl/Cmd to select multiple users</small>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" 
                                       {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Active Chat
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.chats.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Create Chat
                        </button>
                    </div>
                </form>
            </div>
        </div>
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

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleGroupFields();
});
</script>
@endpush 