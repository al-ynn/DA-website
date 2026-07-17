<script setup lang="ts">
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

const props = defineProps<{
  request: TodoRequest
  value?: string
  submitted?: boolean
}>()

defineEmits<{
  updateValue: [value: string]
  submit: []
  cancel: []
}>()

function getFieldLabel(taskType: TaskType) {
  switch (taskType) {
    case 'pH':
      return 'pH Value'
    case 'Zinc':
    case 'Copper':
    case 'Iron':
    case 'Manganese':
      return `${taskType} Concentration (ppm)`
    case 'EC Analysis':
      return 'EC Value (dS/m)'
    case 'Reviewer':
      return 'Review Notes'
    case 'Certifier':
      return 'Certification Notes'
    case 'Noter':
      return 'Noter Notes'
    default:
      return 'Result'
  }
}

function getPlaceholder(taskType: TaskType) {
  switch (taskType) {
    case 'pH':
      return 'Enter pH value'
    case 'Zinc':
    case 'Copper':
    case 'Iron':
    case 'Manganese':
      return `Enter ${taskType.toLowerCase()} concentration`
    case 'EC Analysis':
      return 'Enter EC value'
    case 'Reviewer':
      return 'Enter review notes'
    case 'Certifier':
      return 'Enter certification notes'
    case 'Noter':
      return 'Enter noter notes'
    default:
      return 'Enter result'
  }
}

function getSubmitLabel(taskType: TaskType) {
  switch (taskType) {
    case 'Reviewer':
      return 'Review'
    case 'Certifier':
      return 'Certify'
    case 'Noter':
      return 'Note'
    default:
      return 'Submit'
  }
}

function isNumericTask(taskType: TaskType) {
  return ['pH', 'Zinc', 'Copper', 'Iron', 'Manganese', 'EC Analysis'].includes(taskType)
}
</script>

<template>
  <div class="rounded-xl border border-zinc-200 bg-white p-4 transition hover:border-[#0E3D1A] hover:shadow-sm">
    <div class="flex items-center gap-3">
      <div class="w-36 shrink-0">
        <span class="text-sm font-medium text-zinc-900">
          {{ props.request.code }}
        </span>
        <p class="mt-0.5 text-xs text-zinc-500">
          Due: {{ props.request.dueDate }}
        </p>
      </div>

      <template v-if="submitted">
        <div class="flex-1"></div>

        <div class="flex items-center gap-3">
          <div class="text-right">
            <div class="text-xs text-zinc-500">
              {{ getFieldLabel(props.request.taskType) }}
            </div>
            <div class="text-sm font-medium text-zinc-900">
              {{ value || 'No answer provided' }}
            </div>
          </div>

          <button
            type="button"
            @click="$emit('cancel')"
            class="rounded-lg border border-zinc-200 bg-white px-4 py-2 text-xs font-medium text-zinc-700 transition hover:bg-zinc-50"
          >
            Cancel
          </button>
        </div>
      </template>

      <template v-else>
        <div class="flex-1"></div>

        <div class="flex items-center gap-3">
          <input
            :type="isNumericTask(props.request.taskType) ? 'number' : 'text'"
            step="0.01"
            :placeholder="getPlaceholder(props.request.taskType)"
            :value="value || ''"
            @input="$emit('updateValue', ($event.target as HTMLInputElement).value)"
            class="h-10 w-52 rounded-lg border border-zinc-200 bg-white px-3 text-sm text-zinc-900 outline-none focus:border-[#0E3D1A]"
          />

          <button
            type="button"
            @click="$emit('submit')"
            class="rounded-lg bg-[#0E3D1A] px-4 py-2 text-xs font-semibold text-white transition hover:opacity-90"
          >
            {{ getSubmitLabel(props.request.taskType) }}
          </button>
        </div>
      </template>
    </div>
  </div>
</template>