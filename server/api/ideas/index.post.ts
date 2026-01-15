import { query } from '../../utils/db'
import { requireAuth } from '../../utils/auth'

export default defineEventHandler(async (event) => {
  const auth = requireAuth(event)
  const body = await readBody(event)
  const { content, tagIds = [] } = body

  if (!content?.trim()) {
    throw createError({
      statusCode: 400,
      message: 'Inhoud is verplicht'
    })
  }

  const id = crypto.randomUUID()

  // Create idea
  await query(
    'INSERT INTO ideas (id, user_id, content) VALUES (?, ?, ?)',
    [id, auth.userId, content.trim()]
  )

  // Add tags (filter out any null/undefined values)
  const validTagIds = tagIds.filter((tid: string | null) => tid != null)
  for (const tagId of validTagIds) {
    await query(
      'INSERT INTO idea_tags (idea_id, tag_id) VALUES (?, ?)',
      [id, tagId]
    )
  }

  return {
    idea: {
      id,
      content: content.trim(),
      tagIds: validTagIds,
      createdAt: Date.now()
    }
  }
})
