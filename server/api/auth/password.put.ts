import { query } from '../../utils/db'
import { requireAuth, hashPassword, verifyPassword } from '../../utils/auth'

interface UserRow {
  password: string
}

export default defineEventHandler(async (event) => {
  const auth = requireAuth(event)
  const body = await readBody(event)
  const { currentPassword, newPassword } = body

  if (!currentPassword || !newPassword) {
    throw createError({
      statusCode: 400,
      message: 'Huidig en nieuw wachtwoord zijn verplicht'
    })
  }

  if (newPassword.length < 6) {
    throw createError({
      statusCode: 400,
      message: 'Nieuw wachtwoord moet minimaal 6 tekens zijn'
    })
  }

  // Get current password hash
  const users = await query<UserRow[]>(
    'SELECT password FROM users WHERE id = ?',
    [auth.userId]
  )

  if (users.length === 0) {
    throw createError({
      statusCode: 404,
      message: 'Gebruiker niet gevonden'
    })
  }

  // Verify current password
  if (!verifyPassword(currentPassword, users[0].password)) {
    throw createError({
      statusCode: 400,
      message: 'Huidig wachtwoord is onjuist'
    })
  }

  // Update password
  const newHash = hashPassword(newPassword)
  await query(
    'UPDATE users SET password = ? WHERE id = ?',
    [newHash, auth.userId]
  )

  return { success: true }
})
