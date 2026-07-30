<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import PublicHeader from '@/Components/PublicHeader.vue'

const code = ref('')
const error = ref(null)
const processing = ref(false)

function submit() {
  error.value = null

  if (!code.value) {
    error.value = 'Please enter a valid report number.'
    return
  }

  processing.value = true

  router.get(`/results/verify/${code.value}`)
}
</script>

<template>
  <Head title="Request Laboratory Results" />

  <div class="min-h-screen bg-[#f6f7f8] text-sm text-zinc-900">

    <PublicHeader />

    <div class="flex min-h-[calc(100vh-72px)] flex-col items-center justify-center gap-10 px-4 py-8 lg:flex-row lg:gap-20">
      <div>
        <img src="/images/RSL CAR logo.png" class="w-56 md:w-64 lg:w-87.5" />
        <img src="/images/RSL CAR logo DM.png" class="hidden w-56 md:w-64 lg:w-87.5" />
      </div>

      <div class="w-full max-w-md rounded-xl border border-zinc-200 bg-white p-6 shadow-xl md:p-10">
        <p class="mb-4 text-center text-sm text-zinc-700">
          Enter laboratory Result Report No.
        </p>

        <input
          v-model="code"
          type="text"
          placeholder="RSL-YYYY-X"
          class="w-full rounded-md border border-zinc-300 bg-white px-4 py-3 text-center outline-none focus:border-[#0E3D1A]"
        />

        <p v-if="error" class="mt-2 text-center text-sm text-red-600">
          {{ error }}
        </p>

        <button
          type="button"
          @click="submit"
          :disabled="processing"
          class="mt-4 w-full rounded-md bg-[#0E3D1A] py-3 font-semibold text-white transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-60"
        >
          {{ processing ? 'Processing...' : 'Enter' }}
        </button>
      </div>
    </div>
  </div>
</template>
