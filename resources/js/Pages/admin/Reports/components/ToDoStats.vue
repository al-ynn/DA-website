<script setup lang="ts">
import { CheckCircle2, Circle, Clock3 } from 'lucide-vue-next'

defineProps<{
  activeTasks: number
  missedTasks: number
  completedTasks: number
}>()
</script>

<template>
  <div class="grid gap-4 md:grid-cols-3">
    <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm transition hover:border-[#0E3D1A]">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-zinc-500">Active Tasks</p>
          <p class="mt-1 text-2xl font-bold text-zinc-900">
            {{ activeTasks }}
          </p>
        </div>

        <div class="rounded-xl bg-zinc-100 p-2.5">
          <Circle class="h-5 w-5 text-zinc-600" />
        </div>
      </div>
    </div>

    <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm transition hover:border-rose-500">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-zinc-500">Missed Tasks</p>
          <p class="mt-1 text-2xl font-bold text-zinc-900">
            {{ missedTasks }}
          </p>
        </div>

        <div class="rounded-xl bg-rose-100 p-2.5">
          <Clock3 class="h-5 w-5 text-rose-600" />
        </div>
      </div>
    </div>

    <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm transition hover:border-emerald-500">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-zinc-500">Completed Tasks</p>
          <p class="mt-1 text-2xl font-bold text-zinc-900">
            {{ completedTasks }}
          </p>
        </div>

        <div class="rounded-xl bg-emerald-100 p-2.5">
          <CheckCircle2 class="h-5 w-5 text-emerald-600" />
        </div>
      </div>
    </div>
  </div>
</template>