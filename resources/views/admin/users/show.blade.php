@extends('layouts.app')

@section('content')
<div class="admin-page">
    <div class="admin-header">
        <div class="header-left">
            <a href="{{ route('admin.users') }}" class="back-link">← {{ __('admin.users') }}</a>
            <h1 class="admin-title">{{ $user->name }}</h1>
        </div>
    </div>

    @if(session('success'))
        <div class="success-message">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="error-message">{{ $errors->first() }}</div>
    @endif

    <div class="admin-card">
        <div class="user-profile-header">
            <span class="user-avatar large">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
            <div class="user-profile-info">
                <h2 class="user-profile-name">{{ $user->name }}</h2>
                <p class="user-profile-email">{{ $user->email }}</p>
                <div class="user-profile-meta">
                    <span class="user-role {{ $user->role }}">{{ $user->role }}</span>
                    <span class="user-joined">{{ __('admin.member_since', ['date' => $user->created_at->translatedFormat('F Y')]) }}</span>
                </div>
            </div>
            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary">{{ __('admin.edit_profile') }}</a>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <span class="stat-icon">✨</span>
            <div class="stat-info">
                <span class="stat-value">{{ $stats['ideas'] }}</span>
                <span class="stat-label">{{ __('admin.ideas') }}</span>
            </div>
        </div>
        <div class="stat-card">
            <span class="stat-icon">🏷️</span>
            <div class="stat-info">
                <span class="stat-value">{{ $stats['tags'] }}</span>
                <span class="stat-label">{{ __('admin.tags') }}</span>
            </div>
        </div>
        <div class="stat-card">
            <span class="stat-icon">📋</span>
            <div class="stat-info">
                <span class="stat-value">{{ $stats['checklists'] }}</span>
                <span class="stat-label">{{ __('admin.checklists') }}</span>
            </div>
        </div>
        <div class="stat-card">
            <span class="stat-icon">✓</span>
            <div class="stat-info">
                <span class="stat-value">{{ $stats['tasks'] }}</span>
                <span class="stat-label">{{ __('admin.tasks') }}</span>
            </div>
        </div>
    </div>

    @if($user->id !== auth()->id())
        <div class="danger-zone">
            <h2 class="section-title danger-title">{{ __('admin.danger_zone') }}</h2>
            <p class="danger-description">{{ __('admin.delete_user_warning') }}</p>
            <button type="button" class="delete-account-btn" onclick="confirmDeleteUser()">{{ __('admin.delete_user') }}</button>
        </div>
    @endif
</div>

<div class="modal-overlay" id="delete-user-modal" style="display: none;">
    <div class="modal">
        <button type="button" class="close-btn" onclick="closeDeleteModal()">×</button>
        <h2 class="modal-title">{{ __('admin.delete_user_title') }}</h2>
        <p class="modal-text">{{ __('admin.delete_user_confirm', ['name' => $user->name]) }}</p>
        <div class="modal-actions">
            <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">{{ __('common.cancel') }}</button>
            <form method="POST" action="{{ route('admin.users.delete', $user) }}" style="flex: 1;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-full">{{ __('admin.delete_user_btn') }}</button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function confirmDeleteUser() {
    document.getElementById('delete-user-modal').style.display = 'flex';
}
function closeDeleteModal() {
    document.getElementById('delete-user-modal').style.display = 'none';
}
</script>
@endpush
@endsection
