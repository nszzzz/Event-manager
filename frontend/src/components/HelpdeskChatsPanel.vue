<script setup lang="ts">
import HelpdeskConversationSidebar from "@/components/helpdesk/HelpdeskConversationSidebar.vue"
import HelpdeskConversationChatPane from "@/components/helpdesk/HelpdeskConversationChatPane.vue"
import { useHelpdeskChatsPanel } from "@/composables/useHelpdeskChatsPanel"

const {
  sortedQueueConversations,
  sortedAssignedConversations,
  selectedConversationId,
  selectedConversation,
  messages,
  isLoadingLists,
  isLoadingMessages,
  isAccepting,
  isClosing,
  isSending,
  listError,
  chatError,
  composerError,
  messageInput,
  currentUserId,
  canAcceptConversation,
  canCloseConversation,
  canSendMessage,
  selectConversation,
  acceptConversation,
  closeConversation,
  sendMessage,
} = useHelpdeskChatsPanel()
</script>

<template>
  <div class="grid min-h-0 flex-1 gap-4 xl:grid-cols-[360px_minmax(0,1fr)]">
    <HelpdeskConversationSidebar
      :is-loading="isLoadingLists"
      :error="listError"
      :queue-conversations="sortedQueueConversations"
      :assigned-conversations="sortedAssignedConversations"
      :selected-conversation-id="selectedConversationId"
      @select-conversation="selectConversation"
    />

    <HelpdeskConversationChatPane
      :selected-conversation="selectedConversation"
      :messages="messages"
      :current-user-id="currentUserId"
      :is-loading-messages="isLoadingMessages"
      :chat-error="chatError"
      :can-accept-conversation="canAcceptConversation"
      :is-accepting="isAccepting"
      :can-close-conversation="canCloseConversation"
      :is-closing="isClosing"
      :message-input="messageInput"
      :is-sending="isSending"
      :can-send-message="canSendMessage"
      :composer-error="composerError"
      @accept-conversation="acceptConversation"
      @close-conversation="closeConversation"
      @update:message-input="messageInput = $event"
      @send-message="sendMessage"
    />
  </div>
</template>

