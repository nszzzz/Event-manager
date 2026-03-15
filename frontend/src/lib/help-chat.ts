export type ConversationStatus = "bot_active" | "waiting_for_agent" | "agent_active" | "closed"

export interface ChatUser {
  id?: number | string | null
  name?: string | null
  email?: string | null
}

export interface ChatMessage {
  id: number | string
  conversation_id: number | string
  sender_type: "user" | "bot" | "agent" | "system" | string
  sender_user_id?: number | string | null
  content: string
  message_type?: string
  created_at?: string
  updated_at?: string
  user?: ChatUser | null
}

export interface ChatConversation {
  id: number | string
  user_id: number | string
  assigned_agent_id?: number | string | null
  status: ConversationStatus | string
  channel?: string
  subject?: string | null
  closed_at?: string | null
  created_at?: string
  updated_at?: string
  messages_count?: number
  user?: ChatUser | null
  assigned_agent?: ChatUser | null
  assignedAgent?: ChatUser | null
  messages?: ChatMessage[]
}

export function getAssignedAgent(conversation: ChatConversation | null): ChatUser | null {
  if (!conversation) {
    return null
  }

  return conversation.assigned_agent ?? conversation.assignedAgent ?? null
}

export function getConversationTitle(conversation: ChatConversation) {
  const subject = conversation.subject?.trim()
  if (subject) {
    return subject
  }

  return `Conversation #${conversation.id}`
}

export function getStatusLabel(status: ChatConversation["status"]) {
  if (status === "bot_active") return "Bot Active"
  if (status === "waiting_for_agent") return "Waiting For Agent"
  if (status === "agent_active") return "Agent Active"
  if (status === "closed") return "Closed"
  return "Unknown"
}

export function getStatusVariant(status: ChatConversation["status"]) {
  if (status === "agent_active") return "default"
  if (status === "waiting_for_agent") return "secondary"
  if (status === "closed") return "outline"
  return "secondary"
}

export function formatDateTime(dateValue: string | null | undefined) {
  if (!dateValue) {
    return "-"
  }

  const date = new Date(dateValue)
  if (Number.isNaN(date.getTime())) {
    return dateValue
  }

  return new Intl.DateTimeFormat(undefined, {
    year: "numeric",
    month: "short",
    day: "2-digit",
    hour: "2-digit",
    minute: "2-digit",
  }).format(date)
}

export function getUserDisplayName(user: ChatUser | null | undefined, fallback = "Unknown user") {
  const name = user?.name?.trim()
  if (name) {
    return name
  }

  return fallback
}

export function getUserInitial(user: ChatUser | null | undefined, fallback = "U") {
  const source = user?.name?.trim() || user?.email?.trim() || fallback
  return source.charAt(0).toUpperCase()
}

export function getUserAvatarStyle(user: ChatUser | null | undefined, seedPrefix = "") {
  const seed = `${seedPrefix}-${user?.id ?? ""}-${user?.name ?? ""}-${user?.email ?? ""}`
  const hue = hashString(seed) % 360

  return {
    backgroundColor: `hsl(${hue} 68% 45%)`,
    color: "#ffffff",
  }
}

function hashString(input: string) {
  let hash = 0
  for (let index = 0; index < input.length; index += 1) {
    hash = (hash << 5) - hash + input.charCodeAt(index)
    hash |= 0
  }

  return Math.abs(hash)
}
