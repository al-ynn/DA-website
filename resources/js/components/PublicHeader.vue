<script setup>
defineProps({
  sticky: {
    type: Boolean,
    default: true,
  },
})
</script>

<template>
  <header
    class="z-50 flex min-h-18 items-center gap-3 bg-white px-6 py-4 shadow-sm dark:bg-[#111111] dark:shadow-zinc-900/40"
    :class="sticky ? 'sticky top-0' : ''"
  >
    <img
      src="/images/RSL CAR logo.png"
      class="h-10 w-auto shrink-0 dark:hidden"
    />

    <img
      src="/images/RSL CAR logo DM.png"
      class="hidden h-10 w-auto shrink-0 dark:block"
    />

    <div>
      <h1 class="text-lg font-bold leading-tight text-zinc-900 dark:text-white">
        DEPARTMENT OF AGRICULTURE
      </h1>

      <p class="text-xs leading-tight text-zinc-600 dark:text-zinc-400">
        Region Field Office Cordillera Administrative Region (CAR) Regional Soils Laboratory
      </p>
    </div>
  </header>
</template>