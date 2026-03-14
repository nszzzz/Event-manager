<script setup lang="ts">
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card"
import EventListItem from "@/components/events/EventListItem.vue"
import { Input } from "@/components/ui/input"
import { Skeleton } from "@/components/ui/skeleton"
import type { EventItem } from "@/lib/events"

const props = withDefaults(defineProps<{
  title: string
  description: string
  events: EventItem[]
  emptyText: string
  isLoading?: boolean
  loadError?: string
  showPastBadge?: boolean
  layout?: "list" | "grid"
  clickable?: boolean
  showDeleteAction?: boolean
  deletingIds?: Array<number | string>
  skeletonCount?: number
  searchQuery?: string
  monthFilter?: string
}>(), {
  isLoading: false,
  loadError: "",
  showPastBadge: false,
  layout: "list",
  clickable: true,
  showDeleteAction: false,
  deletingIds: () => [],
  skeletonCount: 6,
  searchQuery: "",
  monthFilter: "",
})

const emit = defineEmits<{
  openDetails: [event: EventItem]
  delete: [event: EventItem]
  "update:searchQuery": [value: string]
  "update:monthFilter": [value: string]
}>()

function isDeleting(event: EventItem) {
  const target = String(event.id)
  return props.deletingIds.some((id) => String(id) === target)
}

function handleSearchUpdate(value: string | number) {
  emit("update:searchQuery", String(value))
}

function handleMonthUpdate(value: string | number) {
  emit("update:monthFilter", String(value))
}
</script>

<template>
  <Card>
    <CardHeader class="gap-4">
      <div class="flex flex-col gap-1">
        <CardTitle>{{ props.title }}</CardTitle>
        <CardDescription>
          {{ props.description }}
        </CardDescription>
      </div>

      <div class="grid gap-2 md:ml-auto md:w-auto md:grid-cols-2">
        <Input
          :model-value="props.searchQuery"
          placeholder="Search in this section..."
          class="md:w-56"
          @update:model-value="handleSearchUpdate"
        />
        <Input
          :model-value="props.monthFilter"
          type="month"
          class="md:w-44"
          @update:model-value="handleMonthUpdate"
        />
      </div>
    </CardHeader>
    <CardContent>
      <div
        v-if="props.isLoading"
        :class="props.layout === 'grid' ? 'grid gap-4 sm:grid-cols-2 xl:grid-cols-3' : 'space-y-3'"
      >
        <div
          v-for="index in props.skeletonCount"
          :key="`${props.title}-skeleton-${index}`"
          class="event-loading-card rounded-lg border p-4"
        >
          <div class="flex items-center gap-3">
            <Skeleton class="h-9 w-9 rounded-full" />
            <div class="min-w-0 flex-1 space-y-2">
              <Skeleton class="h-4 w-3/4" />
              <Skeleton class="h-3 w-1/2" />
            </div>
          </div>
          <div class="mt-3 space-y-2">
            <Skeleton class="h-3 w-full" />
            <Skeleton class="h-3 w-5/6" />
          </div>
        </div>
      </div>
      <div v-else-if="props.loadError" class="text-sm text-destructive">
        {{ props.loadError }}
      </div>
      <div v-else-if="props.events.length === 0" class="text-sm text-muted-foreground">
        {{ props.emptyText }}
      </div>
      <div v-else :class="props.layout === 'grid' ? 'grid gap-4 sm:grid-cols-2 xl:grid-cols-3' : 'space-y-3'">
        <EventListItem
          v-for="event in props.events"
          :key="`${props.title}-${event.id}`"
          :event="event"
          :show-past-badge="props.showPastBadge"
          :clickable="props.clickable"
          :show-delete-action="props.showDeleteAction"
          :is-deleting="isDeleting(event)"
          @open-details="emit('openDetails', $event)"
          @delete="emit('delete', $event)"
        />
      </div>
    </CardContent>
  </Card>
</template>

<style scoped>
@keyframes event-shimmer {
  100% {
    transform: translateX(100%);
  }
}

.event-loading-card {
  position: relative;
  overflow: hidden;
}

.event-loading-card::after {
  content: "";
  position: absolute;
  inset: 0;
  transform: translateX(-100%);
  background: linear-gradient(
    90deg,
    transparent,
    rgb(255 255 255 / 35%),
    transparent
  );
  animation: event-shimmer 1.9s infinite;
}
</style>
