<script setup lang="ts">
import { nextTick, ref, watch } from "vue"
import { IconCheck, IconHeadset, IconSend2 } from "@tabler/icons-vue"
import { Button } from "@/components/ui/button"
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card"
import { Input } from "@/components/ui/input"
import { Badge } from "@/components/ui/badge"
import { Skeleton } from "@/components/ui/skeleton"
import HelpMessageBubble from "@/components/help/HelpMessageBubble.vue"
import type { ChatConversation, ChatMessage } from "@/lib/help-chat"
import {
  formatDateTime,
  getConversationTitle,
  getStatusLabel,
  getStatusVariant,
} from "@/lib/help-chat"

const props = defineProps<{
  selectedConversation: ChatConversation | null
  messages: ChatMessage[]
  currentUserId: number | string | null
  isLoadingMessages: boolean
  chatError: string
  showBotDecisionActions: boolean
  latestBotMessageId: string
  isRequestingAgent: boolean
  isResolvingConversation: boolean
  activeAgentName: string
  messageInput: string
  isSendingMessage: boolean
  canSendMessage: boolean
  composerError: string
}>()

const emit = defineEmits<{
  acknowledgeBotReply: []
  requestHumanAgent: []
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

function handleInputUpdate(value: string | number) {
  emit("update:messageInput", String(value))
}

function handleSendHotkey() {
  emit("sendMessage")
}
</script>

<template>
  <Card class="h-full min-h-0 flex flex-col overflow-hidden">
    <CardHeader v-if="selectedConversation">
      <div class="flex items-start justify-between gap-3">
        <div>
          <CardTitle>{{ getConversationTitle(selectedConversation) }}</CardTitle>
          <CardDescription>
            Last updated: {{ formatDateTime(selectedConversation.updated_at) }}
          </CardDescription>
        </div>
        <Badge :variant="getStatusVariant(selectedConversation.status)">
          {{ getStatusLabel(selectedConversation.status) }}
        </Badge>
      </div>

      <p v-if="selectedConversation.status === 'waiting_for_agent'" class="text-sm text-muted-foreground">
        Human agent requested. You can continue chatting while we wait.
      </p>
      <p v-else-if="selectedConversation.status === 'agent_active'" class="text-sm text-emerald-600">
        {{ activeAgentName ? `${activeAgentName} has joined the conversation.` : "An agent has joined the conversation." }}
      </p>
      <p v-else-if="selectedConversation.status === 'closed'" class="text-sm text-muted-foreground">
        This conversation is closed.
      </p>
    </CardHeader>

    <CardContent
      ref="messageContainerRef"
      class="help-chat-scroll min-h-0 flex-1 space-y-3 overflow-y-auto pr-1"
    >
      <div v-if="!selectedConversation" class="rounded-xl border border-dashed p-5 text-sm text-muted-foreground">
        Select or create a conversation to start chatting.
      </div>

      <div v-else-if="isLoadingMessages" class="space-y-2">
        <Skeleton v-for="index in 6" :key="`message-loading-${index}`" class="h-14 w-full rounded-xl" />
      </div>

      <p v-else-if="chatError" class="text-sm text-destructive">
        {{ chatError }}
      </p>

      <div v-else-if="messages.length === 0" class="rounded-xl border border-dashed p-5 text-sm text-muted-foreground">
        No messages yet. Write your first question.
      </div>

      <template v-else>
        <template v-for="message in messages" :key="message.id">
          <HelpMessageBubble
            :message="message"
            :current-user-id="currentUserId"
          />

          <div
            v-if="showBotDecisionActions && String(message.id) === latestBotMessageId"
            class="ml-9 flex flex-wrap items-center gap-2 rounded-xl border bg-muted/25 p-2"
          >
            <Button
              type="button"
              size="sm"
              variant="outline"
              :disabled="isResolvingConversation || isRequestingAgent"
              @click="emit('acknowledgeBotReply')"
            >
              <IconCheck class="size-4" />
              {{ isResolvingConversation ? "Closing..." : "I Understand" }}
            </Button>
            <Button
              type="button"
              size="sm"
              :disabled="isRequestingAgent || isResolvingConversation"
              @click="emit('requestHumanAgent')"
            >
              <IconHeadset class="size-4" />
              {{ isRequestingAgent ? "Requesting..." : "Request Human Agent" }}
            </Button>
          </div>
        </template>
      </template>
    </CardContent>

    <div class="border-t p-3">
      <div class="flex items-center gap-2 rounded-xl border bg-background p-2">
        <Input
          id="help-message-input"
          :model-value="messageInput"
          class="h-10 border-0 bg-transparent shadow-none focus-visible:ring-0"
          placeholder="Type your question..."
          :disabled="!selectedConversation || isSendingMessage || !canSendMessage"
          @update:model-value="handleInputUpdate"
          @keydown.enter.prevent="handleSendHotkey"
        />
        <Button
          class="h-10 shrink-0 px-4"
          :disabled="!selectedConversation || isSendingMessage || !canSendMessage"
          @click="emit('sendMessage')"
        >
          <IconSend2 class="size-4" />
          {{ isSendingMessage ? "Sending..." : "Send" }}
        </Button>
      </div>
      <p v-if="composerError" class="mt-2 text-xs text-destructive">
        {{ composerError }}
      </p>
    </div>
  </Card>
</template>
