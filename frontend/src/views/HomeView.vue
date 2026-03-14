<script setup lang="ts">
import { computed, onMounted, ref, watch } from "vue"
import { IconHelp, IconListDetails, IconUsers } from "@tabler/icons-vue"
import AppSidebar from "@/components/AppSidebar.vue"
import EventsPanel from "@/components/EventsPanel.vue"
import {
  SidebarInset,
  SidebarProvider,
  SidebarTrigger,
} from "@/components/ui/sidebar"
import { normalizeRole } from "@/lib/auth-user"
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
          <IconListDetails v-if="activeSection === 'events'" class="size-4" />
          <IconUsers v-else-if="activeSection === 'user_chats'" class="size-4" />
          <IconHelp v-else class="size-4" />
          <span>{{ sectionTitle }}</span>
        </div>
      </header>

      <section class="flex min-h-0 flex-1 p-4 md:p-6">
        <div class="flex min-h-0 w-full flex-col">
          <EventsPanel v-if="activeSection === 'events'"/>

          <div
            v-else-if="activeSection === 'help'"
            class="min-h-0 flex-1 overflow-y-auto rounded-xl border border-dashed border-border/80 bg-background/70 p-6"
          >
            <p class="text-sm text-muted-foreground">
              Help content placeholder2.
            </p>
          </div>

          <div
            v-else
            class="min-h-0 flex-1 overflow-y-auto rounded-xl border border-dashed border-border/80 bg-background/70 p-6"
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
