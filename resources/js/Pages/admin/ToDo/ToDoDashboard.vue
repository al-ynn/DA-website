<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import { FlaskConical, Tractor, Star } from 'lucide-vue-next'
import { computed, ref } from 'vue'

type UserRole = 'admin' | 'chemist' | 'agriculturist'

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
  | 'Soil Moisture'
  | 'Soil Texture'
  | 'Particle Size Analysis'
  | 'Fertilizer Recommendation'

type SampleDescription = 'Soil' | 'Water' | 'Fertilizer'

type TodoRequest = {
  id: string
  code?: string
  labCode?: string
  testRequestCode?: string
  taskType: TaskType
  dueDate: string
  role: UserRole
  sampleDescription?: SampleDescription
  status?: 'available' | 'assigned' | 'submitted'
  assignedTo?: string
  assignedToName?: string
  resultValue?: string
  resultRemarks?: string
}

const props = defineProps<{
  authUser?: {
    id: number
    name: string
    role: string
    roles: string[]
    assignedTasks: string[]
  }
  todoRequests?: TodoRequest[]
}>()

const currentUser = computed(() => ({
  id: String(props.authUser?.id ?? ''),
  name: props.authUser?.name ?? '',
  roles: (props.authUser?.roles ?? []) as UserRole[],
  assignedTasks: (props.authUser?.assignedTasks ?? []) as TaskType[],
}))

const completedRequests = ref<string[]>([])
const selectedRole = ref<'all' | UserRole>('all')
const adminFilter = ref<TaskType | ''>('')
const chemistFilter = ref<TaskType | ''>('')
const agriFilter = ref<TaskType | ''>('')
const stagedRequests = ref<TodoRequest[]>([])

const resultValues = ref<Record<string, string>>({})
const showSubmitConfirm = ref(false)
const selectedSubmitRequest = ref<TodoRequest | null>(null)

const adminTasks: TaskType[] = ['Reviewer', 'Certifier', 'Noter']

const chemistTasks: TaskType[] = [
  'pH',
  'Zinc',
  'Copper',
  'Iron',
  'Manganese',
  'EC Analysis',
]

const agriTasks: TaskType[] = [
  'Soil Moisture',
  'Soil Texture',
  'Particle Size Analysis',
  'Fertilizer Recommendation',
]

const myRequests = computed<TodoRequest[]>(() =>
  [...(props.todoRequests ?? []), ...stagedRequests.value].filter(
    (task) =>
      task.status === 'assigned' &&
      task.assignedTo === String(currentUser.value.id) &&
      currentUser.value.roles.includes(task.role),
  ),
)

const visibleRequests = computed(() => {
  let data = myRequests.value.filter((request) =>
    currentUser.value.roles.includes(request.role),
  )

  if (selectedRole.value !== 'all') data = data.filter((request) => request.role === selectedRole.value)
  if (adminFilter.value) data = data.filter((request) => request.taskType === adminFilter.value)
  if (chemistFilter.value) data = data.filter((request) => request.taskType === chemistFilter.value)
  if (agriFilter.value) data = data.filter((request) => request.taskType === agriFilter.value)

  return data
})

function clearFilters() {
  selectedRole.value = 'all'
  adminFilter.value = ''
  chemistFilter.value = ''
  agriFilter.value = ''
}

function parseDueDate(date: string) {
  const parsed = new Date(date)
  parsed.setHours(23, 59, 59, 999)
  return parsed
}

function isMissed(request: TodoRequest) {
  return (
    !completedRequests.value.includes(request.id) &&
    parseDueDate(request.dueDate).getTime() < new Date().getTime()
  )
}

const activeTasksCount = computed(() =>
  visibleRequests.value.filter(
    (request) =>
      !completedRequests.value.includes(request.id) &&
      !isMissed(request),
  ).length,
)

const finishedTasksCount = computed(() =>
  visibleRequests.value.filter((request) =>
    completedRequests.value.includes(request.id),
  ).length,
)

const missedTasksCount = computed(() =>
  visibleRequests.value.filter((request) => isMissed(request)).length,
)

const groupedRequests = computed(() => {
  return visibleRequests.value.reduce(
    (acc: Record<string, TodoRequest[]>, req) => {
      if (!acc[req.taskType]) acc[req.taskType] = []
      acc[req.taskType].push(req)
      return acc
    },
    {},
  )
})

const formattedRoles = computed(() =>
  currentUser.value.roles.map((role) => role.charAt(0).toUpperCase() + role.slice(1)),
)

function goToAvailableTasks(taskType?: string) {
  if (taskType) {
    router.visit(`/todo/available-tasks/${encodeURIComponent(taskType)}`)
    return
  }
  router.visit('/todo/available-tasks')
}

function getTaskIcon(taskType: TaskType) {
  if (adminTasks.includes(taskType)) return Star
  if (agriTasks.includes(taskType)) return Tractor
  return FlaskConical
}

function getSamplePrefix(sampleDescription?: SampleDescription) {
  if (sampleDescription === 'Soil') return 'S'
  if (sampleDescription === 'Water') return 'W'
  return 'F'
}

function getTestRequestCode(request: TodoRequest) {
  if (request.testRequestCode) return request.testRequestCode
  if (request.code?.startsWith('RSL-')) return request.code

  const year = new Date().getFullYear()
  const requestIndex = myRequests.value.findIndex((item) => item.id === request.id) + 1

  return `RSL-${year}-${String(requestIndex).padStart(3, '0')}`
}

function getFlowKey(request: TodoRequest) {
  return request.testRequestCode || request.code || request.labCode || getTestRequestCode(request)
}

function getLabCode(request: TodoRequest) {
  if (request.labCode) return request.labCode

  const year = String(new Date().getFullYear()).slice(-2)
  const prefix = getSamplePrefix(request.sampleDescription)

  const sameSampleRequests = myRequests.value.filter(
    (item) =>
      item.sampleDescription === request.sampleDescription &&
      (item.role === 'chemist' || item.role === 'agriculturist'),
  )

  const sampleIndex = sameSampleRequests.findIndex((item) => item.id === request.id) + 1

  return `${prefix}${year}-${String(sampleIndex).padStart(3, '0')}`
}

function getDisplayCode(request: TodoRequest) {
  if (request.role === 'chemist' || request.role === 'agriculturist') {
    return getLabCode(request)
  }
  return getTestRequestCode(request)
}

function getPlaceholder(taskType: TaskType) {
  switch (taskType) {
    case 'pH': return 'Enter pH value'
    case 'EC Analysis': return 'Enter EC value'
    case 'Zinc':
    case 'Copper':
    case 'Iron':
    case 'Manganese':
      return `Enter ${taskType.toLowerCase()} concentration`
    case 'Soil Moisture': return 'Enter soil moisture'
    case 'Soil Texture': return 'Enter soil texture'
    case 'Particle Size Analysis': return 'Enter particle size result'
    case 'Fertilizer Recommendation': return 'Enter recommendation'
    default: return 'Enter result'
  }
}

function saveMyTasks() {
  void myRequests.value
}

function askSubmitTask(request: TodoRequest) {
  const result = resultValues.value[request.id]?.trim()
  if (!result) return

  selectedSubmitRequest.value = request
  showSubmitConfirm.value = true
}

function confirmSubmitTask() {
  const request = selectedSubmitRequest.value
  if (!request) return

  submitTask(request)

  showSubmitConfirm.value = false
  selectedSubmitRequest.value = null
}

function submitTask(request: TodoRequest) {
  const result = resultValues.value[request.id]?.trim()

  if (!result) return
  if (request.role !== 'chemist' && request.role !== 'agriculturist') return

  request.status = 'submitted'
  request.resultValue = result

  completedRequests.value.push(request.id)

  const requestKey = getFlowKey(request)

  const hasRemainingSameStageTasks = myRequests.value.some(
    (task) =>
      getFlowKey(task) === requestKey &&
      task.role === request.role &&
      task.status === 'assigned',
  )

  if (request.role === 'chemist' && !hasRemainingSameStageTasks) {
    const agriculturistTask: TodoRequest = {
      ...request,
      id: crypto.randomUUID(),
      taskType: 'Fertilizer Recommendation',
      role: 'agriculturist',
      status: 'available',
      assignedTo: undefined,
      assignedToName: undefined,
      resultValue: '',
    }

    stagedRequests.value.push(agriculturistTask)
  }

  if (request.role === 'agriculturist' && !hasRemainingSameStageTasks) {
    const reviewerTask: TodoRequest = {
      ...request,
      id: crypto.randomUUID(),
      taskType: 'Reviewer',
      role: 'admin',
      status: 'assigned',
      assignedTo: currentUser.value.id,
      assignedToName: currentUser.value.name,
      resultValue: '',
    }

    stagedRequests.value.push(reviewerTask)
  }
}

function getActionLabel(taskType: TaskType) {
  if (taskType === 'Reviewer') return 'Review'
  if (taskType === 'Certifier') return 'Certify'
  if (taskType === 'Noter') return 'Note'
  return 'Submit'
}

function openAdminTask(request: TodoRequest) {
  if (request.taskType === 'Reviewer') {
    router.visit(`/todo/task/admin/reviewer/${request.id}`)
    return
  }

  if (request.taskType === 'Certifier') {
    router.visit(`/todo/task/admin/certifier/${request.id}`)
    return
  }

  if (request.taskType === 'Noter') {
    router.visit(`/todo/task/admin/noter/${request.id}`)
  }
}
</script>

<template>
  <div class="relative p-3 sm:p-4 md:p-6">
    <div class="mx-auto max-w-6xl space-y-5 sm:space-y-6">
      <div class="rounded-xl border border-zinc-200 bg-white shadow-sm">
        <div class="px-4 py-4 sm:px-6 md:px-8">
          <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-3 sm:gap-4">
              <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#0E3D1A] text-white sm:h-12 sm:w-12">
                <Star class="h-5 w-5 sm:h-6 sm:w-6" />
              </div>

              <div>
                <h1 class="text-xl font-bold text-zinc-900 sm:text-2xl">
                  My To-Do List
                </h1>

                <div class="mt-2 flex flex-wrap items-center gap-2">
                  <span
                    v-for="role in formattedRoles"
                    :key="role"
                    class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800"
                  >
                    {{ role }}
                  </span>
                </div>
              </div>
            </div>

            <button
              type="button"
              @click="goToAvailableTasks()"
              class="inline-flex items-center justify-center rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50"
            >
              All Available Tasks
            </button>
          </div>
        </div>
      </div>

      <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
        <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm">
          <p class="text-xs text-zinc-500 sm:text-sm">Active Tasks</p>
          <p class="mt-1 text-2xl font-bold text-zinc-900">{{ activeTasksCount }}</p>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm">
          <p class="text-xs text-zinc-500 sm:text-sm">Missed Tasks</p>
          <p class="mt-1 text-2xl font-bold text-zinc-900">{{ missedTasksCount }}</p>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm sm:col-span-2 lg:col-span-1">
          <p class="text-xs text-zinc-500 sm:text-sm">Completed Tasks</p>
          <p class="mt-1 text-2xl font-bold text-zinc-900">{{ finishedTasksCount }}</p>
        </div>
      </div>

      <div class="flex flex-wrap items-center gap-2">
        <button type="button" @click="selectedRole = 'all'" class="min-w-24 rounded-xl border px-4 py-2 text-xs font-semibold sm:min-w-32 sm:px-8 sm:py-3 sm:text-sm" :class="selectedRole === 'all' ? 'border-emerald-600 text-emerald-500' : 'border-zinc-300 text-zinc-700'">
          All
        </button>

        <button type="button" @click="selectedRole = 'admin'" class="min-w-24 rounded-xl border px-4 py-2 text-xs font-semibold sm:min-w-32 sm:px-8 sm:py-3 sm:text-sm" :class="selectedRole === 'admin' ? 'border-emerald-600 text-emerald-500' : 'border-zinc-300 text-zinc-700'">
          Admin
        </button>

        <button type="button" @click="selectedRole = 'chemist'" class="min-w-24 rounded-xl border px-4 py-2 text-xs font-semibold sm:min-w-32 sm:px-8 sm:py-3 sm:text-sm" :class="selectedRole === 'chemist' ? 'border-emerald-600 text-emerald-500' : 'border-zinc-300 text-zinc-700'">
          Chemist
        </button>

        <button type="button" @click="selectedRole = 'agriculturist'" class="min-w-24 rounded-xl border px-4 py-2 text-xs font-semibold sm:min-w-32 sm:px-8 sm:py-3 sm:text-sm" :class="selectedRole === 'agriculturist' ? 'border-emerald-600 text-emerald-500' : 'border-zinc-300 text-zinc-700'">
          Agriculturist
        </button>
      </div>

      <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center">
        <div v-if="currentUser.roles.includes('admin')" class="relative w-full sm:w-auto">
          <Star class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-emerald-500" />
          <select v-model="adminFilter" class="h-11 w-full rounded-xl border border-zinc-300 bg-white pl-10 pr-4 text-sm font-semibold text-zinc-900 sm:min-w-64">
            <option value="">Filter admin task</option>
            <option v-for="task in adminTasks" :key="task" :value="task">{{ task }}</option>
          </select>
        </div>

        <div v-if="currentUser.roles.includes('chemist')" class="relative w-full sm:w-auto">
          <FlaskConical class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-emerald-500" />
          <select v-model="chemistFilter" class="h-11 w-full rounded-xl border border-zinc-300 bg-white pl-10 pr-4 text-sm font-semibold text-zinc-900 sm:min-w-64">
            <option value="">Filter task</option>
            <option v-for="task in chemistTasks" :key="task" :value="task">{{ task }}</option>
          </select>
        </div>

        <div v-if="currentUser.roles.includes('agriculturist')" class="relative w-full sm:w-auto">
          <Tractor class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-emerald-500" />
          <select v-model="agriFilter" class="h-11 w-full rounded-xl border border-zinc-300 bg-white pl-10 pr-4 text-sm font-semibold text-zinc-900 sm:min-w-64">
            <option value="">Filter task</option>
            <option v-for="task in agriTasks" :key="task" :value="task">{{ task }}</option>
          </select>
        </div>

        <button type="button" @click="clearFilters" class="h-10 rounded-full border border-red-500 px-4 text-xs font-semibold text-red-500 transition hover:bg-red-500 hover:text-white">
          Clear
        </button>
      </div>

      <div v-if="visibleRequests.length === 0" class="rounded-xl border border-zinc-200 bg-white px-6 py-12 text-center shadow-sm">
        <h3 class="mb-2 text-base font-semibold text-zinc-900">
          No Tasks Yet
        </h3>
      </div>

      <div v-else class="space-y-5">
        <div
          v-for="(requests, taskType) in groupedRequests"
          :key="taskType"
          class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm"
        >
          <div class="border-b border-zinc-200 px-4 py-3">
            <div class="flex flex-wrap items-center gap-3">
              <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#0E3D1A] text-white">
                <component :is="getTaskIcon(taskType as TaskType)" class="h-4 w-4" />
              </div>

              <div>
                <h2 class="font-semibold text-zinc-900">
                  {{ taskType }}
                </h2>
                <p class="text-xs text-zinc-500 sm:text-sm">
                  {{ requests.length }} task{{ requests.length !== 1 ? 's' : '' }}
                </p>
              </div>

              <button type="button" @click="goToAvailableTasks(taskType)" class="ml-auto rounded-lg bg-[#F1911F] px-3 py-2 text-xs font-semibold text-white transition hover:bg-[#d97f1a]">
                + Add More
              </button>
            </div>
          </div>

          <div class="space-y-3 p-3 sm:p-4">
            <div
              v-for="request in requests"
              :key="request.id"
              class="rounded-xl border border-zinc-200 bg-white p-4 transition hover:border-[#0E3D1A] hover:shadow-sm"
            >
              <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
                <div class="flex-1">
                  <span class="text-sm font-medium text-zinc-900">
                    {{ getDisplayCode(request) }}
                  </span>

                  <p class="mt-0.5 flex flex-wrap items-center gap-2 text-xs" :class="isMissed(request) ? 'text-zinc-900' : 'text-zinc-500'">
                    Due: {{ request.dueDate || '—' }}

                    <span v-if="isMissed(request)" class="rounded bg-red-100 px-1.5 py-0.5 text-[10px] font-semibold uppercase text-red-700">
                      Missed
                    </span>
                  </p>
                </div>

                <input
                  v-if="request.role === 'chemist' || request.role === 'agriculturist'"
                  v-model="resultValues[request.id]"
                  type="text"
                  :placeholder="getPlaceholder(request.taskType)"
                  class="h-10 w-full rounded-lg border-0 bg-zinc-100 px-4 text-sm outline-none focus:ring-1 focus:ring-[#0E3D1A] lg:w-60"
                />

                <button
                  v-if="request.role === 'admin'"
                  type="button"
                  @click="openAdminTask(request)"
                  class="rounded-lg bg-[#0E3D1A] px-6 py-2.5 text-xs font-semibold text-white transition hover:opacity-90"
                >
                  {{ getActionLabel(request.taskType) }}
                </button>

                <button
                  v-else
                  type="button"
                  @click="askSubmitTask(request)"
                  class="rounded-lg bg-[#0E3D1A] px-6 py-2.5 text-xs font-semibold text-white transition hover:opacity-90"
                >
                  Submit
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div
      v-if="showSubmitConfirm"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4"
    >
      <div class="w-full max-w-md rounded-2xl border border-zinc-200 bg-white p-5 shadow-xl sm:p-6">
        <h2 class="text-lg font-bold text-zinc-900">
          Confirm Task Submission
        </h2>

        <p class="mt-2 text-sm text-zinc-500">
          Are you sure you want to submit this result? Once confirmed, this task will be forwarded to the next stage.
        </p>

        <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
          <button
            type="button"
            @click="showSubmitConfirm = false"
            class="rounded-lg border border-zinc-200 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50"
          >
            Cancel
          </button>

          <button
            type="button"
            @click="confirmSubmitTask"
            class="rounded-lg bg-[#0E3D1A] px-5 py-2 text-sm font-semibold text-white transition hover:opacity-90"
          >
            Confirm Submit
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
