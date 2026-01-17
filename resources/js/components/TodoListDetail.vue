<template>
    <div class="todo-detail">
        <!-- Back Link -->
        <Transition name="form-fade" appear>
            <a href="/todo" class="back-link-header">← Terug naar lijsten</a>
        </Transition>

        <!-- Loading State -->
        <div v-if="isLoading" class="loader-container">
            <div class="loader"></div>
            <p class="loader-text">Laden...</p>
        </div>

        <template v-if="!isLoading && list">
            <!-- Header Form Box -->
            <Transition name="form-fade" appear>
            <div class="todo-header-box">
                <div class="todo-title-section">
                    <template v-if="!isEditingTitle">
                        <h1 class="todo-title">
                            <span v-if="list.emoji" class="title-emoji">{{ list.emoji }}</span>
                            {{ list.name }}
                        </h1>
                        <button class="title-edit-btn" @click="startEditTitle" title="Bewerken">✎</button>
                    </template>
                    <template v-else>
                        <input
                            v-model="editName"
                            type="text"
                            class="title-edit-input"
                            @keyup.enter="saveTitle"
                            @keyup.escape="cancelEditTitle"
                            ref="titleInput"
                        />
                        <button class="title-save-btn" @click="saveTitle">✓</button>
                        <button class="title-cancel-btn" @click="cancelEditTitle">×</button>
                    </template>
                </div>
                <p class="todo-count">
                    <template v-if="list.items.length > 0 && completedCount === list.items.length">
                        <span class="todo-all-done">🎉 Alles voltooid!</span>
                    </template>
                    <template v-else>
                        {{ list.items.length }} {{ list.items.length === 1 ? 'taak' : 'taken' }}<span v-if="completedCount > 0" class="todo-completed-count"> · {{ completedCount }} voltooid</span>
                    </template>
                </p>

                <form class="todo-form-inner" @submit.prevent="addItem">
                    <div class="todo-form-row">
                        <input
                            v-model="newItemTitle"
                            type="text"
                            class="todo-input"
                            placeholder="Wat moet er gebeuren?..."
                        />
                        <div class="priority-selector">
                            <button
                                type="button"
                                class="priority-option priority-low"
                                :class="{ active: newItemPriority === 'low' }"
                                @click="newItemPriority = 'low'"
                                title="Lage prioriteit"
                            >L</button>
                            <button
                                type="button"
                                class="priority-option priority-medium"
                                :class="{ active: newItemPriority === 'medium' }"
                                @click="newItemPriority = 'medium'"
                                title="Normale prioriteit"
                            >M</button>
                            <button
                                type="button"
                                class="priority-option priority-high"
                                :class="{ active: newItemPriority === 'high' }"
                                @click="newItemPriority = 'high'"
                                title="Hoge prioriteit"
                            >H</button>
                        </div>
                    </div>
                    <textarea
                        v-model="newItemDescription"
                        class="todo-description-input"
                        placeholder="Beschrijving (optioneel)..."
                        rows="2"
                    ></textarea>
                    <button type="submit" class="submit-btn" :disabled="!newItemTitle.trim()">
                        Toevoegen
                    </button>
                </form>
            </div>
            </Transition>

            <!-- Items List -->
            <TransitionGroup name="todo-task" tag="div" class="todo-items-list" appear v-if="sortedItems.length > 0">
                <div
                    v-for="item in sortedItems"
                    :key="item.id"
                    class="todo-item-card"
                    :class="{ completed: item.isCompleted, [`priority-${item.priority}`]: true }"
                >
                    <div class="card-actions">
                        <button class="edit-btn" @click="startEditItem(item)" title="Bewerken">✎</button>
                        <button class="delete-btn" @click="deleteItem(item.id)" title="Verwijderen">×</button>
                    </div>

                    <div class="todo-item-main" @click="toggleItem(item.id)">
                        <div class="todo-checkbox" :class="{ checked: item.isCompleted }">
                            <span v-if="item.isCompleted" class="check-icon">✓</span>
                        </div>
                        <div class="todo-item-content">
                            <span class="todo-item-title">{{ item.title }}</span>
                            <p v-if="item.description" class="todo-item-description">{{ item.description }}</p>
                        </div>
                    </div>

                    <div class="todo-item-footer">
                        <span class="priority-badge" :class="`priority-${item.priority}`">
                            {{ priorityLabel(item.priority) }}
                        </span>
                        <span v-if="item.dueDate" class="due-date">{{ formatDate(item.dueDate) }}</span>
                    </div>
                </div>
            </TransitionGroup>

            <!-- Empty State -->
            <Transition name="ideas-appear">
                <div v-if="!isLoading && sortedItems.length === 0" class="empty-state">
                    <div class="empty-icon">📋</div>
                    <p>Nog geen taken.<br/>Voeg hierboven je eerste taak toe!</p>
                </div>
            </Transition>

            <!-- List Settings -->
            <div class="list-settings">
                <button class="delete-list-btn" @click="confirmDeleteList">
                    Lijst verwijderen
                </button>
            </div>
        </template>

        <!-- Edit Item Modal -->
        <div v-if="editingItem" class="modal-overlay" @click.self="cancelEditItem">
            <div class="modal todo-edit-modal">
                <h3 class="modal-title">Taak bewerken</h3>
                <div class="todo-form-inner">
                    <div class="todo-form-row">
                        <input
                            v-model="editItemTitle"
                            type="text"
                            class="todo-input"
                            placeholder="Wat moet er gebeuren?..."
                            ref="editItemInput"
                        />
                        <div class="priority-selector">
                            <button
                                type="button"
                                class="priority-option priority-low"
                                :class="{ active: editItemPriority === 'low' }"
                                @click="editItemPriority = 'low'"
                            >L</button>
                            <button
                                type="button"
                                class="priority-option priority-medium"
                                :class="{ active: editItemPriority === 'medium' }"
                                @click="editItemPriority = 'medium'"
                            >M</button>
                            <button
                                type="button"
                                class="priority-option priority-high"
                                :class="{ active: editItemPriority === 'high' }"
                                @click="editItemPriority = 'high'"
                            >H</button>
                        </div>
                    </div>
                    <textarea
                        v-model="editItemDescription"
                        class="todo-description-input"
                        placeholder="Beschrijving (optioneel)..."
                        rows="3"
                    ></textarea>
                </div>
                <div class="modal-actions">
                    <button class="btn btn-secondary" @click="cancelEditItem">Annuleren</button>
                    <button class="btn btn-primary" @click="saveEditItem" :disabled="!editItemTitle.trim()">Opslaan</button>
                </div>
            </div>
        </div>

        <!-- Delete List Confirmation -->
        <div v-if="showDeleteConfirm" class="modal-overlay" @click.self="showDeleteConfirm = false">
            <div class="modal">
                <h3 class="modal-title">Lijst verwijderen?</h3>
                <p>Weet je zeker dat je "{{ list?.name }}" wilt verwijderen? Alle taken worden ook verwijderd.</p>
                <div class="modal-actions">
                    <button class="btn btn-secondary" @click="showDeleteConfirm = false">Annuleren</button>
                    <button class="btn btn-primary" @click="deleteList">Verwijderen</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'TodoListDetail',
    props: {
        listId: {
            type: Number,
            required: true
        }
    },
    data() {
        return {
            list: null,
            isLoading: true,
            isEditingTitle: false,
            editName: '',
            newItemTitle: '',
            newItemDescription: '',
            newItemPriority: 'medium',
            showDeleteConfirm: false,
            editingItem: null,
            editItemTitle: '',
            editItemDescription: '',
            editItemPriority: 'medium'
        };
    },
    computed: {
        sortedItems() {
            if (!this.list?.items) return [];
            return [...this.list.items].sort((a, b) => {
                if (a.isCompleted !== b.isCompleted) {
                    return a.isCompleted ? 1 : -1;
                }
                return a.position - b.position;
            });
        },
        completedCount() {
            if (!this.list?.items) return 0;
            return this.list.items.filter(item => item.isCompleted).length;
        }
    },
    async mounted() {
        await this.loadList();
    },
    methods: {
        priorityLabel(priority) {
            const labels = { low: 'Laag', medium: 'Normaal', high: 'Hoog' };
            return labels[priority] || 'Normaal';
        },
        formatDate(dateStr) {
            const date = new Date(dateStr);
            return date.toLocaleDateString('nl-NL', {
                day: 'numeric',
                month: 'short'
            });
        },
        async loadList() {
            this.isLoading = true;
            try {
                const res = await fetch(`/api/todo/lists/${this.listId}`, { credentials: 'include' });
                if (res.ok) {
                    const data = await res.json();
                    this.list = data.list;
                } else {
                    window.location.href = '/todo';
                }
            } catch (e) {
                console.error('Failed to load list:', e);
                window.location.href = '/todo';
            } finally {
                this.isLoading = false;
            }
        },
        startEditTitle() {
            this.isEditingTitle = true;
            this.editName = this.list.name;
            this.$nextTick(() => {
                this.$refs.titleInput?.focus();
            });
        },
        async saveTitle() {
            if (!this.editName.trim()) return;
            try {
                const res = await fetch(`/api/todo/lists/${this.list.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'include',
                    body: JSON.stringify({ name: this.editName.trim() })
                });
                if (res.ok) {
                    const data = await res.json();
                    this.list.name = data.list.name;
                }
            } catch (e) {
                console.error('Failed to update list:', e);
            }
            this.isEditingTitle = false;
        },
        cancelEditTitle() {
            this.isEditingTitle = false;
            this.editName = '';
        },
        confirmDeleteList() {
            this.showDeleteConfirm = true;
        },
        async deleteList() {
            try {
                const res = await fetch(`/api/todo/lists/${this.list.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'include'
                });
                if (res.ok) {
                    window.location.href = '/todo';
                }
            } catch (e) {
                console.error('Failed to delete list:', e);
            }
        },
        async addItem() {
            if (!this.newItemTitle.trim()) return;
            try {
                const res = await fetch(`/api/todo/lists/${this.list.id}/items`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'include',
                    body: JSON.stringify({
                        title: this.newItemTitle.trim(),
                        description: this.newItemDescription.trim() || null,
                        priority: this.newItemPriority
                    })
                });
                if (res.ok) {
                    const data = await res.json();
                    this.list.items.push(data.item);
                    this.newItemTitle = '';
                    this.newItemDescription = '';
                    this.newItemPriority = 'medium';
                }
            } catch (e) {
                console.error('Failed to add item:', e);
            }
        },
        startEditItem(item) {
            this.editingItem = item;
            this.editItemTitle = item.title;
            this.editItemDescription = item.description || '';
            this.editItemPriority = item.priority;
            this.$nextTick(() => {
                this.$refs.editItemInput?.focus();
            });
        },
        cancelEditItem() {
            this.editingItem = null;
            this.editItemTitle = '';
            this.editItemDescription = '';
            this.editItemPriority = 'medium';
        },
        async saveEditItem() {
            if (!this.editItemTitle.trim()) return;
            try {
                const res = await fetch(`/api/todo/items/${this.editingItem.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'include',
                    body: JSON.stringify({
                        title: this.editItemTitle.trim(),
                        description: this.editItemDescription.trim() || null,
                        priority: this.editItemPriority
                    })
                });
                if (res.ok) {
                    const data = await res.json();
                    const idx = this.list.items.findIndex(i => i.id === this.editingItem.id);
                    if (idx !== -1) {
                        this.list.items[idx] = data.item;
                    }
                }
            } catch (e) {
                console.error('Failed to update item:', e);
            }
            this.cancelEditItem();
        },
        async deleteItem(itemId) {
            try {
                const res = await fetch(`/api/todo/items/${itemId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'include'
                });
                if (res.ok) {
                    this.list.items = this.list.items.filter(i => i.id !== itemId);
                }
            } catch (e) {
                console.error('Failed to delete item:', e);
            }
        },
        async toggleItem(itemId) {
            try {
                const res = await fetch(`/api/todo/items/${itemId}/toggle`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'include'
                });
                if (res.ok) {
                    const data = await res.json();
                    const idx = this.list.items.findIndex(i => i.id === itemId);
                    if (idx !== -1) {
                        this.list.items[idx] = data.item;
                    }
                }
            } catch (e) {
                console.error('Failed to toggle item:', e);
            }
        }
    }
};
</script>
