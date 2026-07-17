<script setup lang="ts">
defineProps<{
  incoming: boolean
}>()
</script>

<template>
  <div
    class="max-w-xs px-3 py-2 rounded-lg text-sm"
    :class="incoming
      ? 'bg-white/10 self-start'
      : 'bg-green-700 self-end'"
  >
    <slot />
  </div>
</template>