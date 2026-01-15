import { removeAuthCookie } from '../../utils/auth'

export default defineEventHandler(async (event) => {
  removeAuthCookie(event)
  return { success: true }
})
