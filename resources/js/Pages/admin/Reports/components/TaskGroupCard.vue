<script setup lang="ts">
import {
  FlaskConical,
  ClipboardCheck,
  BadgeCheck,
  NotebookPen,
} from 'lucide-vue-next'
import TaskRow from './TaskRow.vue'

type TaskType =
  | 'Reviewer'
  | 'Certifier'
  | 'Noter'
  | 'pH'
  | 'Zinc'
  | 'Copper'
  | 'Iron'
  | 'Manganese'
  | 'EC Analysis'

type TodoRequest = {
  id: string
  code: string
  taskType: TaskType
  dueDate: string
}

defineProps<{
  taskType: string
  requests: TodoRequest[]
  formData: Record<string, Record<string, string>>
  submittedRequests: string[]
}>()

defineEmits<{
  addMore: [taskType: string]
  updateField: [requestId: string, field: string, value: string]
  submit: [requestId: string]
  cancel: [requestId: string]
}>()

function getFieldKey(taskType: TaskType) {
  switch (taskType) {
    case 'pH':
      return 'phValue'
    case 'Zinc':
    case 'Copper':
    case 'Iron':
    case 'Manganese':
      return 'concentration'
    case 'EC Analysis':
      return 'ecValue'
    case 'Reviewer':
      return 'reviewNotes'
    case 'Certifier':
      return 'certifierNotes'
    case 'Noter':
      return 'noterNotes'
    default:
      return 'result'
  }
}

function getTaskIcon(taskType: string) {
  switch (taskType) {
    case 'Reviewer':
      return ClipboardCheck
    case 'Certifier':
      return BadgeCheck
    case 'Noter':
      return NotebookPen
    default:
      return FlaskConical
  }
}
</script>

<template>
  <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
    <div class="border-b border-zinc-200 px-6 py-4">
      <div class="flex items-center gap-3">
        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#0E3D1A] text-white">
          <component :is="getTaskIcon(taskType)" class="h-5 w-5" />
        </div>

        <div>
          <h2 class="font-semibold text-zinc-900">
            {{ taskType }}
          </h2>
          <p class="text-sm text-zinc-500">
            {{ requests.length }} task{{ requests.length !== 1 ? 's' : '' }}
          </p>
        </div>

        <button
          type="button"
          @click="$emit('addMore', taskType)"
          class="ml-auto rounded-lg bg-[#F1911F] px-3 py-2 text-xs font-semibold text-white transition hover:bg-[#d97f1a]"
        >
          + Add More
        </button>
      </div>
    </div>

    <div class="space-y-3 p-4">
      <TaskRow
        v-for="request in requests"
        :key="request.id"
        :request="request"
        :submitted="submittedRequests.includes(request.id)"
        :value="formData[request.id]?.[getFieldKey(request.taskType)] || ''"
        @update-value="(value) => $emit('updateField', request.id, getFieldKey(request.taskType), value)"
        @submit="$emit('submit', request.id)"
        @cancel="$emit('cancel', request.id)"
      />
    </div>
  </div>
</template>
