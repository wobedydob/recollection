<template>
    <div class="todo-app">
        <!-- Add List Form -->
        <Transition name="form-fade" appear>
            <div class="add-list-form">
                <input
                    v-model="newListName"
                    type="text"
                    class="list-input"
                    :placeholder="t('todos.new_list')"
                    @keyup.enter="createList"
                />
                <button class="add-list-btn" @click="createList" :disabled="!newListName.trim()" v-tooltip="t('todos.add_list')">+</button>
            </div>
        </Transition>

        <!-- Loading State -->
        <div v-if="isLoading" class="loader-container">
            <div class="loader"></div>
            <p class="loader-text">{{ t('common.loading') }}</p>
        </div>

        <!-- Lists Overview -->
        <TransitionGroup name="todo-item" tag="div" class="todo-lists-grid" appear v-if="!isLoading && lists.length > 0">
            <a
                v-for="list in lists"
                :key="list.id"
                :href="`/checklist/${list.id}`"
                class="todo-list-card-link"
            >
                <div class="todo-list-card">
                    <div class="todo-list-card-header">
                        <span v-if="list.emoji" class="todo-list-emoji">{{ list.emoji }}</span>
                        <h3 class="todo-list-name">{{ list.name }}</h3>
                    </div>
                    <p class="todo-list-count">
                        {{ list.itemCount }} {{ list.itemCount === 1 ? t('todos.task') : t('todos.tasks') }}
                    </p>
                </div>
            </a>
        </TransitionGroup>

        <!-- Empty State -->
        <Transition name="ideas-appear">
            <div v-if="!isLoading && lists.length === 0" class="empty-state">
                <div class="empty-icon">📋</div>
                <p v-html="t('todos.empty')"></p>
            </div>
        </Transition>
    </div>
</template>

<script>
import { __ } from '@/utils/translations';

export default {
    name: 'TodoApp',
    data() {
        return {
            lists: [],
            newListName: '',
            isLoading: true
        };
    },
    async mounted() {
        await this.loadLists();
    },
    methods: {
        t(key, replacements = {}) {
            return __(key, replacements);
        },
        async loadLists() {
            this.isLoading = true;
            try {
                const res = await fetch('/api/checklist/lists', { credentials: 'include' });
                const data = await res.json();
                this.lists = Array.isArray(data.lists) ? data.lists : [];
            } catch (e) {
                console.error('Failed to load lists:', e);
                this.lists = [];
            } finally {
                this.isLoading = false;
            }
        },
        async createList() {
            if (!this.newListName.trim()) return;
            try {
                const res = await fetch('/api/checklist/lists', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'include',
                    body: JSON.stringify({ name: this.newListName })
                });
                if (res.ok) {
                    const data = await res.json();
                    // Redirect to the new list
                    window.location.href = `/checklist/${data.list.id}`;
                }
            } catch (e) {
                console.error('Failed to create list:', e);
            }
        }
    }
};
</script>
