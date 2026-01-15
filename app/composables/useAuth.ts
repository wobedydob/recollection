export interface User {
  id: string
  email: string
  name: string
  avatar?: string
  createdAt: number
}

export function useAuth() {
  const user = useState<User | null>('auth-user', () => null)
  const isLoading = useState('auth-loading', () => true)

  // Load user from API
  async function loadUser() {
    try {
      const response = await $fetch<{ user: User | null }>('/api/auth/me')
      user.value = response.user || null
    } catch {
      user.value = null
    } finally {
      isLoading.value = false
    }
  }

  // Register a new user
  async function register(email: string, password: string, name: string): Promise<{ success: boolean; error?: string }> {
    try {
      const response = await $fetch<{ user: User }>('/api/auth/register', {
        method: 'POST',
        body: { email, password, name }
      })
      user.value = response.user
      return { success: true }
    } catch (e: any) {
      return { success: false, error: e.data?.message || 'Registratie mislukt' }
    }
  }

  // Login
  async function login(email: string, password: string): Promise<{ success: boolean; error?: string }> {
    try {
      const response = await $fetch<{ user: User }>('/api/auth/login', {
        method: 'POST',
        body: { email, password }
      })
      user.value = response.user
      return { success: true }
    } catch (e: any) {
      return { success: false, error: e.data?.message || 'Inloggen mislukt' }
    }
  }

  // Logout
  async function logout() {
    try {
      await $fetch('/api/auth/logout', { method: 'POST' })
    } finally {
      user.value = null
      // Clear ideas/tags data to prevent data leak between accounts
      const { clearData } = useIdeas()
      clearData()
    }
  }

  // Update profile
  async function updateProfile(updates: Partial<Pick<User, 'name' | 'avatar'>>): Promise<{ success: boolean; error?: string }> {
    if (!user.value) return { success: false, error: 'Niet ingelogd' }

    try {
      await $fetch('/api/auth/profile', {
        method: 'PUT',
        body: updates
      })
      user.value = { ...user.value, ...updates }
      return { success: true }
    } catch (e: any) {
      return { success: false, error: e.data?.message || 'Bijwerken mislukt' }
    }
  }

  const isLoggedIn = computed(() => !!user.value)

  return {
    user: readonly(user),
    isLoading: readonly(isLoading),
    isLoggedIn,
    loadUser,
    register,
    login,
    logout,
    updateProfile
  }
}
