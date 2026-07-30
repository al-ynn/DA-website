<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import AppSidebarLayout from '@/Layouts/app/AppSidebarLayout.vue'
import type { BreadcrumbItem } from '@/types'
import ToDoDashboard from './ToDoDashboard.vue'

defineProps<{
  authUser?: {
    id: number
    name: string
    role: string
    roles: string[]
    assignedTasks: string[]
  }
  todoRequests?: any[]
}>()

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
    <ToDoDashboard :auth-user="authUser" :todo-requests="todoRequests" />
  </AppSidebarLayout>
</template>
