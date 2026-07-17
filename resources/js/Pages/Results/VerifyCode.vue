<script setup>
import { Head, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import PublicHeader from '@/components/PublicHeader.vue'

const props = defineProps({
  code: String,
})

const otp = ref('')
const error = ref(null)
const processing = ref(false)

function submit() {
  error.value = null

  if (otp.value.length !== 6) {
    error.value = 'Please enter the 6-digit verification code.'
    return
  }

  processing.value = true

  if (otp.value !== '123456') {
    error.value = 'Invalid verification code.'
    processing.value = false
    return
  }

  router.get(`/results/view/${props.code}`)
}
</script>

<template>
  <Head title="Verify Code" />

  <div class="min-h-screen bg-[#f6f7f8] text-sm text-zinc-900">
    
    <PublicHeader />

    <div class="flex min-h-[calc(100vh-72px)] items-center justify-center px-4 py-8">
      
      <div class="w-full max-w-md rounded-xl border border-zinc-200 bg-white p-6 shadow-xl md:p-10">
        
        <h1 class="mb-2 text-center text-xl font-semibold">
          Enter verification code
        </h1>

        <p class="mb-4 text-center text-sm text-zinc-600">
          Enter the 6-digit code sent to your phone.
        </p>

        <input
          v-model="otp"
          maxlength="6"
          inputmode="numeric"
          placeholder="XXXXXX"
          class="mb-3 w-full rounded-md border border-zinc-300 bg-white px-4 py-3 text-center tracking-widest outline-none focus:border-[#0E3D1A]"
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
          {{ processing ? 'Verifying...' : 'Verify' }}
        </button>

      </div>
    </div>
  </div>
</template>