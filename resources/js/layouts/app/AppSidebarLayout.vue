<script setup lang="ts">
import { provide, ref } from 'vue'
import AppSidebar from '@/Components/AppSidebar.vue'
import AppSidebarHeader from '@/Components/AppSidebarHeader.vue'

import ChangePasswordReminder from '@/Pages/admin/UserManagement/components/ChangePasswordReminder.vue'
import PasswordWarningBanner from '@/Pages/admin/UserManagement/components/PasswordWarningBanner.vue'


import type { BreadcrumbItem } from '@/types'

type Props = {
    breadcrumbs?: BreadcrumbItem[]
}

const props = withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
})

const collapsed = ref(false)

const toggleSidebar = () => {
    collapsed.value = !collapsed.value
}

provide('sidebarCollapsed', collapsed)
provide('toggleSidebar', toggleSidebar)

</script>

<template>
  <div class="flex h-screen overflow-hidden bg-background text-foreground">
    <AppSidebar :collapsed="collapsed" />

    <div class="flex-1 min-w-0 min-h-0 flex flex-col overflow-hidden">
      <AppSidebarHeader :breadcrumbs="props.breadcrumbs" />

      <PasswordWarningBanner />

      <ChangePasswordReminder />

      <main class="flex-1 min-h-0 overflow-y-auto overflow-x-hidden">
        <slot />
      </main>
    </div>
  </div>
</template>
