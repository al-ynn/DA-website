<script setup lang="ts">
import { router } from '@inertiajs/vue3'

function goPage(page: number) {
  router.visit(`/test-reports/create/page-${page}`)
}
</script>

<template>
  <div class="space-y-4">
    <button
      type="button"
      @click="goPage(1)"
      class="rounded-lg bg-[#0E3D1A] px-4 py-2 text-sm font-semibold text-white transition hover:opacity-90"
    >
      Go to Page 1
    </button>

    <button
      type="button"
      @click="goPage(2)"
      class="rounded-lg bg-[#0E3D1A] px-4 py-2 text-sm font-semibold text-white transition hover:opacity-90"
    >
      Go to Page 2
    </button>

    <button
      type="button"
      @click="goPage(3)"
      class="rounded-lg bg-[#0E3D1A] px-4 py-2 text-sm font-semibold text-white transition hover:opacity-90"
    >
      Go to Page 3
    </button>
  </div>
</template>
