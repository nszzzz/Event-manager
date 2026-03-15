<script setup lang="ts">
import HelpConversationSidebar from "@/components/help/HelpConversationSidebar.vue"
import HelpConversationChatPane from "@/components/help/HelpConversationChatPane.vue"
import { useHelpPanel } from "@/composables/useHelpPanel"

const {
  sortedConversations,
  selectedConversationId,
  selectedConversation,
  messages,
  isLoadingConversations,
  isLoadingMessages,
  isCreatingConversation,
  isSendingMessage,
  isRequestingAgent,
  isResolvingConversation,
  listError,
  chatError,
  composerError,
  createError,
  newConversationSubject,
  messageInput,
  currentUserId,
  showBotDecisionActions,
  latestBotMessageId,
  activeAgentName,
  canSendMessage,
  createConversation,
  selectConversation,
  sendMessage,
  acknowledgeBotReply,
  requestHumanAgent,
} = useHelpPanel()
</script>

<template>
  <div class="grid h-full min-h-0 flex-1 gap-4 overflow-hidden xl:grid-cols-[340px_minmax(0,1fr)]">
    <HelpConversationSidebar
      :is-loading="isLoadingConversations"
      :error="listError"
      :conversations="sortedConversations"
      :selected-conversation-id="selectedConversationId"
      :new-conversation-subject="newConversationSubject"
      :is-creating-conversation="isCreatingConversation"
      :create-error="createError"
      @update:new-conversation-subject="newConversationSubject = $event"
      @create-conversation="createConversation"
      @select-conversation="selectConversation"
    />

    <HelpConversationChatPane
      :selected-conversation="selectedConversation"
      :messages="messages"
      :current-user-id="currentUserId"
      :is-loading-messages="isLoadingMessages"
      :chat-error="chatError"
      :show-bot-decision-actions="showBotDecisionActions"
      :latest-bot-message-id="latestBotMessageId"
      :is-requesting-agent="isRequestingAgent"
      :is-resolving-conversation="isResolvingConversation"
      :active-agent-name="activeAgentName"
      :message-input="messageInput"
      :is-sending-message="isSendingMessage"
      :can-send-message="canSendMessage"
      :composer-error="composerError"
      @acknowledge-bot-reply="acknowledgeBotReply"
      @request-human-agent="requestHumanAgent"
      @update:message-input="messageInput = $event"
      @send-message="sendMessage"
    />
  </div>
</template>
