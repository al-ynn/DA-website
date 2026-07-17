<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref } from 'vue'
import { ClipboardCheck, ShieldCheck, BadgeCheck } from 'lucide-vue-next'

const confirmed = ref(false)
const completedIndex = -1

const documents = [
  '/review-documents/T1.pdf',
  '/review-documents/TR2.pdf',
  '/review-documents/TR3.pdf',
  '/review-documents/TR4.pdf',
]

const steps = [
  { label: 'Reviewed', icon: ClipboardCheck },
  { label: 'Certified', icon: ShieldCheck },
  { label: 'Noted', icon: BadgeCheck },
]
</script>

<template>
  <AppSidebarLayout>
    <div class="space-y-6 p-6">

      <!-- BREADCRUMB -->
      <div class="flex items-center gap-2 text-sm text-zinc-500">
        <span>To do</span>
        <span>›</span>
        <span class="text-[#0E3D1A] font-semibold">Review</span>
      </div>

      <h1 class="text-2xl font-bold">Review Test Request</h1>

      <!-- STATUS -->
      <div class="rounded-xl border p-5 bg-white flex items-center">
        <template v-for="(step, i) in steps" :key="i">
          <div class="flex flex-col items-center">
            <div
              class="h-10 w-10 flex items-center justify-center rounded-full border-2"
              :class="i <= completedIndex ? 'bg-green-700 text-white border-green-700' : 'bg-gray-100 text-gray-400'"
            >
              <component :is="step.icon" class="w-5 h-5"/>
            </div>
            <span class="text-xs mt-1">{{ step.label }}</span>
          </div>

          <div
            v-if="i < steps.length-1"
            class="flex-1 h-1 mx-3 rounded"
            :class="i <= completedIndex ? 'bg-green-700' : 'bg-gray-300'"
          />
        </template>
      </div>

      <!-- DOCUMENTS -->
      <div class="max-w-5xl mx-auto space-y-6">
        <iframe
          v-for="doc in documents"
          :key="doc"
          :src="`${doc}#toolbar=0`"
          class="w-full h-[600px] border rounded"
        />
      </div>

      <!-- ACTION -->
      <label class="flex gap-3 max-w-5xl mx-auto">
        <input type="checkbox" v-model="confirmed"/>
        <span>I confirm this is reviewed.</span>
      </label>

      <button
        :disabled="!confirmed"
        class="w-full max-w-5xl mx-auto block bg-[#0E3D1A] text-white py-3 rounded disabled:bg-gray-300"
      >
        Submit Review
      </button>

    </div>
  </AppSidebarLayout>
</template>