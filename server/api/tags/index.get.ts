import { query } from '../../utils/db'
import { requireAuth } from '../../utils/auth'

interface TagRow {
  id: string
  name: string
  color: string
  emoji: string | null
  created_at: Date
}

export default defineEventHandler(async (event) => {
  const auth = requireAuth(event)

  const tags = await query<TagRow[]>(
    'SELECT id, name, color, emoji, created_at FROM tags WHERE user_id = ? ORDER BY name',
    [auth.userId]
  )

  return {
    tags: tags.map(tag => ({
      id: tag.id,
      name: tag.name,
      color: tag.color,
      emoji: tag.emoji || undefined
    }))
  }
})
