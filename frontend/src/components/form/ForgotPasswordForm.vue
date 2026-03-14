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
import ErrorPopup from "../ui/error-popup/ErrorPopup.vue"

const props = defineProps<{
  class?: HTMLAttributes["class"]
}>()

const authStore = useAuthStore()
const { errorMessage } = storeToRefs(authStore)
const { forgotPassword } = authStore

const formData = reactive({
  email: "",
})

const successMessage = ref("")
const isSubmitting = ref(false)

async function handleSubmit() {
  if (isSubmitting.value) {
    return
  }

  isSubmitting.value = true
  successMessage.value = ""
  try {
    const result = await forgotPassword(formData.email)
    if (result.ok) {
      successMessage.value = result.message
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
          Password reset
        </CardTitle>
        <CardDescription>
          Enter your email and we will send a reset link.
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
                placeholder="m@example.com"
                required
                :disabled="isSubmitting"
                v-model="formData.email"
              />
              <FieldDescription v-if="successMessage" class="text-green-600">
                {{ successMessage }}
              </FieldDescription>
            </Field>
            <Field>
              <Button type="submit" :disabled="isSubmitting">
                <IconLoader2 v-if="isSubmitting" class="size-4 animate-spin" />
                {{ isSubmitting ? "Loading..." : "Send reset link" }}
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
      title="Request failed"
      :message="errorMessage"
      @close="dismissError"
    />
  </div>
</template>
