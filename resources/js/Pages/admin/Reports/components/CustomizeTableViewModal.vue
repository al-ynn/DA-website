<script setup lang="ts">
import { computed } from 'vue'
import { X } from 'lucide-vue-next'

const props = defineProps<{
  open: boolean
  columns: TableColumn[]
  draftColumnKeys: string[]
}>()

type TableColumn = {
  key: string
  label: string
  default: boolean
  category: string
  locked?: boolean
  align?: 'left' | 'center' | 'right'
}

type ColumnGroup = {
  category: string
  columns: TableColumn[]
}

const emit = defineEmits<{
  close: []
  toggleColumn: [key: string]
  defaultView: []
  detailedView: []
  resetSavedView: []
  saveView: []
}>()

const categoryOrder = [
  'Customer Information',
  'Other Details',
  'Test Request Information',
  'Sample Information',
  'Payment Status',
  'System',
]

const coordinateChildKeys = ['longitude', 'latitude']

const groupedColumns = computed<ColumnGroup[]>(() => {
  const categories = Array.from(
    new Set(props.columns.map((column) => column.category)),
  )

  return categories
    .sort((a, b) => {
      const aIndex = categoryOrder.indexOf(a)
      const bIndex = categoryOrder.indexOf(b)

      if (aIndex === -1 && bIndex === -1) return a.localeCompare(b)
      if (aIndex === -1) return 1
      if (bIndex === -1) return -1

      return aIndex - bIndex
    })
    .map((category) => ({
      category,
      columns: props.columns
        .filter((column) => column.category === category)
        .filter((column) => !coordinateChildKeys.includes(column.key)),
    }))
})

function getCoordinateChildren() {
  return coordinateChildKeys
    .map((key) => props.columns.find((column) => column.key === key))
    .filter((column): column is TableColumn => Boolean(column))
}

function splitColumns(columns: TableColumn[], side: 'left' | 'right') {
  const midpoint = Math.ceil(columns.length / 2)
  return side === 'left' ? columns.slice(0, midpoint) : columns.slice(midpoint)
}
</script>

<template>
  <div
    v-if="open"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4"
  >
    <div
      class="flex max-h-[90vh] w-full max-w-5xl flex-col rounded-2xl border border-zinc-200 bg-white shadow-xl"
    >
      <!-- Header -->
      <div
        class="flex items-center justify-between border-b border-zinc-200 px-6 py-4"
      >
        <div>
          <h2 class="text-lg font-semibold text-zinc-900">
            Customize Table View
          </h2>

          <p class="text-sm text-zinc-500">
            Select the fields to display in the table.
          </p>
        </div>

        <button
          type="button"
          @click="emit('close')"
          class="rounded-lg p-2 text-zinc-500 transition hover:bg-zinc-100"
        >
          <X class="h-5 w-5" />
        </button>
      </div>

      <!-- Toolbar -->
      <div class="border-b border-zinc-200 px-6 py-4">
        <div class="flex flex-wrap gap-2">
          <button
            type="button"
            @click="emit('defaultView')"
            class="rounded-lg border border-zinc-200 px-3 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50"
          >
            Default View
          </button>

          <button
            type="button"
            @click="emit('detailedView')"
            class="rounded-lg border border-zinc-200 px-3 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50"
          >
            Show All Fields
          </button>

          <button
            type="button"
            @click="emit('resetSavedView')"
            class="rounded-lg border border-red-300 px-3 py-2 text-sm font-medium text-red-600 transition hover:bg-red-50"
          >
            Reset Saved View
          </button>
        </div>
      </div>

      <!-- Body -->
      <div class="flex-1 overflow-y-auto px-6 py-5">
        <div class="space-y-6">

          <section
            v-for="group in groupedColumns"
            :key="group.category"
            class="rounded-xl border border-[#0E3D1A] p-5"
          >
            <h3
              class="mb-5 text-lg font-bold uppercase tracking-wide text-[#00D084]"
            >
              {{ group.category }}
            </h3>

            <div class="grid grid-cols-2 gap-x-20">

              <!-- Left -->
              <div class="space-y-3">
                <template
                  v-for="column in splitColumns(group.columns, 'left')"
                  :key="column.key"
                >
                  <div
                    v-if="column.key === 'coordinates'"
                    class="rounded-xl border border-zinc-200 p-4"
                  >
                    <label
                      class="flex cursor-pointer items-center gap-3 border-b border-zinc-200 pb-3 text-sm font-medium text-zinc-700"
                    >
                      <input
                        type="checkbox"
                        :checked="draftColumnKeys.includes(column.key)"
                        :disabled="column.locked"
                        class="h-4 w-4 shrink-0 rounded border-zinc-300 text-[#0E3D1A] focus:ring-0 disabled:cursor-not-allowed disabled:opacity-60"
                        @change="emit('toggleColumn', column.key)"
                      />

                      <span class="leading-5">
                        {{ column.label }}
                      </span>
                    </label>

                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                      <label
                        v-for="childColumn in getCoordinateChildren()"
                        :key="childColumn.key"
                        class="flex cursor-pointer items-center gap-3 rounded-xl border border-zinc-200 px-4 py-3 text-sm text-zinc-700 transition hover:bg-zinc-100"
                      >
                        <input
                          type="checkbox"
                          :checked="draftColumnKeys.includes(childColumn.key)"
                          :disabled="childColumn.locked"
                          class="h-4 w-4 shrink-0 rounded border-zinc-300 text-[#0E3D1A] focus:ring-0 disabled:cursor-not-allowed disabled:opacity-60"
                          @change="emit('toggleColumn', childColumn.key)"
                        />

                        <span class="leading-5">
                          {{ childColumn.label }}
                        </span>
                      </label>
                    </div>
                  </div>

                  <label
                    v-else
                    class="flex cursor-pointer items-center gap-3 rounded-md px-2 py-2 text-sm text-zinc-700 transition hover:bg-zinc-100"
                  >
                    <input
                      type="checkbox"
                      :checked="draftColumnKeys.includes(column.key)"
                      :disabled="column.locked"
                      class="h-4 w-4 shrink-0 rounded border-zinc-300 text-[#0E3D1A] focus:ring-0 disabled:cursor-not-allowed disabled:opacity-60"
                      @change="emit('toggleColumn', column.key)"
                    />

                    <span class="leading-5">
                      {{ column.label }}
                    </span>
                  </label>
                </template>
              </div>

              <!-- Right -->
              <div class="space-y-3">
                <template
                  v-for="column in splitColumns(group.columns, 'right')"
                  :key="column.key"
                >
                  <div
                    v-if="column.key === 'coordinates'"
                    class="rounded-xl border border-zinc-200 p-4"
                  >
                    <label
                      class="flex cursor-pointer items-center gap-3 border-b border-zinc-200 pb-3 text-sm font-medium text-zinc-700"
                    >
                      <input
                        type="checkbox"
                        :checked="draftColumnKeys.includes(column.key)"
                        :disabled="column.locked"
                        class="h-4 w-4 shrink-0 rounded border-zinc-300 text-[#0E3D1A] focus:ring-0 disabled:cursor-not-allowed disabled:opacity-60"
                        @change="emit('toggleColumn', column.key)"
                      />

                      <span class="leading-5">
                        {{ column.label }}
                      </span>
                    </label>

                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                      <label
                        v-for="childColumn in getCoordinateChildren()"
                        :key="childColumn.key"
                        class="flex cursor-pointer items-center gap-3 rounded-xl border border-zinc-200 px-4 py-3 text-sm text-zinc-700 transition hover:bg-zinc-100"
                      >
                        <input
                          type="checkbox"
                          :checked="draftColumnKeys.includes(childColumn.key)"
                          :disabled="childColumn.locked"
                          class="h-4 w-4 shrink-0 rounded border-zinc-300 text-[#0E3D1A] focus:ring-0 disabled:cursor-not-allowed disabled:opacity-60"
                          @change="emit('toggleColumn', childColumn.key)"
                        />

                        <span class="leading-5">
                          {{ childColumn.label }}
                        </span>
                      </label>
                    </div>
                  </div>

                  <label
                    v-else
                    class="flex cursor-pointer items-center gap-3 rounded-md px-2 py-2 text-sm text-zinc-700 transition hover:bg-zinc-100"
                  >
                    <input
                      type="checkbox"
                      :checked="draftColumnKeys.includes(column.key)"
                      :disabled="column.locked"
                      class="h-4 w-4 shrink-0 rounded border-zinc-300 text-[#0E3D1A] focus:ring-0 disabled:cursor-not-allowed disabled:opacity-60"
                      @change="emit('toggleColumn', column.key)"
                    />

                    <span class="leading-5">
                      {{ column.label }}
                    </span>
                  </label>
                </template>
              </div>

            </div>
          </section>

        </div>
      </div>

      <!-- Footer -->
      <div
        class="flex justify-end gap-3 border-t border-zinc-200 px-6 py-4"
      >
        <button
          type="button"
          @click="emit('close')"
          class="rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50"
        >
          Cancel
        </button>

        <button
          type="button"
          @click="emit('saveView')"
          class="rounded-lg bg-[#0E3D1A] px-5 py-2.5 text-sm font-semibold text-white transition hover:opacity-90"
        >
          Save View
        </button>
      </div>
    </div>
  </div>
</template>
