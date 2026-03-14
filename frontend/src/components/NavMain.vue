<script setup lang="ts">
import type { Component } from "vue"

import {
  SidebarGroupLabel,
  SidebarGroup,
  SidebarGroupContent,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
} from '@/components/ui/sidebar'

interface NavItem {
  key: string
  title: string
  icon?: Component
}

defineProps<{
  items: NavItem[]
  activeItem: string
}>()

const emit = defineEmits<{
  select: [key: string]
}>()
</script>

<template>
  <SidebarGroup>
    <SidebarGroupLabel>Menu</SidebarGroupLabel>
    <SidebarGroupContent>
      <SidebarMenu>
        <SidebarMenuItem v-for="item in items" :key="item.key">
          <SidebarMenuButton
            :tooltip="item.title"
            :is-active="activeItem === item.key"
            @click="emit('select', item.key)"
          >
            <component :is="item.icon" v-if="item.icon" />
            <span>{{ item.title }}</span>
          </SidebarMenuButton>
        </SidebarMenuItem>
      </SidebarMenu>
    </SidebarGroupContent>
  </SidebarGroup>
</template>
