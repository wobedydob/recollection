@extends('layouts.app')

@section('content')
<div class="admin-page">
    <div class="admin-header">
        <h1 class="admin-title">Admin Dashboard</h1>
    </div>

    <div class="admin-loader" id="admin-loader">
        <div class="loader"></div>
        <p class="loader-text">Laden...</p>
    </div>

    <div class="admin-content" id="admin-content">
        <div class="stats-grid">
        <div class="stat-card">
            <span class="stat-icon">👥</span>
            <div class="stat-info">
                <span class="stat-value">{{ $stats['users'] }}</span>
                <span class="stat-label">Gebruikers</span>
            </div>
        </div>
        <div class="stat-card">
            <span class="stat-icon">🛡️</span>
            <div class="stat-info">
                <span class="stat-value">{{ $stats['admins'] }}</span>
                <span class="stat-label">Admins</span>
            </div>
        </div>
        <div class="stat-card">
            <span class="stat-icon">✨</span>
            <div class="stat-info">
                <span class="stat-value">{{ $stats['ideas'] }}</span>
                <span class="stat-label">Ideeën</span>
            </div>
        </div>
        <div class="stat-card">
            <span class="stat-icon">🏷️</span>
            <div class="stat-info">
                <span class="stat-value">{{ $stats['tags'] }}</span>
                <span class="stat-label">Tags</span>
            </div>
        </div>
        <div class="stat-card">
            <span class="stat-icon">📋</span>
            <div class="stat-info">
                <span class="stat-value">{{ $stats['checklists'] }}</span>
                <span class="stat-label">Checklists</span>
            </div>
        </div>
        <div class="stat-card">
            <span class="stat-icon">✓</span>
            <div class="stat-info">
                <span class="stat-value">{{ $stats['tasks'] }}</span>
                <span class="stat-label">Taken</span>
            </div>
        </div>
    </div>

    <div class="admin-actions">
        <a href="{{ route('admin.users') }}" class="btn btn-primary">Gebruikers beheren</a>
        <a href="{{ route('admin.suggestions') }}" class="btn btn-secondary">Suggesties @if($stats['new_suggestions'] > 0)<span class="badge">{{ $stats['new_suggestions'] }}</span>@endif</a>
    </div>

    <div class="admin-card">
        <h2 class="section-title">Recente gebruikers</h2>
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
                <p class="empty-message">Geen gebruikers gevonden.</p>
            @endforelse
        </div>
    </div>
    </div>
</div>

@push('scripts')
<script>
setTimeout(function() {
    document.getElementById('admin-loader').style.display = 'none';
    document.getElementById('admin-content').classList.add('loaded');
}, 300);
</script>
@endpush
@endsection
