<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue'
import { Head } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import type { BreadcrumbItem } from '@/types'
import {
  ListTodo,
  Hand,
  FlaskConical,
  Tractor,
} from 'lucide-vue-next'

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
  taskType: TaskType
  status: 'available' | 'assigned'
  assignedTo?: string
  assignedToName?: string
  dueDate: string
  role?: UserRole
  sampleDescription: SampleDescription
  labCode?: string
  testRequestCode?: string
  sampleLabel?: string
}

const props = defineProps<{
  taskType?: string
}>()

const decodedTaskType = computed(() => (props.taskType ? decodeURIComponent(props.taskType) : ''))

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'To do', href: '/todo' },
  {
    title: decodedTaskType.value ? `Available ${decodedTaskType.value} Tasks` : 'All Available Tasks',
    href: '/todo/available-tasks',
  },
]

const currentUser = ref({
  id: 'user-1',
  name: 'Ean Test',
  roles: ['admin', 'chemist', 'agriculturist'] as UserRole[],
  assignedTasks: [
    'Reviewer',
    'Certifier',
    'Noter',
    'pH',
    'Zinc',
    'EC Analysis',
    'Soil Moisture',
    'Soil Texture',
    'Particle Size Analysis',
    'Fertilizer Recommendation',
  ] as TaskType[],
})

const chemistTasks: TaskType[] = [
  'pH',
  'Zinc',
  'Copper',
  'Iron',
  'Manganese',
  'EC Analysis',
]

const agriculturistTasks: TaskType[] = [
  'Soil Moisture',
  'Soil Texture',
  'Particle Size Analysis',
  'Fertilizer Recommendation',
]

const grabTaskTypes: TaskType[] = [...chemistTasks, ...agriculturistTasks]

const STORAGE_KEY = `todo-my-requests-${currentUser.value.id}`
const AVAILABLE_TASKS_STORAGE_KEY = 'todo-available-tasks'
const OLD_AVAILABLE_TASKS_STORAGE_KEY = 'available-tasks'

const roleFilter = ref<'all' | 'chemist' | 'agriculturist'>('all')
const chemistTaskFilter = ref('')
const agriculturistTaskFilter = ref('')

function normalizeSampleDescription(value: unknown): SampleDescription {
  const sample = String(value || '').toLowerCase()

  if (sample === 'water') return 'Water'
  if (sample === 'fertilizer') return 'Fertilizer'
  return 'Soil'
}

function normalizeSavedTask(item: any): TodoRequest {
  return {
    ...item,
    taskType: item.taskType as TaskType,
    sampleDescription: normalizeSampleDescription(item.sampleDescription),
    status: item.status ?? 'available',
  }
}

function getSavedAvailableTasks(): TodoRequest[] {
  try {
    if (typeof localStorage === 'undefined') return []

    const rawMain = localStorage.getItem(AVAILABLE_TASKS_STORAGE_KEY)
    const rawOld = localStorage.getItem(OLD_AVAILABLE_TASKS_STORAGE_KEY)

    const mainParsed = rawMain ? JSON.parse(rawMain) : []
    const oldParsed = rawOld ? JSON.parse(rawOld) : []

    const combined = [
      ...(Array.isArray(mainParsed) ? mainParsed : []),
      ...(Array.isArray(oldParsed) ? oldParsed : []),
    ]

    const unique = combined.filter(
      (item, index, self) =>
        index === self.findIndex((task) =>
          task.id === item.id ||
          (
            task.testRequestCode === item.testRequestCode &&
            task.labCode === item.labCode &&
            task.taskType === item.taskType &&
            task.role === item.role
          ),
        ),
    )

    return unique
      .map(normalizeSavedTask)
      .filter((task) => task.role === 'chemist' || task.role === 'agriculturist')
  } catch {
    return []
  }
}

function saveAvailableTasks(items: TodoRequest[]) {
  try {
    if (typeof localStorage === 'undefined') return

    localStorage.setItem(
      AVAILABLE_TASKS_STORAGE_KEY,
      JSON.stringify(items.filter((task) => task.role === 'chemist' || task.role === 'agriculturist')),
    )
    localStorage.removeItem(OLD_AVAILABLE_TASKS_STORAGE_KEY)
  } catch {
    // frontend-only fallback
  }
}

const requests = ref<TodoRequest[]>(getSavedAvailableTasks())

const allowedTaskTypes = computed<TaskType[]>(() => currentUser.value.assignedTasks)

const accessibleRequests = computed(() => {
  let filtered = requests.value.filter((request) =>
    allowedTaskTypes.value.includes(request.taskType) &&
    grabTaskTypes.includes(request.taskType) &&
    (request.role === 'chemist' || request.role === 'agriculturist'),
  )

  if (decodedTaskType.value) {
    if (!allowedTaskTypes.value.includes(decodedTaskType.value as TaskType)) return []
    if (!grabTaskTypes.includes(decodedTaskType.value as TaskType)) return []

    filtered = filtered.filter((request) => request.taskType === decodedTaskType.value)
  }

  if (roleFilter.value !== 'all') {
    filtered = filtered.filter((request) => request.role === roleFilter.value)
  }

  if (chemistTaskFilter.value) {
    filtered = filtered.filter((request) => request.taskType === chemistTaskFilter.value)
  }

  if (agriculturistTaskFilter.value) {
    filtered = filtered.filter((request) => request.taskType === agriculturistTaskFilter.value)
  }

  return filtered
})

const chemistRequests = computed(() =>
  accessibleRequests.value.filter((request) => request.role === 'chemist'),
)

const agriculturistRequests = computed(() =>
  accessibleRequests.value.filter((request) => request.role === 'agriculturist'),
)

const chemistAvailableRequests = computed(() =>
  chemistRequests.value.filter((request) => request.status === 'available'),
)

const chemistGrabbedRequests = computed(() =>
  chemistRequests.value.filter((request) => request.status === 'assigned'),
)

const agriculturistAvailableRequests = computed(() =>
  agriculturistRequests.value.filter((request) => request.status === 'available'),
)

const agriculturistGrabbedRequests = computed(() =>
  agriculturistRequests.value.filter((request) => request.status === 'assigned'),
)

function parseDueDate(date: string) {
  const parsed = new Date(date)
  parsed.setHours(23, 59, 59, 999)
  return parsed
}

function isMissed(request: TodoRequest) {
  if (!request.dueDate) return false
  return parseDueDate(request.dueDate).getTime() < new Date().getTime()
}

const availableRequests = computed(() =>
  accessibleRequests.value.filter((request) => request.status === 'available'),
)

function groupByTask(items: TodoRequest[]) {
  return items.reduce((acc: Record<string, TodoRequest[]>, req) => {
    if (!acc[req.taskType]) acc[req.taskType] = []
    acc[req.taskType].push(req)
    return acc
  }, {})
}

const groupedChemistAvailableRequests = computed(() => groupByTask(chemistAvailableRequests.value))
const groupedChemistGrabbedRequests = computed(() => groupByTask(chemistGrabbedRequests.value))
const groupedAgriculturistAvailableRequests = computed(() => groupByTask(agriculturistAvailableRequests.value))
const groupedAgriculturistGrabbedRequests = computed(() => groupByTask(agriculturistGrabbedRequests.value))

function clearFilters() {
  roleFilter.value = 'all'
  chemistTaskFilter.value = ''
  agriculturistTaskFilter.value = ''
}

function setRoleFilter(role: 'all' | 'chemist' | 'agriculturist') {
  roleFilter.value = role
  chemistTaskFilter.value = ''
  agriculturistTaskFilter.value = ''
}

function getSamplePrefix(sampleDescription: SampleDescription) {
  if (sampleDescription === 'Soil') return 'S'
  if (sampleDescription === 'Water') return 'W'
  return 'FM'
}

function getLabCode(request: TodoRequest) {
  if (request.labCode) return request.labCode

  const year = String(new Date().getFullYear()).slice(-2)
  const prefix = getSamplePrefix(request.sampleDescription)

  const sameSampleRequests = requests.value.filter(
    (item) =>
      item.sampleDescription === request.sampleDescription &&
      (item.role === 'chemist' || item.role === 'agriculturist'),
  )

  const sampleIndex = sameSampleRequests.findIndex((item) => item.id === request.id) + 1

  return `${prefix}${year}-${String(sampleIndex).padStart(4, '0')}`
}

function getDisplayCode(request: TodoRequest) {
  return getLabCode(request)
}

function getStoredMyRequests(): TodoRequest[] {
  try {
    const raw = localStorage.getItem(STORAGE_KEY)
    if (!raw) return []
    const parsed = JSON.parse(raw)
    return Array.isArray(parsed) ? parsed : []
  } catch {
    return []
  }
}

function setStoredMyRequests(items: TodoRequest[]) {
  localStorage.setItem(STORAGE_KEY, JSON.stringify(items))
}

function addToMyTodo(request: TodoRequest) {
  const existing = getStoredMyRequests()
  const withoutDuplicate = existing.filter((item) => item.id !== request.id)

  setStoredMyRequests([
    ...withoutDuplicate,
    {
      ...request,
      labCode: getLabCode(request),
      status: 'assigned',
      assignedTo: currentUser.value.id,
      assignedToName: currentUser.value.name,
    },
  ])
}

function handleGrabTask(requestId: string) {
  const request = requests.value.find((r) => r.id === requestId)
  if (!request) return
  if (request.role !== 'chemist' && request.role !== 'agriculturist') return

  request.status = 'assigned'
  request.assignedTo = currentUser.value.id
  request.assignedToName = currentUser.value.name
  request.labCode = getLabCode(request)

  addToMyTodo(request)
  saveAvailableTasks(requests.value)
}
</script>

<template>
  <Head title="Available Tasks" />

  <AppSidebarLayout :breadcrumbs="breadcrumbs">
    <div class="relative p-4 md:p-6">
      <div class="mx-auto max-w-6xl space-y-6">
        <div class="rounded-2xl border border-zinc-200 bg-white shadow-sm">
          <div class="px-6 py-5 md:px-8">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
              <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#0E3D1A] text-white">
                  <ListTodo class="h-6 w-6" />
                </div>

                <h1 class="text-2xl font-bold text-zinc-900">
                  {{ decodedTaskType ? `Available ${decodedTaskType} Tasks` : 'All Available Tasks' }}
                </h1>
              </div>

              <a
                href="/todo"
                class="inline-flex items-center rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50"
              >
                My To-Do List
              </a>
            </div>
          </div>
        </div>

        <div class="grid gap-4 lg:grid-cols-[1fr_320px]">
          <div class="space-y-3">
            <div class="flex flex-wrap gap-3">
              <button
                type="button"
                @click="setRoleFilter('all')"
                class="min-w-28 rounded-lg border px-4 py-2 text-sm font-semibold transition"
                :class="roleFilter === 'all'
                  ? 'border-[#0E3D1A] text-emerald-500'
                  : 'border-zinc-200 text-zinc-700'"
              >
                All
              </button>

              <button
                v-if="currentUser.roles.includes('chemist')"
                type="button"
                @click="setRoleFilter('chemist')"
                class="min-w-28 rounded-lg border px-4 py-2 text-sm font-semibold transition"
                :class="roleFilter === 'chemist'
                  ? 'border-[#0E3D1A] text-emerald-500'
                  : 'border-zinc-200 text-zinc-700'"
              >
                Chemist
              </button>

              <button
                v-if="currentUser.roles.includes('agriculturist')"
                type="button"
                @click="setRoleFilter('agriculturist')"
                class="min-w-28 rounded-lg border px-4 py-2 text-sm font-semibold transition"
                :class="roleFilter === 'agriculturist'
                  ? 'border-[#0E3D1A] text-emerald-500'
                  : 'border-zinc-200 text-zinc-700'"
              >
                Agriculturist
              </button>
            </div>

            <div class="flex flex-wrap gap-3">
              <div v-if="currentUser.roles.includes('chemist')" class="relative">
                <FlaskConical class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-emerald-500" />
                <select
                  v-model="chemistTaskFilter"
                  class="h-11 w-56 rounded-lg border border-zinc-200 bg-white pl-10 pr-3 text-sm font-semibold text-zinc-900 outline-none focus:border-[#0E3D1A]"
                >
                  <option value="">Filter task</option>
                  <option v-for="task in chemistTasks" :key="task" :value="task">
                    {{ task }}
                  </option>
                </select>
              </div>

              <div v-if="currentUser.roles.includes('agriculturist')" class="relative">
                <Tractor class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-emerald-500" />
                <select
                  v-model="agriculturistTaskFilter"
                  class="h-11 w-56 rounded-lg border border-zinc-200 bg-white pl-10 pr-3 text-sm font-semibold text-zinc-900 outline-none focus:border-[#0E3D1A]"
                >
                  <option value="">Filter task</option>
                  <option v-for="task in agriculturistTasks" :key="task" :value="task">
                    {{ task }}
                  </option>
                </select>
              </div>

              <button
                type="button"
                @click="clearFilters"
                class="h-8 rounded-full border border-red-500 px-4 text-xs font-semibold text-red-500 transition hover:bg-red-500 hover:text-white"
              >
                Clear
              </button>
            </div>
          </div>

          <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-zinc-500">Available Tasks</p>
                <p class="mt-1 text-2xl font-bold text-zinc-900">
                  {{ availableRequests.length }}
                </p>
              </div>
              <div class="rounded-xl bg-blue-100 p-2.5">
                <ListTodo class="h-5 w-5 text-blue-600" />
              </div>
            </div>
          </div>
        </div>

        <div
          v-if="accessibleRequests.length === 0"
          class="rounded-2xl border border-zinc-200 bg-white px-6 py-16 text-center shadow-sm"
        >
          <h3 class="mb-2 text-lg font-semibold text-zinc-900">
            No Tasks Available
          </h3>
        </div>

        <div v-else class="space-y-6">
          <div v-if="roleFilter === 'all' || roleFilter === 'chemist'" class="rounded-2xl border border-zinc-200 bg-white shadow-sm">
            <div class="border-b border-zinc-200 px-6 py-4">
              <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#0E3D1A] text-white">
                  <FlaskConical class="h-5 w-5" />
                </div>

                <div>
                  <h2 class="font-semibold text-zinc-900">
                    Chemist Tasks
                  </h2>
                  <p class="text-sm text-zinc-500">
                    {{ chemistRequests.length }} task{{ chemistRequests.length !== 1 ? 's' : '' }}
                  </p>
                </div>
              </div>
            </div>

            <div class="grid gap-6 p-4 lg:grid-cols-2">
              <div class="space-y-4">
                <h3 class="font-semibold text-zinc-900">
                  Available Chemist Tasks
                </h3>

                <div v-if="chemistAvailableRequests.length === 0" class="rounded-xl border border-zinc-200 px-6 py-10 text-center text-sm text-zinc-500">
                  No ungrabbed chemist tasks available.
                </div>

                <div v-else class="space-y-4">
                  <div
                    v-for="(group, taskType) in groupedChemistAvailableRequests"
                    :key="taskType"
                    class="overflow-hidden rounded-xl border border-zinc-200"
                  >
                    <div class="border-b border-zinc-200 bg-zinc-50 px-4 py-3">
                      <h3 class="text-sm font-semibold text-zinc-900">
                        {{ taskType }}
                      </h3>
                    </div>

                    <div class="space-y-3 p-3">
                      <div
                        v-for="request in group"
                        :key="request.id"
                        class="rounded-xl border border-zinc-200 bg-white p-4 transition hover:border-[#0E3D1A] hover:shadow-sm"
                      >
                        <div class="flex items-center gap-3">
                          <div class="flex-1">
                            <p class="text-sm font-semibold text-zinc-900">
                              {{ getDisplayCode(request) }}
                            </p>

                            <p class="mt-0.5 text-xs text-zinc-500">
                              Due: {{ request.dueDate || '—' }}

                              <span
                                v-if="isMissed(request)"
                                class="ml-2 rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-bold uppercase text-red-700"
                              >
                                Missed
                              </span>
                            </p>
                          </div>

                          <button
                            type="button"
                            @click="handleGrabTask(request.id)"
                            class="inline-flex items-center gap-1 rounded-lg bg-[#0E3D1A] px-4 py-2 text-xs font-semibold text-white transition hover:opacity-90"
                          >
                            <Hand class="h-3.5 w-3.5" />
                            Grab
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="space-y-4">
                <h3 class="font-semibold text-zinc-900">
                  Grabbed Chemist Tasks
                </h3>

                <div v-if="chemistGrabbedRequests.length === 0" class="rounded-xl border border-zinc-200 px-6 py-10 text-center text-sm text-zinc-500">
                  No grabbed chemist tasks yet.
                </div>

                <div v-else class="space-y-4">
                  <div
                    v-for="(group, taskType) in groupedChemistGrabbedRequests"
                    :key="taskType"
                    class="overflow-hidden rounded-xl border border-zinc-200"
                  >
                    <div class="border-b border-zinc-200 bg-zinc-50 px-4 py-3">
                      <h3 class="text-sm font-semibold text-zinc-900">
                        {{ taskType }}
                      </h3>
                    </div>

                    <div class="space-y-3 p-3">
                      <div
                        v-for="request in group"
                        :key="request.id"
                        class="rounded-xl border border-zinc-200 bg-white p-4 transition hover:border-[#0E3D1A] hover:shadow-sm"
                      >
                        <div class="flex items-center gap-3">
                          <div class="flex-1">
                            <p class="text-sm font-semibold text-zinc-900">
                              {{ getDisplayCode(request) }}
                            </p>

                            <p class="mt-0.5 text-xs text-zinc-500">
                              Due: {{ request.dueDate || '—' }}
                            </p>

                            <p class="mt-1 text-xs font-medium text-amber-700">
                              Grabbed by: {{ request.assignedToName ?? 'Unknown User' }}
                            </p>
                          </div>

                          <button
                            type="button"
                            @click="handleGrabTask(request.id)"
                            class="inline-flex items-center gap-1 rounded-lg bg-[#0E3D1A] px-4 py-2 text-xs font-semibold text-white transition hover:opacity-90"
                          >
                            <Hand class="h-3.5 w-3.5" />
                            Reassign to Me
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div v-if="roleFilter === 'all' || roleFilter === 'agriculturist'" class="rounded-2xl border border-zinc-200 bg-white shadow-sm">
            <div class="border-b border-zinc-200 px-6 py-4">
              <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-600 text-white">
                  <Tractor class="h-5 w-5" />
                </div>

                <div>
                  <h2 class="font-semibold text-zinc-900">
                    Agriculturist Tasks
                  </h2>
                  <p class="text-sm text-zinc-500">
                    {{ agriculturistRequests.length }} task{{ agriculturistRequests.length !== 1 ? 's' : '' }}
                  </p>
                </div>
              </div>
            </div>

            <div class="grid gap-6 p-4 lg:grid-cols-2">
              <div class="space-y-4">
                <h3 class="font-semibold text-zinc-900">
                  Available Agriculturist Tasks
                </h3>

                <div v-if="agriculturistAvailableRequests.length === 0" class="rounded-xl border border-zinc-200 px-6 py-10 text-center text-sm text-zinc-500">
                  No ungrabbed agriculturist tasks available.
                </div>

                <div v-else class="space-y-4">
                  <div
                    v-for="(group, taskType) in groupedAgriculturistAvailableRequests"
                    :key="taskType"
                    class="overflow-hidden rounded-xl border border-zinc-200"
                  >
                    <div class="border-b border-zinc-200 bg-zinc-50 px-4 py-3">
                      <h3 class="text-sm font-semibold text-zinc-900">
                        {{ taskType }}
                      </h3>
                    </div>

                    <div class="space-y-3 p-3">
                      <div
                        v-for="request in group"
                        :key="request.id"
                        class="rounded-xl border border-zinc-200 bg-white p-4 transition hover:border-[#0E3D1A] hover:shadow-sm"
                      >
                        <div class="flex items-center gap-3">
                          <div class="flex-1">
                            <p class="text-sm font-semibold text-zinc-900">
                              {{ getDisplayCode(request) }}
                            </p>

                            <p class="mt-0.5 text-xs text-zinc-500">
                              Due: {{ request.dueDate || '—' }}

                              <span
                                v-if="isMissed(request)"
                                class="ml-2 rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-bold uppercase text-red-700"
                              >
                                Missed
                              </span>
                            </p>
                          </div>

                          <button
                            type="button"
                            @click="handleGrabTask(request.id)"
                            class="inline-flex items-center gap-1 rounded-lg bg-[#0E3D1A] px-4 py-2 text-xs font-semibold text-white transition hover:opacity-90"
                          >
                            <Hand class="h-3.5 w-3.5" />
                            Grab
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="space-y-4">
                <h3 class="font-semibold text-zinc-900">
                  Grabbed Agriculturist Tasks
                </h3>

                <div v-if="agriculturistGrabbedRequests.length === 0" class="rounded-xl border border-zinc-200 px-6 py-10 text-center text-sm text-zinc-500">
                  No grabbed agriculturist tasks yet.
                </div>

                <div v-else class="space-y-4">
                  <div
                    v-for="(group, taskType) in groupedAgriculturistGrabbedRequests"
                    :key="taskType"
                    class="overflow-hidden rounded-xl border border-zinc-200"
                  >
                    <div class="border-b border-zinc-200 bg-zinc-50 px-4 py-3">
                      <h3 class="text-sm font-semibold text-zinc-900">
                        {{ taskType }}
                      </h3>
                    </div>

                    <div class="space-y-3 p-3">
                      <div
                        v-for="request in group"
                        :key="request.id"
                        class="rounded-xl border border-zinc-200 bg-white p-4 transition hover:border-[#0E3D1A] hover:shadow-sm"
                      >
                        <div class="flex items-center gap-3">
                          <div class="flex-1">
                            <p class="text-sm font-semibold text-zinc-900">
                              {{ getDisplayCode(request) }}
                            </p>

                            <p class="mt-0.5 text-xs text-zinc-500">
                              Due: {{ request.dueDate || '—' }}
                            </p>

                            <p class="mt-1 text-xs font-medium text-amber-700">
                              Grabbed by: {{ request.assignedToName ?? 'Unknown User' }}
                            </p>
                          </div>

                          <button
                            type="button"
                            @click="handleGrabTask(request.id)"
                            class="inline-flex items-center gap-1 rounded-lg bg-[#0E3D1A] px-4 py-2 text-xs font-semibold text-white transition hover:opacity-90"
                          >
                            <Hand class="h-3.5 w-3.5" />
                            Reassign to Me
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppSidebarLayout>
</template>