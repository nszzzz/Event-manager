<script setup lang="ts">
import { onBeforeUnmount, onMounted } from "vue"
import { TriangleAlert, X } from "lucide-vue-next"
import { Card, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"

interface Props {
  message: string
  title?: string
  closeLabel?: string
  autoCloseMs?: number
}

const props = withDefaults(defineProps<Props>(), {
  title: "Error",
  closeLabel: "Close",
  autoCloseMs: 5000,
})

const emit = defineEmits<{
  (e: "close"): void
}>()

let closeTimer: ReturnType<typeof setTimeout> | null = null

function closePopup() {
  if (closeTimer) {
    clearTimeout(closeTimer)
    closeTimer = null
  }

  emit("close")
}

onMounted(() => {
  closeTimer = setTimeout(closePopup, props.autoCloseMs)
})

onBeforeUnmount(() => {
  if (closeTimer) {
    clearTimeout(closeTimer)
  }
})
</script>

<template>
  <div v-if="message" class="fixed right-4 top-4 z-50 w-[calc(100%-2rem)] max-w-sm">
    <Card class="w-full border-destructive/50 shadow-lg" role="alert">
      <CardHeader class="relative pr-10">
        <button
          type="button"
          class="absolute right-4 top-4 text-muted-foreground transition-colors hover:text-foreground"
          :aria-label="closeLabel"
          @click="closePopup"
        >
          <X class="size-4" />
        </button>
        <CardTitle class="flex items-center gap-2 text-destructive">
          <TriangleAlert class="size-5" />
          {{ title }}
        </CardTitle>
        <CardDescription class="text-foreground">
          {{ message }}
        </CardDescription>
      </CardHeader>
    </Card>
  </div>
</template>
