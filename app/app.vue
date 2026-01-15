<script setup lang="ts">
const { loadUser } = useAuth()

onMounted(() => {
  loadUser()
})
</script>

<template>
  <div class="app">
    <AppHeader />
    <NuxtPage />
  </div>
</template>

<style>
* {
  box-sizing: border-box;
}

body {
  margin: 0;
  font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
  background: linear-gradient(135deg, #fff5f8 0%, #f5f0ff 50%, #f0f8ff 100%);
  min-height: 100vh;
}
</style>
