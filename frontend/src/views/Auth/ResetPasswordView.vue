<script lang="ts">
export const description = "A reset password page."
</script>

<script setup lang="ts">
import AppBrand from "@/components/AppBrand.vue"
import ResetPasswordForm from "@/components/form/ResetPasswordForm.vue"
</script>

<template>
  <div class="bg-muted flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10">
    <div class="flex w-full max-w-sm flex-col gap-6">
      <AppBrand class="self-center" />
      <ResetPasswordForm />
    </div>
  </div>
</template>
