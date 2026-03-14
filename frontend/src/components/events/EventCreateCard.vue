<script setup lang="ts">
import { Button } from "@/components/ui/button"
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { IconPlus } from "@tabler/icons-vue"

const props = defineProps<{
  title: string
  occurrenceAt: string
  description: string
  isCreating: boolean
  formError: string
  formSuccess: string
}>()

const emit = defineEmits<{
  "update:title": [value: string]
  "update:occurrenceAt": [value: string]
  "update:description": [value: string]
  submit: []
}>()

function handleTitleUpdate(value: string | number) {
  emit("update:title", String(value))
}

function handleOccurrenceAtUpdate(value: string | number) {
  emit("update:occurrenceAt", String(value))
}

function handleDescriptionInput(event: Event) {
  const target = event.target as HTMLTextAreaElement
  emit("update:description", target.value)
}
</script>

<template>
  <Card>
    <CardHeader>
      <CardTitle class="flex items-center gap-2">
        <IconPlus class="size-4" />
        Create Event
      </CardTitle>
      <CardDescription>
        Title and date are required, description is optional.
      </CardDescription>
    </CardHeader>
    <CardContent class="space-y-4">
      <div class="grid gap-4 md:grid-cols-2">
        <div class="space-y-2">
          <Label for="event-title">Title</Label>
          <Input
            id="event-title"
            :model-value="props.title"
            placeholder="Team sync"
            @update:model-value="handleTitleUpdate"
          />
        </div>
        <div class="space-y-2">
          <Label for="event-occurrence-at">Date and Time</Label>
          <Input
            id="event-occurrence-at"
            :model-value="props.occurrenceAt"
            type="datetime-local"
            @update:model-value="handleOccurrenceAtUpdate"
          />
        </div>
      </div>

      <div class="space-y-2">
        <Label for="event-description">Description (optional)</Label>
        <textarea
          id="event-description"
          :value="props.description"
          placeholder="Optional notes for this event."
          class="border-input placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-ring/50 min-h-24 w-full rounded-md border bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:ring-[3px]"
          @input="handleDescriptionInput"
        />
      </div>

      <div class="flex items-center justify-between gap-3">
        <p v-if="props.formError" class="text-sm text-destructive">
          {{ props.formError }}
        </p>
        <p v-else-if="props.formSuccess" class="text-sm text-emerald-600">
          {{ props.formSuccess }}
        </p>
        <p v-else class="text-sm text-muted-foreground">
          Use this form to add a new event.
        </p>

        <Button :disabled="props.isCreating" @click="emit('submit')">
          {{ props.isCreating ? "Creating..." : "Create Event" }}
        </Button>
      </div>
    </CardContent>
  </Card>
</template>
