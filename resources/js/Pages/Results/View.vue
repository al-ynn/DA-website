<script setup>
import { Head, Link } from '@inertiajs/vue3'
import {
  ClipboardCheck,
  FlaskConical,
  Microscope,
  Sprout,
  FileCheck2,
  ShieldCheck,
  BadgeCheck,
  PackageCheck,
  Download,
  MessageSquareText,
} from 'lucide-vue-next'

import PublicHeader from '@/components/PublicHeader.vue'

const props = defineProps({
  code: String,
  found: Boolean,
})

const documents = [
  '/review-documents/T1.pdf',
  '/review-documents/TR2.pdf',
  '/review-documents/TR3.pdf',
  '/review-documents/TR4.pdf',
]

const tracker = [
  { key: 'submitted', label: 'Test request submitted', date: '03/15/2026', time: '08:30 AM', icon: ClipboardCheck, completed: true },
  { key: 'analysis_ongoing', label: 'Ongoing Analyzation', date: '03/16/2026', time: '10:00 AM', icon: FlaskConical, completed: true },
  { key: 'analyzed', label: 'Analyzed', date: '03/18/2026', time: '02:45 PM', icon: Microscope, completed: true },
  { key: 'recommendation_ongoing', label: 'Ongoing Recommendation', date: '03/20/2026', time: '09:15 AM', icon: Sprout, completed: true },
  { key: 'recommended', label: 'Recommended', date: '03/22/2026', time: '11:30 AM', icon: FileCheck2, completed: true },
  { key: 'reviewed', label: 'Reviewed', date: '03/24/2026', time: '03:00 PM', icon: ClipboardCheck, completed: true },
  { key: 'certified', label: 'Certified', date: '03/26/2026', time: '01:45 PM', icon: ShieldCheck, completed: true },
  { key: 'noted', label: 'Noted', date: '03/28/2026', time: '04:20 PM', icon: BadgeCheck, completed: true },
  { key: 'ready', label: 'Ready', date: '03/30/2026', time: '10:00 AM', icon: PackageCheck, completed: true },
]
</script>

<template>
  <Head title="Laboratory Result" />

  <div class="min-h-screen bg-[#f6f7f8] text-sm text-zinc-900">
    <PublicHeader />

    <div v-if="!found" class="mt-20 px-4 text-center">
      <h2 class="text-2xl font-bold sm:text-3xl">Report Not Found</h2>
      <p class="mt-2 text-zinc-500">
        The code <strong>{{ code }}</strong> does not match any records.
      </p>

      <Link
        href="/results/request"
        class="mt-6 inline-block rounded-lg bg-[#0E3D1A] px-6 py-3 font-semibold text-white"
      >
        Try Again
      </Link>
    </div>

    <main v-else class="mx-auto flex w-full max-w-7xl flex-col gap-5 px-4 py-3 sm:px-6 lg:px-8">
      <div
        class="sticky top-[72px] z-40 flex flex-col gap-3 rounded-lg border-2 border-[#0E3D1A] bg-white px-4 py-4 shadow-sm lg:flex-row lg:items-center lg:justify-between"
      >
        <p class="text-base">
          <span class="font-semibold">Result for Test Request No.</span>
          RSL-2026-1
        </p>

        <div class="flex flex-col gap-2 sm:flex-row">
          <button class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#008B3A] px-6 py-2.5 text-sm font-semibold text-white">
            <Download class="h-4 w-4" />
            Download
          </button>

          <button class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white">
            <MessageSquareText class="h-4 w-4" />
            Send via SMS
          </button>
        </div>
      </div>

      <section class="rounded-xl border border-zinc-200 bg-white px-4 py-5 shadow-sm">
        <div class="overflow-x-auto pb-2 no-scrollbar">
          <div class="relative flex min-w-[1050px] items-start justify-between">
            <div class="absolute left-10 right-10 top-4 h-[3px] rounded-full bg-[#008B3A]" />

            <div
              v-for="item in tracker"
              :key="item.key"
              class="relative z-10 flex w-28 flex-col items-center text-center"
            >
              <div
                class="flex h-8 w-8 items-center justify-center rounded-full text-white ring-4 ring-white"
                :class="item.completed ? 'bg-[#008B3A]' : 'bg-zinc-300'"
              >
                <component :is="item.icon" class="h-4 w-4" />
              </div>

              <p class="mt-2 text-xs font-semibold leading-tight">
                {{ item.label }}
              </p>
              <p class="mt-1 text-[11px] leading-tight text-zinc-600">
                {{ item.date }}
              </p>
              <p class="text-[11px] leading-tight text-zinc-600">
                {{ item.time }}
              </p>
            </div>
          </div>
        </div>
      </section>

      <div class="space-y-6">
        <div
          v-for="doc in documents"
          :key="doc"
          class="mx-auto max-w-5xl overflow-hidden rounded-xl border bg-white p-3 shadow-sm"
        >
          <iframe
            :src="`${doc}#toolbar=0&view=FitH`"
            class="h-[520px] w-full border-0 sm:h-[600px] lg:h-[650px]"
          />
        </div>
      </div>

      <Link
        href="/results/request"
        class="mx-auto mb-6 block w-full max-w-sm rounded-lg bg-slate-600 px-5 py-3 text-center text-sm font-semibold text-white"
      >
        Check Another Report
      </Link>
    </main>
  </div>
</template>

<style scoped>
.no-scrollbar {
  scrollbar-width: none;
  -ms-overflow-style: none;
}

.no-scrollbar::-webkit-scrollbar {
  display: none;
}
</style>