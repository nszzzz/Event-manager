<script setup lang="ts">
import { computed, ref } from "vue"
import { IconLoader2 } from "@tabler/icons-vue"
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
  FieldDescription,
  FieldGroup,
  FieldLabel,
} from '@/components/ui/field'
import {
  InputOTP,
  InputOTPGroup,
  InputOTPSlot,
} from '@/components/ui/input-otp'
import { useAuthStore } from '@/stores/auth'
import { storeToRefs } from 'pinia'
import ErrorPopup from '../ui/error-popup/ErrorPopup.vue'
import { cn } from '@/lib/utils'
import { type HTMLAttributes } from "vue"

const props = defineProps<{
  class?: HTMLAttributes['class']
}>()

const authStore = useAuthStore()
const { errorMessage } = storeToRefs(authStore)
const { verifyTwoFactor, resendCode } = authStore

const otpValue = ref('')
const resendMessage = ref('')
const isVerifying = ref(false)
const isResending = ref(false)

const isBusy = computed(() => isVerifying.value || isResending.value)

async function handleSubmit() {
  if (isBusy.value) {
    return
  }

  isVerifying.value = true
  try {
    await verifyTwoFactor(otpValue.value)
  } finally {
    isVerifying.value = false
  }
}

async function handleResend() {
  if (isBusy.value) {
    return
  }

  isResending.value = true
  resendMessage.value = ""

  try {
    const data = await resendCode()
    resendMessage.value = data.message || "Code resent!"
    setTimeout(() => {
      resendMessage.value = ""
    }, 3000)
  } finally {
    isResending.value = false
  }
}

function dismissError() {
  errorMessage.value = ''
}
</script>

<template>
  <div :class="cn('flex flex-col gap-6', props.class)">
  <Card>
    <CardHeader class="text-center">
      <CardTitle class="text-xl">
        Enter verification code
      </CardTitle>
      <CardDescription>We sent a 6-digit code to your email.</CardDescription>
    </CardHeader>
    <CardContent>
      <form @submit.prevent="handleSubmit">
        <FieldGroup>
          <Field>
            <FieldLabel for="otp" class="sr-only">
              Verification code
            </FieldLabel>
            <InputOTP
              id="otp"
              v-model="otpValue"
              :maxlength="6"
              class="justify-center"
              :disabled="isBusy"
              required
            >
              <InputOTPGroup class="gap-2.5 *:data-[slot=input-otp-slot]:rounded-md *:data-[slot=input-otp-slot]:border">
                <InputOTPSlot :index="0" />
                <InputOTPSlot :index="1" />
                <InputOTPSlot :index="2" />
                <InputOTPSlot :index="3" />
                <InputOTPSlot :index="4" />
                <InputOTPSlot :index="5" />
              </InputOTPGroup>
            </InputOTP>
            <FieldDescription class="text-center">
              Enter the 6-digit code sent to your email.
            </FieldDescription>
          </Field>
          <Button type="submit" :disabled="isBusy || otpValue.length !== 6">
            <IconLoader2 v-if="isVerifying" class="size-4 animate-spin" />
            {{ isVerifying ? "Verifying..." : "Verify" }}
          </Button>
          <FieldDescription class="text-center">
            Didn't receive the code?
            <Button
              type="button"
              variant="link"
              class="h-auto p-0 align-baseline underline-offset-4"
              :disabled="isBusy"
              @click="handleResend"
            >
              <IconLoader2 v-if="isResending" class="size-4 animate-spin" />
              {{ isResending ? "Resending..." : "Resend" }}
            </Button>
          </FieldDescription>
          <p v-if="resendMessage" class="text-center text-sm text-green-600">{{ resendMessage }}</p>
        </FieldGroup>
      </form>
    </CardContent>
  </Card>
  <ErrorPopup
    v-if="errorMessage"
    title="Verification failed"
    :message="errorMessage"
    @close="dismissError"
  />
  </div>
</template>
