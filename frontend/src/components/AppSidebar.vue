<script setup lang="ts">
import {
  IconHelp,
  IconInnerShadowTop,
  IconListDetails,
  IconUsers,
} from "@tabler/icons-vue"
import { computed } from "vue"

import NavMain from '@/components/NavMain.vue'
import NavUser from '@/components/NavUser.vue'
import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarHeader,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
} from '@/components/ui/sidebar'
import type { UserRole } from "@/lib/auth-user"

type SectionKey = "events" | "help" | "user_chats"

interface SidebarUser {
  id?: string | number | null
  name: string
  email: string | null
  role: UserRole
}

const props = defineProps<{
  user: SidebarUser
  activeSection: SectionKey
}>()

const emit = defineEmits<{
  sectionChange: [section: SectionKey]
}>()

const secondaryNavItem = computed(() => {
  if (props.user.role === "helpdesk_agent") {
    return {
      key: "user_chats",
      title: "User Chats",
      icon: IconUsers,
    } as const
  }

  return {
    key: "help",
    title: "Help",
    icon: IconHelp,
  } as const
})

const navItems = computed(() => [
  {
    key: "events",
    title: "Events",
    icon: IconListDetails,
  } as const,
  secondaryNavItem.value,
])

function handleSelect(section: string) {
  if (section === "events" || section === "help" || section === "user_chats") {
    emit("sectionChange", section)
  }
}
</script>

<template>
  <Sidebar collapsible="offcanvas">
    <SidebarHeader>
      <SidebarMenu>
        <SidebarMenuItem>
          <SidebarMenuButton
            as-child
            class="data-[slot=sidebar-menu-button]:!p-1.5"
          >
            <a href="#" @click.prevent>
              <IconInnerShadowTop class="!size-5" />
              <span class="text-base font-semibold">Event Manager</span>
            </a>
          </SidebarMenuButton>
        </SidebarMenuItem>
      </SidebarMenu>
    </SidebarHeader>
    <SidebarContent>
      <NavMain
        :items="navItems"
        :active-item="activeSection"
        @select="handleSelect"
      />
    </SidebarContent>
    <SidebarFooter>
      <NavUser :user="user" />
    </SidebarFooter>
  </Sidebar>
</template>
