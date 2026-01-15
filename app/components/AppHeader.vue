<script setup lang="ts">
const { user, isLoggedIn, logout } = useAuth()
const router = useRouter()

const showUserMenu = ref(false)
const menuRef = ref<HTMLElement>()

async function handleLogout() {
  showUserMenu.value = false
  await logout()
  router.push('/login')
}

function handleClickOutside(e: MouseEvent) {
  if (menuRef.value && !menuRef.value.contains(e.target as Node)) {
    showUserMenu.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<template>
  <header class="header" v-if="isLoggedIn">
    <NuxtLink to="/" class="logo">Recollectie</NuxtLink>

    <div class="user-menu" ref="menuRef">
      <button class="user-btn" @click.stop="showUserMenu = !showUserMenu">
        <span class="user-avatar">{{ user?.name.charAt(0).toUpperCase() }}</span>
        <span class="user-name">{{ user?.name }}</span>
        <span class="dropdown-arrow">▼</span>
      </button>

      <div v-if="showUserMenu" class="menu-dropdown">
        <NuxtLink to="/profile" class="menu-item" @click="showUserMenu = false">
          Profiel
        </NuxtLink>
        <button class="menu-item logout" @click="handleLogout">
          Uitloggen
        </button>
      </div>
    </div>
  </header>
</template>

<style scoped>
.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.5rem;
  background: rgba(255, 255, 255, 0.8);
  backdrop-filter: blur(10px);
  border-bottom: 1px solid rgba(232, 213, 240, 0.5);
  position: sticky;
  top: 0;
  z-index: 50;
}

.logo {
  font-size: 1.25rem;
  font-weight: 700;
  background: linear-gradient(135deg, #e879a0 0%, #a87cc9 50%, #79a0e8 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  text-decoration: none;
}

.user-menu {
  position: relative;
}

.user-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.375rem 0.75rem 0.375rem 0.375rem;
  background: white;
  border: 2px solid #e8d5f0;
  border-radius: 2rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.user-btn:hover {
  border-color: #c9a7d8;
}

.user-avatar {
  width: 1.75rem;
  height: 1.75rem;
  background: linear-gradient(135deg, #f0a5c0 0%, #c9a7d8 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.85rem;
  font-weight: 600;
  color: white;
}

.user-name {
  font-size: 0.9rem;
  color: #5a4a6a;
  max-width: 120px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.dropdown-arrow {
  font-size: 0.6rem;
  color: #a595b5;
}

.menu-dropdown {
  position: absolute;
  top: 100%;
  right: 0;
  margin-top: 0.5rem;
  background: white;
  border: 2px solid #e8d5f0;
  border-radius: 0.75rem;
  overflow: hidden;
  box-shadow: 0 4px 15px rgba(200, 170, 220, 0.2);
  animation: dropdown-in 0.15s ease;
  min-width: 140px;
}

@keyframes dropdown-in {
  from { opacity: 0; transform: translateY(-5px); }
  to { opacity: 1; transform: translateY(0); }
}

.menu-item {
  display: block;
  width: 100%;
  padding: 0.75rem 1rem;
  background: none;
  border: none;
  text-align: left;
  font-size: 0.9rem;
  color: #5a4a6a;
  text-decoration: none;
  cursor: pointer;
  transition: background 0.15s ease;
}

.menu-item:hover {
  background: #f8f0fc;
}

.menu-item.logout {
  color: #c57a8a;
  border-top: 1px solid #f0e5f5;
}

.menu-item.logout:hover {
  background: #fef0f5;
}
</style>
