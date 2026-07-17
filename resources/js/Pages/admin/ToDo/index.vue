<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue'
import { Head } from '@inertiajs/vue3'
import type { BreadcrumbItem } from '@/types'
import ToDoDashboard from './ToDoDashboard.vue'

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'To do',
    href: '/todo',
  },
]
</script>

<template>
  <Head title="To do" />

  <AppSidebarLayout :breadcrumbs="breadcrumbs">
    <ToDoDashboard />
  </AppSidebarLayout>
</template>