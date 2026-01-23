@extends('layouts.app')

@section('content')
<div class="admin-page admin-page-wide">
    <div class="admin-header">
        <div class="header-left">
            <a href="{{ route('admin.dashboard') }}" class="back-link">← {{ __('admin.dashboard') }}</a>
            <h1 class="admin-title">{{ __('admin.suggestions') }}</h1>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <span class="stat-icon">💡</span>
            <div class="stat-info">
                <span class="stat-value" id="stat-total">{{ $stats['total'] }}</span>
                <span class="stat-label">{{ __('admin.total') }}</span>
            </div>
        </div>
        <div class="stat-card">
            <span class="stat-icon">🆕</span>
            <div class="stat-info">
                <span class="stat-value" id="stat-new">{{ $stats['new'] }}</span>
                <span class="stat-label">{{ __('admin.new') }}</span>
            </div>
        </div>
        <div class="stat-card">
            <span class="stat-icon">👀</span>
            <div class="stat-info">
                <span class="stat-value" id="stat-reviewed">{{ $stats['reviewed'] }}</span>
                <span class="stat-label">{{ __('admin.seen') }}</span>
            </div>
        </div>
        <div class="stat-card">
            <span class="stat-icon">📅</span>
            <div class="stat-info">
                <span class="stat-value" id="stat-planned">{{ $stats['planned'] }}</span>
                <span class="stat-label">{{ __('admin.planned') }}</span>
            </div>
        </div>
        <div class="stat-card">
            <span class="stat-icon">✅</span>
            <div class="stat-info">
                <span class="stat-value" id="stat-done">{{ $stats['done'] }}</span>
                <span class="stat-label">{{ __('admin.done') }}</span>
            </div>
        </div>
    </div>

    <div class="admin-card">
        <div class="filter-form">
            <div class="filter-row">
                <div class="filter-tags" id="filter-tags">
                    <button type="button" class="filter-tag active" data-status="" onclick="filterByStatus('')">
                        {{ __('admin.all') }}
                    </button>
                    <button type="button" class="filter-tag" data-status="new" onclick="filterByStatus('new')">
                        <span class="status-indicator new"></span>
                        {{ __('admin.new') }}
                    </button>
                    <button type="button" class="filter-tag" data-status="reviewed" onclick="filterByStatus('reviewed')">
                        <span class="status-indicator reviewed"></span>
                        {{ __('admin.seen') }}
                    </button>
                    <button type="button" class="filter-tag" data-status="planned" onclick="filterByStatus('planned')">
                        <span class="status-indicator planned"></span>
                        {{ __('admin.planned') }}
                    </button>
                    <button type="button" class="filter-tag" data-status="done" onclick="filterByStatus('done')">
                        <span class="status-indicator done"></span>
                        {{ __('admin.done') }}
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

        <div class="admin-loader" id="admin-loader" style="display: flex;">
            <div class="loader"></div>
            <p class="loader-text">{{ __('common.loading') }}</p>
        </div>

        <div class="admin-content" id="admin-content" style="display: none;">
            <div class="suggestions-admin-list"></div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal-overlay" id="delete-modal" style="display: none;">
    <div class="modal">
        <h3 class="modal-title">{{ __('admin.delete_suggestion') }}</h3>
        <p>{{ __('admin.delete_suggestion_confirm') }}</p>
        <div class="modal-actions">
            <button class="btn btn-secondary" onclick="closeDeleteModal()">{{ __('common.cancel') }}</button>
            <button class="btn btn-primary" id="confirm-delete-btn">{{ __('common.delete') }}</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentFilter = '';
let allSuggestions = [];

// Make loadSuggestions globally available (only define once)
if (!window.loadSuggestions) {
    window.loadSuggestions = async function() {
        if (window.loadSuggestionsRunning) {
            return;
        }
        window.loadSuggestionsRunning = true;
    const loader = document.getElementById('admin-loader');
    const content = document.getElementById('admin-content');

    // Show loader
    loader.style.display = 'flex';
    content.style.display = 'none';

    // Build URL
    const params = new URLSearchParams();
    if (currentFilter) params.append('status', currentFilter);

    try {
        const res = await fetch(`/admin/suggestions?${params.toString()}`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            credentials: 'include'
        });

        if (res.ok) {
            const data = await res.json();
            allSuggestions = data.suggestions;
            renderSuggestions(data.suggestions);
        }
    } catch (e) {
        console.error('Failed to load suggestions:', e);
    }

    // Hide loader, show content after minimum time for smooth transition
    setTimeout(() => {
        loader.style.display = 'none';
        content.style.display = 'block';
        content.classList.add('loaded');
        window.loadSuggestionsRunning = false;
    }, 300);
    };
}

function renderSuggestions(suggestions) {
        const list = document.querySelector('.suggestions-admin-list');
        const statusLabels = { 'new': '{{ __('admin.new') }}', 'reviewed': '{{ __('admin.seen') }}', 'planned': '{{ __('admin.planned') }}', 'done': '{{ __('admin.done') }}' };

        if (suggestions.length === 0) {
            list.innerHTML = '<p class="empty-message">{{ __('admin.no_suggestions') }}</p>';
            return;
        }

        list.innerHTML = suggestions.map((suggestion) => `
        <div class="suggestion-admin-item" data-status="${suggestion.status}">
            <div class="suggestion-admin-header">
                <div class="suggestion-admin-user">
                    <span class="user-avatar">${suggestion.user.avatar}</span>
                    <div class="user-details">
                        <span class="user-name">${escapeHtml(suggestion.user.name)}</span>
                        <span class="suggestion-date">${suggestion.created_at}</span>
                    </div>
                </div>
                <div class="suggestion-admin-actions">
                    <div class="custom-select custom-select-small" data-suggestion-id="${suggestion.id}" data-current-status="${suggestion.status}">
                        <button type="button" class="custom-select-trigger" onclick="toggleStatusDropdown(this)">
                            <span class="status-indicator ${suggestion.status}"></span>
                            <span class="custom-select-value">${statusLabels[suggestion.status]}</span>
                            <span class="custom-select-arrow">▼</span>
                        </button>
                        <div class="custom-select-dropdown">
                            <button type="button" class="custom-select-option ${suggestion.status === 'new' ? 'active' : ''}" onclick="updateStatusFromDropdown(this, 'new')">
                                <span class="status-indicator new"></span>
                                {{ __('admin.new') }}
                            </button>
                            <button type="button" class="custom-select-option ${suggestion.status === 'reviewed' ? 'active' : ''}" onclick="updateStatusFromDropdown(this, 'reviewed')">
                                <span class="status-indicator reviewed"></span>
                                {{ __('admin.seen') }}
                            </button>
                            <button type="button" class="custom-select-option ${suggestion.status === 'planned' ? 'active' : ''}" onclick="updateStatusFromDropdown(this, 'planned')">
                                <span class="status-indicator planned"></span>
                                {{ __('admin.planned') }}
                            </button>
                            <button type="button" class="custom-select-option ${suggestion.status === 'done' ? 'active' : ''}" onclick="updateStatusFromDropdown(this, 'done')">
                                <span class="status-indicator done"></span>
                                {{ __('admin.done') }}
                            </button>
                        </div>
                    </div>
                    <button type="button" class="delete-btn" title="{{ __('common.delete') }}" onclick="deleteSuggestion(${suggestion.id}, this)">×</button>
                </div>
            </div>
            <p class="suggestion-admin-content">${escapeHtml(suggestion.content)}</p>
        </div>
    `).join('');
}

function filterByStatus(status) {
    currentFilter = status;

    // Update active filter button
    document.querySelectorAll('#filter-tags .filter-tag').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.status === status);
    });

    // Filter and render
    const filtered = status ? allSuggestions.filter(s => s.status === status) : allSuggestions;
    renderSuggestions(filtered);
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
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
    const statusLabels = { 'new': '{{ __('admin.new') }}', 'reviewed': '{{ __('admin.seen') }}', 'planned': '{{ __('admin.planned') }}', 'done': '{{ __('admin.done') }}' };

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

            // Update in allSuggestions array
            const suggestionIndex = allSuggestions.findIndex(s => s.id === parseInt(suggestionId));
            if (suggestionIndex !== -1) {
                allSuggestions[suggestionIndex].status = newStatus;
            }

            // Re-apply current filter if active
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

// Add delete confirmation listener (only once)
if (!window.suggestionsDeleteListenerAdded) {
    window.suggestionsDeleteListenerAdded = true;
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

                // Remove from allSuggestions array
                allSuggestions = allSuggestions.filter(s => s.id !== pendingDeleteId);

                // Animate out and remove the item
                item.style.opacity = '0';
                item.style.transform = 'scale(0.95)';
                item.style.transition = 'all 0.3s ease';
                setTimeout(() => {
                    item.remove();
                    // Check if list is now empty
                    if (allSuggestions.length === 0) {
                        document.querySelector('.suggestions-admin-list').innerHTML = '<p class="empty-message">{{ __('admin.no_suggestions') }}</p>';
                    }
                }, 300);
            }
        } catch (e) {
            console.error('Failed to delete suggestion:', e);
        }

        closeDeleteModal();
    });
}

// Close modal when clicking overlay (only once)
if (!window.suggestionsModalListenerAdded) {
    window.suggestionsModalListenerAdded = true;
    document.getElementById('delete-modal').addEventListener('click', function(e) {
        if (e.target === this) closeDeleteModal();
    });
}

// Close all dropdowns when clicking outside (only once)
if (!window.suggestionsDropdownListenerAdded) {
    window.suggestionsDropdownListenerAdded = true;
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.custom-select')) {
            document.querySelectorAll('.custom-select-dropdown.show').forEach(dd => dd.classList.remove('show'));
        }
    });
}

// Initialize filter buttons based on URL
const urlParams = new URLSearchParams(window.location.search);
const initialStatus = urlParams.get('status') || '';
if (initialStatus) {
    currentFilter = initialStatus;
    document.querySelectorAll('#filter-tags .filter-tag').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.status === initialStatus);
    });
}
</script>

<script data-always-execute>
(function() {
    // Reset animations to show content immediately after page transition
    const adminHeader = document.querySelector('.admin-header');
    const adminCard = document.querySelector('.admin-card');
    const statCards = document.querySelectorAll('.stat-card');

    if (adminHeader) {
        adminHeader.style.animation = 'none';
        adminHeader.style.opacity = '1';
        adminHeader.style.transform = 'translateY(0)';
    }

    if (adminCard) {
        adminCard.style.opacity = '1';
        adminCard.style.transform = 'translateY(0)';
    }

    statCards.forEach(card => {
        card.style.animation = 'none';
        card.style.opacity = '1';
        card.style.transform = 'translateY(0)';
    });

    // Auto-load suggestions if list is empty
    setTimeout(() => {
        const list = document.querySelector('.suggestions-admin-list');
        const loader = document.getElementById('admin-loader');

        // Make sure we have the elements and loadSuggestions function exists
        if (list && loader && window.loadSuggestions) {
            // Check if list is empty and loader is visible
            if (list.innerHTML.trim() === '' && loader.style.display !== 'none') {
                // Only load if not already running
                if (!window.loadSuggestionsRunning) {
                    window.loadSuggestions();
                }
            }
        }
    }, 50);
})();
</script>
@endpush
@endsection
