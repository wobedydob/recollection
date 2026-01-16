<script setup lang="ts">
import type { Tag } from '~/composables/useIdeas'

const props = defineProps<{
  tags: Tag[]
}>()

const emit = defineEmits<{
  close: []
  delete: [tagId: string]
}>()

const tagToDelete = ref<string | null>(null)

const tagToDeleteName = computed(() => {
  if (!tagToDelete.value) return ''
  const tag = props.tags.find(t => t.id === tagToDelete.value)
  return tag?.name || ''
})

function confirmDelete(tagId: string) {
  tagToDelete.value = tagId
}

function cancelDelete() {
  tagToDelete.value = null
}

function doDelete(tagId: string) {
  emit('delete', tagId)
  tagToDelete.value = null
}
</script>

<template>
  <Teleport to="body">
    <div class="modal-overlay" @click.self="emit('close')">
      <div class="modal">
        <button class="close-btn" @click="emit('close')">×</button>

        <h2 class="modal-title">Tags Beheren</h2>

        <div v-if="tags.length" class="tags-list">
          <div v-for="tag in tags" :key="tag.id" class="tag-item">
            <div class="tag-info">
              <span class="tag-preview" :style="{ backgroundColor: tag.color }">
                <span v-if="tag.emoji" class="tag-emoji">{{ tag.emoji }}</span>
                {{ tag.name }}
              </span>
            </div>
            <button
              class="delete-btn"
              @click="confirmDelete(tag.id)"
              title="Tag verwijderen"
            >
              ×
            </button>
          </div>
        </div>
        <div v-else class="empty-state">
          <p>Nog geen tags. Maak er een aan bij het toevoegen van een idee!</p>
        </div>

        <!-- Delete confirmation modal -->
        <Teleport to="body">
          <Transition name="fade">
            <div v-if="tagToDelete" class="confirm-overlay" @click.self="cancelDelete">
              <div class="confirm-modal">
                <p class="confirm-text">
                  Tag "{{ tagToDeleteName }}" verwijderen?
                </p>
                <div class="confirm-actions">
                  <button class="confirm-no" @click="cancelDelete">Nee</button>
                  <button class="confirm-yes" @click="doDelete(tagToDelete)">Ja, verwijderen</button>
                </div>
              </div>
            </div>
          </Transition>
        </Teleport>
      </div>
    </div>
  </Teleport>
</template>

<style scoped>
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(100, 80, 120, 0.4);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 100;
  animation: fade-in 0.2s ease;
}

@keyframes fade-in {
  from { opacity: 0; }
  to { opacity: 1; }
}

.modal {
  background: white;
  border-radius: 1.5rem;
  padding: 2rem;
  width: 90%;
  max-width: 400px;
  max-height: 80vh;
  overflow-y: auto;
  position: relative;
  box-shadow: 0 10px 40px rgba(100, 80, 120, 0.3);
  animation: slide-up 0.25s ease;
}

@keyframes slide-up {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

.close-btn {
  position: absolute;
  top: 1rem;
  right: 1rem;
  width: 2rem;
  height: 2rem;
  border: none;
  background: #fef0f5;
  color: #c57a8a;
  border-radius: 50%;
  font-size: 1.25rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.close-btn:hover {
  background: #fdd;
  transform: scale(1.1);
}

.modal-title {
  margin: 0 0 1.5rem 0;
  font-size: 1.5rem;
  font-weight: 600;
  background: linear-gradient(135deg, #e879a0 0%, #a87cc9 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.tags-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.tag-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.75rem;
  background: #faf8fc;
  border-radius: 0.75rem;
  transition: background 0.2s ease;
}

.tag-item:hover {
  background: #f5f0fa;
}

.tag-info {
  flex: 1;
  min-width: 0;
}

.tag-preview {
  display: inline-flex;
  align-items: center;
  gap: 0.375rem;
  padding: 0.4rem 0.9rem;
  border-radius: 2rem;
  font-size: 0.9rem;
  font-weight: 500;
  color: #444;
  max-width: 100%;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.tag-emoji {
  font-size: 1rem;
  flex-shrink: 0;
}

.delete-btn {
  width: 1.75rem;
  height: 1.75rem;
  display: flex;
  align-items: center;
  justify-content: center;
  background: transparent;
  border: none;
  border-radius: 50%;
  font-size: 1.25rem;
  color: #ccc;
  cursor: pointer;
  transition: all 0.2s ease;
  flex-shrink: 0;
  margin-left: 0.5rem;
}

.delete-btn:hover {
  background: #fee;
  color: #e55;
}

/* Confirmation modal */
.confirm-overlay {
  position: fixed;
  inset: 0;
  background: rgba(100, 80, 120, 0.5);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 200;
}

.confirm-modal {
  background: white;
  border-radius: 1rem;
  padding: 1.5rem;
  width: 90%;
  max-width: 300px;
  text-align: center;
  box-shadow: 0 10px 40px rgba(100, 80, 120, 0.3);
  animation: pop-in 0.2s ease;
}

@keyframes pop-in {
  from { opacity: 0; transform: scale(0.95); }
  to { opacity: 1; transform: scale(1); }
}

.confirm-text {
  font-size: 1rem;
  color: #5a4a6a;
  margin: 0 0 1.25rem 0;
  word-break: break-word;
}

.confirm-actions {
  display: flex;
  gap: 0.75rem;
  justify-content: center;
}

.confirm-yes,
.confirm-no {
  padding: 0.6rem 1.25rem;
  border: none;
  border-radius: 2rem;
  font-size: 0.9rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.confirm-yes {
  background: linear-gradient(135deg, #e88a9a 0%, #d77a8a 100%);
  color: white;
  box-shadow: 0 2px 8px rgba(200, 100, 120, 0.3);
}

.confirm-yes:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(200, 100, 120, 0.4);
}

.confirm-no {
  background: #f0eaf5;
  color: #7b5a8a;
}

.confirm-no:hover {
  background: #e8e0f0;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.empty-state {
  text-align: center;
  padding: 2rem 1rem;
  color: #a595b5;
}

.empty-state p {
  margin: 0;
}
</style>
