import { query } from '../../utils/db'
import { requireAuth } from '../../utils/auth'

interface IdeaRow {
  id: string
  content: string
  created_at: Date
}

interface IdeaTagRow {
  idea_id: string
  tag_id: string
}

export default defineEventHandler(async (event) => {
  const auth = requireAuth(event)

  // Get all ideas for user
  const ideas = await query<IdeaRow[]>(
    'SELECT id, content, created_at FROM ideas WHERE user_id = ? ORDER BY created_at DESC',
    [auth.userId]
  )

  // Get all idea-tag relationships
  const ideaTags = await query<IdeaTagRow[]>(
    `SELECT it.idea_id, it.tag_id
     FROM idea_tags it
     JOIN ideas i ON it.idea_id = i.id
     WHERE i.user_id = ?`,
    [auth.userId]
  )

  // Group tags by idea
  const tagsByIdea = new Map<string, string[]>()
  for (const it of ideaTags) {
    if (!tagsByIdea.has(it.idea_id)) {
      tagsByIdea.set(it.idea_id, [])
    }
    tagsByIdea.get(it.idea_id)!.push(it.tag_id)
  }

  return {
    ideas: ideas.map(idea => ({
      id: idea.id,
      content: idea.content,
      tagIds: tagsByIdea.get(idea.id) || [],
      createdAt: new Date(idea.created_at).getTime()
    }))
  }
})
