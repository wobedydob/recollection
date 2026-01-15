<script setup lang="ts">
const { user, isLoggedIn, updateProfile, logout } = useAuth()
const router = useRouter()

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

  if (newPassword.value !== confirmPassword.value) {
    passwordError.value = 'Wachtwoorden komen niet overeen'
    return
  }

  if (newPassword.value.length < 6) {
    passwordError.value = 'Nieuw wachtwoord moet minimaal 6 tekens zijn'
    return
  }

  isChangingPassword.value = true

  try {
    await $fetch('/api/auth/password', {
      method: 'PUT',
      body: {
        currentPassword: currentPassword.value,
        newPassword: newPassword.value
      }
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
          <input
            id="currentPassword"
            v-model="currentPassword"
            type="password"
            class="input"
            placeholder="Je huidige wachtwoord"
            required
          />
        </div>

        <div class="form-group">
          <label class="label" for="newPassword">Nieuw wachtwoord</label>
          <input
            id="newPassword"
            v-model="newPassword"
            type="password"
            class="input"
            placeholder="Minimaal 6 tekens"
            required
          />
        </div>

        <div class="form-group">
          <label class="label" for="confirmPassword">Bevestig nieuw wachtwoord</label>
          <input
            id="confirmPassword"
            v-model="confirmPassword"
            type="password"
            class="input"
            placeholder="Herhaal nieuw wachtwoord"
            required
          />
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
</style>
