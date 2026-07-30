<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import {
  ArrowLeft,
  Pencil,
  Search,
  ShieldBan,
  ShieldCheck,
  Star,
  FlaskConical,
  Tractor,
  Eye,
  TriangleAlert,
} from 'lucide-vue-next'
import { computed, ref } from 'vue'
import AppSidebarLayout from '@/Layouts/app/AppSidebarLayout.vue'
import type { BreadcrumbItem } from '@/types'

type UserCategory = 'ADMIN' | 'CHEMIST' | 'AGRICULTURIST'
type AdditionalTaskType = 'CHEMIST' | 'AGRICULTURIST'
type AccountStatus = 'PENDING'

type UserRecord = {
  id: number
  surname: string
  givenname: string
  middlename: string
  suffix: string
  email: string
  category: UserCategory
  tasks: AdditionalTaskType[]
  status: AccountStatus
}

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'User Management',
    href: '/user-management',
  },
  {
    title: 'Draft Accounts',
    href: '/user-management/drafts',
  },
]

const search = ref('')
const selectedCategory = ref<'ALL' | UserCategory>('ALL')

const showIncompleteDraftModal = ref(false)
const missingFields = ref<string[]>([])

const props = defineProps<{
  users: any[]
}>()

const users = computed<UserRecord[]>(() =>
  props.users.map((user) => ({
    id: user.id,

    surname: user.last_name ?? '',
    givenname: user.first_name ?? '',
    middlename: user.middle_name ?? '',
    suffix: user.suffix ?? '',

    email: user.email,

    category:
      user.role === 'admin'
        ? 'ADMIN'
        : user.role === 'chemist'
          ? 'CHEMIST'
          : 'AGRICULTURIST',

    tasks: (user.additional_tasks ?? []).map((task: string) =>
      task === 'chemist'
        ? 'CHEMIST'
        : 'AGRICULTURIST'
    ),

    status: 'PENDING',
  }))
)

function buildFullName(user: UserRecord) {
  const base = [(user.surname ?? '').trim(), 
(user.givenname ?? '').trim(), (user.middlename ?? '').trim()]
    .filter(Boolean)
    .join(', ')
    .replace(', ,', ',')

  return user.suffix.trim() ? `${base} ${user.suffix.trim()}` : base
}

const filteredUsers = computed(() => {
  const filtered = users.value.filter((user) => {
    const fullName = buildFullName(user).toLowerCase()

    const matchesSearch =
      fullName.includes(search.value.toLowerCase()) ||
      user.email.toLowerCase().includes(search.value.toLowerCase())

    const matchesCategory =
      selectedCategory.value === 'ALL' || user.category === selectedCategory.value

    return matchesSearch && matchesCategory
  })

  return [...filtered].sort((a, b) => {
    const surnameCompare = (a.surname ?? '').toLowerCase()
      .toLowerCase()
      .localeCompare((b.surname ?? '').toLowerCase())
    if (surnameCompare !== 0) return surnameCompare

    const givenCompare = (a.givenname ?? '').toLowerCase()
      .toLowerCase()
      .localeCompare(b.givenname.toLowerCase())
    if (givenCompare !== 0) return givenCompare

    const middleCompare = (a.middlename ?? '').toLowerCase()
      .toLowerCase()
      .localeCompare((b.middlename ?? '').toLowerCase())
    if (middleCompare !== 0) return middleCompare

    return (a.suffix ?? '').toLowerCase()
      .toLowerCase()
      .localeCompare((b.suffix ?? '').toLowerCase())
  })
})

const totalAccounts = computed(() => filteredUsers.value.length)

const groupedUsers = computed(() => {
  const groups: Record<UserCategory, UserRecord[]> = {
    ADMIN: [],
    CHEMIST: [],
    AGRICULTURIST: [],
  }

  filteredUsers.value.forEach((user) => {
    groups[user.category].push(user)
  })

  return groups
})

const visibleGroupEntries = computed(() =>
  Object.entries(groupedUsers.value).filter(([, list]) => list.length > 0),
)

function editAccount(user: UserRecord) {
  router.visit(`/user-management/${user.id}/edit`)
}

function goBack() {
  router.visit('/user-management')
}

function viewAccount(user: UserRecord) {
  router.visit(`/user-management/${user.id}?source=drafts`)
}

function formatSentenceCase(value: string) {
  const text = String(value).toLowerCase()
  return text.charAt(0).toUpperCase() + text.slice(1)
}

function taskClass(task: AdditionalTaskType) {
  if (task === 'CHEMIST') {
    return 'bg-blue-50 text-black border border-blue-200'
  }

  return 'bg-cyan-50 text-black border border-cyan-200'
}

function approved(id: number) {
    router.patch(`/user-management/${id}/approve`, {}, {
        preserveScroll: true,
        onError: (errors) => {
            if (errors.incomplete) {
                missingFields.value = String(errors.incomplete)
                    .split(':')
                    .pop()
                    ?.split(',')
                    .map(field => field.trim()) ?? []

                showIncompleteDraftModal.value = true
            }
        }
    })
}

function disabled(id: number) {
  router.patch(`/user-management/${id}/disapprove`)
}

</script>

<template>
  <Head title="Draft Accounts" />

  <AppSidebarLayout :breadcrumbs="breadcrumbs">
    <div class="relative p-3 sm:p-4 md:p-5 lg:p-6">
      <div class="flex flex-col gap-3 sm:gap-4">
        <!-- HEADER -->
        <div class="sticky top-0 z-30 -mx-3 -mt-3 border-b border-zinc-200 bg-[#f3f4f6] px-3 pt-3 pb-3 sm:-mx-4 sm:-mt-4 sm:px-4 md:-mx-5 md:-mt-5 md:px-5 lg:-mx-6 lg:-mt-6 lg:px-6 lg:pt-5">
          <div class="flex flex-col gap-3">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
              <div>
                <h1 class="text-lg font-semibold text-zinc-900 sm:text-xl">
                  Draft Accounts
                </h1>
                <p class="text-xs text-zinc-500 sm:text-sm">
                  Total Accounts: {{ totalAccounts }}
                </p>
              </div>

              <button
                type="button"
                @click="goBack"
                class="inline-flex items-center justify-center gap-2 rounded-lg border border-zinc-200 bg-white px-3 py-2 text-xs font-medium text-zinc-800 transition hover:bg-zinc-50 sm:text-sm"
              >
                <ArrowLeft class="h-4 w-4" />
                Back to Accounts
              </button>
            </div>

            <div class="rounded-xl border border-zinc-200 bg-white p-3 shadow-sm">
              <div class="grid gap-3 md:grid-cols-3">
                <div class="relative md:col-span-2">
                  <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-400" />
                  <input
                    v-model="search"
                    type="text"
                    placeholder="Search name or email"
                    class="h-9 w-full rounded-lg border border-zinc-200 bg-white pl-10 pr-3 text-xs text-zinc-900 outline-none ring-0 placeholder:text-zinc-400 focus:border-zinc-400 sm:text-sm"
                  />
                </div>

                <select
                  v-model="selectedCategory"
                  class="h-9 rounded-lg border border-zinc-200 bg-white px-3 text-xs text-zinc-900 outline-none focus:border-zinc-400 sm:text-sm"
                >
                  <option value="ALL">All Categories</option>
                  <option value="ADMIN">Admin</option>
                  <option value="CHEMIST">Chemist</option>
                  <option value="AGRICULTURIST">Agriculturist</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <!-- TABLE GROUPS -->
        <div class="flex flex-col gap-3 pt-1">
          <div
            v-for="[groupName, groupUsers] in visibleGroupEntries"
            :key="groupName"
            class="overflow-hidden rounded-xl border border-zinc-200 border-t-[2px] border-t-[#0E3D1A] bg-white shadow-sm"
          >
            <div class="border-b border-zinc-200 px-4 py-2.5">
              <div class="flex items-center gap-3">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#0E3D1A] text-white">
                  <component
                    :is="groupName === 'CHEMIST' ? FlaskConical : groupName === 'ADMIN' ? Star : Tractor"
                    class="h-4 w-4"
                  />
                </div>

                <div>
                  <h2 class="text-sm font-semibold tracking-wide text-zinc-900 sm:text-base">
                    {{ groupName }}
                  </h2>
                  <p class="text-xs text-zinc-500">
                    {{ groupUsers.length }} account<span v-if="groupUsers.length > 1">s</span>
                  </p>
                </div>
              </div>
            </div>

            <div class="overflow-x-auto no-scrollbar">
              <table class="min-w-[880px] w-full table-fixed text-[13px]">
                <colgroup>
                  <col class="w-[26%]" />
                  <col class="w-[26%]" />
                  <col class="w-[22%]" />
                  <col class="w-[26%]" />
                </colgroup>

                <thead>
                  <tr class="border-b border-zinc-200">
                    <th class="px-3 py-2.5 text-left font-medium text-zinc-600">
                      Full Name
                    </th>
                    <th class="px-3 py-2.5 text-left font-medium text-zinc-600">
                      Email
                    </th>
                    <th class="px-3 py-2.5 text-left font-medium text-zinc-600">
                      Additional Task Type
                    </th>
                    <th class="px-3 py-2.5 text-center font-medium text-zinc-600">
                      Actions
                    </th>
                  </tr>
                </thead>

                <tbody>
                  <tr
                    v-for="user in groupUsers"
                    :key="user.id"
                    class="border-b border-zinc-100 last:border-b-0"
                  >
                    <td class="px-3 py-2.5 align-middle text-zinc-900">
                      <div class="break-words leading-5">
                        {{ buildFullName(user) || '—' }}
                      </div>
                    </td>

                    <td class="px-3 py-2.5 align-middle text-zinc-600">
                      <div class="break-words leading-5">
                        {{ user.email }}
                      </div>
                    </td>

                    <td class="px-3 py-2.5 align-middle">
                      <div v-if="user.tasks.length" class="flex flex-wrap items-center gap-1">
                        <span
                          v-for="task in user.tasks"
                          :key="task"
                          :class="taskClass(task)"
                          class="inline-flex shrink-0 whitespace-nowrap rounded-full px-2 py-[3px] text-[11px] font-semibold"
                        >
                          {{ formatSentenceCase(task) }}
                        </span>
                      </div>
                      <span v-else class="text-zinc-400">
                        —
                      </span>
                    </td>

                    <td class="px-3 py-2.5 align-middle">
                      <div class="flex min-w-max items-center justify-center gap-1.5">
                        <button
                          type="button"
                          @click="viewAccount(user)"
                          class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-md border border-sky-200 bg-sky-50 text-sky-700 transition hover:bg-sky-100"
                        >
                          <Eye class="h-3.5 w-3.5" />
                        </button>

                        <button
                          type="button"
                          @click="editAccount(user)"
                          class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-md border border-zinc-200 text-zinc-700 transition hover:bg-zinc-50"
                        >
                          <Pencil class="h-3.5 w-3.5" />
                        </button>

                        <button
                          type="button"
                          @click="approved(user.id)"
                          class="inline-flex h-7 shrink-0 items-center gap-1 rounded-md bg-emerald-600 px-2.5 text-[11px] font-medium text-white transition hover:bg-emerald-700"
                        >
                          <ShieldCheck class="h-3.5 w-3.5" />
                          Approve
                        </button>

                        <button
                          type="button"
                          @click="disabled(user.id)"
                          class="inline-flex h-7 shrink-0 items-center gap-1 rounded-md bg-rose-600 px-2.5 text-[11px] font-medium text-white transition hover:bg-rose-700"
                        >
                          <ShieldBan class="h-3.5 w-3.5" />
                          Disapprove
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="border-t border-zinc-200 px-4 py-2 text-xs text-zinc-500">
              Showing {{ groupUsers.length }} user(s)
            </div>
          </div>

          <div
            v-if="visibleGroupEntries.length === 0"
            class="rounded-xl border border-dashed border-zinc-300 bg-white px-5 py-8 text-center text-sm text-zinc-500"
          >
            No draft users found.
          </div>
        </div>
      </div>
    </div>

    <div
        v-if="showIncompleteDraftModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4"
      >
        <div
          class="w-full max-w-md overflow-hidden rounded-2xl border border-amber-200 bg-white shadow-xl dark:border-amber-500/30 dark:bg-[#111111]"
        >
          <!-- Header -->
          <div
            class="flex items-center gap-3 border-b border-amber-200 bg-amber-50 px-6 py-4 dark:border-amber-500/30 dark:bg-amber-500/10"
          >
            <div
              class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-100 text-amber-600 dark:bg-amber-500/15 dark:text-amber-400"
            >
              <TriangleAlert class="h-5 w-5" />
            </div>

            <h2 class="text-base font-semibold text-zinc-900 dark:text-white">
              Draft Cannot Be Approved
            </h2>
          </div>

          <!-- Body -->
          <div class="px-6 py-5">
            <p class="text-sm leading-6 text-zinc-700 dark:text-zinc-300">
              This draft cannot be approved because some required information is still
              missing.
            </p>

            <div
              class="mt-4 rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-500/30 dark:bg-amber-500/10"
            >
              <p class="mb-2 text-sm font-semibold text-amber-700 dark:text-amber-300">
                Missing Required Fields
              </p>

              <ul class="list-disc pl-5 text-sm leading-6 text-zinc-700 dark:text-zinc-300">
                <li v-for="field in missingFields" :key="field">
                  {{ field }}
                </li>
              </ul>
            </div>
          </div>

          <!-- Footer -->
          <div
            class="flex justify-end border-t border-zinc-200 px-6 py-4 dark:border-zinc-800"
          >
            <button
              type="button"
              @click="showIncompleteDraftModal = false"
              class="rounded-lg bg-amber-500 px-5 py-2 text-sm font-semibold text-white transition hover:bg-amber-600"
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
