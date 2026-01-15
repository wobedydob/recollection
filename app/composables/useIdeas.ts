export interface Tag {
  id: string
  name: string
  color: string
  emoji?: string
}

export interface Idea {
  id: string
  content: string
  tagIds: string[]
  createdAt: number
}

// Predefined color palette (cute pastels)
export const TAG_COLORS = [
  '#ffb3ba', // pink
  '#ffdfba', // peach
  '#ffffba', // yellow
  '#baffc9', // mint
  '#bae1ff', // sky blue
  '#e0bbff', // lavender
  '#ffc8dd', // rose
  '#bde0fe', // light blue
  '#a2d2ff', // periwinkle
  '#cdb4db', // mauve
]

export function useIdeas() {
  const ideas = useState<Idea[]>('ideas', () => [])
  const tags = useState<Tag[]>('tags', () => [])
  const isLoading = useState('ideas-loading', () => true)

  // Load data from API
  async function loadData() {
    isLoading.value = true
    const minLoadTime = new Promise(resolve => setTimeout(resolve, 400))
    try {
      const [ideasRes, tagsRes] = await Promise.all([
        $fetch<{ ideas: Idea[] }>('/api/ideas'),
        $fetch<{ tags: Tag[] }>('/api/tags'),
        minLoadTime
      ])
      ideas.value = ideasRes.ideas
      tags.value = tagsRes.tags
    } catch (e) {
      // Not logged in or error - reset data
      await minLoadTime
      ideas.value = []
      tags.value = []
    } finally {
      isLoading.value = false
    }
  }

  // Clear all data (used on logout)
  function clearData() {
    ideas.value = []
    tags.value = []
  }

  // Tag management
  async function createTag(name: string, color: string, emoji?: string): Promise<Tag> {
    const response = await $fetch<{ tag: Tag }>('/api/tags', {
      method: 'POST',
      body: { name, color, emoji }
    })
    tags.value.push(response.tag)
    return response.tag
  }

  async function deleteTag(id: string) {
    await $fetch(`/api/tags/${id}`, { method: 'DELETE' })
    tags.value = tags.value.filter(t => t.id !== id)
    // Remove tag from all ideas in local state
    ideas.value.forEach(idea => {
      idea.tagIds = idea.tagIds.filter(tid => tid !== id)
    })
  }

  function getTagById(id: string): Tag | undefined {
    return tags.value.find(t => t.id === id)
  }

  function getTagsForIdea(idea: Idea): Tag[] {
    return idea.tagIds.map(id => getTagById(id)).filter((t): t is Tag => !!t)
  }

  // Idea management
  async function addIdea(content: string, tagIds: string[] = []): Promise<Idea> {
    const response = await $fetch<{ idea: Idea }>('/api/ideas', {
      method: 'POST',
      body: { content, tagIds }
    })
    ideas.value.unshift(response.idea)
    return response.idea
  }

  async function updateIdea(id: string, content: string, tagIds: string[]) {
    await $fetch(`/api/ideas/${id}`, {
      method: 'PUT',
      body: { content, tagIds }
    })
    const idea = ideas.value.find(i => i.id === id)
    if (idea) {
      idea.content = content
      idea.tagIds = tagIds
    }
  }

  async function deleteIdea(id: string) {
    await $fetch(`/api/ideas/${id}`, { method: 'DELETE' })
    ideas.value = ideas.value.filter(idea => idea.id !== id)
  }

  function getIdeaById(id: string): Idea | undefined {
    return ideas.value.find(i => i.id === id)
  }

  // Filter ideas by tag
  function filterByTag(tagId: string | null) {
    if (!tagId) return ideas.value
    return ideas.value.filter(idea => idea.tagIds.includes(tagId))
  }

  return {
    ideas: readonly(ideas),
    tags: readonly(tags),
    isLoading: readonly(isLoading),
    loadData,
    clearData,
    createTag,
    deleteTag,
    getTagById,
    getTagsForIdea,
    addIdea,
    updateIdea,
    deleteIdea,
    getIdeaById,
    filterByTag
  }
}
