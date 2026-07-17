<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { computed } from 'vue'
import type { BreadcrumbItem } from '@/types'
import { ArrowLeft, Eye } from 'lucide-vue-next'

type UserCategory = 'ADMIN' | 'CHEMIST' | 'AGRICULTURIST'
type AdditionalTaskType = 'CHEMIST' | 'AGRICULTURIST'
type SexType = 'MALE' | 'FEMALE'
type ViewSource = 'main' | 'disabled' | 'drafts'

type UserRecord = {
  id: number
  surname: string
  givenname: string
  middlename?: string | null
  suffix?: string | null
  email: string
  sex?: SexType
  birthdate?: string
  contactnumber?: string
  category: UserCategory
  roles?: string[]
  tasks: AdditionalTaskType[]
  status?: 'ACTIVE' | 'DISABLED' | 'PENDING' | 'DRAFT'
  password?: string
}

const props = defineProps<{
  user: UserRecord
  source?: ViewSource
}>()

const storedUser = computed<UserRecord>(() => ({
  id: (props.user as any).id,

  surname: (props.user as any).last_name,
  givenname: (props.user as any).first_name,
  middlename: (props.user as any).middle_name,
  suffix: (props.user as any).suffix,

  email: (props.user as any).email,

  sex: (props.user as any).sex,

  birthdate: (props.user as any).birthdate,

  contactnumber: (props.user as any).contact_number,

  category:
    (props.user as any).role === 'admin'
      ? 'ADMIN'
      : (props.user as any).role === 'chemist'
        ? 'CHEMIST'
        : 'AGRICULTURIST',

  tasks: (((props.user as any).additional_tasks ?? []) as string[]).map(
    (task) =>
      task === 'chemist'
        ? 'CHEMIST'
        : 'AGRICULTURIST',
  ) as AdditionalTaskType[],

  status: (props.user as any).is_disabled
    ? 'DISABLED'
    : 'ACTIVE',
}))

const currentSource = computed<ViewSource>(() => props.source ?? 'main')

const breadcrumbs = computed<BreadcrumbItem[]>(() => {
  const items: BreadcrumbItem[] = [
    {
      title: 'User Management',
      href: '/user-management',
    },
  ]

  if (currentSource.value === 'disabled') {
    items.push({
      title: 'Disabled Accounts',
      href: '/user-management/disabled',
    })
  }

  if (currentSource.value === 'drafts') {
    items.push({
      title: 'Draft Accounts',
      href: '/user-management/drafts',
    })
  }

  items.push({
    title: 'View Account',
    href: `/user-management/${storedUser.value.id}`,
  })

  return items
})

const fullName = computed(() => {
  const user = storedUser.value

  const base = [
    user.surname?.trim(),
    user.givenname?.trim(),
    user.middlename?.trim(),
  ]
    .filter(Boolean)
    .join(', ')
    .replace(', ,', ',')

  return user.suffix?.trim() ? `${base} ${user.suffix.trim()}` : base
})

function goBack() {
  if (currentSource.value === 'disabled') {
    router.visit('/user-management/disabled')
    return
  }

  if (currentSource.value === 'drafts') {
    router.visit('/user-management/drafts')
    return
  }

  router.visit('/user-management')
}

function formatSentenceCase(value?: string) {
  if (!value) return '—'
  const text = String(value).toLowerCase()
  return text.charAt(0).toUpperCase() + text.slice(1)
}

function formatDate(value?: string) {
  if (!value) return '—'

  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return value

  return date.toLocaleDateString('en-CA', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  })
}

function formatPhilippineMobile(value?: string) {
  if (!value) return '—'

  const digits = String(value).replace(/\D/g, '')
  let normalized = digits

  if (normalized.startsWith('639')) {
    normalized = normalized.slice(2)
  } else if (normalized.startsWith('09')) {
    normalized = normalized.slice(1)
  } else if (normalized.startsWith('63')) {
    normalized = normalized.slice(2)
  }

  if (!normalized || !normalized.startsWith('9')) {
    return value
  }

  normalized = normalized.slice(0, 10)

  let formatted = '+63'

  if (normalized.length > 0) formatted += ` ${normalized.slice(0, 3)}`
  if (normalized.length > 3) formatted += ` ${normalized.slice(3, 6)}`
  if (normalized.length > 6) formatted += ` ${normalized.slice(6, 10)}`

  return formatted
}

function taskClass(task: AdditionalTaskType) {
  if (task === 'CHEMIST') {
    return 'bg-blue-50 text-black border border-blue-200'
  }

  return 'bg-cyan-50 text-black border border-cyan-200'
}
</script>

<template>
  <Head title="View Account" />

  <AppSidebarLayout :breadcrumbs="breadcrumbs">
    <div class="p-4 md:p-6">
      <div class="mx-auto max-w-6xl">
        <div class="mb-5 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
          <div class="flex items-start gap-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#0E3D1A] text-white">
              <Eye class="h-6 w-6" />
            </div>

            <div>
              <h1 class="text-xl font-semibold text-zinc-900">
                View Account
              </h1>
              <p class="text-sm text-zinc-500">
                Full details of the selected account.
              </p>
            </div>
          </div>

          <button
            type="button"
            @click="goBack"
            class="inline-flex items-center gap-2 rounded-lg border border-zinc-200 bg-white px-4 py-2 text-sm font-medium text-zinc-800 transition hover:bg-zinc-50"
          >
            <ArrowLeft class="h-4 w-4" />
            Back
          </button>
        </div>

        <div class="grid gap-5">
          <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-base font-semibold text-zinc-900">
              Personal Details
            </h2>

            <div class="space-y-5">
              <div class="grid gap-5 md:grid-cols-4">
                <div>
                  <p class="mb-1 text-sm font-medium text-zinc-500">Surname</p>
                  <p class="text-sm text-zinc-900">{{ storedUser.surname || '—' }}</p>
                </div>

                <div>
                  <p class="mb-1 text-sm font-medium text-zinc-500">Given Name</p>
                  <p class="text-sm text-zinc-900">{{ storedUser.givenname || '—' }}</p>
                </div>

                <div>
                  <p class="mb-1 text-sm font-medium text-zinc-500">Middle Name</p>
                  <p class="text-sm text-zinc-900">{{ storedUser.middlename || '—' }}</p>
                </div>

                <div>
                  <p class="mb-1 text-sm font-medium text-zinc-500">Suffix</p>
                  <p class="text-sm text-zinc-900">{{ storedUser.suffix || '—' }}</p>
                </div>
              </div>

              <div>
                <p class="mb-1 text-sm font-medium text-zinc-500">Full Name</p>
                <p class="text-sm text-zinc-900">{{ fullName || '—' }}</p>
              </div>

              <div class="grid gap-5 md:grid-cols-2">
                <div>
                  <p class="mb-1 text-sm font-medium text-zinc-500">Sex</p>
                  <p class="text-sm text-zinc-900">
                    {{ formatSentenceCase(storedUser.sex) }}
                  </p>
                </div>

                <div>
                  <p class="mb-1 text-sm font-medium text-zinc-500">Birthdate</p>
                  <p class="text-sm text-zinc-900">
                    {{ formatDate(storedUser.birthdate) }}
                  </p>
                </div>
              </div>

              <div class="grid gap-5 md:grid-cols-2">
                <div>
                  <p class="mb-1 text-sm font-medium text-zinc-500">Email</p>
                  <p class="text-sm text-zinc-900">{{ storedUser.email || '—' }}</p>
                </div>

                <div>
                  <p class="mb-1 text-sm font-medium text-zinc-500">Contact Number</p>
                  <p class="text-sm text-zinc-900">
                    {{ formatPhilippineMobile(storedUser.contactnumber) }}
                  </p>
                </div>
              </div>
            </div>
          </div>

          <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm">
            <div class="grid gap-4 md:grid-cols-2">
              <div>
                <p class="mb-1 text-sm font-medium text-zinc-500">Role (Main Task)</p>
                <p class="text-sm text-zinc-900">
                  {{ formatSentenceCase(storedUser.category) }}
                </p>
              </div>

              <div>
                <p class="mb-1 text-sm font-medium text-zinc-500">Status</p>
                <p class="text-sm text-zinc-900">
                  {{ storedUser.status || '—' }}
                </p>
              </div>

              <div class="md:col-span-2">
                <p class="mb-1 text-sm font-medium text-zinc-500">Additional Task Type</p>
                <div class="flex flex-wrap gap-1.5">
                  <template v-if="(storedUser.tasks ?? []).length">
                    <span
                      v-for="task in storedUser.tasks"
                      :key="task"
                      :class="taskClass(task)"
                      class="inline-flex rounded-full px-2 py-0.5 text-[11px] font-semibold"
                    >
                      {{ formatSentenceCase(task) }}
                    </span>
                  </template>
                  <span v-else class="text-xs text-zinc-400">None</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppSidebarLayout>
</template>