<script setup lang="ts">
const { register, isLoggedIn } = useAuth()
const router = useRouter()

const name = ref('')
const email = ref('')
const password = ref('')
const confirmPassword = ref('')
const error = ref('')
const isSubmitting = ref(false)
const showPassword = ref(false)
const showConfirmPassword = ref(false)

// Redirect if already logged in
watch(isLoggedIn, (loggedIn) => {
  if (loggedIn) router.push('/')
}, { immediate: true })

// Password validation
const hasMinLength = computed(() => password.value.length >= 6)
const hasNumber = computed(() => /\d/.test(password.value))
const hasSpecialChar = computed(() => /[!@#$%^&*(),.?":{}|<>_\-+=\[\]\\\/`~]/.test(password.value))
const passwordsMatch = computed(() => confirmPassword.value.length > 0 && password.value === confirmPassword.value)
const passwordsDontMatch = computed(() => confirmPassword.value.length > 0 && password.value !== confirmPassword.value)

const passwordStrength = computed(() => {
  let strength = 0
  if (hasMinLength.value) strength++
  if (hasNumber.value) strength++
  if (hasSpecialChar.value) strength++
  return strength
})

const strengthLabel = computed(() => {
  if (password.value.length === 0) return ''
  if (passwordStrength.value === 0) return 'Zwak'
  if (passwordStrength.value === 1) return 'Zwak'
  if (passwordStrength.value === 2) return 'Gemiddeld'
  return 'Sterk'
})

const strengthColor = computed(() => {
  if (passwordStrength.value <= 1) return '#e57373'
  if (passwordStrength.value === 2) return '#ffb74d'
  return '#81c784'
})

const isPasswordValid = computed(() => hasMinLength.value && hasNumber.value && hasSpecialChar.value)

async function handleSubmit() {
  error.value = ''

  if (!isPasswordValid.value) {
    error.value = 'Wachtwoord voldoet niet aan de eisen'
    return
  }

  if (!passwordsMatch.value) {
    error.value = 'Wachtwoorden komen niet overeen'
    return
  }

  isSubmitting.value = true

  try {
    const result = await register(email.value, password.value, name.value)
    if (result.success) {
      router.push('/')
    } else {
      error.value = result.error || 'Registratie mislukt'
    }
  } catch (e) {
    error.value = 'Er ging iets mis'
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <div class="auth-page">
    <div class="auth-card">
      <h1 class="auth-title">Account Aanmaken</h1>
      <p class="auth-subtitle">Begin je Recollectie avontuur</p>

      <form @submit.prevent="handleSubmit" class="auth-form">
        <div v-if="error" class="error-message">{{ error }}</div>

        <div class="form-group">
          <label class="label" for="name">Naam</label>
          <input
            id="name"
            v-model="name"
            type="text"
            class="input"
            placeholder="Je naam"
            required
            autofocus
          />
        </div>

        <div class="form-group">
          <label class="label" for="email">E-mail</label>
          <input
            id="email"
            v-model="email"
            type="email"
            class="input"
            placeholder="jij@voorbeeld.nl"
            required
          />
        </div>

        <div class="form-group">
          <label class="label" for="password">Wachtwoord</label>
          <div class="password-input-wrapper">
            <input
              id="password"
              v-model="password"
              :type="showPassword ? 'text' : 'password'"
              class="input password-input"
              placeholder="Maak een sterk wachtwoord"
              required
            />
            <button
              type="button"
              class="toggle-password"
              @click="showPassword = !showPassword"
              :title="showPassword ? 'Verberg wachtwoord' : 'Toon wachtwoord'"
            >
              {{ showPassword ? '🙈' : '👁️' }}
            </button>
          </div>

          <!-- Password strength bar -->
          <div v-if="password.length > 0" class="strength-section">
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
          <label class="label" for="confirmPassword">Wachtwoord bevestigen</label>
          <div class="password-input-wrapper">
            <input
              id="confirmPassword"
              v-model="confirmPassword"
              :type="showConfirmPassword ? 'text' : 'password'"
              class="input password-input"
              :class="{ 'match': passwordsMatch, 'no-match': passwordsDontMatch }"
              placeholder="Herhaal je wachtwoord"
              required
            />
            <button
              type="button"
              class="toggle-password"
              @click="showConfirmPassword = !showConfirmPassword"
              :title="showConfirmPassword ? 'Verberg wachtwoord' : 'Toon wachtwoord'"
            >
              {{ showConfirmPassword ? '🙈' : '👁️' }}
            </button>
            <span v-if="passwordsMatch" class="match-indicator match">✓</span>
            <span v-else-if="passwordsDontMatch" class="match-indicator no-match">✗</span>
          </div>
          <p v-if="passwordsDontMatch" class="match-message no-match">Wachtwoorden komen niet overeen</p>
          <p v-else-if="passwordsMatch" class="match-message match">Wachtwoorden komen overeen</p>
        </div>

        <button type="submit" class="submit-btn" :disabled="isSubmitting">
          {{ isSubmitting ? 'Account aanmaken...' : 'Account Aanmaken' }}
        </button>
      </form>

      <p class="auth-footer">
        Heb je al een account?
        <NuxtLink to="/login" class="auth-link">Inloggen</NuxtLink>
      </p>
    </div>
  </div>
</template>

<style scoped>
.auth-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem 1rem;
}

.auth-card {
  background: rgba(255, 255, 255, 0.9);
  backdrop-filter: blur(10px);
  border-radius: 1.5rem;
  padding: 2.5rem;
  width: 100%;
  max-width: 400px;
  box-shadow: 0 10px 40px rgba(200, 170, 220, 0.2);
}

.auth-title {
  margin: 0 0 0.5rem 0;
  font-size: 2rem;
  font-weight: 700;
  background: linear-gradient(135deg, #e879a0 0%, #a87cc9 50%, #79a0e8 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  text-align: center;
}

.auth-subtitle {
  margin: 0 0 2rem 0;
  color: #9b8aab;
  text-align: center;
}

.auth-form {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
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

.input::placeholder {
  color: #b8a5c5;
}

.submit-btn {
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

.submit-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(200, 170, 220, 0.4);
}

.submit-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.auth-footer {
  margin: 1.5rem 0 0 0;
  text-align: center;
  color: #9b8aab;
  font-size: 0.9rem;
}

.auth-link {
  color: #a87cc9;
  text-decoration: none;
  font-weight: 500;
}

.auth-link:hover {
  text-decoration: underline;
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
