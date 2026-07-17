<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue'
import AccountRoleEditModal from '@/Pages/admin/TaskManagement/AccountRoleEditModal.vue'
import ManagePresetsModal from '@/Pages/admin/TaskManagement/ManagePresetsModal.vue'
import { Head, router } from '@inertiajs/vue3'
import { computed, reactive, ref } from 'vue'
import type { BreadcrumbItem } from '@/types'
import {
  Check,
  FlaskConical,
  Pencil,
  Plus,
  Search,
  Star,
  Tractor,
  Trash2,
  X,
} from 'lucide-vue-next'

type MainCategory = 'admin' | 'chemist' | 'agriculturist'
type FilterCategory = 'all' | MainCategory

type LaboratoryTask = {
  id: number
  name: string
  task_category_id: number
}

type Preset = {
  id: number
  name: string
  description: string | null
  category: MainCategory
  task_category_id: number
  tasks: string[]
  task_ids: number[]
}

type AssignedTask = {
  id: number
  name: string
  category: MainCategory | null
  task_category_id: number
}

type TaskCategory = {
  id: number
  key: MainCategory
  name: string
  title: string
  laboratory_tasks: LaboratoryTask[]
  presets: Preset[]
}

type UserPreset = Preset

type UserRecord = {
  id: number
  surname: string
  givenname: string
  middlename: string | null
  suffix: string | null
  category: string
  tasks: string[]
  additionalCategories: MainCategory[]
  assigned_presets: UserPreset[]
  assigned_tasks: AssignedTask[]
}

const props = defineProps<{
  users: UserRecord[]
  taskCategories: TaskCategory[]
  presets: Preset[]
}>()

const ENDPOINTS = {
  userTaskPreset: (userId: number) => `/task-management/users/${userId}/task-presets`,
  bulkUserTaskPreset: '/task-management/users/task-presets',
  presetStore: '/task-management/presets',
  presetUpdate: (presetId: number) => `/task-management/presets/${presetId}`,
  presetDestroy: (presetId: number) => `/task-management/presets/${presetId}`,
} as const

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Task Management',
    href: '/task-management',
  },
]

const selectedFilter = ref<FilterCategory>('all')
const search = ref('')
const showSelectionWarning = ref(false)
const selectionWarningMessage = ref('')
const selectedUsers = reactive<Record<MainCategory, Set<number>>>({
  admin: new Set(),
  chemist: new Set(),
  agriculturist: new Set(),
})

const showAssignmentModal = ref(false)
const assignmentCategory = ref<MainCategory | null>(null)
const assignmentTargetUsers = ref<Array<{ id: number; name: string }>>([])
const assignmentAvailableCategories = ref<MainCategory[]>([])
const assignmentInitialCategory = ref<MainCategory | null>(null)
const assignmentInitialSelections = ref<
  Partial<Record<MainCategory, { presetIds: number[]; taskIds: number[] }>>
>({})

const showManagePresetsDialog = ref(false)
const managePresetsCategory = ref<MainCategory | null>(null)

const taskCategoryMap = computed<Record<MainCategory, TaskCategory | null>>(() => {
  const map: Record<MainCategory, TaskCategory | null> = {
    admin: null,
    chemist: null,
    agriculturist: null,
  }

  props.taskCategories.forEach((category) => {
    map[category.key] = category
  })

  return map
})

const presetCategories = computed(() =>
  props.taskCategories.filter((category) => category.key !== 'admin'),
)

const presetCategoriesByRole = computed<Record<MainCategory, Preset[]>>(() => ({
  admin: [],
  chemist: taskCategoryMap.value.chemist?.presets ?? [],
  agriculturist: taskCategoryMap.value.agriculturist?.presets ?? [],
}))

const visibleCategories = computed(() => {
  const categories: MainCategory[] = ['admin', 'chemist', 'agriculturist']

  if (selectedFilter.value === 'all') {
    return categories
  }

  return categories.filter((category) => category === selectedFilter.value)
})

const activeSelectedCategory = computed<MainCategory | null>(() => {
  for (const category of ['admin', 'chemist', 'agriculturist'] as MainCategory[]) {
    if (selectedUsers[category].size > 0) {
      return category
    }
  }

  return null
})

const totalSelectedAccounts = computed(() => {
  return (
    selectedUsers.admin.size +
    selectedUsers.chemist.size +
    selectedUsers.agriculturist.size
  )
})

const selectedRoleCategory = computed(() => {
  const category = activeSelectedCategory.value
  return category ? taskCategoryMap.value[category]?.title ?? category : null
})

function formatFullName(user: UserRecord) {
  return [
    user.surname,
    user.givenname,
    user.middlename,
  ]
    .filter((value) => Boolean(value && String(value).trim()))
    .join(', ')
}

function normalizeCategory(value: string | null | undefined): MainCategory | null {
  const raw = String(value ?? '').trim().toLowerCase()

  if (raw.includes('chemist')) return 'chemist'
  if (raw.includes('agriculturist')) return 'agriculturist'
  if (raw.includes('admin')) return 'admin'

  return null
}

function getMainCategory(user: UserRecord) {
  return normalizeCategory(user.category)
}

function isAdditionalUser(user: UserRecord, category: MainCategory) {
  const mainCategory = getMainCategory(user)

  return mainCategory !== null && mainCategory !== category
}

function getCategoryLabel(category: MainCategory) {
  return taskCategoryMap.value[category]?.title ?? category
}

function canManagePresets(category: MainCategory) {
  return category !== 'admin' && (taskCategoryMap.value[category]?.presets.length ?? 0) > 0
}

function getCategoryIcon(category: MainCategory) {
  if (category === 'admin') return Star
  if (category === 'chemist') return FlaskConical
  return Tractor
}

function getMainRoleIcon(mainCategory: MainCategory | null | undefined) {
  if (mainCategory === 'admin') return Star
  if (mainCategory === 'chemist') return FlaskConical
  return Tractor
}

function getAdditionalRoleLabel(user: UserRecord) {
  const mainCategory = getMainCategory(user)
  if (!mainCategory) return ''

  return getCategoryLabel(mainCategory)
}

function getSelectedMainCategory() {
  return activeSelectedCategory.value
}

function getEditableCategoriesForUser(user: UserRecord) {
  const mainCategory = getMainCategory(user)
  if (!mainCategory) return []

  return [mainCategory, ...user.additionalCategories].filter(
    (category, index, categories) => categories.indexOf(category) === index,
  )
}

function getCommonEditableCategories(users: UserRecord[]) {
  if (!users.length) return []

  return ['admin', 'chemist', 'agriculturist'].filter((category) =>
    users.every((user) => getEditableCategoriesForUser(user).includes(category as MainCategory)),
  ) as MainCategory[]
}

function isSelectableInCategory(user: UserRecord, category: MainCategory) {
  const mainCategory = getMainCategory(user)
  return mainCategory === category || user.additionalCategories.includes(category)
}

function matchesSearch(user: UserRecord) {
  const query = search.value.trim().toLowerCase()
  if (!query) return true

  return formatFullName(user).toLowerCase().includes(query)
}

function getVisibleUsers(category: MainCategory) {
  return props.users.filter(
    (user) => isSelectableInCategory(user, category) && matchesSearch(user),
  )
}

function getUsersWithTasks(category: MainCategory) {
  return getVisibleUsers(category).filter((user) => getDisplayedTaskGroups(user, category).length > 0)
}

function getUsersWithoutTasks(category: MainCategory) {
  return getVisibleUsers(category).filter((user) => getDisplayedTaskGroups(user, category).length === 0)
}

function getVisibleTasks(category: MainCategory) {
  return taskCategoryMap.value[category]?.laboratory_tasks ?? []
}

function getCategoryAccountCount(category: MainCategory) {
  return getVisibleUsers(category).length
}

function getSelectableUsersInList(category: MainCategory, users: UserRecord[]) {
  return users.filter((user) => isSelectableInCategory(user, category))
}

function getSelectedUsers(category: MainCategory) {
  return props.users.filter(
    (user) => selectedUsers[category].has(user.id) && isSelectableInCategory(user, category),
  )
}

function showProfessionalSelectionWarning() {
  selectionWarningMessage.value =
    'You can only select accounts from one role category at a time. Please clear the current selection first before selecting accounts from another category.'
  showSelectionWarning.value = true
}

function acknowledgeSelectionWarning() {
  showSelectionWarning.value = false
}

function toggleUserSelection(category: MainCategory, user: UserRecord) {
  if (!isSelectableInCategory(user, category)) {
    return
  }

  const currentSelectedMainCategory = getSelectedMainCategory()
  if (currentSelectedMainCategory && currentSelectedMainCategory !== category) {
    showProfessionalSelectionWarning()
    return
  }

  const next = new Set(selectedUsers[category])
  if (next.has(user.id)) {
    next.delete(user.id)
  } else {
    next.add(user.id)
  }
  selectedUsers[category] = next
}

function selectAllInList(category: MainCategory, users: UserRecord[]) {
  const currentSelectedMainCategory = getSelectedMainCategory()
  if (currentSelectedMainCategory && currentSelectedMainCategory !== category) {
    showProfessionalSelectionWarning()
    return
  }

  const next = new Set(selectedUsers[category])
  getSelectableUsersInList(category, users).forEach((user) => {
    next.add(user.id)
  })
  selectedUsers[category] = next
}

function clearAllInList(category: MainCategory, users: UserRecord[]) {
  const next = new Set(selectedUsers[category])
  users.forEach((user) => {
    next.delete(user.id)
  })
  selectedUsers[category] = next
}

function getUserIdsForCategory(category: MainCategory) {
  return Array.from(selectedUsers[category])
}

function getCommonPresetIds(users: UserRecord[]) {
  if (!users.length) return []

  const [firstUser, ...rest] = users
  const common = new Set(firstUser.assigned_presets.map((preset) => preset.id))

  rest.forEach((user) => {
    const userPresetIds = new Set(user.assigned_presets.map((preset) => preset.id))
    Array.from(common).forEach((presetId) => {
      if (!userPresetIds.has(presetId)) {
        common.delete(presetId)
      }
    })
  })

  return Array.from(common)
}

function getCommonPresetIdsForCategory(users: UserRecord[], category: MainCategory) {
  if (!users.length) return []

  const common = new Set(
    users[0].assigned_presets
      .filter((preset) => preset.category === category)
      .map((preset) => preset.id),
  )

  users.slice(1).forEach((user) => {
    const userPresetIds = new Set(
      user.assigned_presets.filter((preset) => preset.category === category).map((preset) => preset.id),
    )
    Array.from(common).forEach((presetId) => {
      if (!userPresetIds.has(presetId)) {
        common.delete(presetId)
      }
    })
  })

  return Array.from(common)
}

function getCommonTaskIds(users: UserRecord[], category: MainCategory) {
  if (!users.length) return []

  const categoryTasksByUser = users.map((user) =>
    user.assigned_tasks.filter((task) => task.category === category),
  )

  const [firstUserTasks, ...rest] = categoryTasksByUser
  const common = new Set(firstUserTasks.map((task) => task.id))

  rest.forEach((tasks) => {
    const taskIds = new Set(tasks.map((task) => task.id))
    Array.from(common).forEach((taskId) => {
      if (!taskIds.has(taskId)) {
        common.delete(taskId)
      }
    })
  })

  return Array.from(common)
}

function getCurrentSelectionsForUser(user: UserRecord, category: MainCategory) {
  return {
    presetIds: user.assigned_presets
      .filter((preset) => preset.category === category)
      .map((preset) => preset.id),
    taskIds: user.assigned_tasks
      .filter((task) => task.category === category)
      .map((task) => task.id),
  }
}

function buildInitialSelections(users: UserRecord[], categories: MainCategory[]) {
  const selections: Partial<Record<MainCategory, { presetIds: number[]; taskIds: number[] }>> = {}

  categories.forEach((category) => {
    if (!users.length) return

    if (users.length === 1) {
      selections[category] = getCurrentSelectionsForUser(users[0], category)
      return
    }

    selections[category] = {
      presetIds: getCommonPresetIdsForCategory(users, category),
      taskIds: getCommonTaskIds(users, category),
    }
  })

  return selections
}

function getPresetTaskIds(user: UserRecord, category: MainCategory) {
  return user.assigned_presets
    .filter((preset) => preset.category === category)
    .flatMap((preset) => preset.task_ids)
}

function getDisplayedTaskGroups(user: UserRecord, category: MainCategory) {
  const presetGroups = user.assigned_presets
    .filter((preset) => preset.category === category)
    .map((preset) => ({
      type: 'preset' as const,
      id: preset.id,
      name: preset.name,
      tasks: preset.tasks,
    }))

  const presetTaskIds = new Set(getPresetTaskIds(user, category))
  const individualTasks = user.assigned_tasks
    .filter((task) => task.category === category && !presetTaskIds.has(task.id))
    .map((task) => ({
      type: 'task' as const,
      id: task.id,
      name: task.name,
    }))

  return [...presetGroups, ...individualTasks]
}

function openAssignmentModal(category: MainCategory, users: UserRecord[]) {
  if (!users.length) return

  assignmentCategory.value = category
  assignmentTargetUsers.value = users.map((user) => ({
    id: user.id,
    name: formatFullName(user),
  }))
  assignmentAvailableCategories.value =
    users.length === 1
      ? getEditableCategoriesForUser(users[0])
      : getCommonEditableCategories(users)
  assignmentInitialCategory.value = category
  assignmentInitialSelections.value = buildInitialSelections(users, assignmentAvailableCategories.value)
  showAssignmentModal.value = true
}

function openAssignmentForSelected(category: MainCategory) {
  openAssignmentModal(category, getSelectedUsers(category))
}

function openAssignmentForUser(category: MainCategory, user: UserRecord) {
  openAssignmentModal(category, [user])
}

function closeAssignmentModal() {
  showAssignmentModal.value = false
  assignmentCategory.value = null
  assignmentTargetUsers.value = []
  assignmentAvailableCategories.value = []
  assignmentInitialCategory.value = null
  assignmentInitialSelections.value = {}
}

function syncTasksToUsers(userIds: number[], presetIds: number[], taskIds: number[]) {
  if (!userIds.length) return

  if (userIds.length === 1) {
    router.put(
      ENDPOINTS.userTaskPreset(userIds[0]),
      { task_presets: presetIds, task_ids: taskIds },
      {
        preserveScroll: true,
        onSuccess: closeAssignmentModal,
      },
    )
    return
  }

  router.put(
    ENDPOINTS.bulkUserTaskPreset,
    { user_ids: userIds, task_presets: presetIds, task_ids: taskIds },
    {
      preserveScroll: true,
      onSuccess: closeAssignmentModal,
    },
  )
}

function handleAssignmentSave(
  payload: Partial<Record<MainCategory, { presetIds: number[]; taskIds: number[] }>>,
) {
  const presetIds = new Set<number>()
  const taskIds = new Set<number>()

  Object.values(payload).forEach((selection) => {
    if (!selection) return

    selection.presetIds.forEach((id) => presetIds.add(id))
    selection.taskIds.forEach((id) => taskIds.add(id))
  })

  syncTasksToUsers(
    assignmentTargetUsers.value.map((user) => user.id),
    Array.from(presetIds),
    Array.from(taskIds),
  )
}

function clearPresetsForSelected(category: MainCategory) {
  const userIds = getUserIdsForCategory(category)
  if (!userIds.length) return

  router.put(
    ENDPOINTS.bulkUserTaskPreset,
    {
      user_ids: userIds,
      task_presets: [],
      task_ids: [],
    },
    {
      preserveScroll: true,
    },
  )
}

function removePresetFromUser(user: UserRecord, presetId: number, category: MainCategory) {
  const remainingPresetIds = user.assigned_presets
    .filter((preset) => preset.category === category && preset.id !== presetId)
    .map((preset) => preset.id)

  router.put(
    ENDPOINTS.userTaskPreset(user.id),
    {
      task_presets: remainingPresetIds,
      task_ids: user.assigned_tasks
        .filter((task) => task.category === category)
        .map((task) => task.id),
    },
    {
      preserveScroll: true,
    },
  )
}

function removeTaskFromUser(user: UserRecord, taskId: number, category: MainCategory) {
  const remainingTaskIds = user.assigned_tasks
    .filter((task) => task.category === category && task.id !== taskId)
    .map((task) => task.id)

  router.put(
    ENDPOINTS.userTaskPreset(user.id),
    {
      task_presets: user.assigned_presets
        .filter((preset) => preset.category === category)
        .map((preset) => preset.id),
      task_ids: remainingTaskIds,
    },
    {
      preserveScroll: true,
    },
  )
}

function openTopManagePresets() {
  const selectedCategory =
    selectedFilter.value !== 'all' && selectedFilter.value !== 'admin'
      ? selectedFilter.value
      : null

  const activeCategory =
    activeSelectedCategory.value && activeSelectedCategory.value !== 'admin'
      ? activeSelectedCategory.value
      : null

  const firstAllowedCategory =
    selectedCategory ?? activeCategory ?? presetCategories.value[0]?.key ?? null

  if (!firstAllowedCategory) {
    return
  }

  managePresetsCategory.value = firstAllowedCategory
  showManagePresetsDialog.value = true
}

function closeManagePresets() {
  showManagePresetsDialog.value = false
  managePresetsCategory.value = null
}

function handlePresetSave(payload: {
  presetId?: number
  task_category_id: number
  name: string
  description: string
  laboratory_tasks: number[]
}) {
  if (payload.presetId) {
    router.put(
      ENDPOINTS.presetUpdate(payload.presetId),
      {
        task_category_id: payload.task_category_id,
        name: payload.name,
        description: payload.description,
        laboratory_tasks: payload.laboratory_tasks,
      },
      {
        preserveScroll: true,
        onSuccess: closeManagePresets,
      },
    )
    return
  }

  router.post(
    ENDPOINTS.presetStore,
    {
      task_category_id: payload.task_category_id,
      name: payload.name,
      description: payload.description,
      laboratory_tasks: payload.laboratory_tasks,
    },
    {
      preserveScroll: true,
      onSuccess: closeManagePresets,
    },
  )
}

function handlePresetDelete(presetId: number) {
  router.delete(ENDPOINTS.presetDestroy(presetId), {
    preserveScroll: true,
    onSuccess: closeManagePresets,
  })
}
</script>

<template>
  <Head title="Task Management" />

  <AppSidebarLayout :breadcrumbs="breadcrumbs">
    <div class="relative p-2 sm:p-3 md:p-4 lg:p-5">
      <div class="flex flex-col gap-3 sm:gap-4">
        <div class="sticky top-0 z-30 -mx-2 -mt-2 border-b border-zinc-200 bg-[#f3f4f6] px-2 pb-3 pt-2 sm:-mx-3 sm:-mt-3 sm:px-3 md:-mx-4 md:-mt-4 md:px-4 md:pt-4">
          <div class="flex flex-col gap-3">
            <div class="flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
              <div>
                <h1 class="text-lg font-semibold text-zinc-900 sm:text-xl">
                  Tasks List
                </h1>
                <p class="mt-1 text-xs text-zinc-500">
                  Assign and manage tasks across different user roles and categories
                </p>
              </div>

              <div class="flex flex-wrap gap-2">
                <button
                  v-if="selectedFilter !== 'admin'"
                  type="button"
                  @click="openTopManagePresets"
                  class="inline-flex h-8 items-center gap-2 rounded-lg bg-[#0E3D1A] px-3 text-xs font-semibold text-white transition hover:opacity-90 sm:h-9"
                >
                  <Pencil class="h-4 w-4" />
                  Pre-sets
                </button>

                <button
                  type="button"
                  :disabled="!activeSelectedCategory || totalSelectedAccounts === 0"
                  @click="activeSelectedCategory && openAssignmentForSelected(activeSelectedCategory)"
                  class="inline-flex h-8 items-center gap-2 rounded-lg bg-[#020826] px-3 text-xs font-semibold text-white transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50 sm:h-9"
                >
                  <Plus class="h-4 w-4" />
                  Role Management
                </button>

                <button
                  type="button"
                  :disabled="!activeSelectedCategory || totalSelectedAccounts === 0"
                  @click="activeSelectedCategory && openAssignmentForSelected(activeSelectedCategory)"
                  class="inline-flex h-8 items-center gap-2 rounded-lg bg-rose-600 px-3 text-xs font-semibold text-white transition hover:bg-rose-700 disabled:cursor-not-allowed disabled:opacity-50 sm:h-9"
                >
                  <Trash2 class="h-4 w-4" />
                  Remove Task
                </button>
              </div>
            </div>

            <div class="rounded-xl border border-zinc-200 bg-white p-2.5 shadow-sm sm:p-3">
              <div class="grid gap-2 md:grid-cols-3">
                <div class="relative md:col-span-2">
                  <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-400" />
                  <input
                    v-model="search"
                    type="text"
                    placeholder="Search account name"
                    class="h-9 w-full rounded-lg border border-zinc-200 bg-white pl-10 pr-3 text-xs text-zinc-900 outline-none ring-0 placeholder:text-zinc-400 focus:border-zinc-400"
                  />
                </div>

                <select
                  v-model="selectedFilter"
                  class="h-9 rounded-lg border border-zinc-200 bg-white px-3 text-xs text-zinc-900 outline-none focus:border-zinc-400"
                >
                  <option value="all">All Roles (Main Task)</option>
                  <option value="admin">Admin</option>
                  <option value="chemist">Chemist</option>
                  <option value="agriculturist">Agriculturist</option>
                </select>
              </div>
            </div>

            <div
              v-if="selectedRoleCategory"
              class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800"
            >
              Selected role category:
              <span class="font-semibold">{{ selectedRoleCategory }}</span>
            </div>
          </div>
        </div>

        <div class="flex flex-col gap-3">
          <template v-for="category in visibleCategories" :key="category">
            <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm">
              <div class="border-b border-zinc-200 px-3 py-2.5">
                <div class="flex items-center gap-3">
                  <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#0E3D1A] text-white">
                    <component :is="getCategoryIcon(category)" class="h-4 w-4" />
                  </div>

                  <div>
                    <h2 class="text-sm font-semibold tracking-wide text-zinc-900 sm:text-base">
                      {{ getCategoryLabel(category).toUpperCase() }}
                    </h2>
                    <p class="text-xs text-zinc-500">
                      {{ getCategoryAccountCount(category) }} account<span v-if="getCategoryAccountCount(category) !== 1">s</span>
                    </p>
                  </div>
                </div>
              </div>

              <div class="p-2.5 sm:p-3">
                <div class="flex flex-col gap-3">
                  <template v-if="getUsersWithTasks(category).length > 0">
                    <div class="flex flex-col gap-2.5">
                      <div class="flex items-center justify-between gap-2 px-1">
                        <h4 class="text-xs font-medium text-zinc-500">
                          {{ getCategoryLabel(category) }} List
                        </h4>

                        <div class="flex shrink-0 gap-2">
                          <button
                            type="button"
                            @click="selectAllInList(category, getUsersWithTasks(category))"
                            :disabled="getSelectableUsersInList(category, getUsersWithTasks(category)).length === 0 || getSelectableUsersInList(category, getUsersWithTasks(category)).every((user) => selectedUsers[category].has(user.id))"
                            class="inline-flex h-7 items-center rounded-lg bg-[#0E3D1A] px-2.5 text-[11px] font-medium text-white transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50"
                          >
                            Select All
                          </button>

                          <button
                            type="button"
                            @click="clearAllInList(category, getUsersWithTasks(category))"
                            :disabled="getSelectableUsersInList(category, getUsersWithTasks(category)).every((user) => !selectedUsers[category].has(user.id))"
                            class="inline-flex h-7 items-center rounded-lg bg-rose-600 px-2.5 text-[11px] font-medium text-white transition hover:bg-rose-700 disabled:cursor-not-allowed disabled:opacity-50"
                          >
                            Clear All
                          </button>
                        </div>
                      </div>

                      <div class="overflow-x-auto rounded-xl border border-zinc-200 no-scrollbar">
                        <table class="min-w-[680px] w-full table-fixed text-[12px] sm:min-w-[760px] sm:text-[13px] lg:min-w-full">
                          <colgroup>
                            <col class="w-[40px]" />
                            <col class="w-[30%]" />
                            <col class="w-[48%]" />
                            <col class="w-[22%]" />
                          </colgroup>

                          <thead>
                            <tr class="bg-zinc-50">
                              <th class="px-2 py-2 text-left sm:px-3 sm:py-2.5"></th>
                              <th class="px-2 py-2 text-left text-xs font-medium text-zinc-700 sm:px-3 sm:py-2.5">
                                Name
                              </th>
                              <th class="px-2 py-2 text-left text-xs font-medium text-zinc-700 sm:px-3 sm:py-2.5">
                                Current Task
                              </th>
                              <th class="px-2 py-2 text-center text-xs font-medium text-zinc-700 sm:px-3 sm:py-2.5">
                                Action
                              </th>
                            </tr>
                          </thead>

                          <tbody>
                            <tr
                              v-for="user in getUsersWithTasks(category)"
                              :key="user.id"
                              class="border-t border-zinc-200"
                              :class="selectedUsers[category].has(user.id) ? 'bg-zinc-50/70' : ''"
                            >
                              <td class="px-2 py-2 align-top sm:px-3 sm:py-2.5">
                                <button
                                  type="button"
                                  :disabled="!isSelectableInCategory(user, category)"
                                  @click="toggleUserSelection(category, user)"
                                  class="inline-flex h-4 w-4 items-center justify-center rounded border transition disabled:cursor-not-allowed disabled:opacity-40"
                                  :class="
                                    selectedUsers[category].has(user.id)
                                      ? 'border-blue-600 bg-blue-600 text-white'
                                      : 'border-zinc-300 bg-white text-transparent'
                                  "
                                >
                                  <Check class="h-3 w-3" />
                                </button>
                              </td>

                              <td class="px-2 py-2 align-top sm:px-3 sm:py-2.5">
                                <div class="flex items-start gap-2">
                                  <div class="flex h-6 w-6 shrink-0 items-center justify-center">
                                    <template v-if="isAdditionalUser(user, category) && getMainCategory(user)">
                                      <div
                                        class="flex h-6 w-6 items-center justify-center rounded-md bg-[#0E3D1A] text-white"
                                      >
                                        <component
                                          :is="getMainRoleIcon(getMainCategory(user))"
                                          class="h-3.5 w-3.5"
                                        />
                                      </div>
                                    </template>
                                  </div>

                                  <div class="min-w-0">
                                    <div class="break-words text-[12px] font-medium leading-5 text-zinc-900 sm:text-[13px]">
                                      {{ formatFullName(user) }}
                                    </div>

                                    <span
                                      v-if="isAdditionalUser(user, category) && getMainCategory(user)"
                                      class="mt-1 inline-flex rounded-full border border-zinc-200 bg-zinc-50 px-2 py-0.5 text-[10px] font-medium text-zinc-700"
                                    >
                                      {{ getAdditionalRoleLabel(user) }}
                                    </span>
                                  </div>
                                </div>
                              </td>

                              <td class="px-2 py-2 align-top sm:px-3 sm:py-2.5">
                                <div class="flex flex-wrap gap-1.5">
                                  <template
                                    v-for="group in getDisplayedTaskGroups(user, category)"
                                    :key="`${user.id}-${group.type}-${group.id}`"
                                  >
                                    <div
                                      v-if="group.type === 'preset'"
                                      class="inline-flex max-w-full flex-col gap-1 rounded-lg border border-zinc-200 bg-zinc-50 px-2 py-1.5"
                                    >
                                      <div class="flex items-center justify-between gap-2">
                                        <span class="text-[11px] font-semibold text-zinc-900">
                                          ({{ group.name }})
                                        </span>

                                        <button
                                          type="button"
                                          @click="removePresetFromUser(user, group.id, category)"
                                          class="rounded p-0.5 text-zinc-500 transition hover:bg-rose-50 hover:text-rose-600"
                                        >
                                          <X class="h-3 w-3" />
                                        </button>
                                      </div>

                                      <div class="flex flex-wrap gap-1">
                                        <span
                                          v-for="task in group.tasks"
                                          :key="`${user.id}-${group.name}-${task}`"
                                          class="inline-flex rounded-full bg-[#020826] px-2 py-0.5 text-[10px] font-semibold text-white"
                                        >
                                          {{ task }}
                                        </span>
                                      </div>
                                    </div>

                                    <span
                                      v-else
                                      class="inline-flex items-center gap-1 rounded-full bg-[#020826] px-2 py-0.5 text-[10px] font-semibold text-white"
                                    >
                                      {{ group.name }}

                                    </span>
                                  </template>

                                  <span
                                    v-if="getDisplayedTaskGroups(user, category).length === 0"
                                    class="text-[11px] text-zinc-400"
                                  >
                                    -
                                  </span>
                                </div>
                              </td>

                              <td class="px-2 py-2 align-top sm:px-3 sm:py-2.5">
                                <button
                                  type="button"
                                  @click="openAssignmentForUser(category, user)"
                                  class="inline-flex h-7 items-center gap-1.5 rounded-lg border border-zinc-200 bg-white px-2.5 text-[11px] font-medium text-zinc-700 transition hover:bg-zinc-50"
                                >
                                  <Pencil class="h-3 w-3" />
                                  Edit
                                </button>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </template>

                  <template v-if="getUsersWithoutTasks(category).length > 0">
                    <div class="flex flex-col gap-2.5">
                      <div class="flex items-center justify-between gap-2 px-1">
                        <h4 class="text-xs font-medium text-zinc-500">
                          No Task Assigned
                        </h4>

                        <div class="flex shrink-0 gap-2">
                          <button
                            type="button"
                            @click="selectAllInList(category, getUsersWithoutTasks(category))"
                            :disabled="getSelectableUsersInList(category, getUsersWithoutTasks(category)).length === 0 || getSelectableUsersInList(category, getUsersWithoutTasks(category)).every((user) => selectedUsers[category].has(user.id))"
                            class="inline-flex h-7 items-center rounded-lg bg-[#0E3D1A] px-2.5 text-[11px] font-medium text-white transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50"
                          >
                            Select All
                          </button>

                          <button
                            type="button"
                            @click="clearAllInList(category, getUsersWithoutTasks(category))"
                            :disabled="getSelectableUsersInList(category, getUsersWithoutTasks(category)).every((user) => !selectedUsers[category].has(user.id))"
                            class="inline-flex h-7 items-center rounded-lg bg-rose-600 px-2.5 text-[11px] font-medium text-white transition hover:bg-rose-700 disabled:cursor-not-allowed disabled:opacity-50"
                          >
                            Clear All
                          </button>
                        </div>
                      </div>

                      <div class="overflow-x-auto rounded-xl border border-zinc-200 no-scrollbar">
                        <table class="min-w-[680px] w-full table-fixed text-[12px] sm:min-w-[760px] sm:text-[13px] lg:min-w-full">
                          <colgroup>
                            <col class="w-[40px]" />
                            <col class="w-[30%]" />
                            <col class="w-[48%]" />
                            <col class="w-[22%]" />
                          </colgroup>

                          <thead>
                            <tr class="bg-zinc-50">
                              <th class="px-2 py-2 text-left sm:px-3 sm:py-2.5"></th>
                              <th class="px-2 py-2 text-left text-xs font-medium text-zinc-700 sm:px-3 sm:py-2.5">
                                Name
                              </th>
                              <th class="px-2 py-2 text-left text-xs font-medium text-zinc-700 sm:px-3 sm:py-2.5">
                                Current Task
                              </th>
                              <th class="px-2 py-2 text-center text-xs font-medium text-zinc-700 sm:px-3 sm:py-2.5">
                                Action
                              </th>
                            </tr>
                          </thead>

                          <tbody>
                            <tr
                              v-for="user in getUsersWithoutTasks(category)"
                              :key="user.id"
                              class="border-t border-zinc-200"
                              :class="selectedUsers[category].has(user.id) ? 'bg-zinc-50/70' : ''"
                            >
                              <td class="px-2 py-2 align-top sm:px-3 sm:py-2.5">
                                <button
                                  type="button"
                                  :disabled="!isSelectableInCategory(user, category)"
                                  @click="toggleUserSelection(category, user)"
                                  class="inline-flex h-4 w-4 items-center justify-center rounded border transition disabled:cursor-not-allowed disabled:opacity-40"
                                  :class="
                                    selectedUsers[category].has(user.id)
                                      ? 'border-blue-600 bg-blue-600 text-white'
                                      : 'border-zinc-300 bg-white text-transparent'
                                  "
                                >
                                  <Check class="h-3 w-3" />
                                </button>
                              </td>

                              <td class="px-2 py-2 align-top sm:px-3 sm:py-2.5">
                                <div class="min-w-0">
                                  <div class="break-words text-[12px] font-medium leading-5 text-zinc-900 sm:text-[13px]">
                                    {{ formatFullName(user) }}
                                  </div>
                                </div>
                              </td>

                              <td class="px-2 py-2 align-top text-zinc-400 sm:px-3 sm:py-2.5">
                                -
                              </td>

                              <td class="px-2 py-2 align-top text-center sm:px-3 sm:py-2.5">
                                <button
                                  type="button"
                                  @click="openAssignmentForUser(category, user)"
                                  class="inline-flex h-7 items-center gap-1.5 rounded-lg border border-zinc-200 bg-white px-2.5 text-[11px] font-medium text-zinc-700 transition hover:bg-zinc-50"
                                >
                                  <Pencil class="h-3 w-3" />
                                  Edit
                                </button>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </template>

                  <template v-if="getUsersWithTasks(category).length === 0 && getUsersWithoutTasks(category).length === 0">
                    <div class="rounded-xl border border-dashed border-zinc-200 px-4 py-6 text-sm text-zinc-500">
                      No accounts found for this role.
                    </div>
                  </template>
                </div>
              </div>
            </div>
          </template>
        </div>
      </div>
    </div>

    <AccountRoleEditModal
      :open="showAssignmentModal"
      :target-users="assignmentTargetUsers"
      :available-categories="assignmentAvailableCategories"
      :initial-category="assignmentInitialCategory"
      :task-categories="taskCategoryMap"
      :presets="presetCategoriesByRole"
      :initial-selections="assignmentInitialSelections"
      @close="closeAssignmentModal"
      @save="handleAssignmentSave"
    />

    <ManagePresetsModal
      :open="showManagePresetsDialog"
      :categories="presetCategories"
      :initial-category="managePresetsCategory"
      @close="closeManagePresets"
      @save="handlePresetSave"
      @delete="handlePresetDelete"
    />

    <div
      v-if="showSelectionWarning"
      class="fixed inset-0 z-[70] flex items-center justify-center bg-black/50 px-4"
    >
      <div class="w-full max-w-md rounded-xl border border-zinc-200 bg-white shadow-xl">
        <div class="flex items-start gap-3 border-b border-zinc-200 px-5 py-4">
          <div class="mt-0.5 flex h-9 w-9 items-center justify-center rounded-full bg-amber-100 text-amber-600">
            <Check class="h-4 w-4" />
          </div>

          <div>
            <h2 class="text-base font-semibold text-zinc-900">
              Selection Restricted
            </h2>
            <p class="mt-1 text-sm text-zinc-500">
              Please review the selection rule below.
            </p>
          </div>
        </div>

        <div class="px-5 py-4">
          <p class="text-sm leading-6 text-zinc-700">
            {{ selectionWarningMessage }}
          </p>
        </div>

        <div class="flex justify-end border-t border-zinc-200 px-5 py-4">
          <button
            type="button"
            @click="acknowledgeSelectionWarning"
            class="rounded-lg bg-[#16a34a] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#15803d]"
          >
            Okay
          </button>
        </div>
      </div>
    </div>
  </AppSidebarLayout>
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
