<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue'
import { Head } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import type { BreadcrumbItem } from '@/types'
import {
  Search,
  Star,
  FlaskConical,
  Tractor,
  FileDigit,
  List,
  Clock,
} from 'lucide-vue-next'

type AccountCategory = 'ADMIN' | 'CHEMIST' | 'AGRICULTURIST'
type AccountFilter = 'ALL' | AccountCategory
type TimeFilter = 'all' | '1day' | '3days' | '1week' | '1month'

type UserSubmission = {
  id: string
  name: string
  category: AccountCategory
  additionalTasks: AccountCategory[]
  lastSubmittedCount: number
  lastSubmitted: Date
}

type GroupedUserItem = {
  user: UserSubmission
  isMainTask: boolean
}

type VisibleGroup = [AccountCategory, GroupedUserItem[]]

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'User Dashboard',
    href: '/user-dashboard',
  },
]

function generateMockUsers(): UserSubmission[] {
  const now = new Date()

  return [
    {
      id: '1',
      name: 'Alyssa Kate Santiago',
      category: 'ADMIN',
      additionalTasks: ['CHEMIST', 'AGRICULTURIST'],
      lastSubmittedCount: 8,
      lastSubmitted: new Date(now.getTime() - 5 * 60 * 1000),
    },
    {
      id: '2',
      name: 'Joan Sanchez',
      category: 'ADMIN',
      additionalTasks: ['AGRICULTURIST'],
      lastSubmittedCount: 12,
      lastSubmitted: new Date(now.getTime() - 1 * 60 * 60 * 1000),
    },
    {
      id: '3',
      name: 'Robert John Martinez',
      category: 'CHEMIST',
      additionalTasks: ['AGRICULTURIST'],
      lastSubmittedCount: 5,
      lastSubmitted: new Date(now.getTime() - 97 * 60 * 1000),
    },
    {
      id: '4',
      name: 'James Miranda',
      category: 'CHEMIST',
      additionalTasks: [],
      lastSubmittedCount: 3,
      lastSubmitted: new Date(now.getTime() - 172 * 60 * 1000),
    },
    {
      id: '5',
      name: 'Maria Santos',
      category: 'AGRICULTURIST',
      additionalTasks: ['CHEMIST'],
      lastSubmittedCount: 6,
      lastSubmitted: new Date(now.getTime() - 3 * 60 * 60 * 1000),
    },
    {
      id: '6',
      name: 'Carlos Reyes',
      category: 'AGRICULTURIST',
      additionalTasks: [],
      lastSubmittedCount: 4,
      lastSubmitted: new Date(now.getTime() - 6 * 60 * 60 * 1000),
    },
    {
      id: '7',
      name: 'Lisa Cruz',
      category: 'CHEMIST',
      additionalTasks: [],
      lastSubmittedCount: 2,
      lastSubmitted: new Date(now.getTime() - 1 * 24 * 60 * 60 * 1000),
    },
    {
      id: '8',
      name: 'Mark Johnson',
      category: 'ADMIN',
      additionalTasks: ['CHEMIST'],
      lastSubmittedCount: 15,
      lastSubmitted: new Date(now.getTime() - 30 * 60 * 1000),
    },
    {
      id: '9',
      name: 'Sarah Williams',
      category: 'AGRICULTURIST',
      additionalTasks: [],
      lastSubmittedCount: 7,
      lastSubmitted: new Date(now.getTime() - 2 * 60 * 60 * 1000),
    },
    {
      id: '10',
      name: 'David Brown',
      category: 'ADMIN',
      additionalTasks: [],
      lastSubmittedCount: 10,
      lastSubmitted: new Date(now.getTime() - 15 * 60 * 1000),
    },
    {
      id: '11',
      name: 'Emily Davis',
      category: 'CHEMIST',
      additionalTasks: ['AGRICULTURIST'],
      lastSubmittedCount: 9,
      lastSubmitted: new Date(now.getTime() - 4 * 24 * 60 * 60 * 1000),
    },
  ]
}

function getSeededUsers(): UserSubmission[] {
  return [
    {
      id: 'seed-1',
      name: 'Test User',
      category: 'ADMIN',
      additionalTasks: [],
      lastSubmittedCount: 0,
      lastSubmitted: new Date(),
    },
    {
      id: 'seed-2',
      name: 'Ean Test',
      category: 'ADMIN',
      additionalTasks: [],
      lastSubmittedCount: 0,
      lastSubmitted: new Date(),
    },
  ]
}

const USERS_STORAGE_KEY = 'rsl-users'

function buildFullNameFromStoredUser(user: any) {
  const base = [user.surname, user.givenname, user.middlename]
    .filter(Boolean)
    .join(', ')
    .replace(', ,', ',')

  return user.suffix ? `${base} ${user.suffix}` : base
}

function getStoredCreatedUsers(): UserSubmission[] {
  try {
    const raw = localStorage.getItem(USERS_STORAGE_KEY)
    if (!raw) return []

    const parsed = JSON.parse(raw)
    if (!Array.isArray(parsed)) return []

    return parsed
      .filter((user) => user.status === 'ACTIVE')
      .map((user) => ({
        id: `stored-${user.id}`,
        name: buildFullNameFromStoredUser(user),
        category: user.category,
        additionalTasks: user.tasks ?? [],
        lastSubmittedCount: 0,
        lastSubmitted: new Date(),
      }))
  } catch {
    return []
  }
}

function getRelativeTime(date: Date): string {
  const now = new Date()
  const diffInMs = now.getTime() - date.getTime()
  const diffInMins = Math.floor(diffInMs / (1000 * 60))
  const diffInHours = Math.floor(diffInMs / (1000 * 60 * 60))
  const diffInDays = Math.floor(diffInMs / (1000 * 60 * 60 * 24))
  const diffInMonths = Math.floor(diffInDays / 30)
  const diffInYears = Math.floor(diffInDays / 365)

  if (diffInMins < 1) return 'just now'
  if (diffInMins < 60) return `${diffInMins} min${diffInMins > 1 ? 's' : ''} ago`

  if (diffInHours < 24) {
    const remainingMins = diffInMins % 60
    if (remainingMins > 0 && diffInHours < 3) {
      return `${diffInHours}hr ${remainingMins}min${remainingMins > 1 ? 's' : ''} ago`
    }
    return `${diffInHours} hr${diffInHours > 1 ? 's' : ''} ago`
  }

  if (diffInDays < 30) return `${diffInDays} day${diffInDays > 1 ? 's' : ''} ago`
  if (diffInMonths < 12) return `${diffInMonths} month${diffInMonths > 1 ? 's' : ''} ago`
  return `${diffInYears} year${diffInYears > 1 ? 's' : ''} ago`
}

const allUsers = ref<UserSubmission[]>([
  ...generateMockUsers(),
  ...getStoredCreatedUsers(),
  ...getSeededUsers(),
])

const accountFilter = ref<AccountFilter>('ALL')
const timeFilter = ref<TimeFilter>('all')
const searchQuery = ref('')

const filteredUsers = computed(() => {
  const now = new Date()

  return allUsers.value.filter((user) => {
    if (accountFilter.value !== 'ALL') {
      const matchesMainCategory = user.category === accountFilter.value
      const matchesAdditionalTask = user.additionalTasks.includes(accountFilter.value)

      if (!matchesMainCategory && !matchesAdditionalTask) return false
    }

    if (searchQuery.value.trim()) {
      const query = searchQuery.value.toLowerCase()
      if (!user.name.toLowerCase().includes(query)) return false
    }

    const timeDiff = now.getTime() - user.lastSubmitted.getTime()
    const daysDiff = timeDiff / (1000 * 60 * 60 * 24)

    switch (timeFilter.value) {
      case '1day':
        return daysDiff <= 1
      case '3days':
        return daysDiff <= 3
      case '1week':
        return daysDiff <= 7
      case '1month':
        return daysDiff <= 30
      default:
        return true
    }
  })
})

const groupedUsers = computed<Record<AccountCategory, GroupedUserItem[]>>(() => {
  const groups: Record<AccountCategory, GroupedUserItem[]> = {
    ADMIN: [],
    CHEMIST: [],
    AGRICULTURIST: [],
  }

  filteredUsers.value.forEach((user) => {
    groups[user.category].push({ user, isMainTask: true })

    user.additionalTasks.forEach((task) => {
      groups[task].push({ user, isMainTask: false })
    })
  })

  ;(Object.keys(groups) as AccountCategory[]).forEach((category) => {
    groups[category].sort((a, b) => {
      if (a.isMainTask && !b.isMainTask) return -1
      if (!a.isMainTask && b.isMainTask) return 1
      return a.user.name.toLowerCase().localeCompare(b.user.name.toLowerCase())
    })
  })

  return groups
})

const visibleGroups = computed<VisibleGroup[]>(() => {
  const groups: VisibleGroup[] = (Object.keys(groupedUsers.value) as AccountCategory[])
    .map((category) => [category, groupedUsers.value[category]] as VisibleGroup)
    .filter(([, list]) => list.length > 0)

  if (accountFilter.value === 'ALL') return groups

  return groups.filter(([category]) => category === accountFilter.value)
})

const userStats = computed(() => {
  const totalSubmissions = filteredUsers.value.reduce(
    (sum, user) => sum + user.lastSubmittedCount,
    0,
  )

  const mostRecentUser =
    filteredUsers.value.length > 0
      ? filteredUsers.value.reduce((latest, current) =>
          current.lastSubmitted > latest.lastSubmitted ? current : latest,
        )
      : null

  return {
    totalUsers: filteredUsers.value.length,
    totalSubmissions,
    mostRecentUser,
  }
})

function getCategoryIcon(category: AccountCategory) {
  if (category === 'ADMIN') return Star
  if (category === 'CHEMIST') return FlaskConical
  return Tractor
}
</script>

<template>
  <Head title="User Dashboard" />

  <AppSidebarLayout :breadcrumbs="breadcrumbs">
    <div class="relative p-3 sm:p-4 md:p-5 lg:p-6">
      <div class="flex flex-col gap-5 sm:gap-6">
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
          <div class="rounded-xl border border-zinc-200 border-l-4 border-l-[#0E3D1A] bg-white p-4 shadow-sm">
            <div class="flex items-center justify-between">
              <p class="text-xs text-zinc-500 sm:text-sm">Total Users</p>
              <FileDigit class="h-4 w-4 text-emerald-500 sm:h-5 sm:w-5" />
            </div>
            <div class="mt-3 text-2xl font-semibold text-zinc-900 sm:text-3xl">
              {{ userStats.totalUsers }}
            </div>
            <p class="mt-1 text-xs text-zinc-500">
              {{ accountFilter === 'ALL' ? 'All account types' : `${accountFilter.toLowerCase()} accounts` }}
            </p>
          </div>

          <div class="rounded-xl border border-zinc-200 border-l-4 border-l-[#0E3D1A] bg-white p-4 shadow-sm">
            <div class="flex items-center justify-between">
              <p class="text-xs text-zinc-500 sm:text-sm">Total Submissions</p>
              <List class="h-4 w-4 text-emerald-500 sm:h-5 sm:w-5" />
            </div>
            <div class="mt-3 text-2xl font-semibold text-zinc-900 sm:text-3xl">
              {{ userStats.totalSubmissions }}
            </div>
            <p class="mt-1 text-xs text-zinc-500">All time</p>
          </div>

          <div class="rounded-xl border border-zinc-200 border-l-4 border-l-[#0E3D1A] bg-white p-4 shadow-sm sm:col-span-2 lg:col-span-1">
            <div class="flex items-center justify-between">
              <p class="text-xs text-zinc-500 sm:text-sm">Last Activity</p>
              <Clock class="h-4 w-4 text-emerald-500 sm:h-5 sm:w-5" />
            </div>
            <template v-if="userStats.mostRecentUser">
              <div class="mt-3 text-xl font-semibold text-zinc-900 sm:text-2xl">
                {{ getRelativeTime(userStats.mostRecentUser.lastSubmitted) }}
              </div>
              <p class="mt-1 truncate text-xs text-zinc-500">
                {{ userStats.mostRecentUser.name }}
              </p>
            </template>
            <template v-else>
              <div class="mt-3 text-lg text-zinc-400">No activity</div>
            </template>
          </div>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-3 shadow-sm sm:p-4">
          <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
            <div class="flex flex-wrap items-center gap-2">
              <span class="mr-1 text-xs text-zinc-500 sm:text-sm">Filter by:</span>

              <button type="button" @click="accountFilter = 'ALL'" class="rounded-full px-3 py-1.5 text-xs transition sm:text-sm" :class="accountFilter === 'ALL' ? 'bg-[#0E3D1A] text-white shadow-sm' : 'bg-zinc-200 text-zinc-700 hover:bg-zinc-300'">
                All Accounts
              </button>

              <button type="button" @click="accountFilter = 'ADMIN'" class="rounded-full px-3 py-1.5 text-xs transition sm:text-sm" :class="accountFilter === 'ADMIN' ? 'bg-[#0E3D1A] text-white shadow-sm' : 'bg-zinc-200 text-zinc-700 hover:bg-zinc-300'">
                Admin
              </button>

              <button type="button" @click="accountFilter = 'CHEMIST'" class="rounded-full px-3 py-1.5 text-xs transition sm:text-sm" :class="accountFilter === 'CHEMIST' ? 'bg-[#0E3D1A] text-white shadow-sm' : 'bg-zinc-200 text-zinc-700 hover:bg-zinc-300'">
                Chemist
              </button>

              <button type="button" @click="accountFilter = 'AGRICULTURIST'" class="rounded-full px-3 py-1.5 text-xs transition sm:text-sm" :class="accountFilter === 'AGRICULTURIST' ? 'bg-[#0E3D1A] text-white shadow-sm' : 'bg-zinc-200 text-zinc-700 hover:bg-zinc-300'">
                Agriculturist
              </button>
            </div>

            <div class="flex w-full flex-col gap-2 sm:flex-row xl:w-auto">
              <select v-model="timeFilter" class="h-10 rounded-lg border border-zinc-200 bg-white px-3 text-xs text-zinc-900 outline-none focus:border-zinc-400 sm:text-sm">
                <option value="all">All Time</option>
                <option value="1day">Last 24 Hours</option>
                <option value="3days">Last 3 Days</option>
                <option value="1week">Last Week</option>
                <option value="1month">Last Month</option>
              </select>

              <div class="relative min-w-0 flex-1 sm:w-72">
                <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-400" />
                <input v-model="searchQuery" type="text" placeholder="Search users..." class="h-10 w-full rounded-lg border border-zinc-200 bg-white pl-10 pr-3 text-xs text-zinc-900 outline-none focus:border-zinc-400 sm:text-sm" />
              </div>
            </div>
          </div>
        </div>

        <div class="flex flex-col gap-4">
          <template v-for="[groupName, groupUsers] in visibleGroups" :key="groupName">
            <div class="rounded-2xl bg-[#0E3D1A] p-[2px] shadow-sm">
              <div class="overflow-hidden rounded-[15px] bg-white">
                <div class="border-b border-zinc-200 px-4 py-3">
                  <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#0E3D1A] text-white">
                      <component :is="getCategoryIcon(groupName)" class="h-4 w-4" />
                    </div>
                    <div>
                      <h2 class="text-base font-semibold tracking-wide text-zinc-900 sm:text-lg">
                        {{ groupName }}
                      </h2>
                      <p class="text-xs text-zinc-500 sm:text-sm">
                        {{ groupUsers.length }} account<span v-if="groupUsers.length > 1">s</span>
                      </p>
                    </div>
                  </div>
                </div>

                <!-- MOBILE / TABLET CARDS -->
                <div class="grid gap-3 p-4 lg:hidden">
                  <div
                    v-for="{ user, isMainTask } in groupUsers"
                    :key="`${user.id}-${groupName}-card`"
                    class="rounded-xl border border-zinc-200 bg-white p-4"
                  >
                    <div class="flex items-start gap-2">
                      <span
                        class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-md"
                        :class="!isMainTask ? 'bg-[#0E3D1A] p-1 text-white' : 'bg-transparent'"
                        :title="!isMainTask ? `Main task: ${user.category}` : ''"
                      >
                        <component
                          v-if="!isMainTask"
                          :is="getCategoryIcon(user.category)"
                          class="h-3.5 w-3.5"
                        />
                      </span>

                      <div class="min-w-0 flex-1">
                        <p class="break-words text-sm font-semibold text-zinc-900">
                          {{ user.name }}
                        </p>
                        <p class="mt-1 text-xs text-zinc-500">
                          Last submitted: {{ getRelativeTime(user.lastSubmitted) }}
                        </p>
                        <p class="mt-1 text-xs text-zinc-500">
                          Submitted: {{ user.lastSubmittedCount }} task<span v-if="user.lastSubmittedCount > 1">s</span>
                        </p>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- DESKTOP TABLE -->
                <div class="hidden lg:block">
                  <table class="w-full table-fixed text-sm">
                    <colgroup>
                      <col class="w-[50%]" />
                      <col class="w-[25%]" />
                      <col class="w-[25%]" />
                    </colgroup>
                    <thead>
                      <tr class="border-b border-zinc-200">
                        <th class="px-4 py-3 text-left font-semibold text-zinc-600">
                          <div class="flex items-center gap-2">
                            <span class="h-5 w-5 shrink-0"></span>
                            <span>Name</span>
                          </div>
                        </th>
                        <th class="px-3 py-3 text-left font-semibold text-zinc-600">Last Submitted</th>
                        <th class="px-3 py-3 text-left font-semibold text-zinc-600">Tasks Submitted</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr
                        v-for="{ user, isMainTask } in groupUsers"
                        :key="`${user.id}-${groupName}`"
                        class="border-b border-zinc-100 last:border-b-0"
                      >
                        <td class="px-4 py-3 align-middle text-zinc-900">
                          <div class="flex items-center gap-2 leading-5">
                            <span
                              class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-md"
                              :class="!isMainTask ? 'bg-[#0E3D1A] p-1 text-white' : 'bg-transparent'"
                              :title="!isMainTask ? `Main task: ${user.category}` : ''"
                            >
                              <component
                                v-if="!isMainTask"
                                :is="getCategoryIcon(user.category)"
                                class="h-3.5 w-3.5"
                              />
                            </span>

                            <span class="break-words">{{ user.name }}</span>
                          </div>
                        </td>
                        <td class="px-3 py-3 align-middle text-zinc-900">
                          {{ getRelativeTime(user.lastSubmitted) }}
                        </td>
                        <td class="px-3 py-3 align-middle text-zinc-900">
                          {{ user.lastSubmittedCount }} task<span v-if="user.lastSubmittedCount > 1">s</span>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>

                <div class="border-t border-zinc-200 px-4 py-2 text-xs text-zinc-500 sm:text-sm">
                  Showing {{ groupUsers.length }} user<span v-if="groupUsers.length > 1">s</span>
                </div>
              </div>
            </div>
          </template>

          <div
            v-if="visibleGroups.length === 0"
            class="rounded-xl border border-dashed border-zinc-300 bg-white px-5 py-8 text-center text-sm text-zinc-500"
          >
            No users found for the selected filters.
          </div>
        </div>
      </div>
    </div>
  </AppSidebarLayout>
</template>