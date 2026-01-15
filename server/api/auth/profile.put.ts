import { query } from '../../utils/db'
import { requireAuth } from '../../utils/auth'

export default defineEventHandler(async (event) => {
  const auth = requireAuth(event)
  const body = await readBody(event)
  const { name, avatar } = body

  if (name !== undefined) {
    await query(
      'UPDATE users SET name = ? WHERE id = ?',
      [name, auth.userId]
    )
  }

  if (avatar !== undefined) {
    await query(
      'UPDATE users SET avatar = ? WHERE id = ?',
      [avatar, auth.userId]
    )
  }

  return { success: true }
})
