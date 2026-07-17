<script setup lang="ts">
import AppSidebar from '@/components/AppSidebar.vue'
import AppSidebarHeader from '@/components/AppSidebarHeader.vue'

import PasswordWarningBanner from '@/Pages/admin/UserManagement/components/PasswordWarningBanner.vue'
import ChangePasswordReminder from '@/Pages/admin/UserManagement/components/ChangePasswordReminder.vue'

import { router } from '@inertiajs/vue3'

import type { BreadcrumbItem } from '@/types'
import { provide, ref } from 'vue'

type Props = {
    breadcrumbs?: BreadcrumbItem[]
}

const props = withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
})

const collapsed = ref(false)

const reminderRef = ref()

const toggleSidebar = () => {
    collapsed.value = !collapsed.value
}

provide('sidebarCollapsed', collapsed)
provide('toggleSidebar', toggleSidebar)

function logout() {
    sessionStorage.removeItem('password-reminder-dismissed')

    router.post('/logout')
}

</script>

<template>
  <div class="h-screen flex overflow-hidden bg-gray-100">
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