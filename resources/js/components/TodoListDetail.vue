<template>
    <div class="todo-detail">
        <!-- Back Link -->
        <Transition name="form-fade" appear>
            <a href="/checklist" class="back-link-header">← {{ t('todos.back_to_lists') }}</a>
        </Transition>

        <!-- Loading State -->
        <div v-if="isLoading" class="loader-container">
            <div class="loader"></div>
            <p class="loader-text">{{ t('common.loading') }}</p>
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
                        <button class="title-edit-btn" @click="startEditTitle" v-tooltip="t('common.edit')">✎</button>
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
                        <span class="todo-all-done">🎉 {{ t('todos.all_completed') }}</span>
                    </template>
                    <template v-else>
                        {{ list.items.length }} {{ list.items.length === 1 ? t('todos.task') : t('todos.tasks') }}<span v-if="completedCount > 0" class="todo-completed-count"> · {{ completedCount }} {{ t('todos.completed') }}</span>
                    </template>
                </p>

                <form class="todo-form-inner" @submit.prevent="addItem">
                    <div class="todo-form-row">
                        <input
                            v-model="newItemTitle"
                            type="text"
                            class="todo-input"
                            :placeholder="t('todos.task_placeholder')"
                        />
                        <div class="priority-selector">
                            <button
                                type="button"
                                class="priority-option priority-low"
                                :class="{ active: newItemPriority === 'low' }"
                                @click="newItemPriority = 'low'"
                                v-tooltip="t('todos.priority_low')"
                            >L</button>
                            <button
                                type="button"
                                class="priority-option priority-medium"
                                :class="{ active: newItemPriority === 'medium' }"
                                @click="newItemPriority = 'medium'"
                                v-tooltip="t('todos.priority_normal')"
                            >M</button>
                            <button
                                type="button"
                                class="priority-option priority-high"
                                :class="{ active: newItemPriority === 'high' }"
                                @click="newItemPriority = 'high'"
                                v-tooltip="t('todos.priority_high')"
                            >H</button>
                        </div>
                    </div>
                    <textarea
                        v-model="newItemDescription"
                        class="todo-description-input"
                        :placeholder="t('todos.description_placeholder')"
                        rows="2"
                    ></textarea>
                    <button type="submit" class="submit-btn" :disabled="!newItemTitle.trim()">
                        {{ t('todos.add') }}
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
                        <button class="edit-btn" @click="startEditItem(item)" v-tooltip="t('common.edit')">✎</button>
                        <button class="delete-btn" @click="deleteItem(item.id)" v-tooltip="t('common.delete')">×</button>
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
                    <p v-html="t('todos.empty_tasks')"></p>
                </div>
            </Transition>

            <!-- List Settings -->
            <div class="list-settings">
                <button class="delete-list-btn" @click="confirmDeleteList">
                    {{ t('common.delete') }}
                </button>
            </div>
        </template>

        <!-- Edit Item Modal -->
        <div v-if="editingItem" class="modal-overlay" @click.self="cancelEditItem">
            <div class="modal todo-edit-modal">
                <h3 class="modal-title">{{ t('todos.edit_task') }}</h3>
                <div class="todo-form-inner">
                    <div class="todo-form-row">
                        <input
                            v-model="editItemTitle"
                            type="text"
                            class="todo-input"
                            :placeholder="t('todos.task_placeholder')"
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
                        :placeholder="t('todos.description_placeholder')"
                        rows="3"
                    ></textarea>
                </div>
                <div class="modal-actions">
                    <button class="btn btn-secondary" @click="cancelEditItem">{{ t('common.cancel') }}</button>
                    <button class="btn btn-primary" @click="saveEditItem" :disabled="!editItemTitle.trim()">{{ t('common.save') }}</button>
                </div>
            </div>
        </div>

        <!-- Delete List Confirmation -->
        <div v-if="showDeleteConfirm" class="modal-overlay" @click.self="showDeleteConfirm = false">
            <div class="modal">
                <h3 class="modal-title">{{ t('todos.delete_list') }}</h3>
                <p>{{ t('todos.delete_list_warning') }}</p>
                <div class="modal-actions">
                    <button class="btn btn-secondary" @click="showDeleteConfirm = false">{{ t('common.cancel') }}</button>
                    <button class="btn btn-primary" @click="deleteList">{{ t('common.delete') }}</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { __, getLocale } from '@/utils/translations';

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
        t(key, replacements = {}) {
            return __(key, replacements);
        },
        priorityLabel(priority) {
            const labels = {
                low: this.t('todos.priority_low'),
                medium: this.t('todos.priority_normal'),
                high: this.t('todos.priority_high')
            };
            return labels[priority] || this.t('todos.priority_normal');
        },
        formatDate(dateStr) {
            const date = new Date(dateStr);
            const locale = getLocale() === 'nl' ? 'nl-NL' : 'en-US';
            return date.toLocaleDateString(locale, {
                day: 'numeric',
                month: 'short'
            });
        },
        async loadList() {
            this.isLoading = true;
            try {
                const res = await fetch(`/api/checklist/lists/${this.listId}`, { credentials: 'include' });
                if (res.ok) {
                    const data = await res.json();
                    this.list = data.list;
                } else {
                    window.location.href = '/checklist';
                }
            } catch (e) {
                console.error('Failed to load list:', e);
                window.location.href = '/checklist';
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
                const res = await fetch(`/api/checklist/lists/${this.list.id}`, {
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
                const res = await fetch(`/api/checklist/lists/${this.list.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'include'
                });
                if (res.ok) {
                    window.location.href = '/checklist';
                }
            } catch (e) {
                console.error('Failed to delete list:', e);
            }
        },
        async addItem() {
            if (!this.newItemTitle.trim()) return;
            try {
                const res = await fetch(`/api/checklist/lists/${this.list.id}/items`, {
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
                const res = await fetch(`/api/checklist/items/${this.editingItem.id}`, {
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
                const res = await fetch(`/api/checklist/items/${itemId}`, {
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
                const res = await fetch(`/api/checklist/items/${itemId}/toggle`, {
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
