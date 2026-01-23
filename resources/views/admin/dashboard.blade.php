@extends('layouts.app')

@section('content')
<div class="admin-page">
    <div class="admin-header">
        <h1 class="admin-title">{{ __('admin.dashboard') }}</h1>
    </div>

    <div class="admin-content" id="admin-content">
        <div class="stats-grid">
        <div class="stat-card">
            <span class="stat-icon">👥</span>
            <div class="stat-info">
                <span class="stat-value">{{ $stats['users'] }}</span>
                <span class="stat-label">{{ __('admin.users') }}</span>
            </div>
        </div>
        <div class="stat-card">
            <span class="stat-icon">🛡️</span>
            <div class="stat-info">
                <span class="stat-value">{{ $stats['admins'] }}</span>
                <span class="stat-label">{{ __('admin.admins') }}</span>
            </div>
        </div>
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

    <div class="admin-actions">
        <a href="{{ route('admin.users') }}" class="btn btn-primary">{{ __('admin.manage_users') }}</a>
        <a href="{{ route('admin.suggestions') }}" class="btn btn-secondary">{{ __('admin.suggestions') }} @if($stats['new_suggestions'] > 0)<span class="badge">{{ $stats['new_suggestions'] }}</span>@endif</a>
    </div>

    <div class="admin-card">
        <h2 class="section-title">{{ __('admin.recent_users') }}</h2>
        <div class="users-list">
            @forelse($recentUsers as $user)
                <a href="{{ route('admin.users.show', $user) }}" class="user-row clickable">
                    <div class="user-info">
                        <span class="user-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                        <div class="user-details">
                            <span class="user-name">{{ $user->name }}</span>
                            <span class="user-email">{{ $user->email }}</span>
                        </div>
                    </div>
                    <div class="user-meta">
                        <span class="user-role {{ $user->role }}">{{ $user->role }}</span>
                        <span class="user-date">{{ $user->created_at->translatedFormat('d M Y') }}</span>
                    </div>
                </a>
            @empty
                <p class="empty-message">{{ __('admin.no_users') }}</p>
            @endforelse
        </div>
    </div>
    </div>
</div>

@push('scripts')
<script data-always-execute>
(function() {
    // Dashboard loads fast, just show content immediately (always execute on page load/transition)
    const adminContent = document.getElementById('admin-content');
    const adminHeader = document.querySelector('.admin-header');
    const statCards = document.querySelectorAll('.stat-card');
    const adminCard = document.querySelector('.admin-card');

    if (adminContent) {
        adminContent.style.animation = 'none';
        adminContent.style.opacity = '1';
        adminContent.style.transform = 'translateY(0)';
        adminContent.style.display = 'block';
        adminContent.classList.add('loaded');
    }

    if (adminHeader) {
        adminHeader.style.animation = 'none';
        adminHeader.style.opacity = '1';
        adminHeader.style.transform = 'translateY(0)';
    }

    statCards.forEach((card) => {
        card.style.animation = 'none';
        card.style.opacity = '1';
        card.style.transform = 'translateY(0)';
    });

    if (adminCard) {
        adminCard.style.animation = 'none';
        adminCard.style.opacity = '1';
        adminCard.style.transform = 'translateY(0)';
    }
})();
</script>
@endpush
@endsection
