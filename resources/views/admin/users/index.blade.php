@extends('layouts.app')

@section('content')
<div class="admin-page admin-page-wide">
    <div class="admin-header">
        <div class="header-left">
            <a href="{{ route('admin.dashboard') }}" class="back-link">← Dashboard</a>
            <h1 class="admin-title">Gebruikers</h1>
        </div>
    </div>

    <div class="admin-card">
        <form method="GET" class="filter-form">
            <div class="filter-row">
                <input
                    type="text"
                    name="search"
                    class="input"
                    placeholder="Zoek op naam of e-mail..."
                    value="{{ request('search') }}"
                />
                <div class="custom-select" id="role-select">
                    <button type="button" class="custom-select-trigger" onclick="toggleDropdown()">
                        <span class="custom-select-value">
                            @if(request('role') === 'admin')
                                Admin
                            @elseif(request('role') === 'user')
                                User
                            @else
                                Alle rollen
                            @endif
                        </span>
                        <span class="custom-select-arrow">▼</span>
                    </button>
                    <div class="custom-select-dropdown" id="role-dropdown">
                        <button type="button" class="custom-select-option {{ !request('role') ? 'active' : '' }}" onclick="selectRole('')">
                            Alle rollen
                        </button>
                        <button type="button" class="custom-select-option {{ request('role') === 'user' ? 'active' : '' }}" onclick="selectRole('user')">
                            <span class="role-indicator user"></span>
                            User
                        </button>
                        <button type="button" class="custom-select-option {{ request('role') === 'admin' ? 'active' : '' }}" onclick="selectRole('admin')">
                            <span class="role-indicator admin"></span>
                            Admin
                        </button>
                    </div>
                    <input type="hidden" name="role" id="role-input" value="{{ request('role') }}" />
                </div>
                <button type="submit" class="btn btn-primary">Zoeken</button>
                @if(request('search') || request('role'))
                    <a href="{{ route('admin.users') }}" class="btn btn-secondary">Reset</a>
                @endif
            </div>
        </form>

        @if(session('success'))
            <div class="success-message">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="error-message">{{ $errors->first() }}</div>
        @endif

        <div class="admin-loader" id="admin-loader">
            <div class="loader"></div>
            <p class="loader-text">Laden...</p>
        </div>

        <div class="admin-content" id="admin-content">
            <div class="users-list">
                @forelse($users as $user)
                    <a href="{{ route('admin.users.show', $user) }}" class="user-row clickable">
                        <div class="user-info">
                            <span class="user-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                            <div class="user-details">
                                <span class="user-name">{{ $user->name }}</span>
                                <span class="user-email">{{ $user->email }}</span>
                            </div>
                        </div>
                        <div class="user-stats">
                            <span class="stat" title="Ideeën">✨ {{ $user->ideas_count }}</span>
                            <span class="stat" title="Checklists">📋 {{ $user->todo_lists_count }}</span>
                        </div>
                        <div class="user-meta">
                            <span class="user-role {{ $user->role }}">{{ $user->role }}</span>
                            <span class="user-date">{{ $user->created_at->translatedFormat('d M Y') }}</span>
                        </div>
                    </a>
                @empty
                    <p class="empty-message">Geen gebruikers gevonden.</p>
                @endforelse
            </div>

            @if($users->hasPages())
                <div class="pagination-wrapper">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
// Show content after minimum loading time
setTimeout(function() {
    document.getElementById('admin-loader').style.display = 'none';
    document.getElementById('admin-content').classList.add('loaded');
}, 300);

function toggleDropdown() {
    document.getElementById('role-dropdown').classList.toggle('show');
}

function selectRole(role) {
    document.getElementById('role-input').value = role;
    const labels = { '': 'Alle rollen', 'user': 'User', 'admin': 'Admin' };
    document.querySelector('.custom-select-value').textContent = labels[role];
    document.getElementById('role-dropdown').classList.remove('show');

    // Update active state
    document.querySelectorAll('.custom-select-option').forEach(opt => opt.classList.remove('active'));
    event.target.classList.add('active');
}

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    const select = document.getElementById('role-select');
    if (select && !select.contains(e.target)) {
        document.getElementById('role-dropdown').classList.remove('show');
    }
});
</script>
@endpush
@endsection
