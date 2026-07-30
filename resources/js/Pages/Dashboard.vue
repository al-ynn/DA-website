<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import PlaceholderPattern from '@/Components/PlaceholderPattern.vue'
import AppSidebarLayout from '@/Layouts/app/AppSidebarLayout.vue'
import { dashboard } from '@/routes'
import type { BreadcrumbItem } from '@/types'

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: dashboard().url,
  },
]
</script>

<template>
  <Head title="Dashboard" />

  <AppSidebarLayout :breadcrumbs="breadcrumbs">
    <div class="h-full min-h-0 overflow-hidden p-4">
      <div class="grid h-full min-h-0 grid-rows-[auto_1fr] gap-4">
        <div class="grid gap-4 md:grid-cols-3">
          <div class="relative h-40 overflow-hidden rounded-xl border border-sidebar-border/70">
            <PlaceholderPattern />
          </div>
          <div class="relative h-40 overflow-hidden rounded-xl border border-sidebar-border/70">
            <PlaceholderPattern />
          </div>
          <div class="relative h-40 overflow-hidden rounded-xl border border-sidebar-border/70">
            <PlaceholderPattern />
          </div>
        </div>

        <div class="relative min-h-0 overflow-hidden rounded-xl border border-sidebar-border/70">
          <PlaceholderPattern />
        </div>
      </div>
    </div>
  </AppSidebarLayout>
</template>
