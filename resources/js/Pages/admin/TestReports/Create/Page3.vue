<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import {
  ArrowLeft,
  ArrowRight,
  CreditCard,
  CalendarDays,
  UserRoundCheck,
  Building2,
  Mail,
  Phone,
  MapPin,
  User,
} from 'lucide-vue-next'
import { computed, ref } from 'vue'
import AppSidebarLayout from '@/Layouts/app/AppSidebarLayout.vue'
import type { BreadcrumbItem } from '@/types'

import TestRequestCodeModal from './TestRequestCodeModal.vue'

const props = defineProps<{
  draftReport?: Record<string, any> | null
  viewOnly?: boolean
}>()

const draft = computed(() => props.draftReport ?? {})

function readDraft(...keys: string[]) {
  for (const key of keys) {
    const value = draft.value?.[key]
    if (value !== undefined && value !== null && value !== '') {
      return value
    }
  }

  return ''
}

const page1 = computed(() => ({
  surname: readDraft('surname', 'last_name', 'lastName'),
  firstName: readDraft('first_name', 'firstName', 'givenname', 'given_name'),
  middleName: readDraft('middle_name', 'middleName'),
  rsbsaNo: readDraft('rsbsa_no', 'rsbsaNo'),
  companyName: readDraft('company_name', 'companyName'),
  address: readDraft('address'),
  contactNumber: readDraft('contact_number', 'contactNumber') || '+63',
  emailAddress: readDraft('email_address', 'emailAddress'),
  sex: readDraft('sex'),
  age: readDraft('age'),
  classification: typeof draft.value.classification === 'string'
    ? draft.value.classification.split(',').map((item: string) => item.trim()).filter(Boolean)
    : (draft.value.classification ?? []),
  studentType: readDraft('student_type', 'studentType'),
  samplingDate: readDraft('sampling_date', 'samplingDate'),
  samplingTime: readDraft('sampling_time', 'samplingTime'),
}))

const page2 = computed(() => ({
  number_of_samples: readDraft('number_of_samples', 'numberOfSamples') || '1',
  sampleInfo: {
    number_of_samples: readDraft('number_of_samples', 'numberOfSamples') || '1',
    date_received: readDraft('date_received', 'dateReceived'),
    received_by: readDraft('received_by', 'receivedBy'),
    mode_of_release: readDraft('mode_of_release', 'modeOfRelease'),
    retrieve_sample: readDraft('retrieve_sample', 'retrieveSample') ? 'yes' : 'no',
    agreed_release_date: readDraft('agreed_release_date', 'agreedReleaseDate'),
    deposit: readDraft('deposit'),
    or_no: readDraft('or_no', 'orNo'),
    payment_date: readDraft('payment_date', 'paymentDate'),
    balance: readDraft('balance'),
  },
  paymentStatus: readDraft('payment_status', 'paymentStatus') || 'pending',
  requestBlocks: draft.value.samples ?? draft.value.requestBlocks ?? [],
}))

const sampleInfo = computed(() => page2.value.sampleInfo ?? page2.value ?? {})
const paymentStatus = computed(() => page2.value.paymentStatus ?? 'pending')

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Reports', href: '/reports' },
  { title: 'Customer Information', href: '/test-reports/create/page-1' },
  { title: 'Test Request Details', href: '/test-reports/create/page-2' },
  { title: 'Summary', href: '/test-reports/create/page-3' },
]

const chemistPrimaryGroups = [
  { label: 'pH and EC Analysis', tasks: ['pH', 'EC Analysis'] },
  { label: 'Organic Matter Analysis', tasks: ['Organic Matter Analysis'] },
  { label: 'Available Phosphorus', tasks: ['Available Phosphorus'] },
  { label: 'Exchangeable Bases', tasks: ['Potassium', 'Calcium', 'Magnesium', 'Sodium'] },
  { label: 'Micronutrients', tasks: ['Zinc', 'Copper', 'Iron', 'Manganese'] },
]
const agriculturistGroups = [
  { label: 'Soil Moisture', tasks: ['Soil Moisture'] },
  { label: 'Particle Size Analysis and Soil Texture', tasks: ['Particle Size Analysis', 'Soil Texture'] },
  { label: 'Fertilizer Recommendation', tasks: ['Fertilizer Recommendation'] },
]
const chemistPrimary = chemistPrimaryGroups.flatMap((group) => group.tasks)
const exchangeableBases = chemistPrimaryGroups.find((group) => group.label === 'Exchangeable Bases')?.tasks ?? []
const micronutrients = chemistPrimaryGroups.find((group) => group.label === 'Micronutrients')?.tasks ?? []
const agriculturistOptions = agriculturistGroups.flatMap((group) => group.tasks)

function normalizeSample(sample: Record<string, any>, index: number) {
  const analysisRequested = Array.isArray(sample.analysisRequested)
    ? sample.analysisRequested
    : String(sample.analysis_requested ?? sample.analysis_requested_chemist ?? sample.analysis_requested_agriculturist ?? '')
      .split(',')
      .map((item: string) => item.trim())
      .filter(Boolean)

  return {
    id: String(sample.id ?? index + 1),
    sampleDescription: sample.sampleDescription ?? sample.sample_description ?? '',
    sampleType: sample.sampleType ?? sample.sample_type ?? '',
    sampleId: sample.sampleId ?? sample.sample_id ?? '',
    soilDescription: sample.soilDescription ?? sample.soil_description ?? {
      condition: sample.soil_condition ?? sample.condition ?? '',
      color: sample.soil_color ?? sample.color ?? '',
      depth: sample.soil_depth ?? sample.depth ?? '',
      others: sample.soil_others ?? sample.others ?? '',
    },
    waterDescription: sample.waterDescription ?? sample.water_description ?? (
      sample.sample_description === 'water'
        ? {
            filtered: sample.water_filtered ?? '',
            temperature: sample.water_temperature ?? '',
            others: sample.water_others ?? '',
          }
        : undefined
    ),
    topography: sample.topography ?? '',
    longitude: sample.longitude ?? '',
    latitude: sample.latitude ?? '',
    region: sample.region ?? '',
    province: sample.province ?? '',
    municipality: sample.municipality ?? '',
    barangay: sample.barangay ?? '',
    farmArea: sample.farmArea ?? sample.farm_area ?? '',
    crops: sample.crops ?? '',
    remarks: sample.remarks ?? '',
    analysisRequested,
    subtotal: Number(sample.subtotal ?? 0),
    labCode: sample.labCode ?? sample.laboratory_code ?? '',
  }
}

function createEmptyViewSample(index: number) {
  return {
    id: String(index + 1),
    sampleDescription: '',
    sampleType: '',
    sampleId: '',
    soilDescription: {
      condition: '',
      color: '',
      depth: '',
      others: '',
    },
    waterDescription: undefined,
    topography: '',
    longitude: '',
    latitude: '',
    region: '',
    province: '',
    municipality: '',
    barangay: '',
    farmArea: '',
    crops: '',
    remarks: '',
    analysisRequested: [],
    subtotal: 0,
  }
}

const requestBlocks = computed<any[]>(() => {
  const blocks =
    page2.value.requestBlocks ??
    draft.value.samples ??
    []

  if (Array.isArray(blocks) && blocks.length > 0) {
    return blocks.map((block, index) => normalizeSample(block, index))
  }

  const count = Number(sampleInfo.value.number_of_samples || 1)

  return Array.from(
    { length: count > 0 ? count : 1 },
    (_, index) => createEmptyViewSample(index),
  )
})

const totalAmount = computed(() =>
  requestBlocks.value.reduce((sum, block) => sum + Number(block.subtotal || 0), 0),
)

function valueOf(value: unknown) {
  if (Array.isArray(value)) return value.length ? value.join(', ') : '—'
  if (value === null || value === undefined || value === '') return '—'

  const text = String(value)
  const isoDateMatch = text.match(/^(\d{4}-\d{2}-\d{2})(?:[T\s].*)?$/)

  if (isoDateMatch) {
    return isoDateMatch[1]
  }

  return text
}

function isSelected(block: any, label: string) {
  return Array.isArray(block.analysisRequested) && block.analysisRequested.includes(label)
}

function peso(value: unknown) {
  return `₱${Number(value || 0).toLocaleString()}`
}

function goBack() {
  router.visit(`/test-reports/create/page-2${props.draftReport?.id ? `?draft_id=${props.draftReport.id}` : ''}`)
}

function getSamplePrefix(sampleDescription: unknown) {
  const desc = String(sampleDescription || '').toLowerCase()

  if (desc === 'soil') return 'S'
  if (desc === 'water') return 'W'
  if (desc === 'fertilizer') return 'FM'

  return 'S'
}

function generateLabCode(block: any, index: number) {
  if (block.labCode) return block.labCode

  const year = String(new Date().getFullYear()).slice(-2)
  const prefix = getSamplePrefix(block.sampleDescription)

  const sameSampleBlocks = requestBlocks.value.filter((item) => {
    return getSamplePrefix(item.sampleDescription) === prefix
  })

  const sampleIndex =
    sameSampleBlocks.findIndex((item) => item.id === block.id) + 1 || index + 1

  return `${prefix}${year}-${String(sampleIndex).padStart(3, '0')}`
}

const showCodeModal = ref(false)
const generatedTestRequestCode = ref('')
const validationError = ref('')

const generatedLabCodes = computed(() =>
  requestBlocks.value.map((block, index) => ({
    sampleLabel: `Sample ${index + 1}`,
    labCode: generateLabCode(block, index),
  })),
)

async function fetchNextRequestCode() {
  const response = await fetch('/reports/next-request-code', {
    headers: {
      Accept: 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    },
  })

  if (!response.ok) {
    throw new Error('Unable to generate request code.')
  }

  const data = await response.json() as { request_code?: string }

  return data.request_code || ''
}

async function submit() {
  const requiredPage1Field = [
    [!page1.value.surname, 'Surname'],
    [!page1.value.firstName, 'First Name'],
    [!page1.value.address, 'Address'],
    [!page1.value.contactNumber || page1.value.contactNumber === '+63', 'Contact No.'],
    [!page1.value.classification?.length, 'Client Classification'],
    [!page1.value.sex, 'Sex'],
    [!page1.value.age, 'Age'],
  ].find(([missing]) => missing)?.[1]

  if (requiredPage1Field) {
    validationError.value = `${requiredPage1Field} is required.`
    return
  }

  if (page1.value.classification?.includes?.('Student') && !page1.value.studentType) {
    validationError.value = 'Specify if Student is required.'
    return
  }

  const requiredPage2Field = [
    [!sampleInfo.value.mode_of_release, 'Mode of Release'],
    [!sampleInfo.value.retrieve_sample, 'Retrieve retained sample after analysis'],
    [!sampleInfo.value.agreed_release_date, 'Agreed Date of Release Results'],
    [!sampleInfo.value.number_of_samples, 'No. of Samples Submitted'],
    [!sampleInfo.value.received_by, 'Received By'],
  ].find(([missing]) => missing)?.[1]

  if (requiredPage2Field) {
    validationError.value = `${requiredPage2Field} is required.`
    return
  }

  const requiredBlockError = requestBlocks.value.flatMap((block, index) => ([
    [!block.sampleDescription, `Sample ${index + 1}: Sample Description`],
    [!block.sampleType, `Sample ${index + 1}: Sample Type`],
    [!block.sampleId, `Sample ${index + 1}: Sample ID`],
    [block.sampleDescription === 'soil' && !block.soilDescription?.condition, `Sample ${index + 1}: Condition`],
    [block.sampleDescription === 'soil' && !block.soilDescription?.color, `Sample ${index + 1}: Color`],
    [block.sampleDescription === 'soil' && !block.soilDescription?.depth, `Sample ${index + 1}: Soil Depth`],
    [block.sampleDescription === 'water' && !block.waterDescription?.filtered, `Sample ${index + 1}: Filtered`],
    [block.sampleDescription === 'water' && !block.waterDescription?.temperature, `Sample ${index + 1}: Temperature`],
    [!block.topography, `Sample ${index + 1}: Topography`],
    [!block.longitude, `Sample ${index + 1}: Longitude`],
    [!block.latitude, `Sample ${index + 1}: Latitude`],
    [!block.region, `Sample ${index + 1}: Region`],
    [!block.province, `Sample ${index + 1}: Province`],
    [!block.municipality, `Sample ${index + 1}: Municipality`],
    [!block.barangay, `Sample ${index + 1}: Barangay`],
    [!block.farmArea, `Sample ${index + 1}: Farm Area`],
    [!block.crops, `Sample ${index + 1}: Crops`],
  ].find(([missing]) => missing)?.[1])).find(Boolean)

  if (requiredBlockError) {
    validationError.value = `${requiredBlockError} is required.`
    return
  }

  validationError.value = ''
  generatedTestRequestCode.value = draft.value.request_code || await fetchNextRequestCode()
  showCodeModal.value = true
}

function saveReportRecord() {
  return {
    request_code: generatedTestRequestCode.value || draft.value.request_code,
    date: new Date().toISOString().slice(0, 10),
    status: 'Test Request submitted',
    is_draft: false,
    surname: page1.value.surname,
    first_name: page1.value.firstName,
    middle_name: page1.value.middleName,
    full_name: [
      page1.value.firstName,
      page1.value.middleName,
      page1.value.surname,
    ].filter(Boolean).join(' '),
    rsbsa_no: page1.value.rsbsaNo,
    company_name: page1.value.companyName,
    classification: page1.value.classification,
    student_type: page1.value.studentType,
    sex: page1.value.sex,
    age: page1.value.age,
    address: page1.value.address,
    contact_number: page1.value.contactNumber,
    email_address: page1.value.emailAddress,
    sampling_date: page1.value.samplingDate,
    sampling_time: page1.value.samplingTime,
    mode_of_release: sampleInfo.value.mode_of_release,
    retrieve_sample: sampleInfo.value.retrieve_sample,
    agreed_release_date: sampleInfo.value.agreed_release_date,
    number_of_samples: sampleInfo.value.number_of_samples,
    date_received: sampleInfo.value.date_received,
    received_by: sampleInfo.value.received_by,
    payment_status: paymentStatus.value,
    deposit: Number(sampleInfo.value.deposit || 0),
    or_no: sampleInfo.value.or_no,
    payment_date: sampleInfo.value.payment_date,
    balance: Number(sampleInfo.value.balance || 0),
    total_amount_due: totalAmount.value,
    samples: requestBlocks.value.map((block, index) => ({
      laboratory_code: generatedLabCodes.value[index]?.labCode,
      sample_id: block.sampleId,
      sample_description: block.sampleDescription,
      sample_type: block.sampleType,
      soil_condition: block.soilDescription?.condition || null,
      soil_color: block.soilDescription?.color || null,
      soil_depth: block.soilDescription?.depth || null,
      soil_others: block.soilDescription?.others || null,
      water_filtered: block.waterDescription?.filtered || null,
      water_temperature: block.waterDescription?.temperature || null,
      water_others: block.waterDescription?.others || null,
      topography: block.topography,
      longitude: block.longitude,
      latitude: block.latitude,
      region: block.region,
      province: block.province,
      municipality: block.municipality,
      barangay: block.barangay,
      farm_area: block.farmArea,
      crops: block.crops,
      remarks: block.remarks,
      analysis_requested: Array.isArray(block.analysisRequested)
        ? block.analysisRequested.join(', ')
        : block.analysisRequested,
      subtotal: Number(block.subtotal || 0),
    })),
  }
}

function confirmSubmit() {
  const payload = saveReportRecord()

  const method = props.draftReport?.id ? 'put' : 'post'
  const url = props.draftReport?.id ? `/reports/${props.draftReport.id}` : '/reports'

  router[method](url, payload, {
    preserveScroll: true,
    onSuccess: () => {
      router.visit('/reports')
    },
  })
}
</script>

<template>
  <Head title="Test Request Summary" />

  <AppSidebarLayout :breadcrumbs="breadcrumbs">
    <div class="relative p-4 md:p-6">
      <div class="mx-auto max-w-7xl">
        <div class="grid gap-6 xl:grid-cols-[1fr_320px]">
          <div class="space-y-5">
            <div class="rounded-xl border border-[#F09816] p-4">
              <h2 class="mb-4 text-base font-semibold text-zinc-900">
                Customer InformationF09816
              </h2>

              <div class="grid gap-4 md:grid-cols-[1fr_1fr_1fr_1.5fr]">
                <div>
                  <label class="label">Surname</label>
                  <div class="view-box">{{ valueOf(page1.surname) }}</div>
                </div>

                <div>
                  <label class="label">First Name</label>
                  <div class="view-box">{{ valueOf(page1.firstName) }}</div>
                </div>

                <div>
                  <label class="label">Middle Name</label>
                  <div class="view-box">{{ valueOf(page1.middleName) }}</div>
                </div>

                <div>
                  <label class="label">RSBSA No.</label>
                  <div class="view-box">{{ valueOf(page1.rsbsaNo) }}</div>
                </div>
              </div>

              <div class="mt-4 grid gap-6 lg:grid-cols-[1.25fr_0.75fr]">
                <div class="space-y-4">
                  <div>
                    <label class="label">Name of this Company</label>
                    <div class="view-box">
                      <Building2 class="h-4 w-4 text-zinc-400" />
                      {{ valueOf(page1.companyName) }}
                    </div>
                  </div>

                  <div>
                    <label class="label">Address</label>
                    <div class="view-box">
                      <MapPin class="h-4 w-4 text-zinc-400" />
                      {{ valueOf(page1.address) }}
                    </div>
                  </div>

                  <div class="grid gap-4 md:grid-cols-2">
                    <div>
                      <label class="label">Contact No.</label>
                      <div class="view-box">
                        <Phone class="h-4 w-4 text-zinc-400" />
                        {{ valueOf(page1.contactNumber) }}
                      </div>
                    </div>

                    <div>
                      <label class="label">Email Address</label>
                      <div class="view-box">
                        <Mail class="h-4 w-4 text-zinc-400" />
                        {{ valueOf(page1.emailAddress) }}
                      </div>
                    </div>
                  </div>
                </div>

                <div class="space-y-4">
                  <div>
                    <label class="label">Client Classification</label>
                    <div class="view-box">{{ valueOf(page1.classification) }}</div>
                  </div>

                  <div v-if="page1.classification?.includes?.('Student')">
                    <label class="label">Specify if Student</label>
                    <div class="view-box">{{ valueOf(page1.studentType) }}</div>
                  </div>

                  <div class="grid gap-4 md:grid-cols-2">
                    <div>
                      <label class="label">Sex</label>
                      <div class="view-box">
                        <User class="h-4 w-4 text-zinc-400" />
                        {{ valueOf(page1.sex) }}
                      </div>
                    </div>

                    <div>
                      <label class="label">Age</label>
                      <div class="view-box">{{ valueOf(page1.age) }}</div>
                    </div>
                  </div>

                  <div class="grid gap-4 md:grid-cols-2">
                    <div>
                      <label class="label">Date of Sampling</label>
                      <div class="view-box">
                        <CalendarDays class="h-4 w-4 text-zinc-400" />
                        {{ valueOf(page1.samplingDate) }}
                      </div>
                    </div>

                    <div>
                      <label class="label">Time of Sampling</label>
                      <div class="view-box">{{ valueOf(page1.samplingTime) }}</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="rounded-xl border-1 border-[#F09816] p-5 light:border-[#F09816]">
              <div class="grid gap-4 md:grid-cols-2">
                <div>
                  <label class="label">Mode of Release</label>
                  <div class="view-box">{{ valueOf(sampleInfo.mode_of_release) }}</div>
                </div>

                <div>
                  <label class="label">Retrieve retained sample after analysis</label>
                  <div class="view-box">{{ valueOf(sampleInfo.retrieve_sample) }}</div>
                </div>
              </div>

              <div class="mt-4 max-w-xs">
                <label class="label">Agreed Date of Release Results</label>
                <div class="view-box">{{ valueOf(sampleInfo.agreed_release_date) }}</div>
              </div>
            </div>

            <div class="rounded-xl border-1 border-[#F09816] p-5 light:border-[#F09816]">
              <div class="grid gap-4 md:grid-cols-3">
                <div>
                  <label class="label">No. of Samples Submitted</label>
                  <div class="view-box">{{ valueOf(sampleInfo.number_of_samples) }}</div>
                </div>

                <div>
                  <label class="label">Date Received</label>
                  <div class="view-box">
                    <CalendarDays class="h-4 w-4 text-zinc-400" />
                    {{ valueOf(sampleInfo.date_received) }}
                  </div>
                </div>

                <div>
                  <label class="label">Received By</label>
                  <div class="view-box">
                    <UserRoundCheck class="h-4 w-4 text-zinc-400" />
                    {{ valueOf(sampleInfo.received_by) }}
                  </div>
                </div>
              </div>
            </div>

            <div
              v-for="(block, index) in requestBlocks"
              :key="block.id || index"
              class="rounded-xl border border-[#1BBE55] p-4"
            >
              <div class="mb-4 flex items-center justify-between gap-4">
                <div class="inline-flex items-center rounded-full border border-zinc-200 bg-zinc-50 px-3 py-1 text-xs font-semibold text-zinc-700">
                  Sample {{ Number(index) + 1 }}
                </div>

                <div class="text-xs font-medium text-zinc-500">
                  {{ Number(index) + 1 }} of {{ sampleInfo.number_of_samples || requestBlocks.length }}
                </div>
              </div>

              <div class="space-y-4">
                <div class="grid gap-4 md:grid-cols-3">
                  <div>
                    <label class="label">Sample Description</label>
                    <div class="view-box">{{ valueOf(block.sampleDescription) }}</div>
                  </div>

                  <div>
                    <label class="label">Sample Type</label>
                    <div class="view-box">{{ valueOf(block.sampleType) }}</div>
                  </div>

                  <div>
                    <label class="label">Sample ID</label>
                    <div class="view-box">{{ valueOf(block.sampleId) }}</div>
                  </div>
                </div>

                <div
                  v-if="block.sampleDescription === 'soil' || block.soilDescription"
                  class="rounded-lg border border-[#0E3D1A] bg-zinc-50 p-3"
                >
                  <label class="mb-2 block text-sm font-semibold text-zinc-900">
                    Soil Description
                  </label>

                  <div class="grid gap-4 md:grid-cols-4">
                    <div>
                      <label class="label">Condition</label>
                      <div class="view-box">{{ valueOf(block.soilDescription?.condition) }}</div>
                    </div>

                    <div>
                      <label class="label">Color</label>
                      <div class="view-box">{{ valueOf(block.soilDescription?.color) }}</div>
                    </div>

                    <div>
                      <label class="label">Soil Depth</label>
                      <div class="view-box">{{ valueOf(block.soilDescription?.depth) }}</div>
                    </div>

                    <div>
                      <label class="label">Others</label>
                      <div class="view-box">{{ valueOf(block.soilDescription?.others) }}</div>
                    </div>
                  </div>
                </div>

                <div
                  v-if="block.sampleDescription === 'water' || block.waterDescription"
                  class="rounded-lg border border-[#0E3D1A] bg-zinc-50 p-3"
                >
                  <label class="mb-2 block text-sm font-semibold text-zinc-900">
                    Water Description
                  </label>

                  <div class="grid gap-4 md:grid-cols-3">
                    <div>
                      <label class="label">Filtered</label>
                      <div class="view-box">{{ valueOf(block.waterDescription?.filtered) }}</div>
                    </div>

                    <div>
                      <label class="label">Temperature</label>
                      <div class="view-box">{{ valueOf(block.waterDescription?.temperature) }}</div>
                    </div>

                    <div>
                      <label class="label">Others</label>
                      <div class="view-box">{{ valueOf(block.waterDescription?.others) }}</div>
                    </div>
                  </div>
                </div>

                <div class="border-t border-[#CF8A16] pt-4">
                  <div class="grid gap-4 md:grid-cols-3">
                    <div>
                      <label class="label">Topography</label>
                      <div class="view-box">{{ valueOf(block.topography) }}</div>
                    </div>

                    <div>
                      <label class="label text-center">Coordinates</label>
                      <div class="view-box">{{ valueOf(block.longitude) }}</div>
                    </div>

                    <div>
                      <label class="label">&nbsp;</label>
                      <div class="view-box">{{ valueOf(block.latitude) }}</div>
                    </div>
                  </div>
                </div>

                <div class="border-t border-[#CF8A16] pt-4">
                  <div class="grid gap-4 md:grid-cols-4">
                    <div>
                      <label class="label">Region</label>
                      <div class="view-box">{{ valueOf(block.region) }}</div>
                    </div>

                    <div>
                      <label class="label">Province</label>
                      <div class="view-box">{{ valueOf(block.province) }}</div>
                    </div>

                    <div>
                      <label class="label">Municipality</label>
                      <div class="view-box">{{ valueOf(block.municipality) }}</div>
                    </div>

                    <div>
                      <label class="label">Barangay</label>
                      <div class="view-box">{{ valueOf(block.barangay) }}</div>
                    </div>
                  </div>
                </div>

                <div class="border-t border-[#CF8A16] pt-4">
                  <div class="grid gap-4 md:grid-cols-2">
                    <div>
                      <label class="label">Farm Area</label>
                      <div class="view-box">{{ valueOf(block.farmArea) }}</div>
                    </div>

                    <div>
                      <label class="label">Crops</label>
                      <div class="view-box">{{ valueOf(block.crops) }}</div>
                    </div>
                  </div>

                  <div class="mt-3">
                    <label class="label">Remarks</label>
                    <div class="view-box min-h-16 items-start py-2">
                      {{ valueOf(block.remarks) }}
                    </div>
                  </div>
                </div>

                <div class="border-t border-[#CF8A16] pt-4">
                  <h3 class="mb-4 text-sm font-semibold text-zinc-900">
                    Analysis Requested
                  </h3>

                  <div class="grid gap-8 md:grid-cols-2 text-sm text-zinc-700">
                    <div>
                      <div class="mb-2 font-semibold text-zinc-900">
                        Chemist
                      </div>

                      <div
                        v-if="
                          !chemistPrimary.some(opt => isSelected(block, opt)) &&
                          !exchangeableBases.some(opt => isSelected(block, opt)) &&
                          !micronutrients.some(opt => isSelected(block, opt))
                        "
                        class="text-zinc-400"
                      >
                        —
                      </div>

                      <ul v-else class="space-y-1">
                        <li
                          v-for="opt in chemistPrimary.filter(opt => isSelected(block, opt))"
                          :key="opt"
                        >
                          · {{ opt }}
                        </li>

                        <li v-if="exchangeableBases.some(opt => isSelected(block, opt))">
                          · Exchangeable Bases
                          <ul class="ml-6 mt-1 space-y-1">
                            <li
                              v-for="opt in exchangeableBases.filter(opt => isSelected(block, opt))"
                              :key="opt"
                            >
                              - {{ opt }}
                            </li>
                          </ul>
                        </li>

                        <li v-if="micronutrients.some(opt => isSelected(block, opt))">
                          · Micronutrients
                          <ul class="ml-6 mt-1 space-y-1">
                            <li
                              v-for="opt in micronutrients.filter(opt => isSelected(block, opt))"
                              :key="opt"
                            >
                              - {{ opt }}
                            </li>
                          </ul>
                        </li>
                      </ul>
                    </div>

                    <div>
                      <div class="mb-2 font-semibold text-zinc-900">
                        Agriculturist
                      </div>

                      <div
                        v-if="!agriculturistOptions.some(opt => isSelected(block, opt))"
                        class="text-zinc-400"
                      >
                        —
                      </div>

                      <ul v-else class="space-y-1">
                        <li
                          v-for="opt in agriculturistOptions.filter(opt => isSelected(block, opt))"
                          :key="opt"
                        >
                          · {{ opt }}
                        </li>
                      </ul>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="space-y-5">
            <div class="h-fit rounded-xl border-1 border-[#F09816] p-5 light:border-[#F09816]">
              <div class="mb-5 flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#0E3D1A] text-white">
                  <CreditCard class="h-5 w-5" />
                </div>

                <div>
                  <h2 class="text-lg font-semibold text-zinc-900">
                    Payment Status
                  </h2>
                </div>
              </div>

              <div class="space-y-4">
                <div>
                  <label class="label">Status</label>
                  <div class="view-box capitalize">{{ valueOf(paymentStatus) }}</div>
                </div>

                <div>
                  <label class="label">Deposit</label>
                  <div class="view-box">{{ valueOf(sampleInfo.deposit) }}</div>
                </div>

                <div>
                  <label class="label">O.R. No.</label>
                  <div class="view-box">{{ valueOf(sampleInfo.or_no) }}</div>
                </div>

                <div>
                  <label class="label">Date</label>
                  <div class="view-box">{{ valueOf(sampleInfo.payment_date) }}</div>
                </div>

                <div>
                  <label class="label">Balance</label>
                  <div class="view-box">{{ valueOf(sampleInfo.balance) }}</div>
                </div>
              </div>
            </div>

            <div class="h-fit rounded-xl border-1 border-[#F09816] p-5 light:border-[#F09816]">
              <h2 class="text-lg font-semibold text-zinc-900">
                Subtotal Summary
              </h2>
              <p class="mt-1 text-sm text-zinc-500">
                Per sample only
              </p>

              <div class="mt-5 space-y-3">
                <div
                  v-for="(block, index) in requestBlocks"
                  :key="`summary-${block.id || index}`"
                  class="rounded-xl border border-zinc-200 p-4"
                >
                  <div class="flex items-center justify-between gap-3">
                    <span class="text-sm font-medium text-zinc-700">
                      Sample {{ Number(index) + 1 }}
                    </span>
                    <span class="text-sm font-semibold text-zinc-900">
                      {{ peso(block.subtotal) }}
                    </span>
                  </div>
                </div>

                <div class="rounded-xl border border-[#0E3D1A] p-4">
                  <div class="flex items-center justify-between gap-3">
                    <span class="text-sm font-semibold text-zinc-900">
                      Total
                    </span>
                    <span class="text-sm font-bold text-[#0E3D1A]">
                      {{ peso(totalAmount) }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div
          v-if="!viewOnly"
          class="mt-8 flex items-center justify-between border-t border-zinc-200 pt-6"
        >
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
            Step 3 of 3
          </div>

          <button
            type="button"
            @click="submit"
            class="inline-flex items-center gap-2 rounded-lg bg-[#0E3D1A] px-5 py-2.5 text-sm font-semibold text-white transition hover:opacity-90"
          >
            Submit
            <ArrowRight class="h-4 w-4" />
          </button>
        </div>
      </div>
    </div>
      <TestRequestCodeModal
        v-if="!viewOnly"
        :show="showCodeModal"
        :test-request-code="generatedTestRequestCode"
        :lab-codes="generatedLabCodes"
        @close="showCodeModal = false"
        @confirm="confirmSubmit"
      />
  </AppSidebarLayout>
</template>

<style scoped>
.view-box {
  display: flex;
  min-height: 2.25rem;
  width: 100%;
  align-items: center;
  gap: 0.5rem;
  border-radius: 0.5rem;
  border: 1px solid rgb(228 228 231);
  background: white;
  padding: 0.45rem 0.65rem;
  font-size: 0.8rem;
  color: rgb(24 24 27);
}

.dark .view-box {
  border-color: rgb(63 63 70);
  background: #0b0b0b;
  color: white;
}

.label {
  margin-bottom: 0.35rem;
  display: block;
  font-size: 0.8rem;
  font-weight: 500;
  color: rgb(63 63 70);
}

.dark .label {
  color: rgb(228 228 231);
}

.check-view {
  display: flex;
  min-height: 2.25rem;
  align-items: center;
  gap: 0.5rem;
  border-radius: 0.5rem;
  border: 1px solid rgb(228 228 231);
  padding: 0.45rem 0.65rem;
  font-size: 0.75rem;
  color: rgb(63 63 70);
}

.dark .check-view {
  border-color: rgb(63 63 70);
  color: rgb(228 228 231);
}

.check-box {
  display: inline-flex;
  height: 0.85rem;
  width: 0.85rem;
  flex-shrink: 0;
  align-items: center;
  justify-content: center;
  border-radius: 0.2rem;
  border: 1px solid rgb(113 113 122);
  background: white;
}

.check-box.selected {
  border-color: #0E3D1A;
  background: #0E3D1A;
}

.check-box.selected::after {
  content: '✓';
  color: white;
  font-size: 0.6rem;
  font-weight: 700;
}

.dark .check-box {
  background: #0b0b0b;
}
</style>
