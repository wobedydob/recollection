@extends('layouts.app')

@section('content')
<div class="admin-page admin-page-wide">
    <div class="admin-header">
        <div class="header-left">
            <a href="{{ route('admin.dashboard') }}" class="back-link">← {{ __('admin.dashboard') }}</a>
            <h1 class="admin-title">{{ __('admin.users') }}</h1>
        </div>
    </div>

    <div class="admin-card">
        <div class="filter-form">
            <div class="filter-row">
                <input
                    type="text"
                    id="search-input"
                    class="input"
                    placeholder="{{ __('admin.search_placeholder') }}"
                    value="{{ request('search') }}"
                />
                <div class="custom-select" id="role-select">
                    <button type="button" class="custom-select-trigger" onclick="toggleDropdown()">
                        <span class="custom-select-value">
                            @if(request('role') === 'admin')
                                Admin
                            @elseif(request('role') === 'user')
                                {{ __('admin.user') }}
                            @else
                                {{ __('admin.all_roles') }}
                            @endif
                        </span>
                        <span class="custom-select-arrow">▼</span>
                    </button>
                    <div class="custom-select-dropdown" id="role-dropdown">
                        <button type="button" class="custom-select-option {{ !request('role') ? 'active' : '' }}" onclick="selectRole('')">
                            {{ __('admin.all_roles') }}
                        </button>
                        <button type="button" class="custom-select-option {{ request('role') === 'user' ? 'active' : '' }}" onclick="selectRole('user')">
                            <span class="role-indicator user"></span>
                            {{ __('admin.user') }}
                        </button>
                        <button type="button" class="custom-select-option {{ request('role') === 'admin' ? 'active' : '' }}" onclick="selectRole('admin')">
                            <span class="role-indicator admin"></span>
                            Admin
                        </button>
                    </div>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="success-message">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="error-message">{{ $errors->first() }}</div>
        @endif

        <div class="admin-loader" id="admin-loader" style="display: flex;">
            <div class="loader"></div>
            <p class="loader-text">{{ __('common.loading') }}</p>
        </div>

        <div class="admin-content" id="admin-content" style="display: none;">
            <div class="users-list"></div>
            <div class="pagination-wrapper"></div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentRole = '{{ request('role') }}';
let currentSearch = '{{ request('search') }}';

// Make loadUsers globally available (only define once)
if (!window.loadUsers) {
    window.loadUsers = async function() {
        if (window.loadUsersRunning) {
            return;
        }
        window.loadUsersRunning = true;

    const loader = document.getElementById('admin-loader');
    const content = document.getElementById('admin-content');

    // Show loader
    loader.style.display = 'flex';
    content.style.display = 'none';

    // Build URL
    const params = new URLSearchParams();
    if (currentSearch) params.append('search', currentSearch);
    if (currentRole) params.append('role', currentRole);

    try {
        const res = await fetch(`/admin/users?${params.toString()}`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            credentials: 'include'
        });

        if (res.ok) {
            const data = await res.json();
            renderUsers(data.users);
        }
    } catch (e) {
        console.error('Failed to load users:', e);
    }

    // Hide loader, show content after minimum time for smooth transition
    setTimeout(() => {
        loader.style.display = 'none';
        content.style.display = 'block';
        content.classList.add('loaded');
        window.loadUsersRunning = false;
    }, 300);
    };
}

async function searchUsers() {
    const search = document.getElementById('search-input').value;
    currentSearch = search;

    // Update URL without reload
    const params = new URLSearchParams();
    if (search) params.append('search', search);
    if (currentRole) params.append('role', currentRole);
    const newUrl = params.toString() ? `/admin/users?${params.toString()}` : '/admin/users';
    history.pushState({}, '', newUrl);

    // Reload users with new filters
    await window.loadUsers();
}

function toggleDropdown() {
    document.getElementById('role-dropdown').classList.toggle('show');
}

function selectRole(role) {
    currentRole = role;
    const labels = { '': '{{ __('admin.all_roles') }}', 'user': '{{ __('admin.user') }}', 'admin': 'Admin' };
    document.querySelector('.custom-select-value').textContent = labels[role];
    document.getElementById('role-dropdown').classList.remove('show');

    // Update active state
    document.querySelectorAll('.custom-select-option').forEach(opt => opt.classList.remove('active'));
    event.target.closest('.custom-select-option').classList.add('active');

    // Search immediately on role change
    searchUsers();
}

// Close dropdown when clicking outside (only add once)
if (!window.usersDropdownListenerAdded) {
    window.usersDropdownListenerAdded = true;
    document.addEventListener('click', function(e) {
        const select = document.getElementById('role-select');
        if (select && !select.contains(e.target)) {
            const dropdown = document.getElementById('role-dropdown');
            if (dropdown) dropdown.classList.remove('show');
        }
    });
}

// Debounced search on input (only add once)
if (!window.usersSearchListenerAdded) {
    window.usersSearchListenerAdded = true;
    let searchTimeout = null;
    const searchInput = document.getElementById('search-input');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(searchUsers, 300);
        });
    }
}

function renderUsers(users) {
    const usersList = document.querySelector('.users-list');

    if (users.length === 0) {
        usersList.innerHTML = '<p class="empty-message">{{ __('admin.no_users') }}</p>';
        return;
    }

    usersList.innerHTML = users.map((user, index) => `
        <a href="${user.show_url}" class="user-row clickable" style="animation-delay: ${0.05 + (index * 0.03)}s">
            <div class="user-info">
                <span class="user-avatar">${user.name.charAt(0).toUpperCase()}</span>
                <div class="user-details">
                    <span class="user-name">${escapeHtml(user.name)}</span>
                    <span class="user-email">${escapeHtml(user.email)}</span>
                </div>
            </div>
            <div class="user-stats">
                <span class="stat" title="{{ __('admin.ideas') }}">✨ ${user.ideas_count}</span>
                <span class="stat" title="{{ __('admin.checklists') }}">📋 ${user.todo_lists_count}</span>
            </div>
            <div class="user-meta">
                <span class="user-role ${user.role}">${user.role}</span>
                <span class="user-date">${user.created_at}</span>
            </div>
        </a>
    `).join('');

    // Remove pagination for now (AJAX results)
    const pagination = document.querySelector('.pagination-wrapper');
    if (pagination) pagination.style.display = 'none';
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>

<script data-always-execute>
(function() {
    // Reset animations to show content immediately after page transition
    const adminHeader = document.querySelector('.admin-header');
    const adminCard = document.querySelector('.admin-card');

    if (adminHeader) {
        adminHeader.style.animation = 'none';
        adminHeader.style.opacity = '1';
        adminHeader.style.transform = 'translateY(0)';
    }

    if (adminCard) {
        adminCard.style.opacity = '1';
        adminCard.style.transform = 'translateY(0)';
    }

    // Auto-load users if list is empty
    setTimeout(() => {
        const list = document.querySelector('.users-list');
        const loader = document.getElementById('admin-loader');

        // Make sure we have the elements and loadUsers function exists
        if (list && loader && window.loadUsers) {
            // Check if list is empty and loader is visible
            if (list.innerHTML.trim() === '' && loader.style.display !== 'none') {
                // Only load if not already running
                if (!window.loadUsersRunning) {
                    window.loadUsers();
                }
            }
        }
    }, 50);
})();
</script>
@endpush
@endsection
