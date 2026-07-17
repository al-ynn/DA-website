<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue'
import { Head, router, useForm, usePage } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import type { BreadcrumbItem } from '@/types'
import { ArrowLeft, Save, X, CircleCheckBig, FileText, TriangleAlert } from 'lucide-vue-next'

type UserCategory = 'ADMIN' | 'CHEMIST' | 'AGRICULTURIST'
type AdditionalTaskType = 'CHEMIST' | 'AGRICULTURIST'
type SexType = 'MALE' | 'FEMALE'

type FormState = {
  surname: string
  givenname: string
  middlename: string
  suffix: string
  email: string
  sex: SexType | ''
  birthdate: string
  contactnumber: string
  category: UserCategory | ''
  status: '' | 'ACTIVE' | 'DISABLED'
  tasks: AdditionalTaskType[]
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

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'User Management',
    href: '/user-management',
  },
  {
    title: 'Create Account',
    href: '/user-management/create',
  },
]

function normalizePhoneForBackend(value: string) {
  let digits = String(value || '').replace(/\D/g, '')

  if (digits.startsWith('63')) {
    digits = digits.slice(2)
  } else if (digits.startsWith('0')) {
    digits = digits.slice(1)
  }

  if (!digits.startsWith('9') && digits.length > 0) {
    digits = `9${digits}`
  }

  digits = digits.slice(0, 10)

  return digits ? `+63${digits}` : '+63'
}

function formatPhoneForDisplay(value: string) {
  let digits = String(value || '').replace(/\D/g, '')

  if (digits.startsWith('63')) {
    digits = digits.slice(2)
  } else if (digits.startsWith('0')) {
    digits = digits.slice(1)
  }

  if (digits && !digits.startsWith('9')) {
    digits = `9${digits}`
  }

  digits = digits.slice(0, 10)

  let formatted = '+63'

  if (digits.length > 0) {
    formatted += ` ${digits.slice(0, 3)}`
  }
  if (digits.length > 3) {
    formatted += ` ${digits.slice(3, 6)}`
  }
  if (digits.length > 6) {
    formatted += ` ${digits.slice(6, 10)}`
  }

  return formatted
}

function validateEmail(email: string) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)
}

const form = useForm<FormState>({
  surname: '',
  givenname: '',
  middlename: '',
  suffix: '',
  email: '',
  sex: '',
  birthdate: '',
  contactnumber: '+63',
  category: '',
  status: '',
  tasks: [],
})

const page = usePage<{
    flash: {
        success?: string
        temporary_password?: string
    }
}>()

const generatedPassword = computed(
    () => page.props.flash?.temporary_password ?? ''
)

const savedSuccessfully = ref(false)
const showSuccessNotification = ref(false)
const showErrorNotification = ref(false)
const isDraft = ref(false)

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

const availableAdditionalTaskTypes = computed<AdditionalTaskType[]>(() => {
  if (form.category === 'ADMIN') {
    return ['CHEMIST', 'AGRICULTURIST']
  }
  if (form.category === 'CHEMIST') {
    return ['AGRICULTURIST']
  }
  if (form.category === 'AGRICULTURIST') {
    return ['CHEMIST']
  }
  return []
})

watch(
  () => form.category,
  () => {
    form.tasks = form.tasks.filter((task) =>
      availableAdditionalTaskTypes.value.includes(task),
    )
  },
)

const fullNamePreview = computed(() => {
  const base = [
    form.surname.trim(),
    form.givenname.trim(),
    form.middlename.trim(),
  ]
    .filter(Boolean)
    .join(', ')
    .replace(', ,', ',')

  return form.suffix.trim() ? `${base} ${form.suffix.trim()}` : base
})

const notificationTitle = computed(() =>
  isDraft.value
    ? 'Draft Saved Successfully'
    : 'Account Created Successfully',
)

const notificationMessage = computed(() =>
  isDraft.value
    ? 'The account has been saved as a draft.'
    : 'The account has been created successfully.',
)

const notificationDescription = computed(() =>
  isDraft.value
    ? 'You can continue editing this account later from the Draft Accounts page.'
    : 'A temporary password has been generated for this account. Provide it to the user and advise them to change it upon their first login.'
)

const invalidFieldsList = computed(() => {
    const labels: Record<string, string> = {
        surname: 'Surname',
        givenname: 'Given Name',
        email: 'Email',
        sex: 'Sex',
        birthdate: 'Birthdate',
        contactnumber: 'Contact Number',
        category: 'Role (Main Task)',
        status: 'Status',

        // Laravel fields
        first_name: 'Given Name',
        last_name: 'Surname',
        middle_name: 'Middle Name',
        contact_number: 'Contact Number',
        role: 'Role (Main Task)',
    }

    const clientErrors = Object.keys(fieldErrors.value)
        .filter((key) => fieldErrors.value[key as FormField])
        .map((key) => labels[key] ?? key)

    const serverErrors = Object.keys(form.errors)
        .map((key) => labels[key] ?? key)

    return [...new Set([...clientErrors, ...serverErrors])]
})

function goBack() {
  router.visit('/user-management')
}

function toggleTask(task: AdditionalTaskType) {
  if (!availableAdditionalTaskTypes.value.includes(task)) return
  if (form.tasks.includes(task)) {
    form.tasks = form.tasks.filter((item) => item !== task)
    return
  }
  form.tasks = [...form.tasks, task]
}

function formatSentenceCase(value: string) {
  const text = String(value).toLowerCase()
  return text.charAt(0).toUpperCase() + text.slice(1)
}

function validateField(field: FormField) {
  const normalizedPhone = normalizePhoneForBackend(form.contactnumber)

  switch (field) {
    case 'surname':
      fieldErrors.value.surname = form.surname.trim()
        ? ''
        : 'Surname is required.'
      break

    case 'givenname':
      fieldErrors.value.givenname = form.givenname.trim()
        ? ''
        : 'Given name is required.'
      break

    case 'email':
      if (!form.email.trim()) {
        fieldErrors.value.email = 'Email is required.'
      } else if (!validateEmail(form.email.trim())) {
        fieldErrors.value.email = 'Please enter a valid email address.'
      } else {
        fieldErrors.value.email = ''
      }
      break

    case 'sex':
      fieldErrors.value.sex = form.sex ? '' : 'Sex is required.'
      break

    case 'birthdate':
      fieldErrors.value.birthdate = form.birthdate ? '' : 'Birthdate is required.'
      break

    case 'contactnumber':
      if (!form.contactnumber.trim() || normalizedPhone === '+63') {
        fieldErrors.value.contactnumber = 'Contact number is required.'
      } else if (!/^\+639\d{9}$/.test(normalizedPhone)) {
        fieldErrors.value.contactnumber = 'Please enter a valid mobile number.'
      } else {
        fieldErrors.value.contactnumber = ''
      }
      break

    case 'category':
      fieldErrors.value.category = form.category
        ? ''
        : 'Role (Main Task) is required.'
      break

    case 'status':
      fieldErrors.value.status =
        form.status ? '' : 'Status is required.'
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

function resetForm() {
    form.clearErrors()

    form.surname = ''
    form.givenname = ''
    form.middlename = ''
    form.suffix = ''
    form.email = ''
    form.sex = ''
    form.birthdate = ''
    form.contactnumber = '+63'
    form.category = ''
    form.status = ''
    form.tasks = []

    savedSuccessfully.value = false
    isDraft.value = false

    fieldErrors.value = {}

    touchedFields.value = {
        surname: false,
        givenname: false,
        email: false,
        sex: false,
        birthdate: false,
        contactnumber: false,
        category: false,
        status: false,
    }

    form.defaults({
        surname: '',
        givenname: '',
        middlename: '',
        suffix: '',
        email: '',
        sex: '',
        birthdate: '',
        contactnumber: '+63',
        category: '',
        status: '',
        tasks: [],
    })
}

function closeSuccessNotification() {
    showSuccessNotification.value = false
    resetForm()
}


function continueCreating() {
    showSuccessNotification.value = false
    resetForm()
}
function goToAccounts() {
    router.visit('/user-management')
}

function closeErrorNotification() {
  showErrorNotification.value = false
}

function handleContactNumberInput(event: Event) {
  const target = event.target as HTMLInputElement
  form.contactnumber = formatPhoneForDisplay(target.value)

  if (touchedFields.value.contactnumber) {
    validateField('contactnumber')
  }
}

function handleContactNumberKeydown(event: KeyboardEvent) {
  const target = event.target as HTMLInputElement
  const cursorStart = target.selectionStart ?? 0
  const cursorEnd = target.selectionEnd ?? 0

  if (
    (event.key === 'Backspace' && cursorStart <= 3 && cursorEnd <= 3) ||
    (event.key === 'Delete' && cursorStart <= 3)
  ) {
    event.preventDefault()
  }
}

function saveAsDraft() {
    form
        .transform((data) => ({
            first_name: data.givenname,
            middle_name: data.middlename,
            last_name: data.surname,
            suffix: data.suffix,

            sex: data.sex,
            birthdate: data.birthdate,
            contact_number: normalizePhoneForBackend(data.contactnumber),

            email: data.email,

            role: data.category ? data.category.toLowerCase() : null,

            additional_tasks: data.tasks.map(task => task.toLowerCase()),

            is_disabled: false,
            is_draft: true,
        }))
        .post('/user-management', {
            preserveScroll: true,

            onSuccess: () => {
                isDraft.value = true
                savedSuccessfully.value = true
                showSuccessNotification.value = true
            },

            onError: (errors) => {
                console.log(errors)
            },
        })
}

function saveAccount() {
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

  form
    .transform((data) => ({
      first_name: data.givenname,
      middle_name: data.middlename,
      last_name: data.surname,
      suffix: data.suffix,

      sex: data.sex,
      birthdate: data.birthdate,
      contact_number: normalizePhoneForBackend(data.contactnumber),

      email: data.email,

      role: data.category.toLowerCase(),

      additional_tasks: data.tasks.map(task => task.toLowerCase()),

      is_disabled: data.status === 'DISABLED',
      is_draft: false,
  }))
  .post('/user-management', {
    preserveScroll: true,

    onSuccess: () => {
      isDraft.value = false
      savedSuccessfully.value = true
      showSuccessNotification.value = true
    },

    onError: (errors) => {
      console.log('Laravel Errors:', errors)
      console.log('Form Errors:', form.errors)

      showErrorNotification.value = true
    },
  })
}

watch(() => form.surname, () => {
  if (touchedFields.value.surname) validateField('surname')
})

watch(() => form.givenname, () => {
  if (touchedFields.value.givenname) validateField('givenname')
})

watch(() => form.email, () => {
  if (touchedFields.value.email) validateField('email')
})

watch(() => form.sex, () => {
  if (touchedFields.value.sex) validateField('sex')
})

watch(() => form.birthdate, () => {
  if (touchedFields.value.birthdate) validateField('birthdate')
})

watch(() => form.category, () => {
  if (touchedFields.value.category) validateField('category')
})

watch(() => form.contactnumber, () => {
  if (touchedFields.value.contactnumber) validateField('contactnumber')
})
</script>

<template>
  <Head title="Create Account" />
  <AppSidebarLayout :breadcrumbs="breadcrumbs">
    <div class="p-4 md:p-6">
      <div class="mx-auto max-w-6xl">
        <div class="mb-5 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
          <div>
            <h1 class="text-xl font-semibold text-zinc-900">
              Create Account
            </h1>
            <p class="text-sm text-zinc-500">
              Fill in the required personal details and assign role and additional task type.
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

        <div class="grid gap-5 xl:grid-cols-[1.5fr_0.9fr]">
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
                  Middle Name <span class="text-zinc-400">(optional)</span>
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
                <p v-if="touchedFields.email && fieldErrors.email" class="mt-1 text-xs text-red-500">
                  {{ fieldErrors.email }}
                </p>
              </div>

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
                <p v-if="touchedFields.contactnumber && fieldErrors.contactnumber" class="mt-1 text-xs text-red-500">
                  {{ fieldErrors.contactnumber }}
                </p>
              </div>

              <div class="md:col-span-2">
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

              <div class="md:col-span-2">
              ```` <label class="mb-1 block text-sm font-medium text-zinc-700">
                  Status <span class="text-rose-500">*</span>
                </label>

                <select
                  v-model="form.status"
                  @change="markFieldTouched('status')"
                  @blur="markFieldTouched('status')"
                  :class="[
                    'h-11 w-full rounded-lg border px-3 text-sm outline-none',
                    touchedFields.status && fieldErrors.status
                      ? 'border-red-500'
                      : 'border-zinc-200'
                  ]"
                >
                  <option value="">Select Status</option>
                  <option value="ACTIVE">Active</option>
                  <option value="DISABLED">Disabled</option>
                </select>

                <p
                  v-if="touchedFields.status && fieldErrors.status"
                  class="mt-1 text-xs text-red-500"
                >
                  {{ fieldErrors.status }}
                </p>
              </div>

              <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-medium text-zinc-700">
                  Additional Task Type
                </label>

                <div v-if="form.category" class="grid gap-3 sm:grid-cols-2">
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

                <div
                  v-else
                  class="rounded-lg border border-dashed border-zinc-300 px-4 py-3 text-sm text-zinc-500"
                >
                  Select role first.
                </div>
              </div>
            </div>

            <div class="mt-6 flex items-center justify-between">
              <button
                type="button"
                @click="saveAsDraft"
                class="inline-flex items-center gap-2 rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-50"
              >
                <FileText class="h-4 w-4" />
                Save as Draft
              </button>

              <button
                type="button"
                @click="saveAccount"
                class="inline-flex items-center gap-2 rounded-lg bg-[#16a34a] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#15803d]"
              >
                <Save class="h-4 w-4" />
                Save Account
              </button>
            </div>
          </div>

          <div class="space-y-5">
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm">
              <h2 class="mb-3 text-base font-semibold text-zinc-900">
                Account Preview
              </h2>

              <div class="space-y-3 text-sm">
                <div>
                  <p class="text-zinc-500">Full Name</p>
                  <p class="text-zinc-900">
                    {{ fullNamePreview || '—' }}
                  </p>
                </div>

                <div>
                  <p class="text-zinc-500">Email</p>
                  <p class="text-zinc-900">
                    {{ form.email || '—' }}
                  </p>
                </div>

                <div>
                  <p class="text-zinc-500">Contact Number</p>
                  <p class="text-zinc-900">
                    {{ form.contactnumber || '—' }}
                  </p>
                </div>

                <div>
                  <p class="text-zinc-500">Role (Main Task)</p>
                  <p class="text-zinc-900">
                    {{ form.category ? formatSentenceCase(form.category) : '—' }}
                  </p>
                </div>

                <div>
                  <p class="text-zinc-500">Additional Task Type</p>
                  <div
                    v-if="form.tasks.length"
                    class="mt-2 flex min-h-[2.25rem] flex-wrap items-center gap-1.5"
                  >
                    <span
                      v-for="task in form.tasks"
                      :key="task"
                      class="inline-flex rounded-full border border-cyan-200 bg-cyan-50 px-2.5 py-1 text-xs font-semibold text-black"
                    >
                      {{ formatSentenceCase(task) }}
                    </span>
                  </div>
                  <p v-else class="text-zinc-900">—</p>
                </div>
              </div>
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
                    {{ notificationTitle }}
                  </h2>
                  <p class="mt-1 text-sm text-zinc-500">
                    {{ notificationMessage }}
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
                {{ notificationDescription }}
              </p>
            
            <div
                v-if="!isDraft"
                class="mt-5 rounded-lg border border-emerald-200 bg-emerald-50 p-4"
            >
                <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">
                    Default Login Password
                </p>

                <p class="mt-2 font-mono text-lg font-bold text-emerald-900">
                    {{ generatedPassword }}
                </p>

                <p class="mt-2 text-xs text-zinc-600">
                    Provide this password to the user. They will be required to change it after their first login.
                </p>
            </div>
              
            </div>

            <div class="flex justify-end gap-2 border-t border-zinc-200 px-6 py-4">
              <button
                  type="button"
                  @click="goToAccounts"
                  class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-semibold text-zinc-700 hover:bg-zinc-50"
              >
                  Return
              </button>

              <button
                  type="button"
                  @click="continueCreating"
                  class="rounded-lg bg-[#16a34a] px-4 py-2 text-sm font-semibold text-white hover:bg-[#15803d]"
              >
                  Continue
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
                <li
                    v-for="(message, key) in form.errors"
                    :key="key"
                >
                    {{ message }}
                </li>

                <li
                    v-for="field in invalidFieldsList.filter(
                        field =>
                            !Object.keys(form.errors).length ||
                            ![
                                'Surname',
                                'Given Name',
                                'Middle Name',
                                'Email',
                                'Contact Number',
                                'Role (Main Task)',
                            ].includes(field)
                    )"
                    :key="field"
                >
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
    </div>
  </AppSidebarLayout>
</template>