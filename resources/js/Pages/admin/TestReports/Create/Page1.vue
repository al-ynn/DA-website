<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import {
  ArrowLeft,
  ArrowRight,
  Calendar,
  ChevronDown,
  Clock3,
} from 'lucide-vue-next'
import { computed, ref } from 'vue'
import AppSidebarLayout from '@/Layouts/app/AppSidebarLayout.vue'
import type { BreadcrumbItem } from '@/types'

const props = defineProps<{
  draftReport?: Record<string, any> | null
}>()

type ClientClassification =
  | 'PWD'
  | 'SC'
  | 'IP'
  | 'Student'
  | 'Farmer'

type StudentType = 'Undergraduate' | 'Post Graduate' | ''

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Reports', href: '/reports' },
  { title: 'Customer Information', href: '/test-reports/create/page-1' },
]

const form = ref({
  surname: '',
  firstName: '',
  middleName: '',
  rsbsaNo: '',
  companyName: '',
  address: '',
  contactNumber: '+63',
  emailAddress: '',
  sex: '',
  age: '',
  classification: [] as ClientClassification[],
  studentType: '' as StudentType,
  samplingDate: '',
  samplingTime: '',
})

if (props.draftReport) {
  form.value = {
    surname: props.draftReport.surname ?? '',
    firstName: props.draftReport.first_name ?? '',
    middleName: props.draftReport.middle_name ?? '',
    rsbsaNo: props.draftReport.rsbsa_no ?? '',
    companyName: props.draftReport.company_name ?? '',
    address: props.draftReport.address ?? '',
    contactNumber: props.draftReport.contact_number ?? '+63',
    emailAddress: props.draftReport.email_address ?? '',
    sex: props.draftReport.sex ?? '',
    age: props.draftReport.age ?? '',
    classification: Array.isArray(props.draftReport.classification)
      ? props.draftReport.classification
      : String(props.draftReport.classification ?? '').split(',').map((item) => item.trim()).filter(Boolean),
    studentType: props.draftReport.student_type ?? '',
    samplingDate: String(props.draftReport.sampling_date ?? '').slice(0, 10),
    samplingTime: props.draftReport.sampling_time ?? '',
  }
}

const classificationOptions: ClientClassification[] = [
  'PWD',
  'SC',
  'IP',
  'Student',
  'Farmer',
]

const showClassificationDropdown = ref(false)
const validationError = ref('')

const selectedClassificationLabel = computed(() => {
  if (!form.value.classification.length) return 'Select classification'
  return form.value.classification.join(', ')
})

const isStudentSelected = computed(() =>
  form.value.classification.includes('Student'),
)

function toggleClassification(option: ClientClassification) {
  const exists = form.value.classification.includes(option)

  if (exists) {
    form.value.classification = form.value.classification.filter(
      (item: ClientClassification) => item !== option,
    )

    if (option === 'Student') {
      form.value.studentType = ''
    }

    return
  }

  form.value.classification.push(option)
}

function closeClassificationDropdown() {
  showClassificationDropdown.value = false
}

function normalizeRsbsaNo() {
  const value = form.value.rsbsaNo.trim()

  if (!value) return

  if (value.toLowerCase() === 'n/a') {
    form.value.rsbsaNo = 'N/A'
  }
}

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

function handleContactNumberInput(event: Event) {
  const target = event.target as HTMLInputElement
  form.value.contactNumber = formatPhoneForDisplay(target.value)
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

function goBack() {
  router.visit('/reports')
}

function getCsrfToken() {
  return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? ''
}

function buildDraftPayload() {
  return {
    draft_id: props.draftReport?.id ?? null,
    user_id: null,
    request_code: props.draftReport?.request_code ?? null,
    date: new Date().toISOString().split('T')[0],
    status: 'draft',
    is_draft: true,
    surname: form.value.surname.trim() || null,
    first_name: form.value.firstName.trim() || null,
    middle_name: form.value.middleName.trim() || null,
    full_name: [form.value.firstName, form.value.middleName, form.value.surname].filter(Boolean).join(' '),
    rsbsa_no: form.value.rsbsaNo.trim() || null,
    company_name: form.value.companyName.trim() || null,
    classification: form.value.classification.join(', '),
    student_type: form.value.studentType || null,
    sex: form.value.sex || null,
    age: form.value.age || null,
    address: form.value.address.trim() || null,
    contact_number: normalizePhoneForBackend(form.value.contactNumber),
    email_address: form.value.emailAddress.trim() || null,
    sampling_date: form.value.samplingDate || null,
    sampling_time: form.value.samplingTime || null,
    samples: [],
  }
}

async function goNext() {
  normalizeRsbsaNo()

  const requiredFieldError = [
    [!form.value.surname.trim(), 'Surname'],
    [!form.value.firstName.trim(), 'First Name'],
    [!form.value.address.trim(), 'Address'],
    [!form.value.contactNumber.trim() || form.value.contactNumber === '+63', 'Contact No.'],
    [!form.value.classification.length, 'Client Classification'],
    [!form.value.sex, 'Sex'],
    [!String(form.value.age || '').trim(), 'Age'],
  ].find(([missing]) => missing)?.[1]

  if (form.value.classification.includes('Student') && !form.value.studentType) {
    validationError.value = 'Please specify if Student.'
    return
  }

  if (requiredFieldError) {
    validationError.value = `${requiredFieldError} is required.`
    return
  }

  validationError.value = ''

  const endpoint = '/reports/draft'
  let response: Response

  try {
    response = await fetch(endpoint, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': getCsrfToken(),
      },
      body: JSON.stringify(buildDraftPayload()),
    })
  } catch {
    validationError.value = 'Unable to save your information. Please check your connection and try again.'
    return
  }

  if (!response.ok) {
    const contentType = response.headers.get('content-type') || ''
    let message = 'Unable to save draft. Please try again.'

    try {
      if (contentType.includes('application/json')) {
        const data = await response.json() as { message?: string; errors?: Record<string, string[]> }
        if (data?.message) {
          message = data.message
        }
        const firstError = data?.errors ? Object.values(data.errors).flat().find(Boolean) : ''
        if (firstError) {
          message = firstError
        }
      } else {
        const text = await response.text()
        if (text) {
          message = text
        }
      }
    } catch {
      // Keep the generic fallback below.
    }

    validationError.value = message
    return
  }

  const data = await response.json() as { report?: { id?: number } }
  router.visit(`/test-reports/create/page-2?draft_id=${data.report?.id ?? props.draftReport?.id}`)
}

const now = new Date()

if (!form.value.samplingDate) {
  form.value.samplingDate = now.toISOString().split('T')[0]
}

if (!form.value.samplingTime) {
  form.value.samplingTime = now.toLocaleTimeString([], {
    hour: '2-digit',
    minute: '2-digit',
  })
}
</script>

<template>
  <Head title="Create Test Request - Page 1" />

  <AppSidebarLayout :breadcrumbs="breadcrumbs">
    <div class="relative p-4 md:p-6">
      <div class="mx-auto max-w-7xl">
        <div class="rounded-2xl border border-zinc-200 bg-white shadow-sm">
          <div class="border-b border-zinc-200 px-6 py-5">
            <div class="text-center md:text-left">
              <h1 class="text-2xl font-bold text-zinc-900">
                Create Test Request
              </h1>
              <p class="mt-1 text-sm text-zinc-500">
                Customer Information
              </p>
            </div>
          </div>

          <div class="px-6 py-6 md:px-8 md:py-8">
            <div class="rounded-xl border-1 border-[#F09816] p-5 light:border-[#F09816]">
              <div class="grid gap-8 lg:grid-cols-[1.25fr_0.75fr]">
                <div class="space-y-5">
                  <div class="grid gap-4 md:grid-cols-[1fr_1fr_1fr_1.5fr]">
                    <div>
                      <label class="mb-1.5 block text-sm font-medium text-zinc-700">
                        Surname <span class="text-rose-500">*</span>
                      </label>
                      <input
                        v-model="form.surname"
                        type="text"
                        class="h-11 w-full rounded-lg border border-zinc-200 bg-white px-3 text-sm text-zinc-900 outline-none focus:border-[#0E3D1A]"
                      />
                    </div>

                    <div>
                      <label class="mb-1.5 block text-sm font-medium text-zinc-700">
                        First Name <span class="text-rose-500">*</span>
                      </label>
                      <input
                        v-model="form.firstName"
                        type="text"
                        class="h-11 w-full rounded-lg border border-zinc-200 bg-white px-3 text-sm text-zinc-900 outline-none focus:border-[#0E3D1A]"
                      />
                    </div>

                    <div>
                      <label class="mb-1.5 block text-sm font-medium text-zinc-700">
                        Middle Name
                      </label>
                      <input
                        v-model="form.middleName"
                        type="text"
                        placeholder="Optional"
                        class="h-11 w-full rounded-lg border border-zinc-200 bg-white px-3 text-sm text-zinc-900 outline-none focus:border-[#0E3D1A]"
                      />
                    </div>

                    <div>
                      <label class="mb-1.5 block text-sm font-medium text-zinc-700">
                        RSBSA No.
                      </label>
                      <input
                        v-model="form.rsbsaNo"
                        type="text"
                        placeholder="Enter RSBSA No. or N/A"
                        @blur="normalizeRsbsaNo"
                        class="h-11 w-full rounded-lg border border-zinc-200 bg-white px-3 text-sm text-zinc-900 outline-none focus:border-[#0E3D1A]"
                      />
                    </div>
                  </div>

                  <div>
                    <label class="mb-1.5 block text-sm font-medium text-zinc-700">
                      Name of this Company
                    </label>
                    <input
                      v-model="form.companyName"
                      type="text"
                      class="h-11 w-full rounded-lg border border-zinc-200 bg-white px-3 text-sm text-zinc-900 outline-none focus:border-[#0E3D1A]"
                    />
                  </div>

                  <div>
                    <label class="mb-1.5 block text-sm font-medium text-zinc-700">
                      Address <span class="text-rose-500">*</span>
                    </label>
                    <input
                      v-model="form.address"
                      type="text"
                      class="h-11 w-full rounded-lg border border-zinc-200 bg-white px-3 text-sm text-zinc-900 outline-none focus:border-[#0E3D1A]"
                    />
                  </div>

                  <div class="grid gap-4 md:grid-cols-2">
                    <div>
                      <label class="mb-1.5 block text-sm font-medium text-zinc-700">
                        Contact No. <span class="text-rose-500">*</span>
                      </label>
                      <input
                        :value="form.contactNumber"
                        type="text"
                        inputmode="numeric"
                        placeholder="+63 9XX XXX XXXX"
                        class="h-11 w-full rounded-lg border border-zinc-200 bg-white px-3 text-sm text-zinc-900 outline-none focus:border-[#0E3D1A]"
                        @input="handleContactNumberInput"
                        @keydown="handleContactNumberKeydown"
                      />
                    </div>

                    <div>
                      <label class="mb-1.5 block text-sm font-medium text-zinc-700">
                        Email Address
                      </label>
                      <input
                        v-model="form.emailAddress"
                        type="email"
                        class="h-11 w-full rounded-lg border border-zinc-200 bg-white px-3 text-sm text-zinc-900 outline-none focus:border-[#0E3D1A]"
                      />
                    </div>
                  </div>
                </div>

                <div class="space-y-5">
                  <div>
                    <label class="mb-1.5 block text-sm font-medium text-zinc-700">
                      Client Classification <span class="text-rose-500">*</span>
                    </label>

                    <div class="relative">
                      <button
                        type="button"
                        @click="showClassificationDropdown = !showClassificationDropdown"
                        class="flex h-11 w-full items-center justify-between rounded-lg border border-zinc-200 bg-white px-3 text-left text-sm text-zinc-900 outline-none transition hover:border-[#0E3D1A]"
                      >
                        <span class="truncate">{{ selectedClassificationLabel }}</span>
                        <ChevronDown
                          class="h-4 w-4 shrink-0 text-zinc-500 transition"
                          :class="showClassificationDropdown ? 'rotate-180' : ''"
                        />
                      </button>

                      <div
                        v-if="showClassificationDropdown"
                        class="absolute z-20 mt-2 w-full rounded-xl border border-zinc-200 bg-white p-2 shadow-lg"
                      >
                        <label
                          v-for="option in classificationOptions"
                          :key="option"
                          class="flex cursor-pointer items-center gap-3 rounded-lg px-3 py-2 text-sm text-zinc-700 transition hover:bg-zinc-50"
                        >
                          <input
                            type="checkbox"
                            :value="option"
                            :checked="form.classification.includes(option)"
                            @change="toggleClassification(option)"
                            class="h-4 w-4 rounded border-zinc-300 text-[#0E3D1A] focus:ring-0"
                          />
                          <span>{{ option }}</span>
                        </label>

                        <div class="px-2 pt-2">
                          <button
                            type="button"
                            @click="closeClassificationDropdown"
                            class="w-full rounded-lg bg-[#0E3D1A] px-3 py-2 text-sm font-medium text-white transition hover:opacity-90"
                          >
                            Done
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div
                    v-if="isStudentSelected"
                    class="rounded-xl border border-zinc-200 bg-zinc-50 p-4"
                  >
                    <label class="mb-2 block text-sm font-medium text-zinc-700">
                      Specify if Student <span class="text-rose-500">*</span>
                    </label>

                    <div class="grid gap-3 sm:grid-cols-2">
                      <label class="flex items-center gap-2 text-sm text-zinc-700">
                        <input
                          v-model="form.studentType"
                          type="radio"
                          value="Undergraduate"
                          class="h-4 w-4 border-zinc-300 text-[#0E3D1A] focus:ring-0"
                        />
                        Undergraduate
                      </label>

                      <label class="flex items-center gap-2 text-sm text-zinc-700">
                        <input
                          v-model="form.studentType"
                          type="radio"
                          value="Post Graduate"
                          class="h-4 w-4 border-zinc-300 text-[#0E3D1A] focus:ring-0"
                        />
                        Post Graduate
                      </label>
                    </div>
                  </div>

                  <div class="grid gap-4 md:grid-cols-2">
                    <div>
                      <label class="mb-1.5 block text-sm font-medium text-zinc-700">
                        Sex <span class="text-rose-500">*</span>
                      </label>
                      <select
                        v-model="form.sex"
                        class="h-11 w-full rounded-lg border border-zinc-200 bg-white px-3 text-sm text-zinc-900 outline-none focus:border-[#0E3D1A]"
                      >
                        <option value="">Select sex</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                      </select>
                    </div>

                    <div>
                      <label class="mb-1.5 block text-sm font-medium text-zinc-700">
                        Age <span class="text-rose-500">*</span>
                      </label>
                      <input
                        v-model.number="form.age"
                        type="number"
                        min="0"
                        class="h-11 w-full rounded-lg border border-zinc-200 bg-white px-3 text-sm text-zinc-900 outline-none focus:border-[#0E3D1A]"
                      />
                    </div>
                  </div>

                  <div class="grid gap-4 md:grid-cols-2">
                    <div>
                      <label class="mb-1.5 block text-sm font-medium text-zinc-700">
                        Date of Sampling
                      </label>
                      <div class="relative">
                        <Calendar class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-400" />
                        <input
                          v-model="form.samplingDate"
                          type="text"
                          readonly
                          class="h-11 w-full rounded-lg border border-zinc-200 bg-zinc-50 pl-10 pr-3 text-sm text-zinc-700 outline-none"
                        />
                      </div>
                    </div>

                    <div>
                      <label class="mb-1.5 block text-sm font-medium text-zinc-700">
                        Time of Sampling
                      </label>
                      <div class="relative">
                        <Clock3 class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-400" />
                        <input
                          v-model="form.samplingTime"
                          type="text"
                          readonly
                          class="h-11 w-full rounded-lg border border-zinc-200 bg-zinc-50 pl-10 pr-3 text-sm text-zinc-700 outline-none"
                        />
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="mt-8 flex items-center justify-between border-t border-zinc-200 pt-6">
              <p v-if="validationError" class="text-sm font-medium text-rose-600">
                {{ validationError }}
              </p>

              <button
                type="button"
                @click="goBack"
                class="inline-flex items-center gap-2 rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50"
              >
                <ArrowLeft class="h-4 w-4" />
                Back
              </button>

              <div class="text-sm text-zinc-500">
                Step 1 of 3
              </div>

              <button
                type="button"
                @click="goNext"
                class="inline-flex items-center gap-2 rounded-lg bg-[#0E3D1A] px-5 py-2.5 text-sm font-semibold text-white transition hover:opacity-90"
              >
                Next
                <ArrowRight class="h-4 w-4" />
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppSidebarLayout>
</template>
