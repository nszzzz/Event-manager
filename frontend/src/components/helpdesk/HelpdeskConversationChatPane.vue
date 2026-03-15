<script setup lang="ts">
import { nextTick, ref, watch } from "vue"
import { IconCheck, IconLock, IconSend2 } from "@tabler/icons-vue"
import { Button } from "@/components/ui/button"
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card"
import { Label } from "@/components/ui/label"
import { Badge } from "@/components/ui/badge"
import { Skeleton } from "@/components/ui/skeleton"
import HelpMessageBubble from "@/components/help/HelpMessageBubble.vue"
import type { ChatConversation, ChatMessage } from "@/lib/help-chat"
import {
  formatDateTime,
  getAssignedAgent,
  getConversationTitle,
  getStatusLabel,
  getStatusVariant,
  getUserDisplayName,
} from "@/lib/help-chat"

const props = defineProps<{
  selectedConversation: ChatConversation | null
  messages: ChatMessage[]
  currentUserId: number | string | null
  isLoadingMessages: boolean
  chatError: string
  canAcceptConversation: boolean
  isAccepting: boolean
  canCloseConversation: boolean
  isClosing: boolean
  messageInput: string
  isSending: boolean
  canSendMessage: boolean
  composerError: string
}>()

const emit = defineEmits<{
  acceptConversation: []
  closeConversation: []
  "update:messageInput": [value: string]
  sendMessage: []
}>()

const messageContainerRef = ref<HTMLElement | null>(null)

watch(
  () => props.messages.length,
  async () => {
    await nextTick()
    if (!messageContainerRef.value) return
    messageContainerRef.value.scrollTop = messageContainerRef.value.scrollHeight
  },
)

function handleMessageInput(event: Event) {
  const target = event.target as HTMLTextAreaElement
  emit("update:messageInput", target.value)
}
</script>

<template>
  <Card class="min-h-0 flex flex-col">
    <CardHeader v-if="selectedConversation">
      <div class="flex items-start justify-between gap-3">
        <div>
          <CardTitle>{{ getConversationTitle(selectedConversation) }}</CardTitle>
          <CardDescription>
            User: {{ getUserDisplayName(selectedConversation.user, `User #${selectedConversation.user_id}`) }} |
            Updated: {{ formatDateTime(selectedConversation.updated_at) }}
          </CardDescription>
        </div>
        <Badge :variant="getStatusVariant(selectedConversation.status)">
          {{ getStatusLabel(selectedConversation.status) }}
        </Badge>
      </div>

      <div class="flex flex-wrap items-center gap-2">
        <Button
          v-if="canAcceptConversation"
          size="sm"
          :disabled="isAccepting"
          @click="emit('acceptConversation')"
        >
          <IconCheck class="size-4" />
          {{ isAccepting ? "Accepting..." : "Accept Conversation" }}
        </Button>

        <Button
          v-if="canCloseConversation"
          size="sm"
          variant="outline"
          :disabled="isClosing"
          @click="emit('closeConversation')"
        >
          <IconLock class="size-4" />
          {{ isClosing ? "Closing..." : "Close Conversation" }}
        </Button>
      </div>

      <p
        v-if="selectedConversation.status === 'agent_active' && getAssignedAgent(selectedConversation)"
        class="text-xs text-muted-foreground"
      >
        Assigned agent: {{ getUserDisplayName(getAssignedAgent(selectedConversation), "Agent") }}
      </p>
    </CardHeader>

    <CardContent
      ref="messageContainerRef"
      class="help-chat-scroll min-h-0 flex-1 space-y-3 overflow-y-auto"
    >
      <div v-if="!selectedConversation" class="rounded-xl border border-dashed p-5 text-sm text-muted-foreground">
        Select a conversation from queue or assigned list.
      </div>

      <div v-else-if="isLoadingMessages" class="space-y-2">
        <Skeleton v-for="index in 6" :key="`helpdesk-chat-loading-${index}`" class="h-14 w-full rounded-xl" />
      </div>

      <p v-else-if="chatError" class="text-sm text-destructive">
        {{ chatError }}
      </p>

      <div v-else-if="messages.length === 0" class="rounded-xl border border-dashed p-5 text-sm text-muted-foreground">
        No messages yet in this conversation.
      </div>

      <div v-else class="space-y-3">
        <HelpMessageBubble
          v-for="message in messages"
          :key="message.id"
          :message="message"
          :current-user-id="currentUserId"
        />
      </div>
    </CardContent>

    <div class="border-t p-4">
      <div class="flex items-end gap-2">
        <div class="flex-1 space-y-1">
          <Label for="agent-message-input">Message</Label>
          <textarea
            id="agent-message-input"
            :value="messageInput"
            class="border-input placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-ring/50 min-h-20 w-full resize-y rounded-md border bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:ring-[3px] disabled:cursor-not-allowed disabled:opacity-50"
            placeholder="Type your reply..."
            :disabled="!selectedConversation || !canSendMessage || isSending"
            @input="handleMessageInput"
          />
        </div>
        <Button
          class="mb-0.5 shrink-0"
          :disabled="!selectedConversation || !canSendMessage || isSending"
          @click="emit('sendMessage')"
        >
          <IconSend2 class="size-4" />
          {{ isSending ? "Sending..." : "Send" }}
        </Button>
      </div>
      <p v-if="composerError" class="mt-2 text-xs text-destructive">
        {{ composerError }}
      </p>
    </div>
  </Card>
</template>
