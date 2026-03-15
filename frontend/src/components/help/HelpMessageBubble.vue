<script setup lang="ts">
import { computed } from "vue"
import {
  Avatar,
  AvatarFallback,
} from "@/components/ui/avatar"
import type { ChatMessage, ChatUser } from "@/lib/help-chat"
import {
  formatDateTime,
  getUserAvatarStyle,
  getUserInitial,
} from "@/lib/help-chat"

const props = defineProps<{
  message: ChatMessage
  currentUserId?: number | string | null
}>()

const isMine = computed(() => {
  if (props.message.sender_type !== "user" && props.message.sender_type !== "agent") {
    return false
  }

  if (props.currentUserId === undefined || props.currentUserId === null) {
    return false
  }

  return String(props.message.sender_user_id ?? "") === String(props.currentUserId)
})

const senderUser = computed<ChatUser | null>(() => props.message.user ?? null)

const senderName = computed(() => {
  if (isMine.value) return "You"
  if (props.message.sender_type === "bot") return "Bot Assistant"
  if (props.message.sender_type === "system") return "System"
  return senderUser.value?.name?.trim() || "Agent"
})

const avatarSeed = computed(() => `${props.message.sender_type}-${senderName.value}`)
</script>

<template>
  <div
    class="flex gap-2"
    :class="isMine ? 'justify-end' : 'justify-start'"
  >
    <Avatar v-if="!isMine" class="mt-1 h-7 w-7">
      <AvatarFallback
        class="text-[11px] font-semibold"
        :style="getUserAvatarStyle(senderUser, avatarSeed)"
      >
        {{ getUserInitial(senderUser, senderName.charAt(0) || "U") }}
      </AvatarFallback>
    </Avatar>

    <div class="max-w-[88%] space-y-1 md:max-w-[75%]">
      <p
        class="text-[11px] font-medium"
        :class="isMine ? 'text-right text-muted-foreground' : 'text-muted-foreground'"
      >
        {{ senderName }}
      </p>
      <div class="space-y-1">
        <div
          class="rounded-2xl px-3 py-2 text-sm leading-relaxed shadow-sm"
          :class="isMine
            ? 'bg-primary text-primary-foreground rounded-tr-sm'
            : props.message.sender_type === 'system'
              ? 'border border-dashed bg-muted/60 text-muted-foreground rounded-tl-sm'
              : props.message.sender_type === 'bot'
                ? 'border bg-background rounded-tl-sm'
                : 'border bg-accent/40 rounded-tl-sm'"
        >
          {{ props.message.content }}
        </div>
        <p
          class="text-[10px] text-muted-foreground"
          :class="isMine ? 'text-right' : ''"
        >
          {{ formatDateTime(props.message.created_at ?? props.message.updated_at ?? null) }}
        </p>
      </div>
    </div>

    <div v-if="isMine" class="mt-1 h-7 w-7 shrink-0" />
  </div>
</template>
