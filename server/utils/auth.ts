import jwt from 'jsonwebtoken'
import bcrypt from 'bcryptjs'
import type { H3Event } from 'h3'

const JWT_SECRET = process.env.JWT_SECRET || 'your-secret-key-change-in-production'

export interface JwtPayload {
  userId: string
  email: string
}

export function hashPassword(password: string): string {
  return bcrypt.hashSync(password, 10)
}

export function verifyPassword(password: string, hash: string): boolean {
  return bcrypt.compareSync(password, hash)
}

export function createToken(payload: JwtPayload): string {
  return jwt.sign(payload, JWT_SECRET, { expiresIn: '7d' })
}

export function verifyToken(token: string): JwtPayload | null {
  try {
    return jwt.verify(token, JWT_SECRET) as JwtPayload
  } catch {
    return null
  }
}

export function setAuthCookie(event: H3Event, token: string) {
  setCookie(event, 'auth_token', token, {
    httpOnly: true,
    secure: process.env.NODE_ENV === 'production',
    sameSite: 'lax',
    maxAge: 60 * 60 * 24 * 7 // 7 days
  })
}

export function removeAuthCookie(event: H3Event) {
  deleteCookie(event, 'auth_token')
}

export function getAuthUser(event: H3Event): JwtPayload | null {
  const token = getCookie(event, 'auth_token')
  if (!token) return null
  return verifyToken(token)
}

export function requireAuth(event: H3Event): JwtPayload {
  const user = getAuthUser(event)
  if (!user) {
    throw createError({
      statusCode: 401,
      message: 'Niet ingelogd'
    })
  }
  return user
}
