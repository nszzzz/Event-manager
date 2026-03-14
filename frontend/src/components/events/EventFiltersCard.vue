<script setup lang="ts">
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle,
} from "@/components/ui/card"
import { Input } from "@/components/ui/input"
import { IconSearch } from "@tabler/icons-vue"

const props = defineProps<{
  searchQuery: string
  monthFilter: string
}>()

const emit = defineEmits<{
  "update:searchQuery": [value: string]
  "update:monthFilter": [value: string]
}>()

function handleSearchUpdate(value: string | number) {
  emit("update:searchQuery", String(value))
}

function handleMonthUpdate(value: string | number) {
  emit("update:monthFilter", String(value))
}
</script>

<template>
  <Card>
    <CardHeader>
      <CardTitle class="flex items-center gap-2">
        <IconSearch class="size-4" />
        Search and Filter
      </CardTitle>
    </CardHeader>
    <CardContent class="grid gap-4 md:grid-cols-2">
      <Input
        :model-value="props.searchQuery"
        placeholder="Search by title, description, or creator..."
        @update:model-value="handleSearchUpdate"
      />
      <Input
        :model-value="props.monthFilter"
        type="month"
        @update:model-value="handleMonthUpdate"
      />
    </CardContent>
  </Card>
</template>
