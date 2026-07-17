<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import AppLogo from '@/components/AppLogo.vue'
</script>

<template>
  <div class="border-b bg-white">
    <div class="mx-auto flex h-16 items-center px-4 md:max-w-7xl">

      <!-- LOGO + TITLE ONLY -->
      <Link href="/" class="flex items-center gap-3">
        <AppLogo />

        <div>
          <h1 class="text-base font-bold leading-tight text-neutral-900">
            DEPARTMENT OF AGRICULTURE
          </h1>
          <p class="text-xs leading-tight text-neutral-500">
            Region Field Office Cordillera Administrative Region (CAR) Regional Soils Laboratory
          </p>
        </div>
      </Link>

    </div>
  </div>
</template>