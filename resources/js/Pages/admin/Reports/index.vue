<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue'
import { Head } from '@inertiajs/vue3'
import type { BreadcrumbItem } from '@/types'
import ReportsDashboard from './ReportsDashboard.vue'

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
    <ReportsDashboard />
  </AppSidebarLayout>
</template>