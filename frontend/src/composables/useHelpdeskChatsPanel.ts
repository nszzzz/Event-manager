import { computed, onMounted, onUnmounted, ref } from "vue"
import { useAuthStore } from "@/stores/auth"
import type { ChatConversation, ChatMessage } from "@/lib/help-chat"
import { extractApiError } from "@/lib/events"

export function useHelpdeskChatsPanel() {
  const authStore = useAuthStore()

  const assignedConversations = ref<ChatConversation[]>([])
  const queueConversations = ref<ChatConversation[]>([])

  const selectedConversationId = ref<string>("")
  const selectedConversation = ref<ChatConversation | null>(null)
  const messages = ref<ChatMessage[]>([])

  const isLoadingLists = ref(false)
  const isLoadingMessages = ref(false)
  const isAccepting = ref(false)
  const isClosing = ref(false)
  const isSending = ref(false)

  const listError = ref("")
  const chatError = ref("")
  const composerError = ref("")

  const messageInput = ref("")
  let pollingTimer: ReturnType<typeof setInterval> | null = null

  const currentUserId = computed(() => authStore.user?.id ?? null)

  const sortedQueueConversations = computed(() =>
    [...queueConversations.value].sort((a, b) => {
      const left = Date.parse(a.updated_at ?? "") || 0
      const right = Date.parse(b.updated_at ?? "") || 0
      return right - left
    }),
  )

  const sortedAssignedConversations = computed(() =>
    [...assignedConversations.value].sort((a, b) => {
      const left = Date.parse(a.updated_at ?? "") || 0
      const right = Date.parse(b.updated_at ?? "") || 0
      return right - left
    }),
  )

  const canAcceptConversation = computed(() => {
    if (!selectedConversation.value) return false
    return selectedConversation.value.status === "waiting_for_agent"
  })

  const canCloseConversation = computed(() => {
    if (!selectedConversation.value || currentUserId.value === null || currentUserId.value === undefined) {
      return false
    }

    return (
      selectedConversation.value.status === "agent_active"
      && String(selectedConversation.value.assigned_agent_id ?? "") === String(currentUserId.value)
    )
  })

  const canSendMessage = computed(() => {
    if (!selectedConversation.value || currentUserId.value === null || currentUserId.value === undefined) {
      return false
    }

    return (
      selectedConversation.value.status === "agent_active"
      && String(selectedConversation.value.assigned_agent_id ?? "") === String(currentUserId.value)
    )
  })

  onMounted(async () => {
    if (!authStore.user) {
      await authStore.getUser()
    }

    await fetchConversationLists()

    pollingTimer = setInterval(() => {
      void fetchConversationLists({ silent: true })
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

  function allConversationIds() {
    return [
      ...queueConversations.value.map((item) => String(item.id)),
      ...assignedConversations.value.map((item) => String(item.id)),
    ]
  }

  function keepSelectionValid() {
    if (!selectedConversationId.value) return

    const ids = allConversationIds()
    if (ids.includes(selectedConversationId.value)) return

    selectedConversationId.value = ""
    selectedConversation.value = null
    messages.value = []
  }

  async function fetchConversationLists(options: { silent?: boolean } = {}) {
    if (!options.silent) {
      isLoadingLists.value = true
    }
    listError.value = ""

    try {
      const [assignedRes, queueRes] = await Promise.all([
        fetch("/api/conversations", { headers: getAuthHeaders() }),
        fetch("/api/helpdesk/conversations/queue", { headers: getAuthHeaders() }),
      ])

      const assignedData = await assignedRes.json()
      const queueData = await queueRes.json()

      if (!assignedRes.ok) {
        listError.value = extractApiError(assignedData, "Could not load assigned conversations.")
        return
      }

      if (!queueRes.ok) {
        listError.value = extractApiError(queueData, "Could not load waiting queue.")
        return
      }

      assignedConversations.value = Array.isArray(assignedData) ? (assignedData as ChatConversation[]) : []
      queueConversations.value = Array.isArray(queueData) ? (queueData as ChatConversation[]) : []

      keepSelectionValid()

      if (!selectedConversationId.value) {
        const candidate = assignedConversations.value[0] ?? queueConversations.value[0]
        if (candidate) {
          selectedConversationId.value = String(candidate.id)
          await fetchConversationDetail(selectedConversationId.value, { silent: true })
        }
      }
    } catch {
      listError.value = "Could not load conversations."
    } finally {
      if (!options.silent) {
        isLoadingLists.value = false
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

      selectedConversation.value = data as ChatConversation
      selectedConversationId.value = String(selectedConversation.value.id)
      messages.value = Array.isArray(selectedConversation.value.messages) ? selectedConversation.value.messages : []
    } catch {
      chatError.value = "Could not load conversation details."
    } finally {
      if (!options.silent) {
        isLoadingMessages.value = false
      }
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

  async function acceptConversation() {
    if (!selectedConversation.value || !canAcceptConversation.value) return

    isAccepting.value = true
    composerError.value = ""

    try {
      const res = await fetch(`/api/conversations/${selectedConversation.value.id}/accept`, {
        method: "POST",
        headers: getAuthHeaders(),
      })
      const data = await res.json()

      if (!res.ok) {
        composerError.value = extractApiError(data, "Could not accept conversation.")
        return
      }

      await fetchConversationLists({ silent: true })
      await fetchConversationDetail(String(selectedConversation.value.id), { silent: true })
    } catch {
      composerError.value = "Could not accept conversation."
    } finally {
      isAccepting.value = false
    }
  }

  async function closeConversation() {
    if (!selectedConversation.value || !canCloseConversation.value) return

    isClosing.value = true
    composerError.value = ""

    try {
      const res = await fetch(`/api/conversations/${selectedConversation.value.id}/close`, {
        method: "POST",
        headers: getAuthHeaders(),
      })
      const data = await res.json()

      if (!res.ok) {
        composerError.value = extractApiError(data, "Could not close conversation.")
        return
      }

      await fetchConversationLists({ silent: true })
      await fetchConversationDetail(String(selectedConversation.value.id), { silent: true })
    } catch {
      composerError.value = "Could not close conversation."
    } finally {
      isClosing.value = false
    }
  }

  async function sendMessage() {
    composerError.value = ""

    if (!selectedConversation.value) {
      composerError.value = "Select a conversation first."
      return
    }

    if (!canSendMessage.value) {
      composerError.value = "You can send messages only after accepting this conversation."
      return
    }

    const content = messageInput.value.trim()
    if (content === "") {
      composerError.value = "Please type a message."
      return
    }

    isSending.value = true

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
      await fetchConversationLists({ silent: true })
    } catch {
      composerError.value = "Could not send message."
    } finally {
      isSending.value = false
    }
  }

  return {
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
  }
}
