<template>
    <div class="suggestions-app">
        <!-- Add Suggestion Form -->
        <Transition name="form-fade" appear>
            <form class="suggestion-form" @submit.prevent="createSuggestion">
                <textarea
                    v-model="newSuggestion"
                    class="suggestion-textarea"
                    placeholder="Deel je idee of suggestie..."
                    rows="3"
                ></textarea>
                <button type="submit" class="submit-btn" :disabled="!newSuggestion.trim() || isSubmitting">
                    {{ isSubmitting ? 'Versturen...' : 'Versturen' }}
                </button>
            </form>
        </Transition>

        <!-- Loading State -->
        <div v-if="isLoading" class="loader-container">
            <div class="loader"></div>
            <p class="loader-text">Laden...</p>
        </div>

        <!-- Suggestions List -->
        <TransitionGroup name="todo-task" tag="div" class="suggestions-list" appear v-if="!isLoading && suggestions.length > 0">
            <div
                v-for="suggestion in suggestions"
                :key="suggestion.id"
                class="suggestion-card"
            >
                <div class="suggestion-header">
                    <span class="suggestion-status" :class="suggestion.status">{{ statusLabel(suggestion.status) }}</span>
                    <button class="delete-btn" @click="deleteSuggestion(suggestion)" title="Verwijderen">×</button>
                </div>
                <p class="suggestion-content">{{ suggestion.content }}</p>
                <span class="suggestion-date">{{ formatDate(suggestion.createdAt) }}</span>
            </div>
        </TransitionGroup>

        <!-- Empty State -->
        <Transition name="ideas-appear">
            <div v-if="!isLoading && suggestions.length === 0" class="empty-state">
                <div class="empty-icon">💡</div>
                <p>Nog geen suggesties.<br/>Deel hierboven je eerste idee!</p>
            </div>
        </Transition>

        <!-- Delete Confirmation Modal -->
        <div v-if="deletingSuggestion" class="modal-overlay" @click.self="deletingSuggestion = null">
            <div class="modal">
                <h3 class="modal-title">Suggestie verwijderen?</h3>
                <p>Weet je zeker dat je deze suggestie wilt verwijderen?</p>
                <div class="modal-actions">
                    <button class="btn btn-secondary" @click="deletingSuggestion = null">Annuleren</button>
                    <button class="btn btn-primary" @click="confirmDelete">Verwijderen</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'SuggestionsApp',
    data() {
        return {
            suggestions: [],
            newSuggestion: '',
            isLoading: true,
            isSubmitting: false,
            deletingSuggestion: null
        };
    },
    async mounted() {
        await this.loadSuggestions();
    },
    methods: {
        async loadSuggestions() {
            this.isLoading = true;
            try {
                const res = await fetch('/api/suggestions', { credentials: 'include' });
                const data = await res.json();
                this.suggestions = Array.isArray(data.suggestions) ? data.suggestions : [];
            } catch (e) {
                console.error('Failed to load suggestions:', e);
                this.suggestions = [];
            } finally {
                this.isLoading = false;
            }
        },
        async createSuggestion() {
            if (!this.newSuggestion.trim() || this.isSubmitting) return;
            this.isSubmitting = true;
            try {
                const res = await fetch('/api/suggestions', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'include',
                    body: JSON.stringify({ content: this.newSuggestion })
                });
                if (res.ok) {
                    const data = await res.json();
                    this.suggestions.unshift(data.suggestion);
                    this.newSuggestion = '';
                }
            } catch (e) {
                console.error('Failed to create suggestion:', e);
            } finally {
                this.isSubmitting = false;
            }
        },
        deleteSuggestion(suggestion) {
            this.deletingSuggestion = suggestion;
        },
        async confirmDelete() {
            if (!this.deletingSuggestion) return;
            try {
                const res = await fetch(`/api/suggestions/${this.deletingSuggestion.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'include'
                });
                if (res.ok) {
                    this.suggestions = this.suggestions.filter(s => s.id !== this.deletingSuggestion.id);
                }
            } catch (e) {
                console.error('Failed to delete suggestion:', e);
            }
            this.deletingSuggestion = null;
        },
        formatDate(timestamp) {
            const date = new Date(timestamp);
            return date.toLocaleDateString('nl-NL', {
                day: 'numeric',
                month: 'short',
                year: 'numeric'
            });
        },
        statusLabel(status) {
            const labels = {
                'new': 'Nieuw',
                'reviewed': 'Bekeken',
                'planned': 'Gepland',
                'done': 'Afgerond'
            };
            return labels[status] || status;
        }
    }
};
</script>
