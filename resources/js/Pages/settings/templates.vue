<script setup lang="ts">
import { computed, ref } from 'vue'

type Role = 'reviewer' | 'certifier' | 'noter'

const role = ref<Role>('reviewer')
const confirmed = ref(false)
const selectedDoc = ref('/review-documents/T1.pdf')

const documents = [
  { name: 'T1.pdf', path: '/review-documents/T1.pdf' },
  { name: 'TR2.pdf', path: '/review-documents/TR2.pdf' },
  { name: 'TR3.pdf', path: '/review-documents/TR3.pdf' },
  { name: 'TR4.pdf', path: '/review-documents/TR4.pdf' },
]

const submitLabel = computed(() => {
  if (role.value === 'reviewer') return 'Submit Review'
  if (role.value === 'certifier') return 'Submit Certification'
  return 'Submit Noting'
})

const confirmText = computed(() => {
  if (role.value === 'reviewer') {
    return 'I confirm that this request has been properly reviewed and is ready for certification.'
  }

  if (role.value === 'certifier') {
    return 'I confirm that this request has been properly certified and is ready for noting.'
  }

  return 'I confirm that this request has been properly noted and is ready for release.'
})

function submitAction() {
  if (!confirmed.value) return
  alert(`${submitLabel.value} submitted.`)
}
</script>

<template>
  <div class="space-y-5 p-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold text-zinc-900">
        Document Approval
      </h1>

      <select v-model="role" class="rounded-lg border px-3 py-2 text-sm">
        <option value="reviewer">Reviewer</option>
        <option value="certifier">Certifier</option>
        <option value="noter">Noter</option>
      </select>
    </div>

    <div class="flex flex-wrap gap-2">
      <button
        v-for="doc in documents"
        :key="doc.path"
        type="button"
        @click="selectedDoc = doc.path"
        class="rounded-lg border px-4 py-2 text-sm font-semibold"
        :class="selectedDoc === doc.path ? 'border-[#0E3D1A] bg-[#0E3D1A] text-white' : 'bg-white text-zinc-700'"
      >
        {{ doc.name }}
      </button>
    </div>

    <div class="h-[70vh] overflow-hidden rounded-xl border bg-white shadow-sm">
      <iframe :src="selectedDoc" class="h-full w-full" />
    </div>

    <label class="flex cursor-pointer gap-4 rounded-xl border bg-white p-5 text-zinc-800 shadow-sm">
      <input v-model="confirmed" type="checkbox" class="mt-1 h-4 w-4" />
      <span>{{ confirmText }}</span>
    </label>

    <button
      type="button"
      :disabled="!confirmed"
      @click="submitAction"
      class="w-full rounded-xl px-5 py-4 font-semibold text-white transition"
      :class="confirmed ? 'bg-[#0E3D1A] hover:opacity-90' : 'cursor-not-allowed bg-zinc-300'"
    >
      {{ submitLabel }}
    </button>
  </div>
</template>