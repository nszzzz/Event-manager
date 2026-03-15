<script setup lang="ts">
import { IconMessagePlus } from "@tabler/icons-vue"
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
import { Skeleton } from "@/components/ui/skeleton"
import HelpConversationCard from "@/components/help/HelpConversationCard.vue"
import type { ChatConversation } from "@/lib/help-chat"

defineProps<{
  isLoading: boolean
  error: string
  conversations: ChatConversation[]
  selectedConversationId: string
  newConversationSubject: string
  isCreatingConversation: boolean
  createError: string
}>()

const emit = defineEmits<{
  "update:newConversationSubject": [value: string]
  createConversation: []
  selectConversation: [conversation: ChatConversation]
}>()

function handleSubjectUpdate(value: string | number) {
  emit("update:newConversationSubject", String(value))
}
</script>

<template>
  <Card class="h-full min-h-0 flex flex-col overflow-hidden">
    <CardHeader>
      <CardTitle>Help Conversations</CardTitle>
      <CardDescription>
        Review your previous chats or start a new support conversation.
      </CardDescription>
    </CardHeader>

    <CardContent class="min-h-0 flex-1 space-y-3 overflow-y-auto">
      <div class="rounded-xl border border-dashed border-primary/35 bg-primary/5 p-3">
        <div class="flex items-center gap-2 text-sm font-medium">
          <IconMessagePlus class="size-4" />
          <span>New Conversation</span>
        </div>
        <div class="mt-3 space-y-2">
          <Label for="new-conversation-subject">Subject (optional)</Label>
          <Input
            id="new-conversation-subject"
            :model-value="newConversationSubject"
            placeholder="Example: I need help with my account"
            :disabled="isCreatingConversation"
            @update:model-value="handleSubjectUpdate"
          />
        </div>
        <p v-if="createError" class="mt-2 text-xs text-destructive">
          {{ createError }}
        </p>
        <Button
          class="mt-3 w-full"
          :disabled="isCreatingConversation"
          @click="emit('createConversation')"
        >
          {{ isCreatingConversation ? "Creating..." : "Start New Conversation" }}
        </Button>
      </div>

      <div v-if="isLoading" class="space-y-2">
        <Skeleton v-for="index in 3" :key="`conversation-loading-${index}`" class="h-20 w-full rounded-xl" />
      </div>

      <p v-else-if="error" class="text-sm text-destructive">
        {{ error }}
      </p>

      <p
        v-else-if="conversations.length === 0"
        class="rounded-xl border border-dashed p-4 text-sm text-muted-foreground"
      >
        No conversations yet. Create your first help conversation.
      </p>

      <div v-else class="space-y-2">
        <HelpConversationCard
          v-for="conversation in conversations"
          :key="conversation.id"
          :conversation="conversation"
          :selected="selectedConversationId === String(conversation.id)"
          @select="emit('selectConversation', $event)"
        />
      </div>
    </CardContent>
  </Card>
</template>
