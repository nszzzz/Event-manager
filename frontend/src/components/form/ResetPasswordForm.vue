<script setup lang="ts">
import { reactive, ref, type HTMLAttributes } from "vue"
import { IconLoader2 } from "@tabler/icons-vue"
import { cn } from "@/lib/utils"
import { Button } from "@/components/ui/button"
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card"
import {
  Field,
  FieldDescription,
  FieldGroup,
  FieldLabel,
} from "@/components/ui/field"
import { Input } from "@/components/ui/input"
import { useAuthStore } from "@/stores/auth"
import { storeToRefs } from "pinia"
import { useRoute, useRouter } from "vue-router"
import ErrorPopup from "../ui/error-popup/ErrorPopup.vue"

const props = defineProps<{
  class?: HTMLAttributes["class"]
}>()

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const { errorMessage } = storeToRefs(authStore)
const { resetPassword } = authStore

const formData = reactive({
  token: typeof route.query.token === "string" ? route.query.token : "",
  email: typeof route.query.email === "string" ? route.query.email : "",
  password: "",
  password_confirmation: "",
})

const successMessage = ref("")
const localError = ref("")
const isSubmitting = ref(false)

async function handleSubmit() {
  if (isSubmitting.value) {
    return
  }

  isSubmitting.value = true
  localError.value = ""
  successMessage.value = ""

  if (!formData.token || !formData.email) {
    localError.value = "Missing reset token or email."
    isSubmitting.value = false
    return
  }

  try {
    const result = await resetPassword({
      token: formData.token,
      email: formData.email,
      password: formData.password,
      password_confirmation: formData.password_confirmation,
    })

    if (result.ok) {
      successMessage.value = result.message
      setTimeout(() => {
        router.push({ name: "login" })
      }, 1200)
    }
  } finally {
    isSubmitting.value = false
  }
}

function dismissError() {
  errorMessage.value = ""
}
</script>

<template>
  <div :class="cn('flex flex-col gap-6', props.class)">
    <Card>
      <CardHeader class="text-center">
        <CardTitle class="text-xl">
          Set a new password
        </CardTitle>
        <CardDescription>
          Enter your new password to complete reset.
        </CardDescription>
      </CardHeader>
      <CardContent>
        <form @submit.prevent="handleSubmit">
          <FieldGroup>
            <Field>
              <FieldLabel for="email">
                Email
              </FieldLabel>
              <Input
                id="email"
                type="email"
                required
                :disabled="isSubmitting"
                v-model="formData.email"
              />
            </Field>
            <Field>
              <FieldLabel for="password">
                New password
              </FieldLabel>
              <Input
                id="password"
                type="password"
                required
                :disabled="isSubmitting"
                v-model="formData.password"
              />
            </Field>
            <Field>
              <FieldLabel for="password_confirmation">
                Confirm password
              </FieldLabel>
              <Input
                id="password_confirmation"
                type="password"
                required
                :disabled="isSubmitting"
                v-model="formData.password_confirmation"
              />
              <FieldDescription v-if="localError" class="text-destructive">
                {{ localError }}
              </FieldDescription>
              <FieldDescription v-if="successMessage" class="text-green-600">
                {{ successMessage }}
              </FieldDescription>
            </Field>
            <Field>
              <Button type="submit" :disabled="isSubmitting">
                <IconLoader2 v-if="isSubmitting" class="size-4 animate-spin" />
                {{ isSubmitting ? "Loading..." : "Reset password" }}
              </Button>
            </Field>
            <FieldDescription class="text-center">
              Back to
              <RouterLink
                :to="{ name: 'login' }"
                class="underline underline-offset-4 hover:text-primary"
                :class="{ 'pointer-events-none opacity-50': isSubmitting }"
              >
                login
              </RouterLink>
            </FieldDescription>
          </FieldGroup>
        </form>
      </CardContent>
    </Card>
    <ErrorPopup
      v-if="errorMessage"
      title="Reset failed"
      :message="errorMessage"
      @close="dismissError"
    />
  </div>
</template>
