import { query } from '../../utils/db'
import { hashPassword, createToken, setAuthCookie } from '../../utils/auth'

interface User {
  id: string
  email: string
  name: string
  created_at: Date
}

export default defineEventHandler(async (event) => {
  const body = await readBody(event)
  const { email, password, name } = body

  if (!email || !password || !name) {
    throw createError({
      statusCode: 400,
      message: 'E-mail, wachtwoord en naam zijn verplicht'
    })
  }

  if (password.length < 6) {
    throw createError({
      statusCode: 400,
      message: 'Wachtwoord moet minimaal 6 tekens bevatten'
    })
  }

  // Check if user already exists
  const existing = await query<User[]>(
    'SELECT id FROM users WHERE email = ?',
    [email]
  )

  if (existing.length > 0) {
    throw createError({
      statusCode: 400,
      message: 'Er bestaat al een account met dit e-mailadres'
    })
  }

  // Create user
  const id = crypto.randomUUID()
  const hashedPassword = hashPassword(password)

  await query(
    'INSERT INTO users (id, email, password, name) VALUES (?, ?, ?, ?)',
    [id, email, hashedPassword, name]
  )

  // Create token and set cookie
  const token = createToken({ userId: id, email })
  setAuthCookie(event, token)

  return {
    user: {
      id,
      email,
      name,
      createdAt: Date.now()
    }
  }
})
