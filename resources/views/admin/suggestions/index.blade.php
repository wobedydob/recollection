@extends('layouts.app')

@section('content')
<div class="admin-page admin-page-wide">
    <div class="admin-header">
        <div class="header-left">
            <a href="{{ route('admin.dashboard') }}" class="back-link">← Dashboard</a>
            <h1 class="admin-title">Suggesties</h1>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <span class="stat-icon">💡</span>
            <div class="stat-info">
                <span class="stat-value" id="stat-total">{{ $stats['total'] }}</span>
                <span class="stat-label">Totaal</span>
            </div>
        </div>
        <div class="stat-card">
            <span class="stat-icon">🆕</span>
            <div class="stat-info">
                <span class="stat-value" id="stat-new">{{ $stats['new'] }}</span>
                <span class="stat-label">Nieuw</span>
            </div>
        </div>
        <div class="stat-card">
            <span class="stat-icon">👀</span>
            <div class="stat-info">
                <span class="stat-value" id="stat-reviewed">{{ $stats['reviewed'] }}</span>
                <span class="stat-label">Bekeken</span>
            </div>
        </div>
        <div class="stat-card">
            <span class="stat-icon">📅</span>
            <div class="stat-info">
                <span class="stat-value" id="stat-planned">{{ $stats['planned'] }}</span>
                <span class="stat-label">Gepland</span>
            </div>
        </div>
        <div class="stat-card">
            <span class="stat-icon">✅</span>
            <div class="stat-info">
                <span class="stat-value" id="stat-done">{{ $stats['done'] }}</span>
                <span class="stat-label">Afgerond</span>
            </div>
        </div>
    </div>

    <div class="admin-card">
        <div class="filter-form">
            <div class="filter-row">
                <div class="filter-tags" id="filter-tags">
                    <button type="button" class="filter-tag active" data-status="" onclick="filterByStatus('')">
                        Alle
                    </button>
                    <button type="button" class="filter-tag" data-status="new" onclick="filterByStatus('new')">
                        <span class="status-indicator new"></span>
                        Nieuw
                    </button>
                    <button type="button" class="filter-tag" data-status="reviewed" onclick="filterByStatus('reviewed')">
                        <span class="status-indicator reviewed"></span>
                        Bekeken
                    </button>
                    <button type="button" class="filter-tag" data-status="planned" onclick="filterByStatus('planned')">
                        <span class="status-indicator planned"></span>
                        Gepland
                    </button>
                    <button type="button" class="filter-tag" data-status="done" onclick="filterByStatus('done')">
                        <span class="status-indicator done"></span>
                        Afgerond
                    </button>
                </div>
            </div>
        </div>

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
            <div class="suggestions-admin-list">
                @forelse($suggestions as $suggestion)
                    <div class="suggestion-admin-item" data-status="{{ $suggestion->status }}">
                        <div class="suggestion-admin-header">
                            <div class="suggestion-admin-user">
                                <span class="user-avatar">{{ strtoupper(substr($suggestion->user->name, 0, 1)) }}</span>
                                <div class="user-details">
                                    <span class="user-name">{{ $suggestion->user->name }}</span>
                                    <span class="suggestion-date">{{ $suggestion->created_at->translatedFormat('d M Y H:i') }}</span>
                                </div>
                            </div>
                            <div class="suggestion-admin-actions">
                                <div class="custom-select custom-select-small" data-suggestion-id="{{ $suggestion->id }}" data-current-status="{{ $suggestion->status }}">
                                    <button type="button" class="custom-select-trigger" onclick="toggleStatusDropdown(this)">
                                        <span class="status-indicator {{ $suggestion->status }}"></span>
                                        <span class="custom-select-value">
                                            @if($suggestion->status === 'new') Nieuw
                                            @elseif($suggestion->status === 'reviewed') Bekeken
                                            @elseif($suggestion->status === 'planned') Gepland
                                            @elseif($suggestion->status === 'done') Afgerond
                                            @endif
                                        </span>
                                        <span class="custom-select-arrow">▼</span>
                                    </button>
                                    <div class="custom-select-dropdown">
                                        <button type="button" class="custom-select-option {{ $suggestion->status === 'new' ? 'active' : '' }}" onclick="updateStatusFromDropdown(this, 'new')">
                                            <span class="status-indicator new"></span>
                                            Nieuw
                                        </button>
                                        <button type="button" class="custom-select-option {{ $suggestion->status === 'reviewed' ? 'active' : '' }}" onclick="updateStatusFromDropdown(this, 'reviewed')">
                                            <span class="status-indicator reviewed"></span>
                                            Bekeken
                                        </button>
                                        <button type="button" class="custom-select-option {{ $suggestion->status === 'planned' ? 'active' : '' }}" onclick="updateStatusFromDropdown(this, 'planned')">
                                            <span class="status-indicator planned"></span>
                                            Gepland
                                        </button>
                                        <button type="button" class="custom-select-option {{ $suggestion->status === 'done' ? 'active' : '' }}" onclick="updateStatusFromDropdown(this, 'done')">
                                            <span class="status-indicator done"></span>
                                            Afgerond
                                        </button>
                                    </div>
                                </div>
                                <button type="button" class="delete-btn" title="Verwijderen" onclick="deleteSuggestion({{ $suggestion->id }}, this)">×</button>
                            </div>
                        </div>
                        <p class="suggestion-admin-content">{{ $suggestion->content }}</p>
                    </div>
                @empty
                    <p class="empty-message">Geen suggesties gevonden.</p>
                @endforelse
            </div>

            @if($suggestions->hasPages())
                <div class="pagination-wrapper">
                    {{ $suggestions->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal-overlay" id="delete-modal" style="display: none;">
    <div class="modal">
        <h3 class="modal-title">Suggestie verwijderen?</h3>
        <p>Weet je zeker dat je deze suggestie wilt verwijderen?</p>
        <div class="modal-actions">
            <button class="btn btn-secondary" onclick="closeDeleteModal()">Annuleren</button>
            <button class="btn btn-primary" id="confirm-delete-btn">Verwijderen</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
setTimeout(function() {
    document.getElementById('admin-loader').style.display = 'none';
    document.getElementById('admin-content').classList.add('loaded');
}, 300);

let currentFilter = '';

function filterByStatus(status) {
    currentFilter = status;

    // Update active filter button
    document.querySelectorAll('#filter-tags .filter-tag').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.status === status);
    });

    // Filter suggestions
    const items = document.querySelectorAll('.suggestion-admin-item');
    let visibleCount = 0;

    items.forEach(item => {
        const itemStatus = item.dataset.status;
        const shouldShow = !status || itemStatus === status;

        if (shouldShow) {
            item.style.display = '';
            visibleCount++;
        } else {
            item.style.display = 'none';
        }
    });

    // Show/hide empty message
    let emptyMsg = document.querySelector('.suggestions-admin-list .empty-message');
    if (visibleCount === 0) {
        if (!emptyMsg) {
            emptyMsg = document.createElement('p');
            emptyMsg.className = 'empty-message';
            document.querySelector('.suggestions-admin-list').appendChild(emptyMsg);
        }
        emptyMsg.textContent = status ? 'Geen suggesties met deze status.' : 'Geen suggesties gevonden.';
        emptyMsg.style.display = '';
    } else if (emptyMsg) {
        emptyMsg.style.display = 'none';
    }
}

function toggleStatusDropdown(btn) {
    // Close all other dropdowns first
    document.querySelectorAll('.suggestion-admin-actions .custom-select-dropdown.show').forEach(dd => {
        if (dd !== btn.nextElementSibling) dd.classList.remove('show');
    });
    btn.nextElementSibling.classList.toggle('show');
}

async function updateStatusFromDropdown(optionEl, newStatus) {
    const customSelect = optionEl.closest('.custom-select');
    const suggestionId = customSelect.dataset.suggestionId;
    const oldStatus = customSelect.dataset.currentStatus;

    // Close dropdown
    customSelect.querySelector('.custom-select-dropdown').classList.remove('show');

    if (oldStatus === newStatus) return;

    const trigger = customSelect.querySelector('.custom-select-trigger');
    const statusLabels = { 'new': 'Nieuw', 'reviewed': 'Bekeken', 'planned': 'Gepland', 'done': 'Afgerond' };

    try {
        const res = await fetch(`/admin/suggestions/${suggestionId}/status`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            credentials: 'include',
            body: JSON.stringify({ status: newStatus })
        });

        if (res.ok) {
            // Update stats counters
            const oldStatEl = document.getElementById('stat-' + oldStatus);
            const newStatEl = document.getElementById('stat-' + newStatus);
            if (oldStatEl) oldStatEl.textContent = parseInt(oldStatEl.textContent) - 1;
            if (newStatEl) newStatEl.textContent = parseInt(newStatEl.textContent) + 1;

            // Update dropdown display
            customSelect.dataset.currentStatus = newStatus;
            trigger.querySelector('.status-indicator').className = 'status-indicator ' + newStatus;
            trigger.querySelector('.custom-select-value').textContent = statusLabels[newStatus];

            // Update active state
            customSelect.querySelectorAll('.custom-select-option').forEach(opt => opt.classList.remove('active'));
            optionEl.classList.add('active');

            // Update item's data-status for filtering
            customSelect.closest('.suggestion-admin-item').dataset.status = newStatus;

            // Re-apply current filter
            if (currentFilter) {
                filterByStatus(currentFilter);
            }

            // Show brief success feedback
            trigger.style.borderColor = '#4ade80';
            setTimeout(() => trigger.style.borderColor = '', 1000);
        }
    } catch (e) {
        console.error('Failed to update status:', e);
        trigger.style.borderColor = '#ef4444';
        setTimeout(() => trigger.style.borderColor = '', 1000);
    }
}

let pendingDeleteId = null;
let pendingDeleteBtn = null;

function deleteSuggestion(suggestionId, btnEl) {
    pendingDeleteId = suggestionId;
    pendingDeleteBtn = btnEl;
    document.getElementById('delete-modal').style.display = 'flex';
}

function closeDeleteModal() {
    document.getElementById('delete-modal').style.display = 'none';
    pendingDeleteId = null;
    pendingDeleteBtn = null;
}

document.getElementById('confirm-delete-btn').addEventListener('click', async function() {
    if (!pendingDeleteId) return;

    try {
        const res = await fetch(`/admin/suggestions/${pendingDeleteId}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            credentials: 'include'
        });

        if (res.ok) {
            // Update stats counters
            const item = pendingDeleteBtn.closest('.suggestion-admin-item');
            const customSelect = item.querySelector('.custom-select');
            const currentStatus = customSelect.dataset.currentStatus;

            const totalEl = document.getElementById('stat-total');
            const statusEl = document.getElementById('stat-' + currentStatus);
            if (totalEl) totalEl.textContent = parseInt(totalEl.textContent) - 1;
            if (statusEl) statusEl.textContent = parseInt(statusEl.textContent) - 1;

            // Animate out and remove the item
            item.style.opacity = '0';
            item.style.transform = 'scale(0.95)';
            item.style.transition = 'all 0.3s ease';
            setTimeout(() => item.remove(), 300);
        }
    } catch (e) {
        console.error('Failed to delete suggestion:', e);
    }

    closeDeleteModal();
});

// Close modal when clicking overlay
document.getElementById('delete-modal').addEventListener('click', function(e) {
    if (e.target === this) closeDeleteModal();
});

// Close all dropdowns when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.custom-select')) {
        document.querySelectorAll('.custom-select-dropdown.show').forEach(dd => dd.classList.remove('show'));
    }
});
</script>
@endpush
@endsection
