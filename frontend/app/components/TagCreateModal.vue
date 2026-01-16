<script setup lang="ts">
import { TAG_COLORS } from '~/composables/useIdeas'

const emit = defineEmits<{
  close: []
  create: [name: string, color: string, emoji?: string]
}>()

const name = ref('')
const selectedColor = ref(TAG_COLORS[0])
const emoji = ref('')

const EMOJI_SUGGESTIONS = ['✨', '💡', '❤️', '⭐', '🎯', '📝', '🎨', '🌸', '🦋', '🌙', '☀️', '🍀']

function handleSubmit() {
  if (!name.value.trim()) return
  emit('create', name.value, selectedColor.value, emoji.value || undefined)
  // Reset form
  name.value = ''
  selectedColor.value = TAG_COLORS[0]
  emoji.value = ''
}

function selectEmoji(e: string) {
  emoji.value = emoji.value === e ? '' : e
}
</script>

<template>
  <Teleport to="body">
    <div class="modal-overlay" @click.self="emit('close')">
      <div class="modal">
        <button class="close-btn" @click="emit('close')">×</button>

        <h2 class="modal-title">Nieuwe Tag Maken</h2>

        <form @submit.prevent="handleSubmit" class="modal-form">
          <div class="form-group">
            <label class="label">Tag naam <span class="char-count">{{ name.length }}/25</span></label>
            <input
              v-model="name"
              type="text"
              class="input"
              placeholder="bijv. recepten, dromen, doelen..."
              maxlength="25"
              autofocus
            />
          </div>

          <div class="form-group">
            <label class="label">Kleur</label>
            <div class="color-grid">
              <button
                v-for="color in TAG_COLORS"
                :key="color"
                type="button"
                class="color-option"
                :class="{ selected: selectedColor === color }"
                :style="{ backgroundColor: color }"
                @click="selectedColor = color"
              >
                <span v-if="selectedColor === color" class="check">✓</span>
              </button>
            </div>
          </div>

          <div class="form-group">
            <label class="label">Emoji (optioneel)</label>
            <div class="emoji-section">
              <input
                v-model="emoji"
                type="text"
                class="emoji-input"
                placeholder=""
                maxlength="2"
              />
              <div class="emoji-suggestions">
                <button
                  v-for="e in EMOJI_SUGGESTIONS"
                  :key="e"
                  type="button"
                  class="emoji-option"
                  :class="{ selected: emoji === e }"
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
              <span class="tag-preview" :style="{ backgroundColor: selectedColor }">
                <span v-if="emoji" class="tag-emoji">{{ emoji }}</span>
                {{ name || 'tag naam' }}
              </span>
            </div>
          </div>

          <button type="submit" class="submit-btn" :disabled="!name.trim()">
            Tag Maken
          </button>
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
  max-width: 400px;
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
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.char-count {
  font-size: 0.75rem;
  font-weight: 400;
  color: #a595b5;
}

.input {
  padding: 0.75rem 1rem;
  border: 2px solid #e8d5f0;
  border-radius: 0.75rem;
  font-size: 1rem;
  color: #4a3a5a;
  transition: border-color 0.2s ease;
}

.input:focus {
  outline: none;
  border-color: #c9a7d8;
}

.input::placeholder {
  color: #b8a5c5;
}

.color-grid {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 0.5rem;
}

.color-option {
  aspect-ratio: 1;
  border: 3px solid transparent;
  border-radius: 0.75rem;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}

.color-option:hover {
  transform: scale(1.1);
}

.color-option.selected {
  border-color: #6b5a7a;
  transform: scale(1.05);
}

.check {
  color: #4a3a5a;
  font-weight: bold;
  font-size: 0.9rem;
}

.emoji-section {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.emoji-input {
  width: 80px;
  padding: 0.5rem 0.75rem;
  border: 2px solid #e8d5f0;
  border-radius: 0.75rem;
  font-size: 1.25rem;
  text-align: center;
}

.emoji-input:focus {
  outline: none;
  border-color: #c9a7d8;
}

.emoji-suggestions {
  display: flex;
  flex-wrap: wrap;
  gap: 0.375rem;
}

.emoji-option {
  width: 2.25rem;
  height: 2.25rem;
  border: 2px solid #e8d5f0;
  background: white;
  border-radius: 0.5rem;
  font-size: 1.1rem;
  cursor: pointer;
  transition: all 0.15s ease;
}

.emoji-option:hover {
  border-color: #c9a7d8;
  transform: scale(1.1);
}

.emoji-option.selected {
  border-color: #a87cc9;
  background: #f8f0fc;
}

.preview {
  padding: 1rem;
  background: #faf8fc;
  border-radius: 0.75rem;
  text-align: center;
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
}

.tag-emoji {
  font-size: 1rem;
}

.submit-btn {
  padding: 0.875rem 1.5rem;
  background: linear-gradient(135deg, #f0a5c0 0%, #c9a7d8 50%, #a5c0f0 100%);
  border: none;
  border-radius: 2rem;
  color: white;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 4px 15px rgba(200, 170, 220, 0.3);
}

.submit-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(200, 170, 220, 0.4);
}

.submit-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>
