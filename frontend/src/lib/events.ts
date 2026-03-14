export interface EventUser {
  id?: number | string | null
  name?: string | null
  email?: string | null
}

export interface EventItem {
  id: number | string
  user_id?: number | string | null
  title: string
  occurrence_at: string
  description?: string | null
  user?: EventUser | null
}

export function toIsoString(localDateTime: string) {
  const parsed = new Date(localDateTime)
  if (Number.isNaN(parsed.getTime())) {
    return localDateTime
  }
  return parsed.toISOString()
}

export function toTimestamp(dateValue: string) {
  const parsed = new Date(dateValue)
  if (Number.isNaN(parsed.getTime())) {
    return Number.MAX_SAFE_INTEGER
  }
  return parsed.getTime()
}

export function isPastEvent(event: EventItem) {
  return toTimestamp(event.occurrence_at) < Date.now()
}

export function toMonthKey(dateValue: string) {
  const date = new Date(dateValue)
  if (Number.isNaN(date.getTime())) {
    return ""
  }

  const month = `${date.getMonth() + 1}`.padStart(2, "0")
  return `${date.getFullYear()}-${month}`
}

export function formatEventDate(dateValue: string) {
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

export function extractApiError(payload: unknown, fallback: string) {
  if (!payload || typeof payload !== "object") {
    return fallback
  }

  const data = payload as Record<string, unknown>
  if (typeof data.message === "string" && data.message.trim() !== "") {
    return data.message
  }

  if (data.errors && typeof data.errors === "object") {
    const parts = Object.values(data.errors as Record<string, unknown>)
      .flatMap((entry) => Array.isArray(entry) ? entry : [entry])
      .filter((entry): entry is string => typeof entry === "string" && entry.trim() !== "")

    if (parts.length > 0) {
      return parts.join(", ")
    }
  }

  return fallback
}

export function getCreatorName(event: EventItem) {
  const fromRelation = event.user?.name?.trim()
  if (fromRelation) return fromRelation
  if (event.user_id !== undefined && event.user_id !== null) return `User #${event.user_id}`
  return "Unknown user"
}

export function getCreatorInitial(event: EventItem) {
  return getCreatorName(event).charAt(0).toUpperCase()
}

export function getCreatorAvatarStyle(event: EventItem) {
  const seed = `${event.user?.id ?? event.user_id ?? ""}-${getCreatorName(event)}`
  const hue = hashString(seed) % 360

  return {
    backgroundColor: `hsl(${hue} 68% 45%)`,
    color: "#ffffff",
  }
}

function hashString(input: string) {
  let hash = 0
  for (let i = 0; i < input.length; i += 1) {
    hash = (hash << 5) - hash + input.charCodeAt(i)
    hash |= 0
  }
  return Math.abs(hash)
}
