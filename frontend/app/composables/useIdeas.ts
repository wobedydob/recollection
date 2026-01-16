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
  const config = useRuntimeConfig()
  const apiBase = config.public.apiBase
  const ideas = useState<Idea[]>('ideas', () => [])
  const tags = useState<Tag[]>('tags', () => [])
  const isLoading = useState('ideas-loading', () => true)
  const error = useState<string | null>('ideas-error', () => null)

  // Load data from API
  async function loadData() {
    isLoading.value = true
    const minLoadTime = new Promise(resolve => setTimeout(resolve, 400))
    try {
      const [ideasRes, tagsRes] = await Promise.all([
        $fetch<{ ideas: Idea[] }>(`${apiBase}/ideas`, { credentials: 'include' }),
        $fetch<{ tags: Tag[] }>(`${apiBase}/tags`, { credentials: 'include' }),
        minLoadTime
      ])
      // Filter out any invalid items
      ideas.value = (ideasRes?.ideas || []).filter(i => i?.id)
      tags.value = (tagsRes?.tags || []).filter(t => t?.id)
    } catch (e) {
      // Not logged in or error - reset data
      console.error('Failed to load data:', e)
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
  async function createTag(name: string, color: string, emoji?: string): Promise<Tag | undefined> {
    try {
      const response = await $fetch<{ tag: Tag }>(`${apiBase}/tags`, {
        method: 'POST',
        body: { name, color, emoji },
        credentials: 'include'
      })
      if (response?.tag?.id) {
        tags.value.push(response.tag)
        return response.tag
      }
    } catch (e) {
      console.error('Failed to create tag:', e)
    }
    return undefined
  }

  async function deleteTag(id: string) {
    await $fetch(`${apiBase}/tags/${id}`, { method: 'DELETE', credentials: 'include' })
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
  async function addIdea(content: string, tagIds: string[] = []): Promise<Idea | undefined> {
    error.value = null
    try {
      const response = await $fetch<{ idea: Idea }>(`${apiBase}/ideas`, {
        method: 'POST',
        body: { content, tagIds },
        credentials: 'include'
      })
      console.log('API response:', response)
      if (response?.idea?.id) {
        ideas.value.unshift(response.idea)
        return response.idea
      }
      error.value = 'Ongeldig antwoord: ' + JSON.stringify(response).substring(0, 100)
    } catch (e: any) {
      console.error('Failed to add idea:', e)
      error.value = e?.data?.message || e?.message || 'Kon idee niet opslaan'
    }
    return undefined
  }

  async function updateIdea(id: string, content: string, tagIds: string[]) {
    await $fetch(`${apiBase}/ideas/${id}`, {
      method: 'PUT',
      body: { content, tagIds },
      credentials: 'include'
    })
    const idea = ideas.value.find(i => i.id === id)
    if (idea) {
      idea.content = content
      idea.tagIds = tagIds
    }
  }

  async function deleteIdea(id: string) {
    await $fetch(`${apiBase}/ideas/${id}`, { method: 'DELETE', credentials: 'include' })
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

  function clearError() {
    error.value = null
  }

  return {
    ideas: readonly(ideas),
    tags: readonly(tags),
    isLoading: readonly(isLoading),
    error: readonly(error),
    loadData,
    clearData,
    clearError,
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
