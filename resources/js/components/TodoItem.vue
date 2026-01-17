<template>
    <div class="todo-item" :class="{ completed: item.isCompleted, [`priority-${item.priority}`]: true }">
        <div class="todo-item-checkbox" @click="$emit('toggle')">
            <span v-if="item.isCompleted" class="check-icon">✓</span>
        </div>
        <div class="todo-item-content">
            <template v-if="!isEditing">
                <span class="todo-item-title">{{ item.title }}</span>
                <span v-if="item.dueDate" class="todo-item-due">{{ formatDate(item.dueDate) }}</span>
            </template>
            <template v-else>
                <input
                    v-model="editTitle"
                    type="text"
                    class="edit-item-input"
                    @keyup.enter="saveEdit"
                    @keyup.escape="cancelEdit"
                    ref="editInput"
                />
            </template>
        </div>
        <div class="todo-item-actions">
            <template v-if="!isEditing">
                <span class="priority-indicator" :title="priorityLabel">{{ priorityIcon }}</span>
                <button class="edit-btn" @click="startEdit" title="Bewerken">✎</button>
                <button class="delete-btn" @click="$emit('delete')" title="Verwijderen">×</button>
            </template>
            <template v-else>
                <select v-model="editPriority" class="priority-select">
                    <option value="low">Laag</option>
                    <option value="medium">Normaal</option>
                    <option value="high">Hoog</option>
                </select>
                <button class="save-btn" @click="saveEdit" title="Opslaan">✓</button>
                <button class="cancel-btn" @click="cancelEdit" title="Annuleren">×</button>
            </template>
        </div>
    </div>
</template>

<script>
export default {
    name: 'TodoItem',
    props: {
        item: {
            type: Object,
            required: true
        },
        listId: {
            type: Number,
            required: true
        }
    },
    emits: ['toggle', 'update', 'delete'],
    data() {
        return {
            isEditing: false,
            editTitle: '',
            editPriority: 'medium'
        };
    },
    computed: {
        priorityIcon() {
            const icons = { low: '○', medium: '◐', high: '●' };
            return icons[this.item.priority] || '◐';
        },
        priorityLabel() {
            const labels = { low: 'Laag', medium: 'Normaal', high: 'Hoog' };
            return labels[this.item.priority] || 'Normaal';
        }
    },
    methods: {
        startEdit() {
            this.isEditing = true;
            this.editTitle = this.item.title;
            this.editPriority = this.item.priority;
            this.$nextTick(() => {
                this.$refs.editInput?.focus();
            });
        },
        saveEdit() {
            if (this.editTitle.trim()) {
                this.$emit('update', {
                    ...this.item,
                    listId: this.listId,
                    title: this.editTitle.trim(),
                    priority: this.editPriority
                });
            }
            this.isEditing = false;
        },
        cancelEdit() {
            this.isEditing = false;
            this.editTitle = '';
        },
        formatDate(dateStr) {
            const date = new Date(dateStr);
            return date.toLocaleDateString('nl-NL', {
                day: 'numeric',
                month: 'short'
            });
        }
    }
};
</script>
