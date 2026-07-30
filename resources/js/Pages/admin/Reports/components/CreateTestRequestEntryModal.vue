<script setup lang="ts">
import { Search, Plus, X, ChevronDown, ChevronUp } from 'lucide-vue-next'
import { computed, ref } from 'vue'

type Client = {
  id: string
  code: string
  name: string
  type: string
}

const props = defineProps<{
  open: boolean
  clients?: Client[]
}>()

defineEmits<{
  close: []
  createNew: []
  addRecord: [clientId: string]
}>()

const searchQuery = ref('')
const expandedClientId = ref<string | null>(null)

const clientList = computed(() => props.clients ?? [])

const filteredClients = computed(() => {
  const query = searchQuery.value.toLowerCase().trim()

  return clientList.value.filter(
    (client) =>
      client.name.toLowerCase().includes(query) ||
      client.code.toLowerCase().includes(query),
  )
})

function toggleClient(clientId: string) {
  expandedClientId.value = expandedClientId.value === clientId ? null : clientId
}
</script>

<template>
  <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center px-4">
    <div class="absolute inset-0 bg-black/50" @click="$emit('close')"></div>

    <div
      class="relative z-10 w-full max-w-2xl rounded-2xl border border-zinc-200 bg-white shadow-xl"
    >
      <div class="flex items-center justify-between border-b border-zinc-200 px-6 py-4">
        <h2 class="text-xl font-semibold text-zinc-900">
          Create Test Request
        </h2>

        <button
          type="button"
          @click="$emit('close')"
          class="rounded-md p-1 text-zinc-400 transition hover:bg-zinc-100 hover:text-zinc-600"
        >
          <X class="h-5 w-5" />
        </button>
      </div>

      <div class="p-6">
        <div class="relative mb-4">
          <Search
            class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-zinc-400"
          />
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search clients by name or RSBSA no..."
            class="h-11 w-full rounded-lg border border-zinc-200 bg-white pl-10 pr-3 text-sm text-zinc-900 outline-none focus:border-[#0E3D1A]"
          />
        </div>

        <button
          type="button"
          @click="$emit('createNew')"
          class="mb-4 inline-flex w-full items-center justify-center gap-2 rounded-lg border-2 border-dashed border-[#0E3D1A] bg-white px-4 py-2.5 text-sm font-semibold text-[#0E3D1A] transition hover:bg-[#0E3D1A] hover:text-white"
        >
          <Plus class="h-4 w-4" />
          Create New Test Request
        </button>

        <div class="max-h-96 overflow-y-auto rounded-xl border border-zinc-200">
          <div
            v-if="filteredClients.length === 0"
            class="p-8 text-center text-zinc-500"
          >
            No clients found
          </div>

          <div v-else>
            <div
              v-for="client in filteredClients"
              :key="client.id"
              class="border-b border-zinc-200 last:border-b-0"
            >
              <button
                type="button"
                @click="toggleClient(client.id)"
                class="w-full p-4 text-left transition hover:bg-zinc-50"
              >
                <div class="flex items-center justify-between gap-3">
                  <div class="font-medium text-zinc-900">
                    {{ client.name }}
                  </div>

                  <component
                    :is="expandedClientId === client.id ? ChevronUp : ChevronDown"
                    class="h-4 w-4 text-zinc-500"
                  />
                </div>
              </button>

              <div
                v-if="expandedClientId === client.id"
                class="flex justify-center border-t border-zinc-200 bg-zinc-50 px-4 py-3"
              >

                <button
                  type="button"
                  @click="$emit('addRecord', client.id)"
                  class="inline-flex items-center justify-center gap-2 rounded-lg border border-[#F1911F] bg-white px-4 py-2.5 text-sm font-semibold text-[#F1911F] transition hover:bg-[#F1911F] hover:text-white"
                >
                  <Plus class="h-4 w-4" />
                  Add New Test Request
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
