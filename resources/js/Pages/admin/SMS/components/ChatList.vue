<script setup lang="ts">
import { ref } from 'vue'
import MessageBubble from './MessageBubble.vue'

defineProps<{
  messages: { id: number; text: string; incoming: boolean }[]
}>()

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
  <div class="h-full flex flex-col">

    <!-- HEADER -->
    <div class="p-4 border-b border-white/10">
      <div class="font-semibold">Client</div>
    </div>

    <!-- MESSAGES -->
    <div class="flex-1 p-4 space-y-3 overflow-y-auto">
      <MessageBubble
        v-for="msg in messages"
        :key="msg.id"
        :incoming="msg.incoming"
      >
        {{ msg.text }}
      </MessageBubble>
    </div>

    <!-- INPUT -->
    <div class="p-4 border-t border-white/10 flex gap-2">
      <input
        v-model="message"
        class="flex-1 bg-black border border-white/20 rounded-md px-3 py-2 text-sm"
        placeholder="Type a message…"
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