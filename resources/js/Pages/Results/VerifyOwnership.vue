<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import PublicHeader from '@/Components/PublicHeader.vue'

const props = defineProps({
  code: String,
  maskedPhone: String,
})

const phone = ref('')
const error = ref(null)
const processing = ref(false)

function submit() {
  error.value = null

  if (!phone.value) {
    error.value = 'Please enter your phone number.'
    return
  }

  processing.value = true

  if (phone.value !== '09171234567') {
    error.value = 'Invalid phone number. This number does not match the registered owner.'
    processing.value = false
    return
  }

  router.get(`/results/otp/${props.code}`)
}
</script>

<template>
  <Head title="Verify Ownership" />

  <div class="min-h-screen bg-[#f6f7f8] text-sm text-zinc-900">
    
    <PublicHeader />

    <div class="flex min-h-[calc(100vh-72px)] flex-col items-center justify-center gap-10 px-4 py-8 lg:flex-row lg:gap-20">
      
  
      <div>
        <img src="/images/RSL CAR logo.png" class="w-56 md:w-64 lg:w-87.5" />
        <img src="/images/RSL CAR logo DM.png" class="hidden w-56 md:w-64 lg:w-87.5" />
      </div>

      <div class="w-full max-w-md rounded-xl border border-zinc-200 bg-white p-6 shadow-xl md:p-10">
        
        <h1 class="mb-2 text-center text-xl font-semibold">
          Verify ownership
        </h1>

        <p class="mb-6 text-center text-sm text-zinc-600">
          This result is registered under phone number
          <strong>{{ maskedPhone }}</strong>
        </p>

        <input
          v-model="phone"
          type="text"
          inputmode="tel"
          placeholder="Enter phone number"
          class="mb-3 w-full rounded-md border border-zinc-300 bg-white px-4 py-3 text-center outline-none focus:border-[#0E3D1A]"
        />

        <p v-if="error" class="mb-2 text-center text-sm text-red-600">
          {{ error }}
        </p>

        <button
          type="button"
          :disabled="processing"
          @click="submit"
          class="mt-2 w-full rounded-md bg-[#0E3D1A] py-3 font-semibold text-white transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-60"
        >
          {{ processing ? 'Checking...' : 'Continue' }}
        </button>

      </div>
    </div>
  </div>
</template>
