<script setup lang="ts">
import { Badge } from "@/components/ui/badge"
import type { ChatConversation } from "@/lib/help-chat"
import {
  formatDateTime,
  getConversationTitle,
  getStatusLabel,
  getStatusVariant,
} from "@/lib/help-chat"

const props = withDefaults(defineProps<{
  conversation: ChatConversation
  selected?: boolean
  subtitle?: string
}>(), {
  selected: false,
  subtitle: "",
})

const emit = defineEmits<{
  select: [conversation: ChatConversation]
}>()
</script>

<template>
  <button
    type="button"
    class="w-full rounded-xl border px-4 py-3 text-left transition hover:border-primary/50 hover:bg-accent/40"
    :class="props.selected ? 'border-primary bg-primary/5 shadow-sm' : 'border-border bg-background/75'"
    @click="emit('select', props.conversation)"
  >
    <div class="flex items-start justify-between gap-2">
      <p class="line-clamp-1 text-sm font-semibold">
        {{ getConversationTitle(props.conversation) }}
      </p>
      <Badge :variant="getStatusVariant(props.conversation.status)">
        {{ getStatusLabel(props.conversation.status) }}
      </Badge>
    </div>

    <p v-if="props.subtitle" class="mt-1 line-clamp-1 text-xs text-muted-foreground">
      {{ props.subtitle }}
    </p>

    <div class="mt-2 flex items-center justify-between gap-2 text-xs text-muted-foreground">
      <span>
        {{ props.conversation.messages_count ?? 0 }} messages
      </span>
      <span>
        {{ formatDateTime(props.conversation.updated_at) }}
      </span>
    </div>
  </button>
</template>
