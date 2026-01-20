@extends('layouts.app')

@section('content')
<div class="admin-page">
    <div class="admin-header">
        <div class="header-left">
            <a href="{{ route('admin.users.show', $user) }}" class="back-link">← {{ $user->name }}</a>
            <h1 class="admin-title">{{ __('admin.edit_profile') }}</h1>
        </div>
    </div>

    @if(session('success'))
        <div class="success-message">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="error-message">{{ $errors->first() }}</div>
    @endif

    <div class="admin-card">
        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="edit-form">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="name" class="label">{{ __('admin.name') }}</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    class="input"
                    value="{{ old('name', $user->name) }}"
                    required
                />
            </div>

            <div class="form-group">
                <label for="email" class="label">{{ __('admin.email') }}</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="input"
                    value="{{ old('email', $user->email) }}"
                    required
                />
            </div>

            <div class="form-group">
                <label class="label">{{ __('admin.role') }}</label>
                <div class="custom-select" id="role-select">
                    <button type="button" class="custom-select-trigger" onclick="toggleDropdown()">
                        <span class="custom-select-value">
                            @if(old('role', $user->role) === 'admin')
                                <span class="role-indicator admin"></span> Admin
                            @else
                                <span class="role-indicator user"></span> {{ __('admin.user') }}
                            @endif
                        </span>
                        <span class="custom-select-arrow">▼</span>
                    </button>
                    <div class="custom-select-dropdown" id="role-dropdown">
                        <button type="button" class="custom-select-option {{ old('role', $user->role) === 'user' ? 'active' : '' }}" onclick="selectRole('user')">
                            <span class="role-indicator user"></span>
                            {{ __('admin.user') }}
                        </button>
                        <button type="button" class="custom-select-option {{ old('role', $user->role) === 'admin' ? 'active' : '' }}" onclick="selectRole('admin')">
                            <span class="role-indicator admin"></span>
                            Admin
                        </button>
                    </div>
                    <input type="hidden" name="role" id="role-input" value="{{ old('role', $user->role) }}" />
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">{{ __('common.save') }}</button>
                <a href="{{ route('admin.users.show', $user) }}" class="btn btn-secondary">{{ __('common.cancel') }}</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function toggleDropdown() {
    document.getElementById('role-dropdown').classList.toggle('show');
}

function selectRole(role) {
    document.getElementById('role-input').value = role;
    const indicators = { 'user': '<span class="role-indicator user"></span> {{ __('admin.user') }}', 'admin': '<span class="role-indicator admin"></span> Admin' };
    document.querySelector('.custom-select-value').innerHTML = indicators[role];
    document.getElementById('role-dropdown').classList.remove('show');

    // Update active state
    document.querySelectorAll('.custom-select-option').forEach(opt => opt.classList.remove('active'));
    event.target.closest('.custom-select-option').classList.add('active');
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
