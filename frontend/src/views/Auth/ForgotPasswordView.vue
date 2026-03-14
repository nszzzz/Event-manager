<script lang="ts">
export const description = "A forgot password page."
</script>

<script setup lang="ts">
import AppBrand from "@/components/AppBrand.vue"
import ForgotPasswordForm from "@/components/form/ForgotPasswordForm.vue"
</script>

<template>
  <div class="bg-muted flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10">
    <div class="flex w-full max-w-sm flex-col gap-6">
      <AppBrand class="self-center" />
      <ForgotPasswordForm />
    </div>
  </div>
</template>
