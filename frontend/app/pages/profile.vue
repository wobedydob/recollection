<script setup lang="ts">
const { user, isLoggedIn, updateProfile, logout } = useAuth()
const router = useRouter()
const config = useRuntimeConfig()
const apiBase = config.public.apiBase

const name = ref('')
const isSaving = ref(false)
const successMessage = ref('')
const errorMessage = ref('')

// Password change
const currentPassword = ref('')
const newPassword = ref('')
const confirmPassword = ref('')
const isChangingPassword = ref(false)
const passwordSuccess = ref('')
const passwordError = ref('')
const showCurrentPassword = ref(false)
const showNewPassword = ref(false)
const showConfirmPassword = ref(false)

// Password validation
const hasMinLength = computed(() => newPassword.value.length >= 6)
const hasNumber = computed(() => /\d/.test(newPassword.value))
const hasSpecialChar = computed(() => /[!@#$%^&*(),.?":{}|<>_\-+=\[\]\\\/`~]/.test(newPassword.value))
const passwordsMatch = computed(() => confirmPassword.value.length > 0 && newPassword.value === confirmPassword.value)
const passwordsDontMatch = computed(() => confirmPassword.value.length > 0 && newPassword.value !== confirmPassword.value)

const passwordStrength = computed(() => {
  let strength = 0
  if (hasMinLength.value) strength++
  if (hasNumber.value) strength++
  if (hasSpecialChar.value) strength++
  return strength
})

const strengthLabel = computed(() => {
  if (newPassword.value.length === 0) return ''
  if (passwordStrength.value <= 1) return 'Zwak'
  if (passwordStrength.value === 2) return 'Gemiddeld'
  return 'Sterk'
})

const strengthColor = computed(() => {
  if (passwordStrength.value <= 1) return '#e57373'
  if (passwordStrength.value === 2) return '#ffb74d'
  return '#81c784'
})

const isPasswordValid = computed(() => hasMinLength.value && hasNumber.value && hasSpecialChar.value)

// Redirect if not logged in
watch(isLoggedIn, (loggedIn) => {
  if (!loggedIn) router.push('/login')
}, { immediate: true })

// Initialize form with current user data
watch(user, (u) => {
  if (u) {
    name.value = u.name
  }
}, { immediate: true })

async function handleSave() {
  successMessage.value = ''
  errorMessage.value = ''
  isSaving.value = true

  try {
    const result = await updateProfile({ name: name.value })
    if (result.success) {
      successMessage.value = 'Profiel succesvol bijgewerkt!'
      setTimeout(() => successMessage.value = '', 3000)
    } else {
      errorMessage.value = result.error || 'Profiel bijwerken mislukt'
    }
  } catch (e) {
    errorMessage.value = 'Er ging iets mis'
  } finally {
    isSaving.value = false
  }
}

async function handleLogout() {
  await logout()
  router.push('/login')
}

async function handleChangePassword() {
  passwordSuccess.value = ''
  passwordError.value = ''

  if (!isPasswordValid.value) {
    passwordError.value = 'Nieuw wachtwoord voldoet niet aan de eisen'
    return
  }

  if (!passwordsMatch.value) {
    passwordError.value = 'Wachtwoorden komen niet overeen'
    return
  }

  isChangingPassword.value = true

  try {
    await $fetch(`${apiBase}/auth/password`, {
      method: 'PUT',
      body: {
        currentPassword: currentPassword.value,
        newPassword: newPassword.value
      },
      credentials: 'include'
    })
    passwordSuccess.value = 'Wachtwoord succesvol gewijzigd!'
    currentPassword.value = ''
    newPassword.value = ''
    confirmPassword.value = ''
    setTimeout(() => passwordSuccess.value = '', 3000)
  } catch (e: any) {
    passwordError.value = e.data?.message || 'Wachtwoord wijzigen mislukt'
  } finally {
    isChangingPassword.value = false
  }
}

const memberSince = computed(() => {
  if (!user.value) return ''
  return new Date(user.value.createdAt).toLocaleDateString('nl-NL', {
    month: 'long',
    year: 'numeric'
  })
})
</script>

<template>
  <div class="profile-page" v-if="user">
    <div class="profile-card">
      <div class="profile-header">
        <div class="avatar">
          {{ user.name.charAt(0).toUpperCase() }}
        </div>
        <div class="profile-info">
          <h1 class="profile-name">{{ user.name }}</h1>
          <p class="profile-email">{{ user.email }}</p>
          <p class="profile-member">Lid sinds {{ memberSince }}</p>
        </div>
      </div>

      <form @submit.prevent="handleSave" class="profile-form">
        <h2 class="section-title">Profiel Bewerken</h2>

        <div v-if="successMessage" class="success-message">{{ successMessage }}</div>
        <div v-if="errorMessage" class="error-message">{{ errorMessage }}</div>

        <div class="form-group">
          <label class="label" for="name">Weergavenaam</label>
          <input
            id="name"
            v-model="name"
            type="text"
            class="input"
            placeholder="Je naam"
            required
          />
        </div>

        <div class="form-group">
          <label class="label" for="email">E-mail</label>
          <input
            id="email"
            :value="user.email"
            type="email"
            class="input"
            disabled
          />
          <p class="input-hint">E-mail kan niet worden gewijzigd</p>
        </div>

        <button type="submit" class="save-btn" :disabled="isSaving">
          {{ isSaving ? 'Opslaan...' : 'Wijzigingen Opslaan' }}
        </button>
      </form>

      <form @submit.prevent="handleChangePassword" class="password-form">
        <h2 class="section-title">Wachtwoord Wijzigen</h2>

        <div v-if="passwordSuccess" class="success-message">{{ passwordSuccess }}</div>
        <div v-if="passwordError" class="error-message">{{ passwordError }}</div>

        <div class="form-group">
          <label class="label" for="currentPassword">Huidig wachtwoord</label>
          <div class="password-input-wrapper">
            <input
              id="currentPassword"
              v-model="currentPassword"
              :type="showCurrentPassword ? 'text' : 'password'"
              class="input password-input"
              placeholder="Je huidige wachtwoord"
              required
            />
            <button
              type="button"
              class="toggle-password"
              @click="showCurrentPassword = !showCurrentPassword"
            >
              {{ showCurrentPassword ? '🙈' : '👁️' }}
            </button>
          </div>
        </div>

        <div class="form-group">
          <label class="label" for="newPassword">Nieuw wachtwoord</label>
          <div class="password-input-wrapper">
            <input
              id="newPassword"
              v-model="newPassword"
              :type="showNewPassword ? 'text' : 'password'"
              class="input password-input"
              placeholder="Maak een sterk wachtwoord"
              required
            />
            <button
              type="button"
              class="toggle-password"
              @click="showNewPassword = !showNewPassword"
            >
              {{ showNewPassword ? '🙈' : '👁️' }}
            </button>
          </div>

          <!-- Password strength bar -->
          <div v-if="newPassword.length > 0" class="strength-section">
            <div class="strength-bar">
              <div
                class="strength-fill"
                :style="{ width: (passwordStrength / 3 * 100) + '%', backgroundColor: strengthColor }"
              ></div>
            </div>
            <span class="strength-label" :style="{ color: strengthColor }">{{ strengthLabel }}</span>
          </div>

          <!-- Password requirements -->
          <div class="password-requirements">
            <div class="requirement" :class="{ met: hasMinLength }">
              <span class="requirement-icon">{{ hasMinLength ? '✓' : '○' }}</span>
              Minimaal 6 tekens
            </div>
            <div class="requirement" :class="{ met: hasNumber }">
              <span class="requirement-icon">{{ hasNumber ? '✓' : '○' }}</span>
              Minimaal 1 cijfer
            </div>
            <div class="requirement" :class="{ met: hasSpecialChar }">
              <span class="requirement-icon">{{ hasSpecialChar ? '✓' : '○' }}</span>
              Minimaal 1 speciaal teken
            </div>
          </div>
        </div>

        <div class="form-group">
          <label class="label" for="confirmPassword">Bevestig nieuw wachtwoord</label>
          <div class="password-input-wrapper">
            <input
              id="confirmPassword"
              v-model="confirmPassword"
              :type="showConfirmPassword ? 'text' : 'password'"
              class="input password-input"
              :class="{ 'match': passwordsMatch, 'no-match': passwordsDontMatch }"
              placeholder="Herhaal nieuw wachtwoord"
              required
            />
            <button
              type="button"
              class="toggle-password"
              @click="showConfirmPassword = !showConfirmPassword"
            >
              {{ showConfirmPassword ? '🙈' : '👁️' }}
            </button>
            <span v-if="passwordsMatch" class="match-indicator match">✓</span>
            <span v-else-if="passwordsDontMatch" class="match-indicator no-match">✗</span>
          </div>
          <p v-if="passwordsDontMatch" class="match-message no-match">Wachtwoorden komen niet overeen</p>
          <p v-else-if="passwordsMatch" class="match-message match">Wachtwoorden komen overeen</p>
        </div>

        <button type="submit" class="save-btn" :disabled="isChangingPassword">
          {{ isChangingPassword ? 'Wijzigen...' : 'Wachtwoord Wijzigen' }}
        </button>
      </form>

      <div class="profile-actions">
        <NuxtLink to="/" class="back-link">Terug naar Memory Box</NuxtLink>
        <button class="logout-btn" @click="handleLogout">Uitloggen</button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.profile-page {
  min-height: 100vh;
  display: flex;
  align-items: flex-start;
  justify-content: center;
  padding: 2rem 1rem;
}

.profile-card {
  background: rgba(255, 255, 255, 0.9);
  backdrop-filter: blur(10px);
  border-radius: 1.5rem;
  padding: 2.5rem;
  width: 100%;
  max-width: 500px;
  box-shadow: 0 10px 40px rgba(200, 170, 220, 0.2);
}

.profile-header {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  padding-bottom: 2rem;
  border-bottom: 2px solid #f0e5f5;
  margin-bottom: 2rem;
}

.avatar {
  width: 5rem;
  height: 5rem;
  background: linear-gradient(135deg, #f0a5c0 0%, #c9a7d8 50%, #a5c0f0 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  font-weight: 600;
  color: white;
}

.profile-info {
  flex: 1;
}

.profile-name {
  margin: 0 0 0.25rem 0;
  font-size: 1.5rem;
  font-weight: 600;
  color: #4a3a5a;
}

.profile-email {
  margin: 0 0 0.25rem 0;
  color: #7b6a8a;
}

.profile-member {
  margin: 0;
  font-size: 0.85rem;
  color: #a595b5;
}

.profile-form,
.password-form {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.password-form {
  margin-top: 2rem;
  padding-top: 2rem;
  border-top: 2px solid #f0e5f5;
}

.section-title {
  margin: 0 0 0.5rem 0;
  font-size: 1.1rem;
  font-weight: 600;
  color: #6b5a7a;
}

.success-message {
  padding: 0.75rem 1rem;
  background: #f0fef0;
  border: 1px solid #9d9;
  border-radius: 0.75rem;
  color: #595;
  font-size: 0.9rem;
  text-align: center;
}

.error-message {
  padding: 0.75rem 1rem;
  background: #fef0f0;
  border: 1px solid #fcc;
  border-radius: 0.75rem;
  color: #c55;
  font-size: 0.9rem;
  text-align: center;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.label {
  font-size: 0.875rem;
  font-weight: 500;
  color: #6b5a7a;
}

.input {
  padding: 0.875rem 1rem;
  border: 2px solid #e8d5f0;
  border-radius: 0.75rem;
  font-size: 1rem;
  color: #4a3a5a;
  transition: border-color 0.2s ease;
}

.input:focus {
  outline: none;
  border-color: #c9a7d8;
}

.input:disabled {
  background: #f8f5fa;
  color: #9b8aab;
}

.input::placeholder {
  color: #b8a5c5;
}

.input-hint {
  margin: 0;
  font-size: 0.8rem;
  color: #a595b5;
}

.save-btn {
  margin-top: 0.5rem;
  padding: 0.875rem 1.5rem;
  background: linear-gradient(135deg, #f0a5c0 0%, #c9a7d8 50%, #a5c0f0 100%);
  border: none;
  border-radius: 2rem;
  color: white;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 4px 15px rgba(200, 170, 220, 0.3);
}

.save-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(200, 170, 220, 0.4);
}

.save-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.profile-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 2rem;
  padding-top: 2rem;
  border-top: 2px solid #f0e5f5;
}

.back-link {
  color: #a87cc9;
  text-decoration: none;
  font-weight: 500;
}

.back-link:hover {
  text-decoration: underline;
}

.logout-btn {
  padding: 0.5rem 1rem;
  background: transparent;
  border: 1px solid #e8d5f0;
  border-radius: 2rem;
  color: #c57a8a;
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.logout-btn:hover {
  background: #fef0f5;
  border-color: #f0c0c0;
}

/* Password input with toggle */
.password-input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.password-input {
  flex: 1;
  padding-right: 5rem;
}

.toggle-password {
  position: absolute;
  right: 0.75rem;
  background: none;
  border: none;
  font-size: 1.1rem;
  cursor: pointer;
  padding: 0.25rem;
  opacity: 0.6;
  transition: opacity 0.2s;
}

.toggle-password:hover {
  opacity: 1;
}

.match-indicator {
  position: absolute;
  right: 2.75rem;
  font-size: 1rem;
  font-weight: bold;
}

.match-indicator.match {
  color: #81c784;
}

.match-indicator.no-match {
  color: #e57373;
}

/* Strength bar */
.strength-section {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-top: 0.5rem;
}

.strength-bar {
  flex: 1;
  height: 6px;
  background: #e8d5f0;
  border-radius: 3px;
  overflow: hidden;
}

.strength-fill {
  height: 100%;
  border-radius: 3px;
  transition: width 0.3s ease, background-color 0.3s ease;
}

.strength-label {
  font-size: 0.75rem;
  font-weight: 600;
  min-width: 4rem;
}

/* Password requirements */
.password-requirements {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  margin-top: 0.75rem;
}

.requirement {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.8rem;
  color: #9b8aab;
  transition: color 0.2s;
}

.requirement.met {
  color: #81c784;
}

.requirement-icon {
  font-size: 0.75rem;
  width: 1rem;
  text-align: center;
}

/* Match feedback */
.input.match {
  border-color: #81c784;
}

.input.no-match {
  border-color: #e57373;
}

.match-message {
  margin: 0.375rem 0 0 0;
  font-size: 0.8rem;
}

.match-message.match {
  color: #81c784;
}

.match-message.no-match {
  color: #e57373;
}
</style>
