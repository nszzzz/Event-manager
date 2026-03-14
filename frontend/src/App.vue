<script setup lang="ts">
  import { RouterLink, RouterView } from 'vue-router'
  import { onMounted } from 'vue'
  import { useAuthStore } from "./stores/auth"

  const authStore = useAuthStore()

  onMounted(() => {authStore.getUser()})
</script>

<template>
  <RouterView />
</template>
