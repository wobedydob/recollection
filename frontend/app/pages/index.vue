<script setup lang="ts">
const { isLoggedIn, isLoading } = useAuth()
const router = useRouter()

// Redirect to login if not authenticated
watch([isLoggedIn, isLoading], ([loggedIn, loading]) => {
  if (!loading && !loggedIn) {
    router.push('/login')
  }
}, { immediate: true })

const { ideas, tags, isLoading: isLoadingIdeas, error, loadData, addIdea, updateIdea, deleteIdea, createTag, deleteTag, filterByTag, getTagById, getIdeaById, clearError } = useIdeas()

const newIdeaContent = ref('')
const newIdeaTagIds = ref<string[]>([])
const activeFilter = ref<string | null>(null)
const showTagModal = ref(false)
const showManageModal = ref(false)
const editingIdeaId = ref<string | null>(null)
const editModalRef = ref<{ addTagId: (id: string) => void } | null>(null)

const editingIdea = computed(() => {
  if (!editingIdeaId.value) return null
  return getIdeaById(editingIdeaId.value)
})

const displayedIdeas = computed(() => {
  return filterByTag(activeFilter.value)
})

const activeFilterTag = computed(() => {
  if (!activeFilter.value) return null
  return getTagById(activeFilter.value)
})

async function handleSubmit() {
  if (!newIdeaContent.value.trim()) return
  const result = await addIdea(newIdeaContent.value, newIdeaTagIds.value)
  if (result) {
    // Only clear form on success
    newIdeaContent.value = ''
    newIdeaTagIds.value = []
  }
}

function handleTagClick(tagId: string) {
  activeFilter.value = activeFilter.value === tagId ? null : tagId
}

function clearFilter() {
  activeFilter.value = null
}

async function handleCreateTag(name: string, color: string, emoji?: string) {
  try {
    const newTag = await createTag(name, color, emoji)
    if (newTag?.id) {
      if (editingIdeaId.value && editModalRef.value) {
        editModalRef.value.addTagId(newTag.id)
      } else {
        newIdeaTagIds.value.push(newTag.id)
      }
    }
  } catch (e) {
    console.error('Failed to create tag:', e)
  }
  showTagModal.value = false
}

function handleDeleteTag(tagId: string) {
  newIdeaTagIds.value = newIdeaTagIds.value.filter(id => id !== tagId)
  if (activeFilter.value === tagId) {
    activeFilter.value = null
  }
  deleteTag(tagId)
}

function openManageModal() {
  showManageModal.value = true
}

function openEditModal(ideaId: string) {
  editingIdeaId.value = ideaId
}

function handleSaveEdit(id: string, content: string, tagIds: string[]) {
  updateIdea(id, content, tagIds)
  editingIdeaId.value = null
}

onMounted(() => {
  loadData()
})
</script>

<template>
  <div class="ideas-page" v-if="isLoggedIn">
    <header class="page-header">
      <h1 class="title">Memory Box</h1>
      <p class="subtitle">Een fijne plek voor je ideetjes</p>
    </header>

    <main class="main">
      <form class="idea-form" @submit.prevent="handleSubmit">
        <textarea
          v-model="newIdeaContent"
          class="idea-textarea"
          placeholder="Wat zit er in je hoofd?..."
          rows="3"
        />
        <TagSelector
          v-model="newIdeaTagIds"
          :tags="tags"
          @create-tag="showTagModal = true"
          @manage-tags="openManageModal"
        />
        <button type="submit" class="submit-btn" :disabled="!newIdeaContent.trim()">
          Opslaan in Memory Box
        </button>
        <Transition name="fade">
          <div v-if="error" class="error-message" @click="clearError">
            {{ error }}
            <span class="error-dismiss">×</span>
          </div>
        </Transition>
      </form>

      <Transition name="fade">
        <div v-if="tags.length" class="filter-section">
          <span class="filter-label">Filter op tag:</span>
          <div class="filter-tags">
            <button
              v-for="tag in tags"
              :key="tag.id"
              class="filter-tag"
              :class="{ active: activeFilter === tag.id }"
              :style="activeFilter === tag.id ? { backgroundColor: tag.color } : {}"
              @click="handleTagClick(tag.id)"
            >
              <span v-if="tag.emoji" class="tag-emoji">{{ tag.emoji }}</span>
              {{ tag.name }}
            </button>
            <Transition name="fade">
              <button v-if="activeFilter" class="clear-filter" @click="clearFilter">
                Wissen
              </button>
            </Transition>
          </div>
        </div>
      </Transition>

      <div class="ideas-list">
        <Transition name="fade" mode="out-in">
          <div v-if="isLoadingIdeas" key="loader" class="loader-container">
            <div class="loader"></div>
            <p class="loader-text">Laden...</p>
          </div>

          <div v-else key="content" class="ideas-content">
          <TransitionGroup name="ideas" tag="div" class="ideas-grid">
            <IdeaCard
              v-for="idea in displayedIdeas"
              :key="idea.id"
              :idea="idea"
              :tags="tags"
              @delete="deleteIdea"
              @edit="openEditModal"
              @tag-click="handleTagClick"
            />
          </TransitionGroup>

          <div v-if="!displayedIdeas.length" class="empty-state">
            <div class="empty-icon">✨</div>
            <p v-if="activeFilterTag">Geen ideeën met tag "{{ activeFilterTag.name }}"</p>
            <p v-else>Je memory box is leeg.<br/>Voeg hierboven je eerste idee toe!</p>
          </div>
          </div>
        </Transition>
      </div>
    </main>

    <TagCreateModal
      v-if="showTagModal"
      @close="showTagModal = false"
      @create="handleCreateTag"
    />

    <TagManageModal
      v-if="showManageModal"
      :tags="tags"
      @close="showManageModal = false"
      @delete="handleDeleteTag"
    />

    <IdeaEditModal
      v-if="editingIdea"
      ref="editModalRef"
      :idea="editingIdea"
      :tags="tags"
      @close="editingIdeaId = null"
      @save="handleSaveEdit"
      @create-tag="showTagModal = true"
      @manage-tags="showManageModal = true"
    />
  </div>
</template>

<style scoped>
.ideas-page {
  max-width: 600px;
  margin: 0 auto;
  padding: 2rem 1rem;
}

.page-header {
  text-align: center;
  margin-bottom: 2rem;
}

.title {
  font-size: 2.5rem;
  font-weight: 700;
  background: linear-gradient(135deg, #e879a0 0%, #a87cc9 50%, #79a0e8 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  margin: 0 0 0.5rem 0;
}

.subtitle {
  color: #9b8aab;
  margin: 0;
  font-size: 1rem;
}

.main {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.idea-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  background: rgba(255, 255, 255, 0.7);
  backdrop-filter: blur(10px);
  padding: 1.5rem;
  border-radius: 1.5rem;
  border: 2px solid rgba(232, 213, 240, 0.5);
}

.idea-textarea {
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

.idea-textarea:focus {
  outline: none;
  border-color: #c9a7d8;
}

.idea-textarea::placeholder {
  color: #b8a5c5;
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

.submit-btn:active:not(:disabled) {
  transform: translateY(0);
}

.submit-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.filter-section {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.75rem;
}

.filter-label {
  font-size: 0.875rem;
  color: #8a7a9a;
}

.filter-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.filter-tag {
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  padding: 0.375rem 0.875rem;
  background: white;
  border: 2px solid #e8d5f0;
  border-radius: 2rem;
  font-size: 0.875rem;
  color: #5a4a6a;
  cursor: pointer;
  transition: all 0.2s ease;
}

.filter-tag:hover {
  border-color: #c9a7d8;
  background: #faf5fc;
}

.filter-tag.active {
  border-color: transparent;
  color: #444;
  font-weight: 500;
}

.tag-emoji {
  font-size: 0.9rem;
}

.clear-filter {
  padding: 0.375rem 0.875rem;
  background: #fef0f5;
  border: none;
  border-radius: 2rem;
  font-size: 0.875rem;
  color: #c57a8a;
  cursor: pointer;
  transition: all 0.2s ease;
}

.clear-filter:hover {
  background: #fdd;
}

.ideas-list {
  display: flex;
  flex-direction: column;
}

.ideas-grid {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.ideas-move {
  transition: transform 0.3s ease;
}

.ideas-enter-active {
  transition: all 0.3s ease;
}

.ideas-leave-active {
  transition: all 0.25s ease;
}

.ideas-enter-from {
  opacity: 0;
  transform: translateY(-20px);
}

.ideas-leave-to {
  opacity: 0;
  transform: scale(0.95);
}

.empty-state {
  text-align: center;
  padding: 3rem 1rem;
  color: #a595b5;
}

.empty-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
  animation: float 3s ease-in-out infinite;
}

@keyframes float {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-10px); }
}

.empty-state p {
  margin: 0;
  line-height: 1.6;
}

.loader-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 3rem 1rem;
  gap: 1rem;
}

.loader {
  width: 40px;
  height: 40px;
  border: 3px solid #e8d5f0;
  border-top-color: #c9a7d8;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.loader-text {
  color: #a595b5;
  font-size: 0.95rem;
  margin: 0;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.error-message {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.75rem 1rem;
  background: #fee2e2;
  border: 1px solid #fca5a5;
  border-radius: 0.75rem;
  color: #dc2626;
  font-size: 0.875rem;
  cursor: pointer;
}

.error-dismiss {
  font-size: 1.25rem;
  font-weight: bold;
  opacity: 0.6;
}

.error-dismiss:hover {
  opacity: 1;
}

</style>
