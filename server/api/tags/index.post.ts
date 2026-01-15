import { query } from '../../utils/db'
import { requireAuth } from '../../utils/auth'

export default defineEventHandler(async (event) => {
  const auth = requireAuth(event)
  const body = await readBody(event)
  const { name, color, emoji } = body

  if (!name?.trim()) {
    throw createError({
      statusCode: 400,
      message: 'Naam is verplicht'
    })
  }

  if (name.trim().length > 25) {
    throw createError({
      statusCode: 400,
      message: 'Naam mag maximaal 25 tekens zijn'
    })
  }

  if (!color) {
    throw createError({
      statusCode: 400,
      message: 'Kleur is verplicht'
    })
  }

  const id = crypto.randomUUID()

  await query(
    'INSERT INTO tags (id, user_id, name, color, emoji) VALUES (?, ?, ?, ?, ?)',
    [id, auth.userId, name.trim(), color, emoji || null]
  )

  return {
    tag: {
      id,
      name: name.trim(),
      color,
      emoji: emoji || undefined
    }
  }
})
