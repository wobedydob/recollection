<template>
    <div class="suggestions-app">
        <!-- Add Suggestion Form -->
        <Transition name="form-fade" appear>
            <form class="suggestion-form" @submit.prevent="createSuggestion">
                <textarea
                    v-model="newSuggestion"
                    class="suggestion-textarea"
                    :placeholder="t('suggestions.placeholder')"
                    rows="3"
                ></textarea>
                <button type="submit" class="submit-btn" :disabled="!newSuggestion.trim() || isSubmitting">
                    {{ isSubmitting ? t('suggestions.submitting') : t('suggestions.submit') }}
                </button>
            </form>
        </Transition>

        <!-- Loading State -->
        <div v-if="isLoading" class="loader-container">
            <div class="loader"></div>
            <p class="loader-text">{{ t('common.loading') }}</p>
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
                    <button class="delete-btn" @click="deleteSuggestion(suggestion)" :title="t('common.delete')">×</button>
                </div>
                <p class="suggestion-content">{{ suggestion.content }}</p>
                <span class="suggestion-date">{{ formatDate(suggestion.createdAt) }}</span>
            </div>
        </TransitionGroup>

        <!-- Empty State -->
        <Transition name="ideas-appear">
            <div v-if="!isLoading && suggestions.length === 0" class="empty-state">
                <div class="empty-icon">💡</div>
                <p>{{ t('suggestions.empty') }}</p>
            </div>
        </Transition>

        <!-- Delete Confirmation Modal -->
        <div v-if="deletingSuggestion" class="modal-overlay" @click.self="deletingSuggestion = null">
            <div class="modal">
                <h3 class="modal-title">{{ t('suggestions.delete_title') }}</h3>
                <p>{{ t('suggestions.delete_confirm') }}</p>
                <div class="modal-actions">
                    <button class="btn btn-secondary" @click="deletingSuggestion = null">{{ t('common.cancel') }}</button>
                    <button class="btn btn-primary" @click="confirmDelete">{{ t('common.delete') }}</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { __, getLocale } from '@/utils/translations';

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
            const locale = getLocale() === 'nl' ? 'nl-NL' : 'en-US';
            return date.toLocaleDateString(locale, {
                day: 'numeric',
                month: 'short',
                year: 'numeric'
            });
        },
        statusLabel(status) {
            const labels = {
                'new': this.t('suggestions.status_new'),
                'reviewed': this.t('suggestions.status_seen'),
                'planned': this.t('suggestions.status_planned'),
                'done': this.t('suggestions.status_done')
            };
            return labels[status] || status;
        },
        t(key, replacements = {}) {
            return __(key, replacements);
        }
    }
};
</script>
