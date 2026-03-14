<script setup lang="ts">
import {
  Avatar,
  AvatarFallback,
} from "@/components/ui/avatar"
import { Badge } from "@/components/ui/badge"
import {
  Sheet,
  SheetContent,
  SheetDescription,
  SheetHeader,
  SheetTitle,
} from "@/components/ui/sheet"
import {
  formatEventDate,
  getCreatorAvatarStyle,
  getCreatorInitial,
  getCreatorName,
  type EventItem,
} from "@/lib/events"

const props = withDefaults(defineProps<{
  open: boolean
  event: EventItem | null
}>(), {
  event: null,
})

const emit = defineEmits<{
  "update:open": [open: boolean]
}>()
</script>

<template>
  <Sheet :open="props.open" @update:open="emit('update:open', $event)">
    <SheetContent side="right" class="w-full overflow-y-auto p-6 sm:max-w-lg">
      <SheetHeader>
        <SheetTitle>Event Details</SheetTitle>
        <SheetDescription>
          Detailed information about this event.
        </SheetDescription>
      </SheetHeader>

      <div v-if="props.event" class="mt-6 space-y-6">
        <div class="space-y-2">
          <Badge variant="secondary">Event</Badge>
          <h2 class="text-xl font-semibold">
            {{ props.event.title }}
          </h2>
          <p class="text-sm text-muted-foreground">
            {{ formatEventDate(props.event.occurrence_at) }}
          </p>
        </div>

        <div class="rounded-lg border p-4">
          <p class="text-xs font-medium tracking-wide text-muted-foreground uppercase">
            Created By
          </p>
          <div class="mt-3 flex items-center gap-3">
            <Avatar class="h-9 w-9">
              <AvatarFallback class="font-semibold" :style="getCreatorAvatarStyle(props.event)">
                {{ getCreatorInitial(props.event) }}
              </AvatarFallback>
            </Avatar>
            <p class="text-sm font-medium">
              {{ getCreatorName(props.event) }}
            </p>
          </div>
        </div>

        <div class="rounded-lg border p-4">
          <p class="text-xs font-medium tracking-wide text-muted-foreground uppercase">
            Description
          </p>
          <p class="mt-2 text-sm leading-relaxed">
            {{ props.event.description?.trim() || "No description provided." }}
          </p>
        </div>
      </div>
    </SheetContent>
  </Sheet>
</template>
