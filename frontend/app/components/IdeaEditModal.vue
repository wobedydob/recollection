<script setup lang="ts">
import type { Idea, Tag } from '~/composables/useIdeas'

const props = defineProps<{
  idea: Idea
  tags: Tag[]
}>()

const emit = defineEmits<{
  close: []
  save: [id: string, content: string, tagIds: string[]]
  'create-tag': []
  'manage-tags': []
}>()

const content = ref(props.idea.content)
const selectedTagIds = ref([...props.idea.tagIds])

function handleSubmit() {
  if (!content.value.trim()) return
  emit('save', props.idea.id, content.value, selectedTagIds.value)
}

function addTagId(tagId: string) {
  if (!selectedTagIds.value.includes(tagId)) {
    selectedTagIds.value.push(tagId)
  }
}

defineExpose({ addTagId })
</script>

<template>
  <Teleport to="body">
    <div class="modal-overlay" @click.self="emit('close')">
      <div class="modal">
        <button class="close-btn" @click="emit('close')">×</button>

        <h2 class="modal-title">Idee Bewerken</h2>

        <form @submit.prevent="handleSubmit" class="modal-form">
          <div class="form-group">
            <label class="label">Inhoud</label>
            <textarea
              v-model="content"
              class="textarea"
              placeholder="Wat zit er in je hoofd?..."
              rows="4"
              autofocus
            />
          </div>

          <div class="form-group">
            <label class="label">Tags</label>
            <TagSelector
              v-model="selectedTagIds"
              :tags="tags"
              @create-tag="emit('create-tag')"
              @manage-tags="emit('manage-tags')"
            />
          </div>

          <div class="form-actions">
            <button type="button" class="cancel-btn" @click="emit('close')">
              Annuleren
            </button>
            <button type="submit" class="save-btn" :disabled="!content.trim()">
              Opslaan
            </button>
          </div>
        </form>
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
  max-width: 500px;
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

.modal-form {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.label {
  font-size: 0.875rem;
  font-weight: 500;
  color: #6b5a7a;
}

.textarea {
  width: 100%;
  padding: 1rem;
  border: 2px solid #e8d5f0;
  border-radius: 1rem;
  font-size: 1rem;
  font-family: inherit;
  resize: vertical;
  min-height: 100px;
  color: #4a3a5a;
  transition: border-color 0.2s ease;
}

.textarea:focus {
  outline: none;
  border-color: #c9a7d8;
}

.textarea::placeholder {
  color: #b8a5c5;
}

.form-actions {
  display: flex;
  gap: 0.75rem;
  justify-content: flex-end;
  margin-top: 0.5rem;
}

.cancel-btn {
  padding: 0.75rem 1.25rem;
  background: #f5f0fa;
  border: none;
  border-radius: 2rem;
  color: #7b5a8a;
  font-size: 0.95rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.cancel-btn:hover {
  background: #ebe0f5;
}

.save-btn {
  padding: 0.75rem 1.5rem;
  background: linear-gradient(135deg, #f0a5c0 0%, #c9a7d8 50%, #a5c0f0 100%);
  border: none;
  border-radius: 2rem;
  color: white;
  font-size: 0.95rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 4px 15px rgba(200, 170, 220, 0.3);
}

.save-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(200, 170, 220, 0.4);
}

.save-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>
