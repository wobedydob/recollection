<script setup lang="ts">
import type { Tag } from '~/composables/useIdeas'

const props = defineProps<{
  modelValue: string[]
  tags: Tag[]
}>()

const emit = defineEmits<{
  'update:modelValue': [tagIds: string[]]
  'create-tag': []
  'manage-tags': []
}>()

const isOpen = ref(false)
const dropdownRef = ref<HTMLElement>()

const availableTags = computed(() => {
  return props.tags.filter(tag => !props.modelValue.includes(tag.id))
})

const selectedTags = computed(() => {
  return props.tags.filter(tag => props.modelValue.includes(tag.id))
})

function toggleTag(tagId: string) {
  if (props.modelValue.includes(tagId)) {
    emit('update:modelValue', props.modelValue.filter(id => id !== tagId))
  } else {
    emit('update:modelValue', [...props.modelValue, tagId])
  }
}

function removeTag(tagId: string) {
  emit('update:modelValue', props.modelValue.filter(id => id !== tagId))
}

// Close dropdown when clicking outside
function handleClickOutside(e: MouseEvent) {
  if (dropdownRef.value && !dropdownRef.value.contains(e.target as Node)) {
    isOpen.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<template>
  <div class="tag-selector" ref="dropdownRef">
    <div class="selected-tags">
      <TransitionGroup name="tag">
        <span
          v-for="tag in selectedTags"
          :key="tag.id"
          class="tag-pill"
          :style="{ backgroundColor: tag.color }"
        >
          <span v-if="tag.emoji" class="tag-emoji">{{ tag.emoji }}</span>
          {{ tag.name }}
          <button type="button" class="tag-remove" @click="removeTag(tag.id)">×</button>
        </span>
      </TransitionGroup>

      <div class="tag-actions">
        <button
          type="button"
          class="dropdown-toggle"
          @click.stop="isOpen = !isOpen"
          :disabled="availableTags.length === 0"
        >
          <span class="dropdown-icon">▼</span>
          Kies tag
        </button>

        <button type="button" class="add-tag-btn" @click="emit('create-tag')" title="Nieuwe tag maken">
          +
        </button>
        <Transition name="manage-btn">
          <button v-if="props.tags.length" type="button" class="manage-tags-btn" @click="emit('manage-tags')" title="Tags beheren">
            ⚙
          </button>
        </Transition>
      </div>
    </div>

    <Transition name="dropdown">
      <div v-if="isOpen && availableTags.length" class="dropdown">
      <button
        v-for="tag in availableTags"
        :key="tag.id"
        type="button"
        class="dropdown-item"
        @click="toggleTag(tag.id)"
      >
        <span class="tag-preview" :style="{ backgroundColor: tag.color }">
          <span v-if="tag.emoji" class="tag-emoji">{{ tag.emoji }}</span>
          {{ tag.name }}
        </span>
      </button>
      </div>
    </Transition>
  </div>
</template>

<style scoped>
.tag-selector {
  position: relative;
}

.selected-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  padding: 0.75rem;
  background: white;
  border: 2px solid #e8d5f0;
  border-radius: 1rem;
  min-height: 3rem;
  align-items: center;
}

.tag-pill {
  display: inline-flex;
  align-items: center;
  gap: 0.375rem;
  padding: 0.3rem 0.7rem;
  border-radius: 2rem;
  font-size: 0.85rem;
  font-weight: 500;
  color: #444;
}

.tag-emoji {
  font-size: 0.9rem;
}

.tag-remove {
  background: none;
  border: none;
  color: rgba(0, 0, 0, 0.4);
  cursor: pointer;
  font-size: 1.1rem;
  line-height: 1;
  padding: 0;
  margin-left: 0.125rem;
  transition: color 0.2s ease;
}

.tag-remove:hover {
  color: rgba(0, 0, 0, 0.7);
}

.tag-actions {
  display: flex;
  gap: 0.5rem;
  margin-left: auto;
}

.dropdown-toggle {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.4rem 0.75rem;
  background: #f8f0fc;
  border: 1px solid #e8d5f0;
  border-radius: 2rem;
  font-size: 0.85rem;
  color: #7b5a8a;
  cursor: pointer;
  transition: all 0.2s ease;
}

.dropdown-toggle:hover:not(:disabled) {
  background: #f0e5f5;
}

.dropdown-toggle:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.dropdown-icon {
  font-size: 0.65rem;
}

.add-tag-btn {
  width: 2rem;
  height: 2rem;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #f0a5c0 0%, #c9a7d8 100%);
  border: none;
  border-radius: 50%;
  color: white;
  font-size: 1.25rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 2px 8px rgba(200, 170, 220, 0.3);
}

.add-tag-btn:hover {
  transform: scale(1.1);
  box-shadow: 0 4px 12px rgba(200, 170, 220, 0.4);
}

.dropdown {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  margin-top: 0.5rem;
  background: white;
  border: 2px solid #e8d5f0;
  border-radius: 1rem;
  padding: 0.5rem;
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  z-index: 20;
  box-shadow: 0 4px 15px rgba(200, 170, 220, 0.2);
}

.dropdown-item {
  background: none;
  border: none;
  padding: 0;
  cursor: pointer;
  transition: transform 0.15s ease;
}

.dropdown-item:hover {
  transform: scale(1.05);
}

.manage-tags-btn {
  width: 2rem;
  height: 2rem;
  display: flex;
  align-items: center;
  justify-content: center;
  background: transparent;
  border: none;
  border-radius: 50%;
  color: #bbb;
  font-size: 1.25rem;
  cursor: pointer;
  transition: background 0.2s ease, color 0.2s ease;
  overflow: hidden;
  flex-shrink: 0;
}

.manage-tags-btn:hover {
  color: #888;
  background: #f0f0f0;
}

.tag-preview {
  display: inline-flex;
  align-items: center;
  gap: 0.375rem;
  padding: 0.3rem 0.7rem;
  border-radius: 2rem;
  font-size: 0.85rem;
  font-weight: 500;
  color: #444;
}

/* Transitions */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.25s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.dropdown-enter-active {
  transition: all 0.2s ease;
}

.dropdown-leave-active {
  transition: all 0.15s ease;
}

.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}

.tag-enter-active {
  transition: all 0.25s ease;
}

.tag-leave-active {
  transition: all 0.2s ease;
}

.tag-enter-from {
  opacity: 0;
  transform: scale(0.8);
}

.tag-leave-to {
  opacity: 0;
  transform: scale(0.8);
}

.tag-move {
  transition: transform 0.25s ease;
}

.manage-btn-enter-active,
.manage-btn-leave-active {
  transition: all 0.3s ease;
}

.manage-btn-enter-from,
.manage-btn-leave-to {
  opacity: 0;
  width: 0;
  margin-left: -0.5rem;
}
</style>
