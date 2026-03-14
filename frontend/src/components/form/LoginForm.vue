<script setup lang="ts">
import { reactive, type HTMLAttributes } from "vue"
import { cn } from "@/lib/utils"
import { Button } from '@/components/ui/button'
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
        <form @submit.prevent="authenticate(formData, 'login')">
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
                v-model="formData.email"
              />
            </Field>
            <Field>
              <div class="flex items-center">
                <FieldLabel for="password">
                  Password
                </FieldLabel>
                <a
                  href="#"
                  class="ml-auto text-sm underline-offset-4 hover:underline"
                >
                  Forgot your password?
                </a>
              </div>
              <Input id="password" type="password" required v-model="formData.password" />
            </Field>
            <Field>
              <Button type="submit">
                Login
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
