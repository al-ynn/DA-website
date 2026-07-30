<script setup lang="ts">
import { PanelLeftClose, PanelLeftOpen } from 'lucide-vue-next'
import { computed, inject, type Ref } from 'vue'
import Breadcrumbs from '@/Components/Breadcrumbs.vue'
import type { BreadcrumbItem } from '@/types'

withDefaults(
  defineProps<{
    breadcrumbs?: BreadcrumbItem[]
  }>(),
  {
    breadcrumbs: () => [],
  },
)

const collapsed = inject<Ref<boolean> | undefined>('sidebarCollapsed')
const toggleSidebar = inject<(() => void) | undefined>('toggleSidebar')

const isCollapsed = computed(() => collapsed?.value ?? false)
</script>

<template>
  <header
    class="flex h-16 shrink-0 items-center border-b border-gray-200 bg-white px-5"
  >
    <div class="flex w-full items-center justify-between gap-4">
      <div class="flex min-w-0 items-center gap-3">
        <button
          type="button"
          @click="toggleSidebar && toggleSidebar()"
          class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-700 transition hover:bg-gray-100"
        >
          <PanelLeftOpen v-if="isCollapsed" class="h-5 w-5" />
          <PanelLeftClose v-else class="h-5 w-5" />
        </button>

        <div v-if="breadcrumbs && breadcrumbs.length > 0" class="min-w-0">
          <Breadcrumbs :breadcrumbs="breadcrumbs" />
        </div>
      </div>
    </div>
  </header>
</template>
