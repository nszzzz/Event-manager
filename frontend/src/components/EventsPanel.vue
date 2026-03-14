<script setup lang="ts">
import EventCreateCard from "@/components/events/EventCreateCard.vue"
import EventDetailSheet from "@/components/events/EventDetailSheet.vue"
import EventEditSheet from "@/components/events/EventEditSheet.vue"
import EventListSectionCard from "@/components/events/EventListSectionCard.vue"
import {
  extractApiError,
  isPastEvent,
  toIsoString,
  toMonthKey,
  toTimestamp,
  getCreatorName,
  type EventItem,
} from "@/lib/events"
import { useAuthStore } from "@/stores/auth"
import { computed, onMounted, onUnmounted, ref } from "vue"

const authStore = useAuthStore()

const isLoading = ref(false)
const isCreating = ref(false)
const loadError = ref("")
const formError = ref("")
const formSuccess = ref("")
const isDetailsOpen = ref(false)
const selectedEvent = ref<EventItem | null>(null)
const isEditOpen = ref(false)
const editingEvent = ref<EventItem | null>(null)
const isUpdating = ref(false)
const editError = ref("")
const updatingEventIds = ref<string[]>([])
const deletingEventIds = ref<string[]>([])

const events = ref<EventItem[]>([])
const isFetching = ref(false)
let pollingTimer: ReturnType<typeof setInterval> | null = null

const title = ref("")
const occurrenceAt = ref("")
const description = ref("")
const upcomingSearchQuery = ref("")
const upcomingMonthFilter = ref("")
const mySearchQuery = ref("")
const myMonthFilter = ref("")
const pastSearchQuery = ref("")
const pastMonthFilter = ref("")

const upcomingEvents = computed(() =>
  events.value
    .filter((event) => !isPastEvent(event))
    .filter((event) => matchesSectionFilters(event, upcomingSearchQuery.value, upcomingMonthFilter.value))
    .sort((a, b) => toTimestamp(a.occurrence_at) - toTimestamp(b.occurrence_at)),
)

const pastEvents = computed(() =>
  events.value
    .filter((event) => isPastEvent(event))
    .filter((event) => matchesSectionFilters(event, pastSearchQuery.value, pastMonthFilter.value))
    .sort((a, b) => toTimestamp(b.occurrence_at) - toTimestamp(a.occurrence_at)),
)

const myEvents = computed(() =>
  events.value
    .filter((event) => isOwnedByCurrentUser(event))
    .filter((event) => matchesSectionFilters(event, mySearchQuery.value, myMonthFilter.value))
    .sort((a, b) => toTimestamp(a.occurrence_at) - toTimestamp(b.occurrence_at)),
)

onMounted(() => {
  void fetchEvents()
  pollingTimer = setInterval(() => {
    void fetchEvents({ showLoader: false })
  }, 10000)
})

onUnmounted(() => {
  if (pollingTimer) {
    clearInterval(pollingTimer)
    pollingTimer = null
  }
})

async function fetchEvents(options: { showLoader?: boolean } = {}) {
  if (isFetching.value) {
    return
  }

  const showLoader = options.showLoader ?? true

  if (showLoader) {
    isLoading.value = true
  }

  isFetching.value = true
  loadError.value = ""

  try {
    const token = localStorage.getItem("token")
    const res = await fetch("/api/events", {
      headers: {
        authorization: `Bearer ${token}`,
      },
    })

    const data = await res.json()
    if (!res.ok) {
      loadError.value = extractApiError(data, "Could not load events.")
      return
    }

    if (Array.isArray(data)) {
      events.value = data as EventItem[]
      return
    }

    events.value = []
  } catch (error) {
    loadError.value = "Could not load events."
  } finally {
    if (showLoader) {
      isLoading.value = false
    }
    isFetching.value = false
  }
}

async function createEvent() {
  formError.value = ""
  formSuccess.value = ""

  if (title.value.trim() === "") {
    formError.value = "Title is required."
    return
  }

  if (occurrenceAt.value.trim() === "") {
    formError.value = "Date and time are required."
    return
  }

  isCreating.value = true

  try {
    const token = localStorage.getItem("token")
    const res = await fetch("/api/events", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        authorization: `Bearer ${token}`,
      },
      body: JSON.stringify({
        title: title.value.trim(),
        occurrence_at: toIsoString(occurrenceAt.value),
        description: description.value.trim() === "" ? null : description.value.trim(),
      }),
    })

    const data = await res.json()
    if (!res.ok) {
      formError.value = extractApiError(data, "Could not create event.")
      return
    }

    title.value = ""
    occurrenceAt.value = ""
    description.value = ""
    formSuccess.value = "Event created successfully."

    await fetchEvents({ showLoader: false })
  } catch (error) {
    formError.value = "Could not create event."
  } finally {
    isCreating.value = false
  }
}

function openEventDetails(event: EventItem) {
  selectedEvent.value = event
  isDetailsOpen.value = true
}

function openEditEvent(event: EventItem) {
  editError.value = ""
  editingEvent.value = event
  isEditOpen.value = true
}

function handleEditOpenChange(open: boolean) {
  isEditOpen.value = open

  if (!open && !isUpdating.value) {
    editError.value = ""
    editingEvent.value = null
  }
}

async function updateEvent(payload: { title: string; occurrenceAt: string; description: string }) {
  editError.value = ""

  if (!editingEvent.value) {
    editError.value = "Event data is missing."
    return
  }

  const id = String(editingEvent.value.id)
  if (updatingEventIds.value.includes(id)) {
    return
  }

  if (payload.title.trim() === "") {
    editError.value = "Title is required."
    return
  }

  if (payload.occurrenceAt.trim() === "") {
    editError.value = "Date and time are required."
    return
  }

  isUpdating.value = true
  updatingEventIds.value = [...updatingEventIds.value, id]

  try {
    const token = localStorage.getItem("token")
    const res = await fetch(`/api/events/${editingEvent.value.id}`, {
      method: "PUT",
      headers: {
        "Content-Type": "application/json",
        authorization: `Bearer ${token}`,
      },
      body: JSON.stringify({
        title: payload.title.trim(),
        occurrence_at: toIsoString(payload.occurrenceAt),
        description: payload.description.trim() === "" ? null : payload.description.trim(),
      }),
    })

    const data = await res.json()
    if (!res.ok) {
      editError.value = extractApiError(data, "Could not update event.")
      return
    }

    const updatedEvent = (data?.event ?? data) as EventItem
    events.value = events.value.map((event) =>
      String(event.id) === id ? updatedEvent : event,
    )

    if (selectedEvent.value && String(selectedEvent.value.id) === id) {
      selectedEvent.value = updatedEvent
    }

    editingEvent.value = updatedEvent
    isEditOpen.value = false
    editError.value = ""
  } catch (error) {
    editError.value = "Could not update event."
  } finally {
    isUpdating.value = false
    updatingEventIds.value = updatingEventIds.value.filter((eventId) => eventId !== id)
  }
}

async function deleteEvent(event: EventItem) {
  const id = String(event.id)
  if (deletingEventIds.value.includes(id)) {
    return
  }

  deletingEventIds.value = [...deletingEventIds.value, id]

  try {
    const token = localStorage.getItem("token")
    const res = await fetch(`/api/events/${event.id}`, {
      method: "DELETE",
      headers: {
        authorization: `Bearer ${token}`,
      },
    })

    const data = await res.json()
    if (!res.ok) {
      loadError.value = extractApiError(data, "Could not delete event.")
      return
    }

    events.value = events.value.filter((item) => String(item.id) !== id)

    if (selectedEvent.value && String(selectedEvent.value.id) === id) {
      selectedEvent.value = null
      isDetailsOpen.value = false
    }
  } catch (error) {
    loadError.value = "Could not delete event."
  } finally {
    deletingEventIds.value = deletingEventIds.value.filter((eventId) => eventId !== id)
  }
}

function isOwnedByCurrentUser(event: EventItem) {
  const currentUserId = authStore.user?.id
  if (currentUserId === null || currentUserId === undefined) {
    return false
  }

  const userId = event.user?.id ?? event.user_id
  if (userId === null || userId === undefined) {
    return false
  }

  return String(userId) === String(currentUserId)
}

function matchesSectionFilters(event: EventItem, search: string, month: string) {
  const query = search.trim().toLowerCase()
  const matchesQuery = query === ""
    || event.title.toLowerCase().includes(query)
    || (event.description ?? "").toLowerCase().includes(query)
    || getCreatorName(event).toLowerCase().includes(query)

  const matchesMonth = month === "" || toMonthKey(event.occurrence_at) === month

  return matchesQuery && matchesMonth
}
</script>

<template>
  <div class="flex flex-col gap-6">
    <EventCreateCard
      :title="title"
      :occurrence-at="occurrenceAt"
      :description="description"
      :is-creating="isCreating"
      :form-error="formError"
      :form-success="formSuccess"
      @update:title="title = $event"
      @update:occurrence-at="occurrenceAt = $event"
      @update:description="description = $event"
      @submit="createEvent"
    />

    <EventListSectionCard
      title="Upcoming Events"
      description="Nearest event is shown first, then the next ones."
      :events="upcomingEvents"
      empty-text="No upcoming events found."
      :is-loading="isLoading"
      :load-error="loadError"
      layout="grid"
      :search-query="upcomingSearchQuery"
      :month-filter="upcomingMonthFilter"
      @update:search-query="upcomingSearchQuery = $event"
      @update:month-filter="upcomingMonthFilter = $event"
      @open-details="openEventDetails"
    />

    <EventListSectionCard
      title="My Events"
      description="Events created by you."
      :events="myEvents"
      empty-text="You have not created any events yet."
      :is-loading="isLoading"
      :load-error="loadError"
      layout="grid"
      :show-edit-action="true"
      :show-delete-action="true"
      :updating-ids="updatingEventIds"
      :deleting-ids="deletingEventIds"
      :search-query="mySearchQuery"
      :month-filter="myMonthFilter"
      @update:search-query="mySearchQuery = $event"
      @update:month-filter="myMonthFilter = $event"
      @open-details="openEventDetails"
      @edit="openEditEvent"
      @delete="deleteEvent"
    />

    <EventListSectionCard
      title="Past Events"
      description="Previous events grouped separately."
      :events="pastEvents"
      empty-text="No past events found."
      :is-loading="isLoading"
      :load-error="loadError"
      layout="grid"
      :show-past-badge="true"
      :search-query="pastSearchQuery"
      :month-filter="pastMonthFilter"
      @update:search-query="pastSearchQuery = $event"
      @update:month-filter="pastMonthFilter = $event"
      @open-details="openEventDetails"
    />

    <EventDetailSheet
      :open="isDetailsOpen"
      :event="selectedEvent"
      @update:open="isDetailsOpen = $event"
    />

    <EventEditSheet
      :open="isEditOpen"
      :event="editingEvent"
      :is-updating="isUpdating"
      :error-message="editError"
      @update:open="handleEditOpenChange"
      @submit="updateEvent"
    />
  </div>
</template>
