<template>
    <div class="ideas-app">
        <!-- Create Idea Form -->
        <Transition name="form-fade" appear>
        <form class="idea-form" @submit.prevent="createIdea">
            <textarea
                v-model="newIdea"
                class="idea-textarea"
                placeholder="Wat zit er in je hoofd?..."
                rows="3"
            ></textarea>
            <div class="tag-selector-container">
                <div class="tag-selector-row">
                    <div class="selected-tags" :class="{ clickable: availableTags.length > 0 }" @click="availableTags.length > 0 && (showTagDropdown = !showTagDropdown)">
                        <span
                            v-for="tag in selectedTagObjects"
                            :key="tag.id"
                            class="tag-pill clickable"
                            :style="{ backgroundColor: tag.color }"
                            @click.stop="removeTag(tag.id)"
                        >
                            <span v-if="tag.emoji" class="tag-emoji">{{ tag.emoji }}</span>
                            {{ tag.name }}
                            <span class="tag-remove">×</span>
                        </span>

                        <span v-if="selectedTagObjects.length === 0" class="tag-placeholder">
                            {{ availableTags.length > 0 ? 'Klik om tags te selecteren' : 'Geen tags beschikbaar' }}
                        </span>

                        <span v-if="availableTags.length > 0" class="dropdown-indicator">▼</span>
                    </div>

                    <button type="button" class="add-tag-btn" @click="showAddTagModal = true" title="Tags">
                        +
                    </button>
                </div>

                <!-- Tag dropdown (shows only unselected tags) -->
                <div v-if="showTagDropdown && availableTags.length > 0" class="tag-multiselect-dropdown" @click.stop>
                    <button
                        v-for="tag in availableTags"
                        :key="tag.id"
                        type="button"
                        class="tag-multiselect-item"
                        @click="selectTag(tag.id)"
                    >
                        <span class="tag-preview" :style="{ backgroundColor: tag.color }">
                            <span v-if="tag.emoji" class="tag-emoji">{{ tag.emoji }}</span>
                            {{ tag.name }}
                        </span>
                    </button>
                </div>
            </div>
            <button type="submit" class="submit-btn" :disabled="!newIdea.trim() || isSubmitting">
                {{ isSubmitting ? 'Opslaan...' : 'Opslaan in Memory Box' }}
            </button>
        </form>
        </Transition>

        <!-- Filter Section -->
        <Transition name="ideas-appear">
            <div v-if="!isLoading && tags.length > 0" class="filter-section">
            <span class="filter-label">Filter op tags:</span>
            <div class="filter-tags">
                <button
                    v-for="tag in tags"
                    :key="tag.id"
                    class="filter-tag"
                    :class="{ active: filterTags.includes(tag.id) }"
                    :style="{ backgroundColor: filterTags.includes(tag.id) ? tag.color : '' }"
                    @click="setFilter(tag.id)"
                >
                    <span>{{ tag.emoji }}</span>
                    {{ tag.name }}
                </button>
            </div>
            <button v-if="filterTags.length > 0" class="clear-filter" @click="clearFilter">
                Wissen
            </button>
            </div>
        </Transition>

        <!-- Loading State -->
        <div v-if="isLoading" class="loader-container">
            <div class="loader"></div>
            <p class="loader-text">Laden...</p>
        </div>

        <!-- Ideas List -->
        <TransitionGroup name="todo-task" tag="div" class="ideas-list" appear v-if="!isLoading && !isFiltering && filteredIdeas.length > 0" :key="filterTags.join(',') || 'all'">
            <div v-for="idea in filteredIdeas" :key="idea.id" class="idea-card">
                <div class="card-actions">
                    <button class="edit-btn" @click="startEdit(idea)" title="Bewerken">✎</button>
                    <button class="delete-btn" @click="confirmDelete(idea)" title="Verwijderen">×</button>
                </div>
                <p class="idea-content">{{ idea.content }}</p>
                <div class="idea-footer">
                    <div class="idea-tags">
                        <button
                            v-for="tag in idea.tags"
                            :key="tag.id"
                            class="idea-tag"
                            :style="{ backgroundColor: tag.color + '20', color: tag.color }"
                            @click="setFilter(tag.id)"
                        >
                            <span class="tag-emoji">{{ tag.emoji }}</span>
                            {{ tag.name }}
                        </button>
                    </div>
                    <span class="idea-date">{{ formatDate(idea.created_at) }}</span>
                </div>
            </div>
        </TransitionGroup>

        <!-- Empty State -->
        <Transition name="ideas-appear">
            <div v-if="!isLoading && !isFiltering && filteredIdeas.length === 0" class="empty-state">
                <div class="empty-icon">✨</div>
                <p v-if="activeFilterTags.length > 0">Geen ideeën met {{ activeFilterTags.length === 1 ? 'tag' : 'tags' }} "{{ activeFilterTags.map(t => t.name).join('", "') }}"</p>
                <p v-else>Je memory box is leeg.<br/>Voeg hierboven je eerste idee toe!</p>
            </div>
        </Transition>

        <!-- Edit Modal -->
        <div v-if="editingIdea" class="modal-overlay" @click.self="cancelEdit">
            <div class="modal">
                <h3 class="modal-title">Idee bewerken</h3>
                <div class="form-group">
                    <textarea
                        v-model="editContent"
                        class="idea-textarea"
                        rows="4"
                    ></textarea>
                </div>
                <div class="tag-selector-container">
                    <div class="selected-tags">
                        <span
                            v-for="tag in editSelectedTagObjects"
                            :key="tag.id"
                            class="tag-pill"
                            :style="{ backgroundColor: tag.color }"
                        >
                            <span v-if="tag.emoji" class="tag-emoji">{{ tag.emoji }}</span>
                            {{ tag.name }}
                            <button type="button" class="tag-remove" @click="removeEditTag(tag.id)">×</button>
                        </span>

                        <div class="tag-actions">
                            <button
                                type="button"
                                class="dropdown-toggle"
                                @click.stop="showEditTagDropdown = !showEditTagDropdown"
                                :disabled="editAvailableTags.length === 0"
                            >
                                <span class="dropdown-icon">▼</span>
                                Kies tag
                            </button>

                            <button type="button" class="add-tag-btn" @click="showAddTagModal = true" title="Nieuwe tag maken">
                                +
                            </button>
                            <button type="button" class="manage-tags-btn" :class="{ visible: tags.length }" @click="showManageTagsModal = true" title="Tags beheren">
                                ⚙
                            </button>
                        </div>
                    </div>

                    <div v-if="showEditTagDropdown && editAvailableTags.length" class="tag-dropdown">
                        <button
                            v-for="tag in editAvailableTags"
                            :key="tag.id"
                            type="button"
                            class="tag-dropdown-item"
                            @click="selectEditTag(tag.id)"
                        >
                            <span class="tag-preview" :style="{ backgroundColor: tag.color }">
                                <span v-if="tag.emoji" class="tag-emoji">{{ tag.emoji }}</span>
                                {{ tag.name }}
                            </span>
                        </button>
                    </div>
                </div>
                <div class="modal-actions">
                    <button class="btn btn-secondary" @click="cancelEdit">Annuleren</button>
                    <button class="btn btn-primary" @click="saveEdit" :disabled="!editContent.trim()">
                        Opslaan
                    </button>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="deletingIdea" class="modal-overlay" @click.self="deletingIdea = null">
            <div class="modal">
                <h3 class="modal-title">Idee verwijderen?</h3>
                <p>Weet je zeker dat je dit idee wilt verwijderen?</p>
                <div class="modal-actions">
                    <button class="btn btn-secondary" @click="deletingIdea = null">Annuleren</button>
                    <button class="btn btn-primary" @click="deleteIdea">Verwijderen</button>
                </div>
            </div>
        </div>

        <!-- Add Tag Modal -->
        <div v-if="showAddTagModal" class="modal-overlay" @click.self="closeTagModal">
            <div class="modal tag-modal">
                <button class="close-btn" @click="closeTagModal">×</button>
                <h2 class="modal-title-gradient">Tags</h2>

                <!-- Quick select existing tags (mobile) -->
                <div v-if="availableTags.length > 0" class="mobile-tag-select">
                    <label class="label">Selecteer tag</label>
                    <div class="mobile-tag-grid">
                        <button
                            v-for="tag in availableTags"
                            :key="tag.id"
                            type="button"
                            class="mobile-tag-option"
                            :style="{ backgroundColor: tag.color }"
                            @click="selectTagAndClose(tag.id)"
                        >
                            <span v-if="tag.emoji" class="tag-emoji">{{ tag.emoji }}</span>
                            {{ tag.name }}
                        </button>
                    </div>
                </div>

                <div v-if="availableTags.length > 0" class="mobile-divider">
                    <span>of maak een nieuwe</span>
                </div>

                <form @submit.prevent="createTag" class="modal-form">
                    <div class="form-group">
                        <label class="label">
                            Tag naam
                            <span class="char-count">{{ newTag.name.length }}/25</span>
                        </label>
                        <input
                            v-model="newTag.name"
                            type="text"
                            class="input"
                            placeholder="bijv. recepten, dromen, doelen..."
                            maxlength="25"
                        />
                    </div>

                    <div class="form-group">
                        <label class="label">Kleur</label>
                        <div class="color-grid">
                            <button
                                v-for="color in colors"
                                :key="color"
                                type="button"
                                class="color-option"
                                :class="{ selected: newTag.color === color }"
                                :style="{ backgroundColor: color }"
                                @click="newTag.color = color"
                            >
                                <span v-if="newTag.color === color" class="check">✓</span>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="label">Emoji (optioneel)</label>
                        <div class="emoji-section">
                            <input
                                v-model="newTag.emoji"
                                type="text"
                                class="emoji-input"
                                maxlength="2"
                            />
                            <div class="emoji-suggestions">
                                <button
                                    v-for="e in emojiSuggestions"
                                    :key="e"
                                    type="button"
                                    class="emoji-option"
                                    :class="{ selected: newTag.emoji === e }"
                                    @click="selectEmoji(e)"
                                >
                                    {{ e }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="label">Voorbeeld</label>
                        <div class="preview">
                            <span class="tag-preview" :style="{ backgroundColor: newTag.color }">
                                <span v-if="newTag.emoji" class="tag-emoji">{{ newTag.emoji }}</span>
                                {{ newTag.name || 'tag naam' }}
                            </span>
                        </div>
                    </div>

                    <button type="submit" class="submit-btn" :disabled="!newTag.name.trim()">
                        Tag Maken
                    </button>
                </form>

                <!-- Manage tags link (mobile) -->
                <button
                    v-if="tags.length > 0"
                    type="button"
                    class="mobile-manage-link"
                    @click="openManageFromAdd"
                >
                    ⚙ Tags beheren
                </button>
            </div>
        </div>

        <!-- Manage Tags Modal -->
        <div v-if="showManageTagsModal" class="modal-overlay" @click.self="showManageTagsModal = false">
            <div class="modal">
                <h3 class="modal-title">Tags beheren</h3>
                <div v-if="tags.length > 0" class="tag-list">
                    <div v-for="tag in tags" :key="tag.id" class="tag-list-item">
                        <div class="tag-list-info">
                            <span class="tag-list-color" :style="{ backgroundColor: tag.color }"></span>
                            <span>{{ tag.emoji }}</span>
                            <span class="tag-list-name">{{ tag.name }}</span>
                        </div>
                        <div class="tag-list-actions">
                            <button class="tag-edit-btn" @click="startEditTag(tag)" title="Bewerken">✎</button>
                            <button class="tag-delete-btn" @click="deleteTag(tag)" title="Verwijderen">×</button>
                        </div>
                    </div>
                </div>
                <p v-else>Nog geen tags aangemaakt.</p>
                <div class="modal-actions">
                    <button class="btn btn-primary btn-full" @click="showManageTagsModal = false">
                        Sluiten
                    </button>
                </div>
            </div>
        </div>

        <!-- Edit Tag Modal -->
        <div v-if="editingTag" class="modal-overlay" @click.self="cancelEditTag">
            <div class="modal tag-modal">
                <button class="close-btn" @click="cancelEditTag">×</button>
                <h2 class="modal-title-gradient">Tag Bewerken</h2>
                <form @submit.prevent="updateTag" class="modal-form">
                    <div class="form-group">
                        <label class="label">
                            Tag naam
                            <span class="char-count">{{ editingTag.name.length }}/25</span>
                        </label>
                        <input
                            v-model="editingTag.name"
                            type="text"
                            class="input"
                            placeholder="bijv. recepten, dromen, doelen..."
                            maxlength="25"
                        />
                    </div>

                    <div class="form-group">
                        <label class="label">Kleur</label>
                        <div class="color-grid">
                            <button
                                v-for="color in colors"
                                :key="color"
                                type="button"
                                class="color-option"
                                :class="{ selected: editingTag.color === color }"
                                :style="{ backgroundColor: color }"
                                @click="editingTag.color = color"
                            >
                                <span v-if="editingTag.color === color" class="check">✓</span>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="label">Emoji (optioneel)</label>
                        <div class="emoji-section">
                            <input
                                v-model="editingTag.emoji"
                                type="text"
                                class="emoji-input"
                                maxlength="2"
                            />
                            <div class="emoji-suggestions">
                                <button
                                    v-for="e in emojiSuggestions"
                                    :key="e"
                                    type="button"
                                    class="emoji-option"
                                    :class="{ selected: editingTag.emoji === e }"
                                    @click="editingTag.emoji = editingTag.emoji === e ? '' : e"
                                >
                                    {{ e }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="label">Voorbeeld</label>
                        <div class="preview">
                            <span class="tag-preview" :style="{ backgroundColor: editingTag.color }">
                                <span v-if="editingTag.emoji" class="tag-emoji">{{ editingTag.emoji }}</span>
                                {{ editingTag.name || 'tag naam' }}
                            </span>
                        </div>
                    </div>

                    <button type="submit" class="submit-btn" :disabled="!editingTag.name.trim()">
                        Opslaan
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'IdeasApp',
    data() {
        return {
            ideas: [],
            tags: [],
            newIdea: '',
            selectedTags: [],
            filterTags: [],
            isLoading: true,
            isFiltering: false,
            isSubmitting: false,
            editingIdea: null,
            editContent: '',
            editTags: [],
            deletingIdea: null,
            showAddTagModal: false,
            showManageTagsModal: false,
            editingTag: null,
            showTagDropdown: false,
            showEditTagDropdown: false,
            newTag: {
                name: '',
                emoji: '✨',
                color: '#ffb3ba'
            },
            colors: [
                '#ffb3ba', '#ffdfba', '#ffffba', '#baffc9', '#bae1ff',
                '#e0bbff', '#ffc8dd', '#bde0fe', '#a2d2ff', '#cdb4db'
            ],
            emojiSuggestions: ['✨', '💡', '❤️', '⭐', '🎯', '📝', '🎨', '🌸', '🦋', '🌙', '☀️', '🍀', '🐱', '🌈', '💫', '🧸']
        }
    },
    computed: {
        filteredIdeas() {
            const ideas = Array.isArray(this.ideas) ? this.ideas : [];
            if (this.filterTags.length === 0) return ideas;
            return ideas.filter(idea =>
                idea.tags && idea.tags.some(tag => this.filterTags.includes(tag.id))
            );
        },
        activeFilterTags() {
            if (this.filterTags.length === 0) return [];
            const tags = Array.isArray(this.tags) ? this.tags : [];
            return tags.filter(t => this.filterTags.includes(t.id));
        },
        selectedTagObjects() {
            const tags = Array.isArray(this.tags) ? this.tags : [];
            return tags.filter(tag => this.selectedTags.includes(tag.id));
        },
        availableTags() {
            const tags = Array.isArray(this.tags) ? this.tags : [];
            return tags.filter(tag => !this.selectedTags.includes(tag.id));
        },
        editSelectedTagObjects() {
            const tags = Array.isArray(this.tags) ? this.tags : [];
            return tags.filter(tag => this.editTags.includes(tag.id));
        },
        editAvailableTags() {
            const tags = Array.isArray(this.tags) ? this.tags : [];
            return tags.filter(tag => !this.editTags.includes(tag.id));
        }
    },
    async mounted() {
        await this.loadData();
        document.addEventListener('click', this.handleClickOutside);
    },
    beforeUnmount() {
        document.removeEventListener('click', this.handleClickOutside);
    },
    methods: {
        handleClickOutside(e) {
            const container = this.$el.querySelector('.tag-selector-container');
            if (container && !container.contains(e.target)) {
                this.showTagDropdown = false;
            }
        },
        transformIdea(idea) {
            const tags = Array.isArray(this.tags) ? this.tags : [];
            return {
                ...idea,
                created_at: idea.createdAt ? new Date(idea.createdAt).toISOString() : idea.created_at,
                tags: (idea.tagIds || []).map(id => tags.find(t => t.id === id)).filter(Boolean)
            };
        },
        async loadData() {
            this.isLoading = true;
            try {
                const [ideasRes, tagsRes] = await Promise.all([
                    fetch('/api/memory-box/ideas', { credentials: 'include' }),
                    fetch('/api/memory-box/tags', { credentials: 'include' })
                ]);
                const ideasData = await ideasRes.json();
                const tagsData = await tagsRes.json();
                this.tags = Array.isArray(tagsData.tags) ? tagsData.tags : [];
                const rawIdeas = Array.isArray(ideasData.ideas) ? ideasData.ideas : [];
                this.ideas = rawIdeas.map(idea => this.transformIdea(idea));
            } catch (e) {
                console.error('Failed to load data:', e);
                this.ideas = [];
                this.tags = [];
            } finally {
                this.isLoading = false;
            }
        },
        selectTag(tagId) {
            if (!this.selectedTags.includes(tagId)) {
                this.selectedTags.push(tagId);
            }
            // Keep dropdown open for multi-select, closes automatically when all selected
        },
        selectTagAndClose(tagId) {
            this.selectTag(tagId);
            this.showAddTagModal = false;
        },
        openManageFromAdd() {
            this.showAddTagModal = false;
            this.showManageTagsModal = true;
        },
        removeTag(tagId) {
            this.selectedTags = this.selectedTags.filter(id => id !== tagId);
        },
        selectEditTag(tagId) {
            if (!this.editTags.includes(tagId)) {
                this.editTags.push(tagId);
            }
            this.showEditTagDropdown = false;
        },
        removeEditTag(tagId) {
            this.editTags = this.editTags.filter(id => id !== tagId);
        },
        toggleTag(tagId) {
            const idx = this.selectedTags.indexOf(tagId);
            if (idx === -1) {
                this.selectedTags.push(tagId);
            } else {
                this.selectedTags.splice(idx, 1);
            }
        },
        async createIdea() {
            if (!this.newIdea.trim() || this.isSubmitting) return;
            this.isSubmitting = true;
            try {
                const res = await fetch('/api/memory-box/ideas', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'include',
                    body: JSON.stringify({
                        content: this.newIdea,
                        tagIds: this.selectedTags
                    })
                });
                if (res.ok) {
                    const data = await res.json();
                    this.ideas.unshift(this.transformIdea(data.idea));
                    this.newIdea = '';
                    this.selectedTags = [];
                }
            } catch (e) {
                console.error('Failed to create idea:', e);
            } finally {
                this.isSubmitting = false;
            }
        },
        setFilter(tagId) {
            this.isFiltering = true;
            const idx = this.filterTags.indexOf(tagId);
            if (idx === -1) {
                this.filterTags.push(tagId);
            } else {
                this.filterTags.splice(idx, 1);
            }
            setTimeout(() => { this.isFiltering = false; }, 150);
        },
        clearFilter() {
            this.isFiltering = true;
            this.filterTags = [];
            setTimeout(() => { this.isFiltering = false; }, 150);
        },
        startEdit(idea) {
            this.editingIdea = idea;
            this.editContent = idea.content;
            this.editTags = idea.tags ? idea.tags.map(t => t.id) : [];
        },
        cancelEdit() {
            this.editingIdea = null;
            this.editContent = '';
            this.editTags = [];
        },
        toggleEditTag(tagId) {
            const idx = this.editTags.indexOf(tagId);
            if (idx === -1) {
                this.editTags.push(tagId);
            } else {
                this.editTags.splice(idx, 1);
            }
        },
        async saveEdit() {
            if (!this.editContent.trim()) return;
            try {
                const res = await fetch(`/api/memory-box/ideas/${this.editingIdea.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'include',
                    body: JSON.stringify({
                        content: this.editContent,
                        tagIds: this.editTags
                    })
                });
                if (res.ok) {
                    const idx = this.ideas.findIndex(i => i.id === this.editingIdea.id);
                    if (idx !== -1) {
                        const tags = Array.isArray(this.tags) ? this.tags : [];
                        this.ideas[idx].content = this.editContent;
                        this.ideas[idx].tags = this.editTags.map(id => tags.find(t => t.id === id)).filter(Boolean);
                    }
                    this.cancelEdit();
                }
            } catch (e) {
                console.error('Failed to update idea:', e);
            }
        },
        confirmDelete(idea) {
            this.deletingIdea = idea;
        },
        async deleteIdea() {
            try {
                const res = await fetch(`/api/memory-box/ideas/${this.deletingIdea.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'include'
                });
                if (res.ok) {
                    this.ideas = this.ideas.filter(i => i.id !== this.deletingIdea.id);
                    this.deletingIdea = null;
                }
            } catch (e) {
                console.error('Failed to delete idea:', e);
            }
        },
        async createTag() {
            if (!this.newTag.name.trim()) return;
            try {
                const res = await fetch('/api/memory-box/tags', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'include',
                    body: JSON.stringify(this.newTag)
                });
                if (res.ok) {
                    const data = await res.json();
                    this.tags.push(data.tag);
                    this.selectedTags.push(data.tag.id);
                    this.closeTagModal();
                }
            } catch (e) {
                console.error('Failed to create tag:', e);
            }
        },
        closeTagModal() {
            this.showAddTagModal = false;
            this.newTag = { name: '', emoji: '', color: '#ffb3ba' };
        },
        selectEmoji(emoji) {
            this.newTag.emoji = this.newTag.emoji === emoji ? '' : emoji;
        },
        async deleteTag(tag) {
            try {
                const res = await fetch(`/api/memory-box/tags/${tag.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'include'
                });
                if (res.ok) {
                    this.tags = this.tags.filter(t => t.id !== tag.id);
                    this.ideas.forEach(idea => {
                        if (idea.tags) {
                            idea.tags = idea.tags.filter(t => t.id !== tag.id);
                        }
                    });
                }
            } catch (e) {
                console.error('Failed to delete tag:', e);
            }
        },
        startEditTag(tag) {
            this.editingTag = { ...tag };
        },
        cancelEditTag() {
            this.editingTag = null;
        },
        async updateTag() {
            if (!this.editingTag || !this.editingTag.name.trim()) return;
            try {
                const res = await fetch(`/api/memory-box/tags/${this.editingTag.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'include',
                    body: JSON.stringify({
                        name: this.editingTag.name,
                        color: this.editingTag.color,
                        emoji: this.editingTag.emoji
                    })
                });
                if (res.ok) {
                    const data = await res.json();
                    const idx = this.tags.findIndex(t => t.id === data.tag.id);
                    if (idx !== -1) {
                        this.tags.splice(idx, 1, data.tag);
                    }
                    // Update tags in all ideas
                    this.ideas.forEach(idea => {
                        if (idea.tags) {
                            const tagIdx = idea.tags.findIndex(t => t.id === data.tag.id);
                            if (tagIdx !== -1) {
                                idea.tags.splice(tagIdx, 1, data.tag);
                            }
                        }
                    });
                    this.editingTag = null;
                }
            } catch (e) {
                console.error('Failed to update tag:', e);
            }
        },
        formatDate(dateStr) {
            const date = new Date(dateStr);
            return date.toLocaleDateString('nl-NL', {
                day: 'numeric',
                month: 'short',
                year: 'numeric'
            });
        }
    }
}
</script>
