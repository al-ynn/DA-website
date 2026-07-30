<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import AppSidebarLayout from '@/Layouts/app/AppSidebarLayout.vue'
import type { BreadcrumbItem } from '@/types'
import ReportsDashboard from './ReportsDashboard.vue'

defineProps<{
  reports?: {
    data?: any[]
  }
  summary?: Record<string, number>
  filters?: Record<string, string | undefined>
}>()

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Reports',
    href: '/reports',
  },
]
</script>

<template>
  <Head title="Reports" />

  <AppSidebarLayout :breadcrumbs="breadcrumbs">
    <ReportsDashboard :reports="reports" :summary="summary" :filters="filters" />
  </AppSidebarLayout>
</template>
