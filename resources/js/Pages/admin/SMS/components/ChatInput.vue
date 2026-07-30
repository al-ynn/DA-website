<script setup lang="ts">
import { ref } from 'vue'

const message = ref('')

const emit = defineEmits<{
  (e: 'send', text: string): void
}>()

function send() {
  if (!message.value.trim()) return

  emit('send', message.value.trim())
  message.value = ''
}
</script>

<template>
  <div class="p-4 border-t border-white/10 bg-zinc-900">
    <div class="flex gap-2">
      <input
        v-model="message"
        type="text"
        placeholder="Type a message…"
        class="flex-1 rounded-md bg-black border border-white/20 px-3 py-2 text-white"
        @keyup.enter="send"
      />

      <button
        class="px-4 py-2 bg-green-700 rounded-md hover:bg-green-800"
        @click="send"
      >
        Send
      </button>
    </div>
  </div>
</template>
