<script setup lang="ts">
type LabCodeItem = {
  sampleLabel: string
  labCode: string
}

defineProps<{
  show: boolean
  testRequestCode: string
  labCodes: LabCodeItem[]
}>()

const emit = defineEmits<{
  close: []
  confirm: []
}>()
</script>

<template>
  <div
    v-if="show"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4"
  >
    <div class="w-full max-w-lg rounded-2xl border border-zinc-200 bg-white p-6 shadow-xl">

      <p class="mt-2 text-sm text-zinc-500">
        Please review the generated Test Request No. and Lab Code{{ labCodes.length > 1 ? 's' : '' }} before submitting.
      </p>

      <div class="mt-6 rounded-xl border border-[#0E3D1A] bg-emerald-50 p-4">
        <p class="text-sm font-medium text-zinc-600">
          Your Official Test Request No. is
        </p>

        <p class="mt-1 text-xl font-bold tracking-wide text-[#0E3D1A]">
          {{ testRequestCode }}
        </p>
      </div>

      <div class="mt-5">
        <p class="mb-3 text-sm font-semibold text-zinc-900">
          Your Sample Official Lab Code{{ labCodes.length > 1 ? 's are' : ' is' }}
        </p>

        <div class="space-y-3">
          <div
            v-for="item in labCodes"
            :key="item.sampleLabel"
            class="flex items-center justify-between gap-4 rounded-xl border border-zinc-200 bg-white px-4 py-3"
          >
            <span class="text-sm font-medium text-zinc-700">
              {{ item.sampleLabel }}
            </span>

            <span class="text-sm font-bold tracking-wide text-zinc-900">
              {{ item.labCode }}
            </span>
          </div>
        </div>
      </div>

      <div class="mt-6 flex justify-end gap-3">
        <button
          type="button"
          @click="emit('close')"
          class="rounded-lg border border-zinc-200 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50"
        >
          Cancel
        </button>

        <button
          type="button"
          @click="emit('confirm')"
          class="rounded-lg bg-[#0E3D1A] px-5 py-2 text-sm font-semibold text-white transition hover:opacity-90"
        >
          Confirm Submit
        </button>
      </div>
    </div>
  </div>
</template>