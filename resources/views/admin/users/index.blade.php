@extends('layouts.admin')

@section('title', 'Users Management')
@section('page-title', 'Users Management')

@section('toolbar-buttons')
    <a href="{{ route('admin.users.create') }}"
       class="bg-whatsapp-primary hover:bg-whatsapp-dark text-white px-4 py-2 rounded-lg transition-colors duration-200 inline-flex items-center font-medium">
        <i class="fas fa-plus mr-2"></i> Add New User
    </a>
    <a href="{{ route('admin.users.export', request()->query()) }}"
       class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors duration-200 inline-flex items-center font-medium ml-3">
        <i class="fas fa-download mr-2"></i> Export CSV
    </a>
@endsection

@section('content')
    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-lg whatsapp-shadow p-6 mb-6">
        <div class="flex items-center space-x-3 mb-6">
            <div class="bg-whatsapp-light p-2 rounded-lg">
                <i class="fas fa-filter text-whatsapp-primary"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900">Filter & Search Users</h3>
        </div>

        <form method="GET" action="{{ route('admin.users.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <div>
                <label for="search" class="block text-sm font-medium text-whatsapp-text mb-2">Search</label>
                <input type="text"
                       id="search"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Name, email, or phone..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-primary focus:border-whatsapp-primary transition-colors">
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-whatsapp-text mb-2">Status</label>
                <select id="status"
                        name="status"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-primary focus:border-whatsapp-primary transition-colors bg-white">
                    <option value="">All Users</option>
                    <option value="online" {{ request('status') === 'online' ? 'selected' : '' }}>Online</option>
                    <option value="offline" {{ request('status') === 'offline' ? 'selected' : '' }}>Offline</option>
                    <option value="blocked" {{ request('status') === 'blocked' ? 'selected' : '' }}>Blocked</option>
                </select>
            </div>

            <div>
                <label for="sort_by" class="block text-sm font-medium text-whatsapp-text mb-2">Sort By</label>
                <select id="sort_by"
                        name="sort_by"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-primary focus:border-whatsapp-primary transition-colors bg-white">
                    <option value="created_at" {{ request('sort_by') === 'created_at' ? 'selected' : '' }}>Join Date</option>
                    <option value="name" {{ request('sort_by') === 'name' ? 'selected' : '' }}>Name</option>
                    <option value="last_seen_at" {{ request('sort_by') === 'last_seen_at' ? 'selected' : '' }}>Last Seen</option>
                </select>
            </div>

            <div>
                <label for="sort_order" class="block text-sm font-medium text-whatsapp-text mb-2">Order</label>
                <select id="sort_order"
                        name="sort_order"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-whatsapp-primary focus:border-whatsapp-primary transition-colors bg-white">
                    <option value="desc" {{ request('sort_order') === 'desc' ? 'selected' : '' }}>Descending</option>
                    <option value="asc" {{ request('sort_order') === 'asc' ? 'selected' : '' }}>Ascending</option>
                </select>
            </div>

            <div class="flex items-end">
                <button type="submit"
                        class="w-full bg-whatsapp-primary hover:bg-whatsapp-dark text-white px-4 py-2 rounded-lg transition-colors duration-200 flex items-center justify-center font-medium">
                    <i class="fas fa-search mr-2"></i>
                    Search
                </button>
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-xl shadow-lg whatsapp-shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    <div class="bg-whatsapp-light p-2 rounded-lg">
                        <i class="fas fa-users text-whatsapp-primary"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Users Directory</h3>
                        <p class="text-sm text-whatsapp-text">Total: {{ number_format($users->total()) }} users</p>
                    </div>
                </div>
                <div class="text-sm text-whatsapp-text">
                    Showing {{ $users->firstItem() }}-{{ $users->lastItem() }} of {{ $users->total() }}
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-whatsapp-light">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-whatsapp-primary uppercase tracking-wider">User</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-whatsapp-primary uppercase tracking-wider">Contact Info</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-whatsapp-primary uppercase tracking-wider">Stats</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-whatsapp-primary uppercase tracking-wider">Last Seen</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-whatsapp-primary uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-whatsapp-primary uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($users as $user)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-4">
                                @if($user->avatar_url)
                                    <img src="{{ $user->avatar_url }}" class="w-12 h-12 rounded-full" alt="{{ $user->name }}">
                                @else
                                    <div class="w-12 h-12 bg-whatsapp-light rounded-full flex items-center justify-center">
                                        <span class="text-whatsapp-primary font-semibold">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                    </div>
                                @endif
                                <div>
                                    <div class="font-medium text-gray-900">{{ $user->name }}</div>
                                    <div class="text-sm text-whatsapp-text">ID: {{ $user->id }}</div>
                                    <div class="text-sm text-whatsapp-text">Joined: {{ $user->created_at->format('M d, Y') }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="space-y-1">
                                <div class="flex items-center text-sm text-gray-600">
                                    <i class="fas fa-envelope text-whatsapp-primary mr-2"></i>
                                    {{ $user->email }}
                                </div>
                                @if($user->phone_number)
                                    <div class="flex items-center text-sm text-gray-600">
                                        <i class="fas fa-phone text-whatsapp-primary mr-2"></i>
                                        {{ $user->country_code }}{{ $user->phone_number }}
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="space-y-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-whatsapp-light text-whatsapp-primary">
                                    <i class="fas fa-comments mr-1"></i>
                                    {{ $user->chats_count }} chats
                                </span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <i class="fas fa-envelope mr-1"></i>
                                    {{ $user->messages_count }} messages
                                </span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                    <i class="fas fa-phone mr-1"></i>
                                    {{ $user->sent_calls_count + $user->received_calls_count }} calls
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            @if($user->last_seen_at)
                                {{ $user->last_seen_at->diffForHumans() }}
                            @else
                                <span class="text-gray-400">Never</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($user->deleted_at)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <i class="fas fa-ban mr-1"></i>
                                    Blocked
                                </span>
                            @elseif($user->is_online)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-circle text-green-500 mr-1"></i>
                                    Online
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    <i class="fas fa-circle text-gray-400 mr-1"></i>
                                    Offline
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('admin.users.show', $user) }}"
                                   class="bg-blue-100 hover:bg-blue-200 text-blue-600 p-2 rounded-lg transition-colors"
                                   title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.users.edit', $user) }}"
                                   class="bg-yellow-100 hover:bg-yellow-200 text-yellow-600 p-2 rounded-lg transition-colors"
                                   title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button"
                                        class="p-2 rounded-lg transition-colors {{ $user->deleted_at ? 'bg-green-100 hover:bg-green-200 text-green-600' : 'bg-red-100 hover:bg-red-200 text-red-600' }}"
                                        onclick="toggleBlock({{ $user->id }}, '{{ $user->deleted_at ? 'unblock' : 'block' }}')"
                                        title="{{ $user->deleted_at ? 'Unblock' : 'Block' }}">
                                    <i class="fas fa-{{ $user->deleted_at ? 'unlock' : 'lock' }}"></i>
                                </button>
                                <div class="relative">
                                    <button class="bg-gray-100 hover:bg-gray-200 text-gray-600 p-2 rounded-lg transition-colors"
                                            onclick="toggleDropdown({{ $user->id }})"
                                            title="More Actions">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <div id="dropdown-{{ $user->id }}" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg z-10 hidden">
                                        <div class="py-1">
                                            <button class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                                    onclick="resetPassword({{ $user->id }})">
                                                <i class="fas fa-key mr-2"></i> Reset Password
                                            </button>
                                            <hr class="my-1">
                                            <button class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50"
                                                    onclick="deleteUser({{ $user->id }})">
                                                <i class="fas fa-trash mr-2"></i> Delete User
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <div class="bg-whatsapp-light p-4 rounded-full mb-4">
                                    <i class="fas fa-users text-whatsapp-primary text-4xl"></i>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">No Users Found</h3>
                                <p class="text-whatsapp-text mb-4">No users match your current search criteria.</p>
                                <a href="{{ route('admin.users.create') }}"
                                   class="bg-whatsapp-primary hover:bg-whatsapp-dark text-white px-4 py-2 rounded-lg transition-colors inline-flex items-center font-medium">
                                    <i class="fas fa-plus mr-2"></i> Add First User
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
            {{ $users->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
// Close dropdowns when clicking outside
document.addEventListener('click', function(e) {
    const dropdowns = document.querySelectorAll('[id^="dropdown-"]');
    dropdowns.forEach(dropdown => {
        if (!dropdown.contains(e.target) && !e.target.closest('[onclick*="toggleDropdown"]')) {
            dropdown.classList.add('hidden');
        }
    });
});

function toggleDropdown(userId) {
    const dropdown = document.getElementById(`dropdown-${userId}`);
    const allDropdowns = document.querySelectorAll('[id^="dropdown-"]');

    // Close all other dropdowns
    allDropdowns.forEach(d => {
        if (d.id !== `dropdown-${userId}`) {
            d.classList.add('hidden');
        }
    });

    // Toggle current dropdown
    dropdown.classList.toggle('hidden');
}

function toggleBlock(userId, action) {
    if (confirm(`Are you sure you want to ${action} this user?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/users/${userId}/toggle-block`;

        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';

        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'PATCH';

        form.appendChild(csrfToken);
        form.appendChild(methodField);
        document.body.appendChild(form);
        form.submit();
    }
}

function resetPassword(userId) {
    if (confirm('Are you sure you want to reset this user\'s password?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/users/${userId}/reset-password`;

        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';

        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'POST';

        form.appendChild(csrfToken);
        form.appendChild(methodField);
        document.body.appendChild(form);
        form.submit();
    }
}

function deleteUser(userId) {
    if (confirm('Are you sure you want to permanently delete this user? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/users/${userId}`;

        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';

        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';

        form.appendChild(csrfToken);
        form.appendChild(methodField);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endpush