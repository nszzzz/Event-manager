<script setup lang="ts">
import { computed, ref, watch } from "vue"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import {
  Sheet,
  SheetContent,
  SheetDescription,
  SheetHeader,
  SheetTitle,
} from "@/components/ui/sheet"
import type { EventItem } from "@/lib/events"

interface EventUpdatePayload {
  title: string
  occurrenceAt: string
  description: string
}

const props = withDefaults(defineProps<{
  open: boolean
  event: EventItem | null
  isUpdating?: boolean
  errorMessage?: string
}>(), {
  event: null,
  isUpdating: false,
  errorMessage: "",
})

const emit = defineEmits<{
  "update:open": [open: boolean]
  submit: [payload: EventUpdatePayload]
}>()

const title = ref("")
const occurrenceAt = ref("")
const description = ref("")

const hasEvent = computed(() => props.event !== null)

watch(
  () => [props.open, props.event] as const,
  () => {
    if (!props.open || !props.event) {
      return
    }

    title.value = props.event.title ?? ""
    occurrenceAt.value = toLocalDateTimeInputValue(props.event.occurrence_at)
    description.value = props.event.description ?? ""
  },
  { immediate: true },
)

function submitUpdate() {
  emit("submit", {
    title: title.value,
    occurrenceAt: occurrenceAt.value,
    description: description.value,
  })
}

function toLocalDateTimeInputValue(dateValue: string) {
  const date = new Date(dateValue)

  if (Number.isNaN(date.getTime())) {
    return dateValue.slice(0, 16)
  }

  const year = date.getFullYear()
  const month = `${date.getMonth() + 1}`.padStart(2, "0")
  const day = `${date.getDate()}`.padStart(2, "0")
  const hours = `${date.getHours()}`.padStart(2, "0")
  const minutes = `${date.getMinutes()}`.padStart(2, "0")

  return `${year}-${month}-${day}T${hours}:${minutes}`
}
</script>

<template>
  <Sheet :open="props.open" @update:open="emit('update:open', $event)">
    <SheetContent side="right" class="w-full overflow-y-auto p-6 sm:max-w-lg">
      <SheetHeader>
        <SheetTitle>Update Event</SheetTitle>
        <SheetDescription>
          Modify the event details and save your changes.
        </SheetDescription>
      </SheetHeader>

      <form v-if="hasEvent" class="mt-6 space-y-4" @submit.prevent="submitUpdate">
        <div class="space-y-2">
          <Label for="edit-event-title">Title</Label>
          <Input
            id="edit-event-title"
            v-model="title"
            placeholder="Team sync"
            :disabled="props.isUpdating"
            required
          />
        </div>

        <div class="space-y-2">
          <Label for="edit-event-occurrence-at">Date and Time</Label>
          <Input
            id="edit-event-occurrence-at"
            v-model="occurrenceAt"
            type="datetime-local"
            :disabled="props.isUpdating"
            required
          />
        </div>

        <div class="space-y-2">
          <Label for="edit-event-description">Description (optional)</Label>
          <textarea
            id="edit-event-description"
            v-model="description"
            placeholder="Optional notes for this event."
            :disabled="props.isUpdating"
            class="border-input placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-ring/50 min-h-24 w-full rounded-md border bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:ring-[3px] disabled:cursor-not-allowed disabled:opacity-50"
          />
        </div>

        <p v-if="props.errorMessage" class="text-sm text-destructive">
          {{ props.errorMessage }}
        </p>

        <div class="flex items-center justify-end gap-2 pt-2">
          <Button
            type="button"
            variant="outline"
            :disabled="props.isUpdating"
            @click="emit('update:open', false)"
          >
            Cancel
          </Button>
          <Button type="submit" :disabled="props.isUpdating">
            {{ props.isUpdating ? "Updating..." : "Update Event" }}
          </Button>
        </div>
      </form>

      <p v-else class="mt-6 text-sm text-muted-foreground">
        Event data is unavailable.
      </p>
    </SheetContent>
  </Sheet>
</template>
