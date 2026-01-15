import { query } from '../../utils/db'
import { requireAuth } from '../../utils/auth'

export default defineEventHandler(async (event) => {
  const auth = requireAuth(event)
  const id = getRouterParam(event, 'id')
  const body = await readBody(event)
  const { content, tagIds } = body

  if (!id) {
    throw createError({
      statusCode: 400,
      message: 'ID is verplicht'
    })
  }

  // Verify ownership
  const ideas = await query<any[]>(
    'SELECT id FROM ideas WHERE id = ? AND user_id = ?',
    [id, auth.userId]
  )

  if (ideas.length === 0) {
    throw createError({
      statusCode: 404,
      message: 'Idee niet gevonden'
    })
  }

  // Update content if provided
  if (content !== undefined) {
    await query(
      'UPDATE ideas SET content = ? WHERE id = ?',
      [content.trim(), id]
    )
  }

  // Update tags if provided
  if (tagIds !== undefined) {
    // Remove existing tags
    await query('DELETE FROM idea_tags WHERE idea_id = ?', [id])

    // Add new tags (filter out any null/undefined values)
    const validTagIds = tagIds.filter((tid: string | null) => tid != null)
    for (const tagId of validTagIds) {
      await query(
        'INSERT INTO idea_tags (idea_id, tag_id) VALUES (?, ?)',
        [id, tagId]
      )
    }
  }

  return { success: true }
})
