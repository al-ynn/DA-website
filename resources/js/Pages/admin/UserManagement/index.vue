<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import {
  BadgePlus,
  Pencil,
  Search,
  ShieldBan,
  FileText,
  Star,
  FlaskConical,
  Tractor,
  Eye,
} from 'lucide-vue-next'
import { computed, ref } from 'vue'
import AppSidebarLayout from '@/Layouts/app/AppSidebarLayout.vue'
import type { BreadcrumbItem } from '@/types'

type UserCategory = 'ADMIN' | 'CHEMIST' | 'AGRICULTURIST'
type AdditionalTaskType = 'CHEMIST' | 'AGRICULTURIST'
type AccountStatus = 'ACTIVE' | 'DISABLED' | 'PENDING' | 'DRAFT'

type UserRecord = {
  id: number

  surname: string
  givenname: string
  middlename: string
  suffix: string

  sex: 'Male' | 'Female'
  birthdate: string
  contactNumber: string

  email: string

  category: UserCategory
  tasks: AdditionalTaskType[]

  status: AccountStatus

  password?: string
}

interface DatabaseUser {
  id: number

  first_name: string
  middle_name: string | null
  last_name: string
  suffix: string | null

  sex: 'Male' | 'Female'
  birthdate: string
  contact_number: string

  email: string

  role: 'admin' | 'chemist' | 'agriculturist'
  additional_tasks: ('chemist' | 'agriculturist')[] | null

  is_disabled: boolean

  created_at: string
}

const props = defineProps<{
  users: DatabaseUser[]
}>()

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'User Management',
    href: '/user-management',
  },
]

const search = ref('')
const selectedCategory = ref<'ALL' | UserCategory>('ALL')


const users = computed<UserRecord[]>(() =>
  props.users.map((user) => ({
    id: user.id,

    surname: user.last_name ?? '',
    givenname: user.first_name ?? '',
    middlename: user.middle_name ?? '',
    suffix: user.suffix ?? '',

    email: user.email ?? '',

    sex: user.sex,
    birthdate: user.birthdate,
    contactNumber: user.contact_number,

    category:
      user.role === 'admin'
        ? 'ADMIN'
        : user.role === 'chemist'
          ? 'CHEMIST'
          : 'AGRICULTURIST',

    tasks: (user.additional_tasks ?? []).map(task =>
      task === 'chemist'
        ? 'CHEMIST'
        : 'AGRICULTURIST'
    ),

    status: user.is_disabled ? 'DISABLED' : 'ACTIVE',
  }))

)

function buildFullName(user: UserRecord) {
  const base = [
    (user.surname ?? '').trim(),
    (user.givenname ?? '').trim(),
    (user.middlename ?? '').trim(),
  ]
    .filter(Boolean)
    .join(', ')
    .replace(', ,', ',')

  return (user.suffix ?? '').trim()
    ? `${base} ${(user.suffix ?? '').trim()}`
    : base
}

const filteredUsers = computed(() => {
  const filtered = users.value.filter((user) => {
    if (user.status === 'DISABLED' || user.status === 'PENDING' || user.status === 'DRAFT') {
      return false
    }

    const fullName = buildFullName(user).toLowerCase()
    const searchValue = search.value.toLowerCase()

    const matchesSearch =
      fullName.includes(searchValue) ||
      (user.email ?? '').toLowerCase().includes(searchValue)

    const matchesCategory =
      selectedCategory.value === 'ALL' || user.category === selectedCategory.value

    return matchesSearch && matchesCategory
  })

  return [...filtered].sort((a, b) => {
    const surnameCompare = (a.surname ?? '').toLowerCase().localeCompare((b.surname ?? '').toLowerCase())
    if (surnameCompare !== 0) return surnameCompare

    const givenCompare = (a.givenname ?? '').toLowerCase().localeCompare((b.givenname ?? '').toLowerCase())
    if (givenCompare !== 0) return givenCompare

    const middleCompare = (a.middlename ?? '').toLowerCase().localeCompare((b.middlename ?? '').toLowerCase())
    if (middleCompare !== 0) return middleCompare

    return (a.suffix ?? '').toLowerCase().localeCompare((b.suffix ?? '').toLowerCase())
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

function goToDisabledAccounts() {
  router.visit('/user-management/disabled')
}

function goToDraftAccounts() {
  router.visit('/user-management/drafts')
}

function disableAccount(id: number) {
  router.patch(`/user-management/${id}/disable`)
}

function editAccount(user: UserRecord) {
  router.visit(`/user-management/${user.id}/edit`)
}

function viewAccount(user: UserRecord) {
  router.visit(`/user-management/${user.id}?source=main`)
}

function createAccount() {
  router.visit('/user-management/create')
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
</script>

<template>
  <Head title="User Management" />

  <AppSidebarLayout :breadcrumbs="breadcrumbs">
    <div class="relative p-3 sm:p-4 md:p-5 lg:p-6">
      <div class="flex flex-col gap-3 sm:gap-4">
        <!-- HEADER -->
        <div class="sticky top-0 z-30 -mx-3 -mt-3 border-b border-border bg-background px-3 pt-3 pb-3 sm:-mx-4 sm:-mt-4 sm:px-4 md:-mx-5 md:-mt-5 md:px-5 lg:-mx-6 lg:-mt-6 lg:px-6 lg:pt-5">
          <div class="flex flex-col gap-3">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
              <div>
                <h1 class="text-lg font-semibold text-foreground sm:text-xl">
                  Active Accounts
                </h1>
                <p class="text-xs text-muted-foreground sm:text-sm">
                  Total Accounts: {{ totalAccounts }}
                </p>
              </div>

              <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap">
                <button type="button" @click="goToDraftAccounts" class="inline-flex items-center justify-center gap-2 rounded-lg bg-amber-500 px-3 py-2 text-xs font-medium text-white transition hover:bg-amber-600 sm:text-sm">
                  <FileText class="h-4 w-4" />
                  Draft List
                </button>

                <button type="button" @click="goToDisabledAccounts" class="inline-flex items-center justify-center gap-2 rounded-lg bg-rose-600 px-3 py-2 text-xs font-medium text-white transition hover:bg-rose-700 sm:text-sm">
                  <ShieldBan class="h-4 w-4" />
                  Disabled Accounts
                </button>

                <button type="button" @click="createAccount" class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#16a34a] px-3 py-2 text-xs font-semibold text-white transition hover:bg-[#15803d] sm:text-sm">
                  <BadgePlus class="h-4 w-4" />
                  Create Account
                </button>
              </div>
            </div>

            <div class="rounded-xl border border-border bg-card p-3 text-card-foreground shadow-sm">
              <div class="grid gap-3 md:grid-cols-3">
                <div class="relative md:col-span-2">
                  <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                  <input
                    v-model="search"
                    type="text"
                    placeholder="Search name or email"
                    class="h-9 w-full rounded-lg border border-input bg-background pl-10 pr-3 text-xs text-foreground outline-none ring-0 placeholder:text-muted-foreground focus:border-ring sm:text-sm"
                  />
                </div>

                <select
                  v-model="selectedCategory"
                  class="h-9 rounded-lg border border-input bg-background px-3 text-xs text-foreground outline-none focus:border-ring sm:text-sm"
                >
                  <option value="ALL">All Roles (Main Task)</option>
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
            class="overflow-hidden rounded-xl border border-zinc-200 border-t-[2px] border-t-[#0E3D1A] bg-white shadow-md"
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
              <table class="min-w-[780px] w-full table-fixed text-[13px]">
                <colgroup>
                  <col class="w-[28%]" />
                  <col class="w-[30%]" />
                  <col class="w-[25%]" />
                  <col class="w-[17%]" />
                </colgroup>

                <thead>
                  <tr class="border-b border-zinc-200">
                    <th class="px-3 py-2.5 text-left font-medium text-zinc-600">Name</th>
                    <th class="px-3 py-2.5 text-left font-medium text-zinc-600">Email</th>
                    <th class="px-3 py-2.5 text-left font-medium text-zinc-600">Additional Task Type</th>
                    <th class="px-3 py-2.5 text-center font-medium text-zinc-600">Actions</th>
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
                      <span v-else class="text-zinc-400">—</span>
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
                          @click="disableAccount(user.id)"
                          class="inline-flex h-7 shrink-0 items-center gap-1 rounded-md bg-rose-600 px-2.5 text-[11px] font-medium text-white transition hover:bg-rose-700"
                        >
                          <ShieldBan class="h-3.5 w-3.5" />
                          Disable
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
            No users found for the selected filters.
          </div>
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
