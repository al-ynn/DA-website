<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { Pencil, Plus, Save, Trash2, X } from 'lucide-vue-next'

type MainCategory = 'admin' | 'chemist' | 'agriculturist'

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

type TaskCategory = {
  id: number
  key: MainCategory
  name: string
  title: string
  laboratory_tasks: LaboratoryTask[]
  presets: Preset[]
}

const props = defineProps<{
  open: boolean
  categories: TaskCategory[]
  initialCategory: MainCategory | null
}>()

const emit = defineEmits<{
  (e: 'close'): void
  (
    e: 'save',
    payload: {
      presetId?: number
      task_category_id: number
      name: string
      description: string
      laboratory_tasks: number[]
    },
  ): void
  (e: 'delete', presetId: number): void
}>()

const activeCategory = ref<MainCategory | null>(null)
const editingPresetId = ref<number | null>(null)
const isAddingNewPreset = ref(false)
const presetFormName = ref('')
const presetFormDescription = ref('')
const selectedTaskIds = ref<Set<number>>(new Set())

const activeCategoryRecord = computed(() =>
  props.categories.find((category) => category.key === activeCategory.value) ?? null,
)

const activeLaboratoryTasks = computed(() => activeCategoryRecord.value?.laboratory_tasks ?? [])
watch(
  () => props.open,
  (value) => {
    if (value) {
      activeCategory.value = props.initialCategory ?? props.categories[0]?.key ?? null
      resetForm()
    }
  },
  { immediate: true },
)

watch(
  () => props.initialCategory,
  (value) => {
    if (props.open && value) {
      activeCategory.value = value
      resetForm()
    }
  },
)

function resetForm() {
  editingPresetId.value = null
  isAddingNewPreset.value = false
  presetFormName.value = ''
  presetFormDescription.value = ''
  selectedTaskIds.value = new Set()
}

function startAdd(category: MainCategory) {
  activeCategory.value = category
  editingPresetId.value = null
  isAddingNewPreset.value = true
  presetFormName.value = ''
  presetFormDescription.value = ''
  selectedTaskIds.value = new Set()
}

function startEdit(preset: Preset) {
  activeCategory.value = preset.category
  editingPresetId.value = preset.id
  isAddingNewPreset.value = false
  presetFormName.value = preset.name
  presetFormDescription.value = preset.description ?? ''
  selectedTaskIds.value = new Set(preset.task_ids)
}

function cancelEdit() {
  resetForm()
}

function toggleTask(taskId: number) {
  const next = new Set(selectedTaskIds.value)

  if (next.has(taskId)) {
    next.delete(taskId)
  } else {
    next.add(taskId)
  }

  selectedTaskIds.value = next
}

function selectAllTasks() {
  selectedTaskIds.value = new Set(activeLaboratoryTasks.value.map((task) => task.id))
}

function clearAllTasks() {
  selectedTaskIds.value = new Set()
}

function savePreset() {
  if (!activeCategoryRecord.value || !presetFormName.value.trim()) {
    return
  }

  emit('save', {
    presetId: editingPresetId.value ?? undefined,
    task_category_id: activeCategoryRecord.value.id,
    name: presetFormName.value.trim(),
    description: presetFormDescription.value.trim(),
    laboratory_tasks: Array.from(selectedTaskIds.value),
  })
}
</script>

<template>
  <div
    v-if="open"
    class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50 px-4 py-6"
  >
    <div class="flex max-h-[90vh] w-full max-w-5xl flex-col overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-xl">
      <div class="flex items-start justify-between border-b border-zinc-200 px-6 py-5">
        <div>
          <h2 class="text-2xl font-semibold text-zinc-900">
            Manage Presets
          </h2>
          <p class="mt-1 text-sm text-zinc-500">
            Presets are stored in the backend and grouped by task category.
          </p>
        </div>

        <button
          type="button"
          @click="emit('close')"
          class="rounded-lg p-2 text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-900"
        >
          <X class="h-5 w-5" />
        </button>
      </div>

      <div class="flex-1 overflow-y-auto px-6 py-5">
        <div class="grid gap-6 lg:grid-cols-2">
          <div
            v-for="category in categories"
            :key="category.key"
            class="flex flex-col gap-4 rounded-2xl border border-zinc-200 p-4"
          >
            <div class="border-b border-zinc-200 pb-3">
              <div class="flex items-center justify-between gap-2">
                <div>
                  <h3 class="text-xl font-semibold text-zinc-900">
                    {{ category.title }}
                  </h3>
                  <p class="mt-1 text-sm text-zinc-500">
                    {{ category.laboratory_tasks.length }} backend tasks available
                  </p>
                </div>

                <button
                  type="button"
                  @click="startAdd(category.key)"
                  class="inline-flex items-center gap-2 rounded-xl border border-zinc-200 bg-white px-4 py-2 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-50"
                >
                  <Plus class="h-4 w-4" />
                  Add New
                </button>
              </div>
            </div>

            <div
              v-for="preset in category.presets"
              :key="preset.id"
              class="rounded-2xl border border-zinc-200 p-4"
            >
              <template v-if="editingPresetId === preset.id">
                <div class="flex flex-col gap-3">
                  <input
                    v-model="presetFormName"
                    type="text"
                    placeholder="Preset name"
                    class="h-11 w-full rounded-lg border border-zinc-200 px-3 text-sm outline-none focus:border-zinc-400"
                  />

                  <input
                    v-model="presetFormDescription"
                    type="text"
                    placeholder="Description"
                    class="h-11 w-full rounded-lg border border-zinc-200 px-3 text-sm outline-none focus:border-zinc-400"
                  />

                  <div class="flex flex-col gap-2">
                    <p class="text-sm font-medium text-zinc-700">
                      Select tasks:
                    </p>

                    <div class="grid max-h-48 gap-2 overflow-y-auto rounded-xl border border-zinc-200 p-3 sm:grid-cols-2">
                      <label
                        v-for="task in activeLaboratoryTasks"
                        :key="task.id"
                        class="flex cursor-pointer items-center gap-2 rounded-lg border border-zinc-200 px-3 py-2"
                      >
                        <input
                          type="checkbox"
                          class="h-4 w-4 rounded border-zinc-300 text-black focus:ring-0"
                          :checked="selectedTaskIds.has(task.id)"
                          @change="toggleTask(task.id)"
                        />
                        <span class="text-sm text-zinc-900">
                          {{ task.name }}
                        </span>
                      </label>
                    </div>
                  </div>

                  <div class="flex gap-2">
                    <button
                      type="button"
                      @click="selectAllTasks"
                      class="rounded-lg px-3 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100"
                    >
                      Select All
                    </button>

                    <button
                      type="button"
                      @click="clearAllTasks"
                      class="rounded-lg px-3 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100"
                    >
                      Clear All
                    </button>
                  </div>

                  <div class="flex gap-2">
                    <button
                      type="button"
                      @click="savePreset"
                      class="inline-flex items-center gap-2 rounded-lg bg-[#16a34a] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#15803d]"
                    >
                      <Save class="h-4 w-4" />
                      Save
                    </button>

                    <button
                      type="button"
                      @click="cancelEdit"
                      class="rounded-lg border border-zinc-200 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50"
                    >
                      Cancel
                    </button>
                  </div>
                </div>
              </template>

              <template v-else>
                <div class="flex items-start justify-between gap-4">
                  <div class="min-w-0">
                    <h4 class="font-semibold text-zinc-900">
                      {{ preset.name }}
                    </h4>

                    <p class="mt-1 text-sm text-zinc-500">
                      {{ preset.description || 'No description provided' }}
                    </p>

                    <div class="mt-3 flex flex-wrap gap-1.5">
                      <span
                        v-for="task in preset.tasks"
                        :key="task"
                        class="inline-flex rounded-full border border-zinc-200 bg-zinc-50 px-2.5 py-1 text-xs font-medium text-zinc-700"
                      >
                        {{ task }}
                      </span>
                    </div>
                  </div>

                  <div class="flex shrink-0 gap-2">
                    <button
                      type="button"
                      @click="startEdit(preset)"
                      class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-zinc-200 text-zinc-700 transition hover:bg-zinc-50"
                    >
                      <Pencil class="h-4 w-4" />
                    </button>

                    <button
                      type="button"
                      @click="emit('delete', preset.id)"
                      class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-rose-200 text-rose-600 transition hover:bg-rose-50"
                    >
                      <Trash2 class="h-4 w-4" />
                    </button>
                  </div>
                </div>
              </template>
            </div>

            <template v-if="isAddingNewPreset && activeCategory === category.key">
              <div class="rounded-2xl border border-zinc-200 p-4">
                <div class="flex flex-col gap-3">
                  <input
                    v-model="presetFormName"
                    type="text"
                    placeholder="Preset name"
                    class="h-11 w-full rounded-lg border border-zinc-200 px-3 text-sm outline-none focus:border-zinc-400"
                  />

                  <input
                    v-model="presetFormDescription"
                    type="text"
                    placeholder="Description"
                    class="h-11 w-full rounded-lg border border-zinc-200 px-3 text-sm outline-none focus:border-zinc-400"
                  />

                  <div class="flex flex-col gap-2">
                    <p class="text-sm font-medium text-zinc-700">
                      Select tasks:
                    </p>

                    <div class="grid max-h-48 gap-2 overflow-y-auto rounded-xl border border-zinc-200 p-3 sm:grid-cols-2">
                      <label
                        v-for="task in activeLaboratoryTasks"
                        :key="task.id"
                        class="flex cursor-pointer items-center gap-2 rounded-lg border border-zinc-200 px-3 py-2"
                      >
                        <input
                          type="checkbox"
                          class="h-4 w-4 rounded border-zinc-300 text-black focus:ring-0"
                          :checked="selectedTaskIds.has(task.id)"
                          @change="toggleTask(task.id)"
                        />
                        <span class="text-sm text-zinc-900">
                          {{ task.name }}
                        </span>
                      </label>
                    </div>
                  </div>

                  <div class="flex gap-2">
                    <button
                      type="button"
                      @click="selectAllTasks"
                      class="rounded-lg px-3 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100"
                    >
                      Select All
                    </button>

                    <button
                      type="button"
                      @click="clearAllTasks"
                      class="rounded-lg px-3 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100"
                    >
                      Clear All
                    </button>
                  </div>

                  <div class="flex gap-2">
                    <button
                      type="button"
                      @click="savePreset"
                      class="inline-flex items-center gap-2 rounded-lg bg-[#16a34a] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#15803d]"
                    >
                      <Save class="h-4 w-4" />
                      Save
                    </button>

                    <button
                      type="button"
                      @click="cancelEdit"
                      class="rounded-lg border border-zinc-200 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50"
                    >
                      Cancel
                    </button>
                  </div>
                </div>
              </div>
            </template>
          </div>
        </div>
      </div>

      <div class="flex justify-end border-t border-zinc-200 px-6 py-5">
        <button
          type="button"
          @click="emit('close')"
          class="rounded-xl bg-[#16a34a] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#15803d]"
        >
          Close
        </button>
      </div>
    </div>
  </div>
</template>
