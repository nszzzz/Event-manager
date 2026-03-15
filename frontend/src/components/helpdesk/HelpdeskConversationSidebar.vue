<script setup lang="ts">
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card"
import { Badge } from "@/components/ui/badge"
import { Skeleton } from "@/components/ui/skeleton"
import HelpConversationCard from "@/components/help/HelpConversationCard.vue"
import type { ChatConversation } from "@/lib/help-chat"
import { getUserDisplayName } from "@/lib/help-chat"

defineProps<{
  isLoading: boolean
  error: string
  queueConversations: ChatConversation[]
  assignedConversations: ChatConversation[]
  selectedConversationId: string
}>()

const emit = defineEmits<{
  selectConversation: [conversation: ChatConversation]
}>()
</script>

<template>
  <Card class="h-full min-h-0 flex flex-col overflow-hidden">
    <CardHeader>
      <CardTitle>Helpdesk Conversations</CardTitle>
      <CardDescription>
        Accept waiting conversations and continue active support chats.
      </CardDescription>
    </CardHeader>

    <CardContent class="min-h-0 flex-1 space-y-4 overflow-y-auto">
      <div v-if="isLoading" class="space-y-2">
        <Skeleton v-for="index in 4" :key="`helpdesk-list-loading-${index}`" class="h-20 w-full rounded-xl" />
      </div>

      <p v-else-if="error" class="text-sm text-destructive">
        {{ error }}
      </p>

      <template v-else>
        <section class="space-y-2">
          <div class="flex items-center justify-between">
            <h3 class="text-sm font-semibold">Waiting Queue</h3>
            <Badge variant="secondary">{{ queueConversations.length }}</Badge>
          </div>

          <p
            v-if="queueConversations.length === 0"
            class="rounded-xl border border-dashed p-3 text-xs text-muted-foreground"
          >
            No conversations are waiting for an agent.
          </p>

          <div v-else class="space-y-2">
            <HelpConversationCard
              v-for="conversation in queueConversations"
              :key="`queue-${conversation.id}`"
              :conversation="conversation"
              :selected="selectedConversationId === String(conversation.id)"
              :subtitle="`From ${getUserDisplayName(conversation.user, `User #${conversation.user_id}`)}`"
              @select="emit('selectConversation', $event)"
            />
          </div>
        </section>

        <section class="space-y-2">
          <div class="flex items-center justify-between">
            <h3 class="text-sm font-semibold">Assigned To Me</h3>
            <Badge>{{ assignedConversations.length }}</Badge>
          </div>

          <p
            v-if="assignedConversations.length === 0"
            class="rounded-xl border border-dashed p-3 text-xs text-muted-foreground"
          >
            You have no active assigned conversations.
          </p>

          <div v-else class="space-y-2">
            <HelpConversationCard
              v-for="conversation in assignedConversations"
              :key="`assigned-${conversation.id}`"
              :conversation="conversation"
              :selected="selectedConversationId === String(conversation.id)"
              :subtitle="`User: ${getUserDisplayName(conversation.user, `User #${conversation.user_id}`)}`"
              @select="emit('selectConversation', $event)"
            />
          </div>
        </section>
      </template>
    </CardContent>
  </Card>
</template>
