import { query } from '../../utils/db'
import { verifyPassword, createToken, setAuthCookie } from '../../utils/auth'

interface UserRow {
  id: string
  email: string
  password: string
  name: string
  avatar: string | null
  created_at: Date
}

export default defineEventHandler(async (event) => {
  const body = await readBody(event)
  const { email, password } = body

  if (!email || !password) {
    throw createError({
      statusCode: 400,
      message: 'E-mail en wachtwoord zijn verplicht'
    })
  }

  // Find user
  const users = await query<UserRow[]>(
    'SELECT * FROM users WHERE email = ?',
    [email]
  )

  if (users.length === 0) {
    throw createError({
      statusCode: 401,
      message: 'Ongeldige inloggegevens'
    })
  }

  const user = users[0]

  // Verify password
  if (!verifyPassword(password, user.password)) {
    throw createError({
      statusCode: 401,
      message: 'Ongeldige inloggegevens'
    })
  }

  // Create token and set cookie
  const token = createToken({ userId: user.id, email: user.email })
  setAuthCookie(event, token)

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
