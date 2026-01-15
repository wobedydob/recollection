<script setup lang="ts">
const { register, isLoggedIn } = useAuth()
const router = useRouter()

const name = ref('')
const email = ref('')
const password = ref('')
const confirmPassword = ref('')
const error = ref('')
const isSubmitting = ref(false)

// Redirect if already logged in
watch(isLoggedIn, (loggedIn) => {
  if (loggedIn) router.push('/')
}, { immediate: true })

async function handleSubmit() {
  error.value = ''

  if (password.value !== confirmPassword.value) {
    error.value = 'Wachtwoorden komen niet overeen'
    return
  }

  if (password.value.length < 6) {
    error.value = 'Wachtwoord moet minimaal 6 tekens bevatten'
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
          <input
            id="password"
            v-model="password"
            type="password"
            class="input"
            placeholder="Minimaal 6 tekens"
            required
          />
        </div>

        <div class="form-group">
          <label class="label" for="confirmPassword">Wachtwoord bevestigen</label>
          <input
            id="confirmPassword"
            v-model="confirmPassword"
            type="password"
            class="input"
            placeholder="Herhaal je wachtwoord"
            required
          />
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
</style>
