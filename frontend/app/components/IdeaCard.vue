<script setup lang="ts">
import type { Idea, Tag } from '~/composables/useIdeas'

const props = defineProps<{
  idea: Idea
  tags: Tag[]
}>()

const emit = defineEmits<{
  delete: [id: string]
  edit: [id: string]
  'tag-click': [tagId: string]
}>()

const { getTagsForIdea } = useIdeas()

const ideaTags = computed(() => getTagsForIdea(props.idea))

const formattedDate = computed(() => {
  return new Date(props.idea.createdAt).toLocaleDateString('nl-NL', {
    day: 'numeric',
    month: 'short',
    year: 'numeric'
  })
})
</script>

<template>
  <div class="idea-card">
    <div class="card-actions">
      <button class="edit-btn" @click="emit('edit', idea.id)" title="Bewerken">
        ✎
      </button>
      <button class="delete-btn" @click="emit('delete', idea.id)" title="Verwijderen">
        ×
      </button>
    </div>
    <p class="idea-content">{{ idea.content }}</p>
    <div class="idea-footer">
      <div class="idea-tags">
        <button
          v-for="tag in ideaTags"
          :key="tag.id"
          class="idea-tag"
          :style="{ backgroundColor: tag.color }"
          @click="emit('tag-click', tag.id)"
        >
          <span v-if="tag.emoji" class="tag-emoji">{{ tag.emoji }}</span>
          {{ tag.name }}
        </button>
      </div>
      <span class="idea-date">{{ formattedDate }}</span>
    </div>
  </div>
</template>

<style scoped>
.idea-card {
  position: relative;
  background: white;
  border-radius: 1.25rem;
  padding: 1.25rem;
  box-shadow: 0 4px 15px rgba(200, 170, 220, 0.15);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  animation: slide-up 0.3s ease;
}

@keyframes slide-up {
  0% { opacity: 0; transform: translateY(10px); }
  100% { opacity: 1; transform: translateY(0); }
}

.idea-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(200, 170, 220, 0.25);
}

.card-actions {
  position: absolute;
  top: 0.75rem;
  right: 0.75rem;
  display: flex;
  gap: 0.375rem;
  opacity: 0;
  transition: opacity 0.2s ease;
}

.idea-card:hover .card-actions {
  opacity: 1;
}

.edit-btn,
.delete-btn {
  width: 1.75rem;
  height: 1.75rem;
  border: none;
  border-radius: 50%;
  font-size: 1rem;
  line-height: 1;
  cursor: pointer;
  transition: all 0.2s ease;
}

.edit-btn {
  background: #f0f5fe;
  color: #849ad4;
}

.edit-btn:hover {
  background: #e0ebfc;
  color: #5577cc;
  transform: scale(1.1);
}

.delete-btn {
  background: #fef0f5;
  color: #d4849a;
}

.delete-btn:hover {
  background: #fdd;
  color: #c55;
  transform: scale(1.1);
}

.idea-content {
  color: #4a3a5a;
  line-height: 1.6;
  margin: 0 0 1rem 0;
  white-space: pre-wrap;
  word-break: break-word;
}

.idea-footer {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  gap: 1rem;
}

.idea-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 0.375rem;
}

.idea-tag {
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  padding: 0.25rem 0.65rem;
  border: none;
  border-radius: 2rem;
  font-size: 0.75rem;
  font-weight: 500;
  color: #444;
  cursor: pointer;
  transition: all 0.2s ease;
}

.idea-tag:hover {
  transform: scale(1.05);
  filter: brightness(0.95);
}

.tag-emoji {
  font-size: 0.8rem;
}

.idea-date {
  font-size: 0.75rem;
  color: #b8a5c5;
  white-space: nowrap;
}
</style>
