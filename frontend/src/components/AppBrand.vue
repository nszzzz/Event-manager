<script setup lang="ts">
import { IconInnerShadowTop } from "@tabler/icons-vue"
import { cn } from "@/lib/utils"
import type { HTMLAttributes } from "vue"

const props = withDefaults(defineProps<{
  class?: HTMLAttributes["class"]
  label?: string
}>(), {
  label: "Event Manager",
})
</script>

<template>
  <div :class="cn('flex items-center gap-2 font-medium', props.class)">
    <div class="bg-primary text-primary-foreground flex size-6 items-center justify-center rounded-md">
      <IconInnerShadowTop class="size-4" />
    </div>
    <span>{{ props.label }}</span>
  </div>
</template>
