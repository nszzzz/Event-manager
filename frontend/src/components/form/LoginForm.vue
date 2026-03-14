<script setup lang="ts">
import { reactive, ref, type HTMLAttributes } from "vue"
import { cn } from "@/lib/utils"
import { Button } from '@/components/ui/button'
import { IconLoader2 } from "@tabler/icons-vue"
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'
import {
  Field,
  FieldGroup,
  FieldLabel,
} from '@/components/ui/field'
import { Input } from '@/components/ui/input'
import { useAuthStore } from "@/stores/auth"
import { storeToRefs } from "pinia"
import ErrorPopup from "../ui/error-popup/ErrorPopup.vue"

const props = defineProps<{
  class?: HTMLAttributes["class"]
}>()

const authStore = useAuthStore()
const { errorMessage } = storeToRefs(authStore)
const { authenticate } = authStore

const formData = reactive({
  email: "",
  password: "",
})

const isSubmitting = ref(false)

async function handleSubmit() {
  if (isSubmitting.value) {
    return
  }

  isSubmitting.value = true
  try {
    await authenticate(formData, "login")
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
          Welcome back
        </CardTitle>
        <CardDescription>
          Login with your email and password
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
            </Field>
            <Field>
              <div class="flex items-center">
                <FieldLabel for="password">
                  Password
                </FieldLabel>
                <RouterLink
                  :to="{ name: 'forgot-password' }"
                  class="ml-auto text-sm underline-offset-4 hover:underline"
                  :class="{ 'pointer-events-none opacity-50': isSubmitting }"
                >
                  Forgot your password?
                </RouterLink>
              </div>
              <Input id="password" type="password" required :disabled="isSubmitting" v-model="formData.password" />
            </Field>
            <Field>
              <Button type="submit" :disabled="isSubmitting">
                <IconLoader2 v-if="isSubmitting" class="size-4 animate-spin" />
                {{ isSubmitting ? "Loading..." : "Login" }}
              </Button>
            </Field>
          </FieldGroup>
        </form>
      </CardContent>
    </Card>
    <ErrorPopup
      v-if="errorMessage"
      title="Login failed"
      :message="errorMessage"
      @close="dismissError"
    />
  </div>
</template>
