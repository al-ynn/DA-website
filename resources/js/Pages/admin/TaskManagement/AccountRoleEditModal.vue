<script setup lang="ts">
import { Save, Star, FlaskConical, Tractor, X } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

type MainCategory = 'admin' | 'chemist' | 'agriculturist';

type TaskItem =
    | string
    | {
          category: string;
          displayName: string;
          subtasks: string[];
      };

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
    title: string;
    laboratory_tasks: LaboratoryTask[];
};

type CategorySelection = {
    presetIds: number[];
    taskIds: number[];
};

const props = defineProps<{
    open: boolean;
    targetUsers: Array<{
        id: number;
        name: string;
    }>;
    availableCategories: MainCategory[];
    initialCategory: MainCategory | null;
    taskCategories: Record<MainCategory, TaskCategory | null>;
    presets: Record<MainCategory, Preset[]>;
    initialSelections: Partial<Record<MainCategory, CategorySelection>>;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (
        e: 'save',
        payload: Partial<Record<MainCategory, CategorySelection>>,
    ): void;
}>();

const activeCategory = ref<MainCategory>('admin');
const selections = ref<Record<MainCategory, CategorySelection>>({
    admin: { presetIds: [], taskIds: [] },
    chemist: { presetIds: [], taskIds: [] },
    agriculturist: { presetIds: [], taskIds: [] },
});

const TASK_DEFINITIONS: Record<MainCategory, TaskItem[]> = {
    admin: ['Reviewer', 'Certifier', 'Noter'],
    chemist: [
        'pH',
        'EC Analysis',
        'Organic Matter Analysis',
        'Available Phosphorus',
        {
            category: 'Exchangeable Bases',
            displayName:
                'Exchangeable Bases (Potassium, Calcium, Magnesium, Sodium)',
            subtasks: ['Potassium', 'Calcium', 'Magnesium', 'Sodium'],
        },
        {
            category: 'Micronutrients',
            displayName: 'Micronutrients (Zinc, Copper, Iron, Manganese)',
            subtasks: ['Zinc', 'Copper', 'Iron', 'Manganese'],
        },
    ],
    agriculturist: [
        'Soil Moisture',
        'Particle Size Analysis',
        'Soil Texture',
        'Fertilizer Recommendation',
    ],
};

function getDefaultActiveCategory() {
    if (
        props.initialCategory &&
        props.availableCategories.includes(props.initialCategory)
    ) {
        return props.initialCategory;
    }

    return props.availableCategories[0] ?? 'admin';
}

function flattenTaskNames(category: MainCategory) {
    const items = TASK_DEFINITIONS[category];
    const result: string[] = [];

    items.forEach((item) => {
        if (typeof item === 'string') {
            result.push(item);
            return;
        }

        result.push(...item.subtasks);
    });

    return result;
}

function getTaskIdByName(category: MainCategory, taskName: string) {
    return props.taskCategories[category]?.laboratory_tasks.find(
        (task) => task.name === taskName,
    )?.id;
}

function buildSelections() {
    const next: Record<MainCategory, CategorySelection> = {
        admin: { presetIds: [], taskIds: [] },
        chemist: { presetIds: [], taskIds: [] },
        agriculturist: { presetIds: [], taskIds: [] },
    };

    (['admin', 'chemist', 'agriculturist'] as MainCategory[]).forEach(
        (category) => {
            const initial = props.initialSelections[category];
            if (!initial) return;

            next[category] = {
                presetIds: [...initial.presetIds],
                taskIds: [...initial.taskIds],
            };
        },
    );

    selections.value = next;
}

watch(
    () => props.open,
    (value) => {
        if (!value) return;
        activeCategory.value = getDefaultActiveCategory();
        buildSelections();
    },
    { immediate: true },
);

watch(
    () => [
        props.availableCategories,
        props.initialCategory,
        props.initialSelections,
    ],
    () => {
        if (!props.open) return;
        if (!props.availableCategories.includes(activeCategory.value)) {
            activeCategory.value = getDefaultActiveCategory();
        }
        buildSelections();
    },
    { deep: true },
);

const activePresets = computed(() => props.presets[activeCategory.value] ?? []);
const visiblePresetGroups = computed(() => {
    const categories: MainCategory[] =
        activeCategory.value === 'admin' ? [] : [activeCategory.value];

    return categories
        .map((category) => ({
            category,
            label: `${getCategoryLabel(category)} Presets`,
            presets: props.presets[category] ?? [],
        }))
        .filter((group) => group.presets.length > 0);
});
const activeTasks = computed(
    () => TASK_DEFINITIONS[activeCategory.value] ?? [],
);

function getCategoryLabel(category: MainCategory) {
    if (category === 'admin') return 'Admin';
    if (category === 'chemist') return 'Chemist';
    return 'Agriculturist';
}

function getCategoryIcon(category: MainCategory) {
    if (category === 'admin') return Star;
    if (category === 'chemist') return FlaskConical;
    return Tractor;
}

function getFlatTaskIds(category: MainCategory) {
    return flattenTaskNames(category)
        .map((taskName) => getTaskIdByName(category, taskName))
        .filter((id): id is number => typeof id === 'number');
}

function toggleTask(taskId: number) {
    const next = new Set(selections.value[activeCategory.value].taskIds);
    if (next.has(taskId)) next.delete(taskId);
    else next.add(taskId);
    selections.value[activeCategory.value].taskIds = Array.from(next);
}

function togglePreset(preset: Preset) {
    const next = new Set(selections.value[activeCategory.value].presetIds);
    if (next.has(preset.id)) {
        next.delete(preset.id);
    } else {
        next.add(preset.id);
    }

    selections.value[activeCategory.value].presetIds = Array.from(next);
}

function getGroupTaskIds(
    category: MainCategory,
    item: Exclude<TaskItem, string>,
) {
    return item.subtasks
        .map((taskName) => getTaskIdByName(category, taskName))
        .filter((id): id is number => typeof id === 'number');
}

function isTaskGroupSelected(
    category: MainCategory,
    item: Exclude<TaskItem, string>,
) {
    const taskIds = getGroupTaskIds(category, item);
    return (
        taskIds.length > 0 &&
        taskIds.every((taskId) =>
            selections.value[category].taskIds.includes(taskId),
        )
    );
}

function toggleTaskGroup(
    category: MainCategory,
    item: Exclude<TaskItem, string>,
) {
    const next = new Set(selections.value[category].taskIds);
    const shouldSelect = !isTaskGroupSelected(category, item);

    getGroupTaskIds(category, item).forEach((taskId) => {
        if (shouldSelect) next.add(taskId);
        else next.delete(taskId);
    });

    selections.value[category].taskIds = Array.from(next);
}

function selectAllTasks() {
    selections.value[activeCategory.value].taskIds = getFlatTaskIds(
        activeCategory.value,
    );
}

function clearAllTasks() {
    selections.value[activeCategory.value].taskIds = [];
}

function selectAllPresets() {
    selections.value[activeCategory.value].presetIds = activePresets.value.map(
        (preset) => preset.id,
    );
}

function clearAllPresets() {
    selections.value[activeCategory.value].presetIds = [];
}

function handleSave() {
    const payload: Partial<Record<MainCategory, CategorySelection>> = {};

    props.availableCategories.forEach((category) => {
        payload[category] = {
            presetIds: [...selections.value[category].presetIds],
            taskIds: [...selections.value[category].taskIds],
        };
    });

    emit('save', payload);
}

function getTitle() {
    if (props.targetUsers.length === 1) {
        return `Edit Role - ${props.targetUsers[0].name}`;
    }

    return `Edit Role - ${props.targetUsers.length} accounts`;
}
</script>

<template>
    <div
        v-if="open"
        class="fixed inset-0 z-[80] flex items-center justify-center bg-black/50 px-4 py-6"
    >
        <div
            class="flex max-h-[85dvh] w-full max-w-2xl flex-col overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-xl dark:border-zinc-800 dark:bg-[#111111]"
        >
            <div
                class="flex items-start justify-between border-b border-zinc-200 px-6 py-5 dark:border-zinc-800"
            >
                <div>
                    <h2
                        class="text-xl font-semibold text-zinc-900 dark:text-white"
                    >
                        {{ getTitle() }}
                    </h2>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                        Update individual role assignment for this account
                    </p>
                </div>

                <button
                    type="button"
                    @click="emit('close')"
                    class="rounded-lg p-2 text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-900 dark:hover:bg-[#1a1a1a] dark:hover:text-white"
                >
                    <X class="h-5 w-5" />
                </button>
            </div>

            <div class="flex-1 overflow-y-auto px-6 py-5">
                <div class="flex flex-col gap-6">
                    <div
                        v-if="availableCategories.length > 1"
                        class="flex flex-col gap-3"
                    >
                        <div
                            class="text-sm font-semibold text-zinc-900 dark:text-white"
                        >
                            Role Category
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="roleCategory in availableCategories"
                                :key="roleCategory"
                                type="button"
                                @click="activeCategory = roleCategory"
                                class="inline-flex items-center gap-2 rounded-xl border px-4 py-2 text-sm font-medium transition"
                                :class="
                                    activeCategory === roleCategory
                                        ? 'border-[#0E3D1A] bg-[#0E3D1A] text-white'
                                        : 'border-zinc-200 bg-white text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-[#111111] dark:text-zinc-200 dark:hover:bg-[#1a1a1a]'
                                "
                            >
                                <component
                                    :is="getCategoryIcon(roleCategory)"
                                    class="h-4 w-4"
                                />
                                {{ getCategoryLabel(roleCategory) }}
                            </button>
                        </div>
                    </div>

                    <template v-if="visiblePresetGroups.length > 0">
                        <div class="flex flex-col gap-4">
                            <div class="flex items-center justify-between">
                                <h3
                                    class="text-sm font-semibold text-zinc-900 dark:text-white"
                                >
                                    Preset Assignment
                                </h3>

                                <div class="flex gap-2">
                                    <button
                                        type="button"
                                        @click="selectAllPresets"
                                        class="rounded-lg px-3 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100 dark:text-zinc-200 dark:hover:bg-[#1a1a1a]"
                                    >
                                        Select All
                                    </button>

                                    <button
                                        type="button"
                                        @click="clearAllPresets"
                                        class="rounded-lg px-3 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100 dark:text-zinc-200 dark:hover:bg-[#1a1a1a]"
                                    >
                                        Clear All
                                    </button>
                                </div>
                            </div>

                            <div
                                v-for="group in visiblePresetGroups"
                                :key="group.category"
                                class="flex flex-col gap-3"
                            >
                                <h4
                                    class="text-sm font-semibold text-zinc-700 dark:text-zinc-200"
                                >
                                    {{ group.label }}
                                </h4>

                                <div class="grid gap-3">
                                    <label
                                        v-for="preset in group.presets"
                                        :key="preset.id"
                                        class="flex cursor-pointer items-start gap-3 rounded-2xl border border-zinc-200 bg-white px-4 py-3 transition hover:bg-zinc-50 dark:border-zinc-700 dark:bg-[#111111] dark:hover:bg-[#171717]"
                                    >
                                        <input
                                            type="checkbox"
                                            class="mt-1 h-5 w-5 rounded border-zinc-300 text-black focus:ring-0 dark:border-zinc-600 dark:bg-[#0b0b0b]"
                                            :checked="
                                                selections[
                                                    activeCategory
                                                ].presetIds.includes(preset.id)
                                            "
                                            @change="togglePreset(preset)"
                                        />

                                        <div class="min-w-0">
                                            <div
                                                class="text-base font-semibold text-zinc-900 dark:text-white"
                                            >
                                                {{ preset.name }}
                                            </div>
                                            <div
                                                class="mt-1 text-sm text-zinc-500 dark:text-zinc-400"
                                            >
                                                {{
                                                    preset.description ||
                                                    'No description provided'
                                                }}
                                            </div>
                                            <div
                                                class="mt-2 flex flex-wrap gap-1.5"
                                            >
                                                <span
                                                    v-for="task in preset.tasks"
                                                    :key="`${preset.id}-${task}`"
                                                    class="inline-flex rounded-full border border-zinc-200 bg-zinc-50 px-2 py-0.5 text-[11px] font-medium text-zinc-700 dark:border-zinc-700 dark:bg-[#171717] dark:text-zinc-200"
                                                >
                                                    {{ task }}
                                                </span>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="relative">
                            <div class="absolute inset-0 flex items-center">
                                <div
                                    class="w-full border-t border-zinc-200 dark:border-zinc-800"
                                ></div>
                            </div>
                            <div class="relative flex justify-center">
                                <span
                                    class="bg-white px-3 text-sm font-medium text-zinc-500 dark:bg-[#111111] dark:text-zinc-400"
                                >
                                    OR SELECT MANUALLY
                                </span>
                            </div>
                        </div>
                    </template>

                    <div class="flex flex-col gap-3">
                        <div class="flex items-center justify-between">
                            <h3
                                class="text-sm font-semibold text-zinc-900 dark:text-white"
                            >
                                Individual Tasks
                            </h3>

                            <div class="flex gap-2">
                                <button
                                    type="button"
                                    @click="selectAllTasks"
                                    class="rounded-lg px-3 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100 dark:text-zinc-200 dark:hover:bg-[#1a1a1a]"
                                >
                                    Select All
                                </button>

                                <button
                                    type="button"
                                    @click="clearAllTasks"
                                    class="rounded-lg px-3 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100 dark:text-zinc-200 dark:hover:bg-[#1a1a1a]"
                                >
                                    Clear All
                                </button>
                            </div>
                        </div>

                        <div class="flex flex-col gap-3">
                            <template
                                v-for="(item, idx) in activeTasks"
                                :key="idx"
                            >
                                <template v-if="typeof item === 'string'">
                                    <label
                                        class="flex cursor-pointer items-center gap-3 rounded-2xl border border-zinc-200 bg-white px-4 py-3 transition hover:bg-zinc-50 dark:border-zinc-700 dark:bg-[#111111] dark:hover:bg-[#171717]"
                                    >
                                        <input
                                            type="checkbox"
                                            class="h-5 w-5 rounded border-zinc-300 text-black focus:ring-0 dark:border-zinc-600 dark:bg-[#0b0b0b]"
                                            :checked="
                                                selections[
                                                    activeCategory
                                                ].taskIds.includes(
                                                    getTaskIdByName(
                                                        activeCategory,
                                                        item,
                                                    ) ?? -1,
                                                )
                                            "
                                            @change="
                                                getTaskIdByName(
                                                    activeCategory,
                                                    item,
                                                ) !== undefined &&
                                                toggleTask(
                                                    getTaskIdByName(
                                                        activeCategory,
                                                        item,
                                                    )!,
                                                )
                                            "
                                        />
                                        <span
                                            class="text-base font-semibold text-zinc-900 dark:text-white"
                                        >
                                            {{ item }}
                                        </span>
                                    </label>
                                </template>

                                <template v-else>
                                    <div
                                        class="rounded-2xl border border-zinc-200 bg-white px-4 py-4 dark:border-zinc-700 dark:bg-[#111111]"
                                    >
                                        <label
                                            class="flex cursor-pointer items-center gap-3 border-b border-zinc-200 pb-3 dark:border-zinc-800"
                                        >
                                            <input
                                                type="checkbox"
                                                class="h-5 w-5 rounded border-zinc-300 text-black focus:ring-0 dark:border-zinc-600 dark:bg-[#0b0b0b]"
                                                :checked="
                                                    isTaskGroupSelected(
                                                        activeCategory,
                                                        item,
                                                    )
                                                "
                                                @change="
                                                    toggleTaskGroup(
                                                        activeCategory,
                                                        item,
                                                    )
                                                "
                                            />
                                            <span
                                                class="text-sm font-semibold text-zinc-900 dark:text-white"
                                            >
                                                {{ item.category }}
                                            </span>
                                        </label>

                                        <div
                                            class="mt-3 grid gap-3 sm:grid-cols-2"
                                        >
                                            <label
                                                v-for="subtask in item.subtasks"
                                                :key="subtask"
                                                class="flex cursor-pointer items-center gap-3 rounded-xl border border-zinc-200 bg-white px-4 py-3 transition hover:bg-zinc-50 dark:border-zinc-700 dark:bg-[#111111] dark:hover:bg-[#171717]"
                                            >
                                                <input
                                                    type="checkbox"
                                                    class="h-5 w-5 rounded border-zinc-300 text-black focus:ring-0 dark:border-zinc-600 dark:bg-[#0b0b0b]"
                                                    :checked="
                                                        selections[
                                                            activeCategory
                                                        ].taskIds.includes(
                                                            getTaskIdByName(
                                                                activeCategory,
                                                                subtask,
                                                            ) ?? -1,
                                                        )
                                                    "
                                                    @change="
                                                        getTaskIdByName(
                                                            activeCategory,
                                                            subtask,
                                                        ) !== undefined &&
                                                        toggleTask(
                                                            getTaskIdByName(
                                                                activeCategory,
                                                                subtask,
                                                            )!,
                                                        )
                                                    "
                                                />
                                                <span
                                                    class="text-sm font-medium text-zinc-900 dark:text-white"
                                                >
                                                    {{ subtask }}
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                </template>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <div
                class="flex items-center justify-end gap-3 border-t border-zinc-200 px-6 py-5 dark:border-zinc-800"
            >
                <button
                    type="button"
                    @click="emit('close')"
                    class="rounded-xl border border-zinc-200 bg-white px-6 py-3 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-50 dark:border-zinc-700 dark:bg-[#111111] dark:text-zinc-200 dark:hover:bg-[#1a1a1a]"
                >
                    Cancel
                </button>

                <button
                    type="button"
                    @click="handleSave"
                    class="inline-flex items-center gap-2 rounded-xl bg-[#16a34a] px-6 py-3 text-sm font-semibold text-white transition hover:bg-[#15803d]"
                >
                    <Save class="h-4 w-4" />
                    Save Changes
                </button>
            </div>
        </div>
    </div>
</template>
