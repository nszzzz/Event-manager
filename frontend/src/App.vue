<script setup lang="ts">
  import { RouterLink, RouterView } from 'vue-router'
  import { onMounted } from 'vue'
  import { useAuthStore } from "./stores/auth"

  const authStore = useAuthStore()

  onMounted(() => {authStore.getUser()})
</script>

<template>
  <header>
    
    <nav>
      <RouterLink :to="{ name: 'home' }">Home</RouterLink>
      <RouterLink :to="{ name: 'login' }">Login</RouterLink>
    </nav>
  </header>

  <RouterView />
</template>
