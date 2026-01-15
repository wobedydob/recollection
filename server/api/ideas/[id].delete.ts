import { query } from '../../utils/db'
import { requireAuth } from '../../utils/auth'

export default defineEventHandler(async (event) => {
  const auth = requireAuth(event)
  const id = getRouterParam(event, 'id')

  if (!id) {
    throw createError({
      statusCode: 400,
      message: 'ID is verplicht'
    })
  }

  // Delete idea (cascade will remove idea_tags)
  const result = await query<any>(
    'DELETE FROM ideas WHERE id = ? AND user_id = ?',
    [id, auth.userId]
  )

  return { success: true }
})
