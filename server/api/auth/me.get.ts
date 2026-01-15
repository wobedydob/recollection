import { query } from '../../utils/db'
import { getAuthUser } from '../../utils/auth'

interface UserRow {
  id: string
  email: string
  name: string
  avatar: string | null
  created_at: Date
}

export default defineEventHandler(async (event) => {
  const auth = getAuthUser(event)

  if (!auth) {
    return { user: null }
  }

  const users = await query<UserRow[]>(
    'SELECT id, email, name, avatar, created_at FROM users WHERE id = ?',
    [auth.userId]
  )

  if (users.length === 0) {
    return { user: null }
  }

  const user = users[0]

  return {
    user: {
      id: user.id,
      email: user.email,
      name: user.name,
      avatar: user.avatar,
      createdAt: new Date(user.created_at).getTime()
    }
  }
})
