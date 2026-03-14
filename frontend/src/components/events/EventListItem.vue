<script setup lang="ts">
import {
  Avatar,
  AvatarFallback,
} from "@/components/ui/avatar"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu"
import {
  formatEventDate,
  getCreatorAvatarStyle,
  getCreatorInitial,
  getCreatorName,
  type EventItem,
} from "@/lib/events"
import { IconDotsVertical, IconPencil, IconTrash } from "@tabler/icons-vue"

const props = withDefaults(defineProps<{
  event: EventItem
  showPastBadge?: boolean
  clickable?: boolean
  showEditAction?: boolean
  showDeleteAction?: boolean
  isUpdating?: boolean
  isDeleting?: boolean
}>(), {
  showPastBadge: false,
  clickable: true,
  showEditAction: false,
  showDeleteAction: false,
  isUpdating: false,
  isDeleting: false,
})

const emit = defineEmits<{
  openDetails: [event: EventItem]
  edit: [event: EventItem]
  delete: [event: EventItem]
}>()

function handleOpenDetails() {
  if (!props.clickable) return
  emit("openDetails", props.event)
}

function handleDeleteAction() {
  if (props.isDeleting || props.isUpdating) return
  emit("delete", props.event)
}

function handleEditAction() {
  if (props.isUpdating || props.isDeleting) return
  emit("edit", props.event)
}
</script>

<template>
  <div
    class="bg-background/80 group flex items-start gap-3 rounded-lg border p-4 transition hover:border-primary/35 hover:shadow-sm"
    :class="props.clickable ? 'cursor-pointer' : ''"
    @click="handleOpenDetails"
  >
    <Avatar class="h-9 w-9">
      <AvatarFallback class="font-semibold" :style="getCreatorAvatarStyle(props.event)">
        {{ getCreatorInitial(props.event) }}
      </AvatarFallback>
    </Avatar>

    <div class="min-w-0 flex-1 space-y-1">
      <div class="flex flex-wrap items-center gap-2">
        <h3 class="truncate text-sm font-semibold">
          {{ props.event.title }}
        </h3>
        <Badge v-if="props.showPastBadge" variant="outline">
          Past
        </Badge>
      </div>
      <p class="text-xs text-muted-foreground">
        {{ formatEventDate(props.event.occurrence_at) }}
      </p>
      <p v-if="props.event.description" class="max-h-10 overflow-hidden text-sm text-foreground/90">
        {{ props.event.description }}
      </p>
      <p class="text-xs text-muted-foreground">
        Created by {{ getCreatorName(props.event) }}
      </p>
    </div>

    <DropdownMenu v-if="props.showDeleteAction || props.showEditAction">
      <DropdownMenuTrigger as-child>
        <Button
          variant="ghost"
          size="icon-sm"
          class="shrink-0"
          :disabled="props.isDeleting || props.isUpdating"
          @click.stop
        >
          <IconDotsVertical class="size-4" />
          <span class="sr-only">Open actions</span>
        </Button>
      </DropdownMenuTrigger>
      <DropdownMenuContent align="end">
        <DropdownMenuItem
          v-if="props.showEditAction"
          :disabled="props.isUpdating || props.isDeleting"
          @select="handleEditAction"
        >
          <IconPencil />
          {{ props.isUpdating ? "Updating..." : "Edit" }}
        </DropdownMenuItem>
        <DropdownMenuItem
          v-if="props.showDeleteAction"
          variant="destructive"
          :disabled="props.isDeleting || props.isUpdating"
          @select="handleDeleteAction"
        >
          <IconTrash />
          {{ props.isDeleting ? "Deleting..." : "Delete" }}
        </DropdownMenuItem>
      </DropdownMenuContent>
    </DropdownMenu>
  </div>
</template>
