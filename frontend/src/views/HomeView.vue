<script setup lang="ts">
import { IconCalendarEvent, IconHelpCircle, IconUsers } from "@tabler/icons-vue"
import { computed, onMounted, ref, watch } from "vue"
import AppSidebar from "@/components/AppSidebar.vue"
import EventsPanel from "@/components/EventsPanel.vue"
import {
  SidebarInset,
  SidebarProvider,
  SidebarTrigger,
} from "@/components/ui/sidebar"
import { normalizeRole, type UserRole } from "@/lib/auth-user"
import { useAuthStore } from "@/stores/auth"

type SectionKey = "events" | "help" | "user_chats"

const authStore = useAuthStore()
const activeSection = ref<SectionKey>("events")

onMounted(async () => {
  if (!authStore.user) {
    await authStore.getUser()
  }
})

const user = computed(() => {
  const current = authStore.user

  return {
    id: current?.id ?? null,
    name: current?.name?.trim() || "User",
    email: current?.email?.trim() || null,
    role: normalizeRole(current?.role),
  }
})

const availableSections = computed<SectionKey[]>(() => {
  if (user.value.role === "helpdesk_agent") {
    return ["events", "user_chats"]
  }

  return ["events", "help"]
})

watch(
  availableSections,
  (sections) => {
    if (!sections.includes(activeSection.value)) {
      activeSection.value = "events"
    }
  },
  { immediate: true },
)

const sectionTitle = computed(() => {
  if (activeSection.value === "events") return "Events"
  if (activeSection.value === "user_chats") return "User Chats"
  return "Help"
})

const panelStyle = computed(() => {
  const seed = `${user.value.id ?? ""}-${user.value.name}-${user.value.email ?? ""}`
  const hue = hashString(seed) % 360
  const secondaryHue = (hue + 24) % 360

  return {
    background: `linear-gradient(145deg, hsl(${hue} 92% 96%), hsl(${secondaryHue} 95% 98%))`,
  }
})

function hashString(input: string) {
  let hash = 0
  for (let i = 0; i < input.length; i += 1) {
    hash = (hash << 5) - hash + input.charCodeAt(i)
    hash |= 0
  }
  return Math.abs(hash)
}

function handleSectionChange(section: SectionKey) {
  if (availableSections.value.includes(section)) {
    activeSection.value = section
  }
}
</script>

<template>
  <SidebarProvider>
    <AppSidebar
      :user="user"
      :active-section="activeSection"
      @section-change="handleSectionChange"
    />
    <SidebarInset>
      <header class="flex h-16 items-center gap-3 border-b bg-background/80 px-4 backdrop-blur-sm">
        <SidebarTrigger />
        <div class="flex items-center gap-2 text-sm font-medium">
          <IconCalendarEvent v-if="activeSection === 'events'" class="size-4" />
          <IconUsers v-else-if="activeSection === 'user_chats'" class="size-4" />
          <IconHelpCircle v-else class="size-4" />
          <span>{{ sectionTitle }}</span>
        </div>
      </header>

      <section class="flex min-h-0 flex-1 p-4 md:p-6">
        <div
          class="flex min-h-0 w-full flex-col rounded-2xl border border-border/70 p-6 shadow-sm"
          :style="panelStyle"
        >
          <h1 class="text-2xl font-semibold tracking-tight">
            {{ sectionTitle }}
          </h1>

          <div
            v-if="activeSection === 'events'"
            class="mt-6 min-h-0 flex-1 overflow-y-auto pr-1"
          >
            <EventsPanel />
          </div>

          <div
            v-else-if="activeSection === 'help'"
            class="mt-6 min-h-0 flex-1 overflow-y-auto rounded-xl border border-dashed border-border/80 bg-background/70 p-6"
          >
            <p class="text-sm text-muted-foreground">
              Help content placeholder.
            </p>
          </div>

          <div
            v-else
            class="mt-6 min-h-0 flex-1 overflow-y-auto rounded-xl border border-dashed border-border/80 bg-background/70 p-6"
          >
            <p class="text-sm text-muted-foreground">
              Helpdesk user chat queue placeholder.
            </p>
          </div>
        </div>
      </section>
    </SidebarInset>
  </SidebarProvider>
</template>
