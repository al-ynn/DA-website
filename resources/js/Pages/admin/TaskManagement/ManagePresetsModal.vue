<script setup lang="ts">
import { Pencil, Plus, Save, Trash2, X } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

type MainCategory = 'admin' | 'chemist' | 'agriculturist';
type PresetCategory = 'chemist' | 'agriculturist';

type LaboratoryTask = {
    id: number;
    name: string;
    task_category_id: number;
};

type Preset = {
    id: number;
    name: string;
    description: string | null;
    category: MainCategory;
    task_category_id: number;
    tasks: string[];
    task_ids: number[];
};

type TaskCategory = {
    id: number;
    key: MainCategory;
    name: string;
    title: string;
    laboratory_tasks: LaboratoryTask[];
    presets: Preset[];
};

type TaskGroup = {
    name: string;
    tasks: LaboratoryTask[];
};

const props = defineProps<{
    open: boolean;
    categories: TaskCategory[];
    initialCategory: MainCategory | null;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (
        e: 'save',
        payload: {
            presetId?: number;
            task_category_id: number;
            name: string;
            description: string;
            laboratory_tasks: number[];
        },
    ): void;
    (e: 'delete', presetId: number): void;
    (e: 'clear', category: PresetCategory): void;
    (e: 'use', payload: { category: PresetCategory; taskIds: number[] }): void;
}>();

const activeCategory = ref<PresetCategory | null>(null);
const editingPresetId = ref<number | null>(null);
const showPresetEditor = ref(false);
const presetFormName = ref('');
const presetFormDescription = ref('');
const selectedTaskIds = ref<Set<number>>(new Set());

const GROUPS: Record<
    PresetCategory,
    Array<{ name: string; tasks: string[] }>
> = {
    chemist: [
        {
            name: 'Exchangeable Bases',
            tasks: ['Potassium', 'Calcium', 'Magnesium', 'Sodium'],
        },
        {
            name: 'Micronutrients',
            tasks: ['Zinc', 'Copper', 'Iron', 'Manganese'],
        },
    ],
    agriculturist: [],
};

const presetCategories = computed(() =>
    props.categories.filter(
        (category): category is TaskCategory & { key: PresetCategory } =>
            category.key === 'chemist' || category.key === 'agriculturist',
    ),
);

const activeCategoryRecord = computed(
    () =>
        presetCategories.value.find(
            (category) => category.key === activeCategory.value,
        ) ?? null,
);

const activeLaboratoryTasks = computed(
    () => activeCategoryRecord.value?.laboratory_tasks ?? [],
);

const activeTaskGroups = computed<TaskGroup[]>(() => {
    if (!activeCategory.value) return [];

    return GROUPS[activeCategory.value]
        .map((group) => ({
            name: group.name,
            tasks: group.tasks
                .map((taskName) =>
                    activeLaboratoryTasks.value.find(
                        (task) => task.name === taskName,
                    ),
                )
                .filter((task): task is LaboratoryTask => Boolean(task)),
        }))
        .filter((group) => group.tasks.length > 0);
});

const activeStandaloneTasks = computed(() => {
    const groupedTaskNames = new Set(
        activeTaskGroups.value.flatMap((group) =>
            group.tasks.map((task) => task.name),
        ),
    );

    return activeLaboratoryTasks.value.filter(
        (task) => !groupedTaskNames.has(task.name),
    );
});

watch(
    () => props.open,
    (value) => {
        if (!value) return;
        resetEditor();
        activeCategory.value =
            props.initialCategory === 'chemist' ||
            props.initialCategory === 'agriculturist'
                ? props.initialCategory
                : (presetCategories.value[0]?.key ?? null);
    },
    { immediate: true },
);

function resetEditor() {
    editingPresetId.value = null;
    showPresetEditor.value = false;
    presetFormName.value = '';
    presetFormDescription.value = '';
    selectedTaskIds.value = new Set();
}

function startAdd(category: PresetCategory) {
    activeCategory.value = category;
    editingPresetId.value = null;
    presetFormName.value = '';
    presetFormDescription.value = '';
    selectedTaskIds.value = new Set();
    showPresetEditor.value = true;
}

function startEdit(preset: Preset) {
    if (preset.category !== 'chemist' && preset.category !== 'agriculturist')
        return;

    activeCategory.value = preset.category;
    editingPresetId.value = preset.id;
    presetFormName.value = preset.name;
    presetFormDescription.value = preset.description ?? '';
    selectedTaskIds.value = new Set(preset.task_ids);
    showPresetEditor.value = true;
}

function closeEditor() {
    resetEditor();
}

function toggleTask(taskId: number) {
    const next = new Set(selectedTaskIds.value);

    if (next.has(taskId)) next.delete(taskId);
    else next.add(taskId);

    selectedTaskIds.value = next;
}

function isGroupSelected(group: TaskGroup) {
    return (
        group.tasks.length > 0 &&
        group.tasks.every((task) => selectedTaskIds.value.has(task.id))
    );
}

function toggleTaskGroup(group: TaskGroup) {
    const next = new Set(selectedTaskIds.value);
    const shouldSelect = !isGroupSelected(group);

    group.tasks.forEach((task) => {
        if (shouldSelect) next.add(task.id);
        else next.delete(task.id);
    });

    selectedTaskIds.value = next;
}

function selectAllTasks() {
    selectedTaskIds.value = new Set(
        activeLaboratoryTasks.value.map((task) => task.id),
    );
}

function clearAllTasks() {
    selectedTaskIds.value = new Set();
}

function usePreset(preset: Preset) {
    if (preset.category !== 'chemist' && preset.category !== 'agriculturist')
        return;

    emit('use', {
        category: preset.category,
        taskIds: [...preset.task_ids],
    });
}

function savePreset() {
    if (
        !activeCategoryRecord.value ||
        !presetFormName.value.trim() ||
        selectedTaskIds.value.size === 0
    ) {
        return;
    }

    emit('save', {
        presetId: editingPresetId.value ?? undefined,
        task_category_id: activeCategoryRecord.value.id,
        name: presetFormName.value.trim(),
        description: presetFormDescription.value.trim(),
        laboratory_tasks: Array.from(selectedTaskIds.value),
    });
}
</script>

<template>
    <div
        v-if="open"
        class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50 px-4 py-6"
    >
        <div
            class="flex max-h-[90dvh] w-full max-w-5xl flex-col overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-xl"
        >
            <div
                class="flex items-start justify-between border-b border-zinc-200 px-6 py-5"
            >
                <div>
                    <h2 class="text-2xl font-semibold text-zinc-900">
                        Manage Presets
                    </h2>
                    <p class="mt-1 text-sm text-zinc-500">
                        Presets are stored in the backend and grouped by task
                        category.
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
                    <section
                        v-for="category in presetCategories"
                        :key="category.key"
                        class="flex flex-col gap-4 rounded-2xl border border-zinc-200 p-4"
                    >
                        <div
                            class="flex items-center justify-between gap-3 border-b border-zinc-200 pb-4"
                        >
                            <div>
                                <h3 class="text-xl font-semibold text-zinc-900">
                                    {{ category.title }}
                                </h3>
                                <p class="mt-1 text-sm text-zinc-500">
                                    {{ category.laboratory_tasks.length }}
                                    backend tasks available
                                </p>
                            </div>

                            <div class="flex shrink-0 gap-2">
                                <button
                                    type="button"
                                    @click="startAdd(category.key)"
                                    class="inline-flex items-center gap-2 rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-50"
                                >
                                    <Plus class="h-4 w-4" />
                                    Add New
                                </button>

                                <button
                                    type="button"
                                    @click="emit('clear', category.key)"
                                    class="rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-50"
                                >
                                    Clear
                                </button>
                            </div>
                        </div>

                        <div v-if="category.presets.length" class="space-y-2">
                            <article
                                v-for="preset in category.presets"
                                :key="preset.id"
                                class="rounded-xl border border-zinc-200 bg-white px-4 py-3"
                            >
                                <div
                                    class="flex items-start justify-between gap-3"
                                >
                                    <div class="min-w-0">
                                        <h5
                                            class="text-sm font-semibold text-zinc-900"
                                        >
                                            {{ preset.name }}
                                        </h5>
                                        <p class="mt-0.5 text-xs text-zinc-500">
                                            {{
                                                preset.description ||
                                                preset.tasks.join(', ')
                                            }}
                                        </p>
                                        <div
                                            class="mt-2 flex flex-wrap gap-1.5"
                                        >
                                            <span
                                                v-for="task in preset.tasks"
                                                :key="`${preset.id}-${task}`"
                                                class="rounded-full border border-zinc-200 bg-zinc-50 px-2 py-0.5 text-[11px] font-medium text-zinc-700"
                                            >
                                                {{ task }}
                                            </span>
                                        </div>
                                    </div>

                                    <div
                                        class="flex shrink-0 items-center gap-1"
                                    >
                                        <button
                                            type="button"
                                            @click="usePreset(preset)"
                                            class="px-2 py-1 text-xs font-semibold text-[#0E3D1A]"
                                        >
                                            Use
                                        </button>
                                        <button
                                            type="button"
                                            @click="startEdit(preset)"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-zinc-200 text-zinc-700 transition hover:bg-zinc-50"
                                        >
                                            <Pencil class="h-3.5 w-3.5" />
                                        </button>
                                        <button
                                            type="button"
                                            @click="emit('delete', preset.id)"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-rose-200 text-rose-600 transition hover:bg-rose-50"
                                        >
                                            <Trash2 class="h-3.5 w-3.5" />
                                        </button>
                                    </div>
                                </div>
                            </article>
                        </div>

                        <div
                            v-else
                            class="flex flex-1 flex-col items-center justify-center rounded-2xl border border-dashed border-zinc-300 bg-zinc-50 px-6 py-10 text-center"
                        >
                            <p class="text-sm text-zinc-500">
                                No presets have been created yet.
                            </p>
                            <button
                                type="button"
                                @click="startAdd(category.key)"
                                class="mt-4 inline-flex items-center gap-2 rounded-xl border border-zinc-200 bg-white px-4 py-2 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-100"
                            >
                                <Plus class="h-4 w-4" />
                                Add New Preset
                            </button>
                        </div>
                    </section>
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

        <div
            v-if="showPresetEditor"
            class="fixed inset-0 z-[70] flex items-center justify-center bg-black/50 px-4 py-6"
        >
            <div
                class="flex max-h-[88dvh] w-full max-w-2xl flex-col overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-xl"
            >
                <div
                    class="flex items-start justify-between border-b border-zinc-200 px-6 py-5"
                >
                    <div>
                        <h2 class="text-xl font-semibold text-zinc-900">
                            {{
                                editingPresetId
                                    ? 'Edit Preset'
                                    : `Add Tasks - ${activeCategoryRecord?.title ?? ''}`
                            }}
                        </h2>
                        <p class="mt-1 text-sm text-zinc-500">
                            Select tasks to include in this preset.
                        </p>
                    </div>

                    <button
                        type="button"
                        @click="closeEditor"
                        class="rounded-lg p-2 text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-900"
                    >
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto px-6 py-5">
                    <div class="space-y-4">
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

                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-zinc-900">
                                Individual Tasks
                            </h3>
                            <div class="flex gap-2">
                                <button
                                    type="button"
                                    @click="selectAllTasks"
                                    class="px-2 py-1 text-xs font-medium text-zinc-700"
                                >
                                    Select All
                                </button>
                                <button
                                    type="button"
                                    @click="clearAllTasks"
                                    class="px-2 py-1 text-xs font-medium text-zinc-700"
                                >
                                    Clear All
                                </button>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label
                                v-for="task in activeStandaloneTasks"
                                :key="task.id"
                                class="flex cursor-pointer items-center gap-3 rounded-xl border border-zinc-200 px-4 py-3"
                            >
                                <input
                                    type="checkbox"
                                    class="h-4 w-4 rounded border-zinc-300 text-black focus:ring-0"
                                    :checked="selectedTaskIds.has(task.id)"
                                    @change="toggleTask(task.id)"
                                />
                                <span class="text-sm font-medium text-zinc-900">
                                    {{ task.name }}
                                </span>
                            </label>

                            <section
                                v-for="group in activeTaskGroups"
                                :key="group.name"
                                class="rounded-xl border border-zinc-200 p-4"
                            >
                                <label
                                    class="flex cursor-pointer items-center gap-3 border-b border-zinc-200 pb-3"
                                >
                                    <input
                                        type="checkbox"
                                        class="h-4 w-4 rounded border-zinc-300 text-black focus:ring-0"
                                        :checked="isGroupSelected(group)"
                                        @change="toggleTaskGroup(group)"
                                    />
                                    <span
                                        class="text-sm font-semibold text-zinc-900"
                                    >
                                        {{ group.name }}
                                    </span>
                                </label>

                                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                    <label
                                        v-for="task in group.tasks"
                                        :key="task.id"
                                        class="flex cursor-pointer items-center gap-3 rounded-xl border border-zinc-200 px-4 py-3"
                                    >
                                        <input
                                            type="checkbox"
                                            class="h-4 w-4 rounded border-zinc-300 text-black focus:ring-0"
                                            :checked="
                                                selectedTaskIds.has(task.id)
                                            "
                                            @change="toggleTask(task.id)"
                                        />
                                        <span
                                            class="text-sm font-medium text-zinc-900"
                                        >
                                            {{ task.name }}
                                        </span>
                                    </label>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>

                <div
                    class="flex justify-end gap-3 border-t border-zinc-200 px-6 py-5"
                >
                    <button
                        type="button"
                        @click="closeEditor"
                        class="rounded-xl border border-zinc-200 bg-white px-5 py-3 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-50"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        :disabled="
                            !presetFormName.trim() || selectedTaskIds.size === 0
                        "
                        @click="savePreset"
                        class="inline-flex items-center gap-2 rounded-xl bg-[#16a34a] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#15803d] disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <Save class="h-4 w-4" />
                        Save
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
