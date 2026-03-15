import { computed, onMounted, onUnmounted, ref } from "vue"
import { useAuthStore } from "@/stores/auth"
import type { ChatConversation, ChatMessage } from "@/lib/help-chat"
import {
  getAssignedAgent,
  getUserDisplayName,
} from "@/lib/help-chat"
import { extractApiError } from "@/lib/events"

export function useHelpPanel() {
  const authStore = useAuthStore()

  const conversations = ref<ChatConversation[]>([])
  const selectedConversationId = ref<string>("")
  const selectedConversation = ref<ChatConversation | null>(null)
  const messages = ref<ChatMessage[]>([])

  const isLoadingConversations = ref(false)
  const isLoadingMessages = ref(false)
  const isCreatingConversation = ref(false)
  const isSendingMessage = ref(false)
  const isRequestingAgent = ref(false)

  const listError = ref("")
  const chatError = ref("")
  const composerError = ref("")
  const createError = ref("")

  const newConversationSubject = ref("")
  const messageInput = ref("")
  const acknowledgedBotMessageIdByConversation = ref<Record<string, string>>({})

  let pollingTimer: ReturnType<typeof setInterval> | null = null

  const currentUserId = computed(() => authStore.user?.id ?? null)

  const sortedConversations = computed(() =>
    [...conversations.value].sort((a, b) => {
      const left = Date.parse(a.updated_at ?? "") || 0
      const right = Date.parse(b.updated_at ?? "") || 0
      return right - left
    }),
  )

  const latestBotMessageId = computed(() => {
    for (let index = messages.value.length - 1; index >= 0; index -= 1) {
      const message = messages.value[index]
      if (!message) {
        continue
      }

      if (message.sender_type === "bot") {
        return String(message.id)
      }
    }

    return ""
  })

  const showBotDecisionActions = computed(() => {
    if (!selectedConversation.value) return false
    if (selectedConversation.value.status !== "bot_active") return false
    if (!latestBotMessageId.value) return false

    const key = String(selectedConversation.value.id)
    return acknowledgedBotMessageIdByConversation.value[key] !== latestBotMessageId.value
  })

  const canSendMessage = computed(() => {
    if (!selectedConversation.value) return false
    return selectedConversation.value.status !== "closed"
  })

  const activeAgentName = computed(() => {
    const agent = getAssignedAgent(selectedConversation.value)
    if (!agent) return ""
    return getUserDisplayName(agent, "Agent")
  })

  onMounted(async () => {
    if (!authStore.user) {
      await authStore.getUser()
    }

    await fetchConversations()

    pollingTimer = setInterval(() => {
      void fetchConversations({ silent: true })
      if (selectedConversationId.value) {
        void fetchConversationDetail(selectedConversationId.value, { silent: true })
      }
    }, 10000)
  })

  onUnmounted(() => {
    if (pollingTimer) {
      clearInterval(pollingTimer)
      pollingTimer = null
    }
  })

  function getAuthHeaders(withJson = false) {
    const token = localStorage.getItem("token")

    return {
      ...(withJson ? { "Content-Type": "application/json" } : {}),
      ...(token ? { authorization: `Bearer ${token}` } : {}),
    }
  }

  function upsertConversation(conversation: ChatConversation) {
    const key = String(conversation.id)
    const index = conversations.value.findIndex((item) => String(item.id) === key)

    if (index === -1) {
      conversations.value = [conversation, ...conversations.value]
      return
    }

    conversations.value = conversations.value.map((item) =>
      String(item.id) === key ? { ...item, ...conversation } : item,
    )
  }

  function removeConversationIfMissing() {
    if (!selectedConversationId.value) return

    const exists = conversations.value.some((item) => String(item.id) === selectedConversationId.value)
    if (exists) return

    selectedConversationId.value = ""
    selectedConversation.value = null
    messages.value = []
  }

  async function fetchConversations(options: { silent?: boolean } = {}) {
    if (!options.silent) {
      isLoadingConversations.value = true
    }
    listError.value = ""

    try {
      const res = await fetch("/api/conversations", {
        headers: getAuthHeaders(),
      })
      const data = await res.json()

      if (!res.ok) {
        listError.value = extractApiError(data, "Could not load conversations.")
        return
      }

      conversations.value = Array.isArray(data) ? (data as ChatConversation[]) : []
      removeConversationIfMissing()

      if (!selectedConversationId.value && conversations.value.length > 0) {
        const first = conversations.value[0]
        if (first) {
          selectedConversationId.value = String(first.id)
          await fetchConversationDetail(selectedConversationId.value, { silent: true })
        }
      }
    } catch {
      listError.value = "Could not load conversations."
    } finally {
      if (!options.silent) {
        isLoadingConversations.value = false
      }
    }
  }

  async function fetchConversationDetail(
    conversationId: string,
    options: { silent?: boolean } = {},
  ) {
    if (!conversationId) return

    if (!options.silent) {
      isLoadingMessages.value = true
    }
    chatError.value = ""

    try {
      const res = await fetch(`/api/conversations/${conversationId}`, {
        headers: getAuthHeaders(),
      })
      const data = await res.json()

      if (!res.ok) {
        chatError.value = extractApiError(data, "Could not load conversation details.")
        return
      }

      const conversation = data as ChatConversation
      selectedConversationId.value = String(conversation.id)
      selectedConversation.value = conversation
      messages.value = Array.isArray(conversation.messages) ? conversation.messages : []
      upsertConversation(conversation)
    } catch {
      chatError.value = "Could not load conversation details."
    } finally {
      if (!options.silent) {
        isLoadingMessages.value = false
      }
    }
  }

  async function createConversation() {
    createError.value = ""

    const subject = newConversationSubject.value.trim() || "Help Request"
    isCreatingConversation.value = true

    try {
      const res = await fetch("/api/conversations", {
        method: "POST",
        headers: getAuthHeaders(true),
        body: JSON.stringify({
          channel: "web_chat",
          subject,
        }),
      })
      const data = await res.json()

      if (!res.ok) {
        createError.value = extractApiError(data, "Could not create conversation.")
        return
      }

      const conversation = data?.conversation as ChatConversation
      if (conversation) {
        upsertConversation(conversation)
        selectedConversationId.value = String(conversation.id)
        await fetchConversationDetail(selectedConversationId.value)
        newConversationSubject.value = ""
      }
    } catch {
      createError.value = "Could not create conversation."
    } finally {
      isCreatingConversation.value = false
    }
  }

  async function selectConversation(conversation: ChatConversation) {
    const targetId = String(conversation.id)
    if (targetId === selectedConversationId.value && selectedConversation.value) {
      return
    }

    selectedConversationId.value = targetId
    await fetchConversationDetail(targetId)
  }

  async function sendMessage() {
    composerError.value = ""

    if (!selectedConversation.value) {
      composerError.value = "Select a conversation first."
      return
    }

    const content = messageInput.value.trim()
    if (content === "") {
      composerError.value = "Please type a message."
      return
    }

    if (!canSendMessage.value) {
      composerError.value = "This conversation is closed."
      return
    }

    isSendingMessage.value = true

    try {
      const res = await fetch("/api/messages", {
        method: "POST",
        headers: getAuthHeaders(true),
        body: JSON.stringify({
          conversation_id: selectedConversation.value.id,
          content,
          message_type: "text",
        }),
      })

      const data = await res.json()
      if (!res.ok) {
        composerError.value = extractApiError(data, "Could not send message.")
        return
      }

      messageInput.value = ""
      await fetchConversationDetail(String(selectedConversation.value.id), { silent: true })
      await fetchConversations({ silent: true })
    } catch {
      composerError.value = "Could not send message."
    } finally {
      isSendingMessage.value = false
    }
  }

  function acknowledgeBotReply() {
    if (!selectedConversation.value || !latestBotMessageId.value) return

    acknowledgedBotMessageIdByConversation.value = {
      ...acknowledgedBotMessageIdByConversation.value,
      [String(selectedConversation.value.id)]: latestBotMessageId.value,
    }
  }

  async function requestHumanAgent() {
    if (!selectedConversation.value) return

    isRequestingAgent.value = true
    composerError.value = ""

    try {
      const res = await fetch(`/api/conversations/${selectedConversation.value.id}/request-agent`, {
        method: "POST",
        headers: getAuthHeaders(),
      })
      const data = await res.json()

      if (!res.ok) {
        composerError.value = extractApiError(data, "Could not request a human agent.")
        return
      }

      const conversation = data?.conversation as ChatConversation
      if (conversation) {
        upsertConversation(conversation)
      }

      await fetchConversationDetail(String(selectedConversation.value.id), { silent: true })
      await fetchConversations({ silent: true })
    } catch {
      composerError.value = "Could not request a human agent."
    } finally {
      isRequestingAgent.value = false
    }
  }

  return {
    sortedConversations,
    selectedConversationId,
    selectedConversation,
    messages,
    isLoadingConversations,
    isLoadingMessages,
    isCreatingConversation,
    isSendingMessage,
    isRequestingAgent,
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
  }
}
