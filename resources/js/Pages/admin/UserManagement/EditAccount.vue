<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import type { BreadcrumbItem } from '@/types'
import { ArrowLeft, Save, X, CircleCheckBig, TriangleAlert } from 'lucide-vue-next'

type UserCategory = 'ADMIN' | 'CHEMIST' | 'AGRICULTURIST'
type AccountStatus = 'ACTIVE' | 'DISABLED'
type AdditionalTaskType = 'CHEMIST' | 'AGRICULTURIST'
type SexType = 'MALE' | 'FEMALE'

type UserRecord = {
  id: number
  surname: string
  givenname: string
  middlename?: string | null
  suffix?: string | null
  email: string
  sex: SexType
  birthdate: string
  contactnumber: string
  category: UserCategory
  tasks: AdditionalTaskType[]
  status: 'ACTIVE' | 'DISABLED' | 'PENDING'
}

type FormField =
  | 'surname'
  | 'givenname'
  | 'email'
  | 'sex'
  | 'birthdate'
  | 'contactnumber'
  | 'category'
  | 'status'

const props = defineProps<{
  user: UserRecord
}>()

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'User Management',
    href: '/user-management',
  },
  {
    title: 'Edit Account',
    href: `/user-management/${(props.user as any).id}/edit`,
  },
]

function extractNormalizedMobile(value: string) {
  const digits = String(value || '').replace(/\D/g, '')
  let normalized = digits

  if (normalized.startsWith('639')) {
    normalized = normalized.slice(2)
  } else if (normalized.startsWith('09')) {
    normalized = normalized.slice(1)
  } else if (normalized.startsWith('63')) {
    normalized = normalized.slice(2)
  }

  if (normalized && !normalized.startsWith('9')) {
    normalized = `9${normalized.slice(0, 9)}`
  }

  return normalized.slice(0, 10)
}

function formatPhilippineMobile(value: string) {
  const normalized = extractNormalizedMobile(value)

  if (!normalized) return '+63'

  let formatted = '+63'

  if (normalized.length > 0) formatted += ` ${normalized.slice(0, 3)}`
  if (normalized.length > 3) formatted += ` ${normalized.slice(3, 6)}`
  if (normalized.length > 6) formatted += ` ${normalized.slice(6, 10)}`

  return formatted
}

function normalizePhoneForBackend(value: string) {
  const normalized = extractNormalizedMobile(value)
  return normalized ? `+63${normalized}` : '+63'
}

function validateEmail(email: string) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)
}

const form = ref({
  surname: (props.user as any).surname ?? (props.user as any).last_name ?? '',
  givenname: (props.user as any).givenname ?? (props.user as any).first_name ?? '',
  middlename: (props.user as any).middlename ?? (props.user as any).middle_name ?? '',
  suffix: (props.user as any).suffix ?? '',
  email: (props.user as any).email ?? '',
  sex: (props.user as any).sex ?? '',
  birthdate: (props.user as any).birthdate ?? '',
  contactnumber: formatPhilippineMobile(
    (props.user as any).contactnumber ??
      (props.user as any).contact_number ??
      '',
  ),

  category:
    (props.user as any).category ??
    (
      (props.user as any).role === 'admin'
        ? 'ADMIN'
        : (props.user as any).role === 'chemist'
          ? 'CHEMIST'
          : 'AGRICULTURIST'
    ),

  tasks: (
    ((props.user as any).tasks ??
      (props.user as any).additional_tasks ??
      []) as string[]
  ).map((task) =>
    task === 'chemist'
      ? 'CHEMIST'
      : task === 'agriculturist'
        ? 'AGRICULTURIST'
        : task,
  ) as AdditionalTaskType[],

  status:
    (props.user as any).status ??
    ((props.user as any).is_disabled ? 'DISABLED' : 'ACTIVE'),
})

const showSuccessNotification = ref(false)
const showErrorNotification = ref(false)

const touchedFields = ref<Record<FormField, boolean>>({
  surname: false,
  givenname: false,
  email: false,
  sex: false,
  birthdate: false,
  contactnumber: false,
  category: false,
  status: false,
})

const fieldErrors = ref<Partial<Record<FormField, string>>>({})

const fullNamePreview = computed(() => {
  const base = [
    form.value.surname.trim(),
    form.value.givenname.trim(),
    form.value.middlename.trim(),
  ]
    .filter(Boolean)
    .join(', ')
    .replace(', ,', ',')

  return form.value.suffix.trim() ? `${base} ${form.value.suffix.trim()}` : base
})

const availableAdditionalTaskTypes = computed<AdditionalTaskType[]>(() => {
  if (form.value.category === 'ADMIN') return ['CHEMIST', 'AGRICULTURIST']
  if (form.value.category === 'CHEMIST') return ['AGRICULTURIST']
  return ['CHEMIST']
})

const invalidFieldsList = computed(() => {
  const labels: Record<FormField, string> = {
    surname: 'Surname',
    givenname: 'Given Name',
    email: 'Email',
    sex: 'Sex',
    birthdate: 'Birthdate',
    contactnumber: 'Contact Number',
    category: 'Role (Main Task)',
    status: 'Status',
  }

  return Object.keys(fieldErrors.value)
    .filter((key) => fieldErrors.value[key as FormField])
    .map((key) => labels[key as FormField])
})

watch(
  () => form.value.category,
  () => {
    form.value.tasks = form.value.tasks.filter((task) =>
      availableAdditionalTaskTypes.value.includes(task),
    )
  },
)

function goBack() {
  router.visit('/user-management')
}

function toggleTask(task: AdditionalTaskType) {
  if (!availableAdditionalTaskTypes.value.includes(task)) return

  if (form.value.tasks.includes(task)) {
    form.value.tasks = form.value.tasks.filter((item) => item !== task)
    return
  }

  form.value.tasks = [...form.value.tasks, task]
}

function formatSentenceCase(value: string) {
  const text = String(value).toLowerCase()
  return text.charAt(0).toUpperCase() + text.slice(1)
}

function handleContactNumberInput(event: Event) {
  const target = event.target as HTMLInputElement
  form.value.contactnumber = formatPhilippineMobile(target.value)

  if (touchedFields.value.contactnumber) {
    validateField('contactnumber')
  }
}

function handleContactNumberKeydown(event: KeyboardEvent) {
  const target = event.target as HTMLInputElement
  const start = target.selectionStart ?? 0
  const end = target.selectionEnd ?? 0

  if (
    (event.key === 'Backspace' && start <= 3 && end <= 3) ||
    (event.key === 'Delete' && start < 3)
  ) {
    event.preventDefault()
  }
}

function validateField(field: FormField) {
  const normalizedPhone = normalizePhoneForBackend(form.value.contactnumber)

  switch (field) {
    case 'surname':
      fieldErrors.value.surname = form.value.surname.trim() ? '' : 'Surname is required.'
      break
    case 'givenname':
      fieldErrors.value.givenname = form.value.givenname.trim() ? '' : 'Given name is required.'
      break
    case 'email':
      if (!form.value.email.trim()) fieldErrors.value.email = 'Email is required.'
      else if (!validateEmail(form.value.email.trim())) fieldErrors.value.email = 'Please enter a valid email address.'
      else fieldErrors.value.email = ''
      break
    case 'sex':
      fieldErrors.value.sex = form.value.sex ? '' : 'Sex is required.'
      break
    case 'birthdate':
      fieldErrors.value.birthdate = form.value.birthdate ? '' : 'Birthdate is required.'
      break
    case 'contactnumber':
      if (!form.value.contactnumber.trim() || normalizedPhone === '+63') {
        fieldErrors.value.contactnumber = 'Contact number is required.'
      } else if (!/^\+639\d{9}$/.test(normalizedPhone)) {
        fieldErrors.value.contactnumber = 'Please enter a valid mobile number.'
      } else {
        fieldErrors.value.contactnumber = ''
      }
      break
    case 'category':
      fieldErrors.value.category = form.value.category ? '' : 'Role (Main Task) is required.'
      break
    case 'status':
      fieldErrors.value.status = form.value.status ? '' : 'Status is required.'
      break
  }
}

function markFieldTouched(field: FormField) {
  touchedFields.value[field] = true
  validateField(field)
}

function validateForm() {
  const fields: FormField[] = [
    'surname',
    'givenname',
    'email',
    'sex',
    'birthdate',
    'contactnumber',
    'category',
    'status',
  ]

  fields.forEach((field) => validateField(field))

  return !fields.some((field) => fieldErrors.value[field])
}

function closeSuccessNotification() {
  showSuccessNotification.value = false
}

function closeErrorNotification() {
  showErrorNotification.value = false
}

function saveChanges() {
    touchedFields.value = {
        surname: true,
        givenname: true,
        email: true,
        sex: true,
        birthdate: true,
        contactnumber: true,
        category: true,
        status: true,
    }

    if (!validateForm()) {
        showErrorNotification.value = true
        return
    }

    router.put(`/user-management/${props.user.id}`, {
        first_name: form.value.givenname,
        middle_name: form.value.middlename,
        last_name: form.value.surname,
        suffix: form.value.suffix,

        sex: form.value.sex,
        birthdate: form.value.birthdate,
        contact_number: normalizePhoneForBackend(form.value.contactnumber),

        email: form.value.email,

        role: form.value.category.toLowerCase(),

        additional_tasks: form.value.tasks.map(task => task.toLowerCase()),

        is_disabled: form.value.status === 'DISABLED',
    }, {
        preserveScroll: true,

        onSuccess: () => {
            form.value.contactnumber = formatPhilippineMobile(form.value.contactnumber)
            showSuccessNotification.value = true
        },

        onError: () => {
            showErrorNotification.value = true
        },
    })
}

watch(() => form.value.surname, () => {
  if (touchedFields.value.surname) validateField('surname')
})

watch(() => form.value.givenname, () => {
  if (touchedFields.value.givenname) validateField('givenname')
})

watch(() => form.value.email, () => {
  if (touchedFields.value.email) validateField('email')
})

watch(() => form.value.sex, () => {
  if (touchedFields.value.sex) validateField('sex')
})

watch(() => form.value.birthdate, () => {
  if (touchedFields.value.birthdate) validateField('birthdate')
})

watch(() => form.value.contactnumber, () => {
  if (touchedFields.value.contactnumber) validateField('contactnumber')
})

watch(() => form.value.category, () => {
  if (touchedFields.value.category) validateField('category')
})

watch(() => form.value.status, () => {
  if (touchedFields.value.status) validateField('status')
})
</script>

<template>
  <Head title="Edit Account" />

  <AppSidebarLayout :breadcrumbs="breadcrumbs">
    <div class="p-4 md:p-6">
      <div class="mx-auto max-w-5xl">
        <div class="mb-5 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
          <div>
            <h1 class="text-xl font-semibold text-zinc-900">
              Edit Account
            </h1>
            <p class="text-sm text-zinc-500">
              Update the current details of this account.
            </p>
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

        <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm">
          <div class="grid gap-4 md:grid-cols-2">
            <div>
              <label class="mb-1 block text-sm font-medium text-zinc-700">
                  Surname <span class="text-rose-500">*</span>
              </label>
              <input
                v-model="form.surname"
                type="text"
                :class="[
                  'h-11 w-full rounded-lg border px-3 text-sm outline-none focus:border-zinc-400',
                  touchedFields.surname && fieldErrors.surname
                    ? 'border-red-500'
                    : 'border-zinc-200',
                ]"
                @input="markFieldTouched('surname')"
                @blur="markFieldTouched('surname')"
              />
              <p v-if="touchedFields.surname && fieldErrors.surname" class="mt-1 text-xs text-red-500">
                {{ fieldErrors.surname }}
              </p>
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-zinc-700">
                  Given Name <span class="text-rose-500">*</span>
              </label>
              <input
                v-model="form.givenname"
                type="text"
                :class="[
                  'h-11 w-full rounded-lg border px-3 text-sm outline-none focus:border-zinc-400',
                  touchedFields.givenname && fieldErrors.givenname
                    ? 'border-red-500'
                    : 'border-zinc-200',
                ]"
                @input="markFieldTouched('givenname')"
                @blur="markFieldTouched('givenname')"
              />
              <p v-if="touchedFields.givenname && fieldErrors.givenname" class="mt-1 text-xs text-red-500">
                {{ fieldErrors.givenname }}
              </p>
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-zinc-700">
                Middle Name
              </label>
              <input
                v-model="form.middlename"
                type="text"
                class="h-11 w-full rounded-lg border border-zinc-200 px-3 text-sm outline-none focus:border-zinc-400"
              />
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-zinc-700">
                Suffix
              </label>
              <input
                v-model="form.suffix"
                type="text"
                placeholder="Jr., Sr., III"
                class="h-11 w-full rounded-lg border border-zinc-200 px-3 text-sm outline-none focus:border-zinc-400"
              />
            </div>

            <div class="md:col-span-2">
              <label class="mb-1 block text-sm font-medium text-zinc-700">
                Full Name Preview
              </label>
              <div
                class="flex min-h-11 items-center rounded-lg border border-zinc-200 bg-zinc-50 px-3 text-sm text-zinc-900"
              >
                {{ fullNamePreview || '—' }}
              </div>
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-zinc-700">
                  Sex <span class="text-rose-500">*</span>
              </label>
              <select
                v-model="form.sex"
                :class="[
                  'h-11 w-full rounded-lg border px-3 text-sm outline-none focus:border-zinc-400',
                  touchedFields.sex && fieldErrors.sex
                    ? 'border-red-500'
                    : 'border-zinc-200',
                ]"
                @change="markFieldTouched('sex')"
                @blur="markFieldTouched('sex')"
              >
                <option value="">Select sex</option>
                <option value="MALE">Male</option>
                <option value="FEMALE">Female</option>
              </select>
              <p v-if="touchedFields.sex && fieldErrors.sex" class="mt-1 text-xs text-red-500">
                {{ fieldErrors.sex }}
              </p>
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-zinc-700">
                  Birthdate <span class="text-rose-500">*</span>
              </label>
              <input
                v-model="form.birthdate"
                type="date"
                :class="[
                  'h-11 w-full rounded-lg border px-3 text-sm outline-none focus:border-zinc-400',
                  touchedFields.birthdate && fieldErrors.birthdate
                    ? 'border-red-500'
                    : 'border-zinc-200',
                ]"
                @change="markFieldTouched('birthdate')"
                @blur="markFieldTouched('birthdate')"
              />
              <p v-if="touchedFields.birthdate && fieldErrors.birthdate" class="mt-1 text-xs text-red-500">
                {{ fieldErrors.birthdate }}
              </p>
            </div>

            <div class="md:col-span-2 grid gap-4 md:grid-cols-2">
              <!-- Email -->
              <div>
                <label class="mb-1 block text-sm font-medium text-zinc-700">
                  Email <span class="text-rose-500">*</span>
                </label>

                <input
                  v-model="form.email"
                  type="email"
                  :class="[
                    'h-11 w-full rounded-lg border px-3 text-sm outline-none focus:border-zinc-400',
                    touchedFields.email && fieldErrors.email
                      ? 'border-red-500'
                      : 'border-zinc-200',
                  ]"
                  @input="markFieldTouched('email')"
                  @blur="markFieldTouched('email')"
                />

                <p
                  v-if="touchedFields.email && fieldErrors.email"
                  class="mt-1 text-xs text-red-500"
                >
                  {{ fieldErrors.email }}
                </p>
              </div>

              <!-- Contact Number -->
              <div>
                <label class="mb-1 block text-sm font-medium text-zinc-700">
                  Contact Number <span class="text-rose-500">*</span>
                </label>

                <input
                  :value="form.contactnumber"
                  type="text"
                  inputmode="numeric"
                  placeholder="+63 9XX XXX XXXX"
                  :class="[
                    'h-11 w-full rounded-lg border px-3 text-sm outline-none focus:border-zinc-400',
                    touchedFields.contactnumber && fieldErrors.contactnumber
                      ? 'border-red-500'
                      : 'border-zinc-200',
                  ]"
                  @input="handleContactNumberInput"
                  @keydown="handleContactNumberKeydown"
                  @blur="markFieldTouched('contactnumber')"
                />

                <p
                  v-if="touchedFields.contactnumber && fieldErrors.contactnumber"
                  class="mt-1 text-xs text-red-500"
                >
                  {{ fieldErrors.contactnumber }}
                </p>
              </div>
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-zinc-700">
                  Role (Main Task) <span class="text-rose-500">*</span>
              </label>
              <select
                v-model="form.category"
                :class="[
                  'h-11 w-full rounded-lg border px-3 text-sm outline-none focus:border-zinc-400',
                  touchedFields.category && fieldErrors.category
                    ? 'border-red-500'
                    : 'border-zinc-200',
                ]"
                @change="markFieldTouched('category')"
                @blur="markFieldTouched('category')"
              >
                <option value="">Select role</option>
                <option value="ADMIN">Admin</option>
                <option value="CHEMIST">Chemist</option>
                <option value="AGRICULTURIST">Agriculturist</option>
              </select>
              <p v-if="touchedFields.category && fieldErrors.category" class="mt-1 text-xs text-red-500">
                {{ fieldErrors.category }}
              </p>
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-zinc-700">
                  Status <span class="text-rose-500">*</span>
              </label>
              <select
                v-model="form.status"
                :class="[
                  'h-11 w-full rounded-lg border px-3 text-sm outline-none focus:border-zinc-400',
                  touchedFields.status && fieldErrors.status
                    ? 'border-red-500'
                    : 'border-zinc-200',
                ]"
                @change="markFieldTouched('status')"
                @blur="markFieldTouched('status')"
              >
                <option value="">Select status</option>
                <option value="ACTIVE">ACTIVE</option>
                <option value="DISABLED">DISABLED</option>
              </select>
              <p v-if="touchedFields.status && fieldErrors.status" class="mt-1 text-xs text-red-500">
                {{ fieldErrors.status }}
              </p>
            </div>

            <div class="md:col-span-2">
              <label class="mb-2 block text-sm font-medium text-zinc-700">
                Additional Task Type
              </label>

              <div class="grid gap-3 sm:grid-cols-2">
                <label
                  v-for="task in availableAdditionalTaskTypes"
                  :key="task"
                  class="flex cursor-pointer items-center gap-3 rounded-lg border border-zinc-200 px-4 py-3 transition hover:bg-zinc-50"
                >
                  <input
                    :checked="form.tasks.includes(task)"
                    type="checkbox"
                    class="h-4 w-4 rounded border-zinc-300 text-[#16a34a] focus:ring-[#16a34a]"
                    @change="toggleTask(task)"
                  />
                  <span class="text-sm text-zinc-800">
                    {{ formatSentenceCase(task) }}
                  </span>
                </label>
              </div>
            </div>
          </div>

          <div class="mt-6 flex justify-end">
            <button
              type="button"
              @click="saveChanges"
              class="inline-flex items-center gap-2 rounded-lg bg-[#16a34a] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#15803d]"
            >
              <Save class="h-4 w-4" />
              Save Changes
            </button>
          </div>
        </div>
      </div>

      <div
        v-if="showSuccessNotification"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4"
      >
        <div class="w-full max-w-lg rounded-2xl border border-zinc-200 bg-white shadow-xl">
          <div class="flex items-start justify-between border-b border-zinc-200 px-6 py-4">
            <div class="flex items-start gap-3">
              <div class="mt-0.5 flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                <CircleCheckBig class="h-5 w-5" />
              </div>

              <div>
                <h2 class="text-lg font-semibold text-zinc-900">
                  Account Successfully Updated
                </h2>
                <p class="mt-1 text-sm text-zinc-500">
                  The account details have been updated successfully.
                </p>
              </div>
            </div>

            <button
              type="button"
              @click="closeSuccessNotification"
              class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-zinc-200 text-zinc-700 transition hover:bg-zinc-50"
            >
              <X class="h-4 w-4" />
            </button>
          </div>

          <div class="px-6 py-5">
            <p class="text-sm leading-6 text-zinc-700">
              All changes for
              <span class="font-semibold text-zinc-900">
                {{ fullNamePreview || form.email }}
              </span>
              have been saved successfully.
            </p>
          </div>

          <div class="flex justify-end gap-2 border-t border-zinc-200 px-6 py-4">
            <button
              type="button"
              @click="closeSuccessNotification"
              class="rounded-lg bg-[#16a34a] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#15803d]"
            >
              Okay
            </button>
          </div>
        </div>
      </div>

      <div
        v-if="showErrorNotification"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4"
      >
        <div class="w-full max-w-lg rounded-2xl border border-zinc-200 bg-white shadow-xl">
          <div class="flex items-start justify-between border-b border-zinc-200 px-6 py-4">
            <div class="flex items-start gap-3">
              <div class="mt-0.5 flex h-10 w-10 items-center justify-center rounded-full bg-amber-100 text-amber-600">
                <TriangleAlert class="h-5 w-5" />
              </div>

              <div>
                <h2 class="text-lg font-semibold text-zinc-900">
                  Invalid or Incomplete Fields
                </h2>
                <p class="mt-1 text-sm text-zinc-500">
                  Please review the required or invalid fields before saving.
                </p>
              </div>
            </div>

            <button
              type="button"
              @click="closeErrorNotification"
              class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-zinc-200 text-zinc-700 transition hover:bg-zinc-50"
            >
              <X class="h-4 w-4" />
            </button>
          </div>

          <div class="px-6 py-5">
            <p class="text-sm leading-6 text-zinc-700">
              Please fix the following field<span v-if="invalidFieldsList.length !== 1">s</span>:
            </p>

            <ul class="mt-3 list-disc pl-5 text-sm leading-6 text-zinc-700">
              <li v-for="field in invalidFieldsList" :key="field">
                {{ field }}
              </li>
            </ul>
          </div>

          <div class="flex justify-end gap-2 border-t border-zinc-200 px-6 py-4">
            <button
              type="button"
              @click="closeErrorNotification"
              class="rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-amber-600"
            >
              Okay
            </button>
          </div>
        </div>
      </div>
    </div>
    
  </AppSidebarLayout>
</template>