<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import type { BreadcrumbItem } from '@/types'
import { computed, ref } from 'vue'
import {
  CalendarDays,
  UserRoundCheck,
  ArrowLeft,
  ArrowRight,
  Plus,
  Copy,
  CreditCard,
  ChevronDown,
  TriangleAlert,
  CircleCheckBig,
} from 'lucide-vue-next'

type SampleDescription = 'soil' | 'water' | 'fertilizer' | ''
type SampleType = string

type ModeOfRelease = 'courier' | 'online' | ''
type RetrieveSample = 'yes' | 'no' | ''

type SoilDescription = {
  condition: 'dry' | 'wet' | ''
  color: string
  depth: string
  others: string
}

type WaterDescription = {
  filtered: 'yes' | 'no' | ''
  temperature: string
  others: string
}

type ProvinceLocationData = {
  municipalities: string[]
  barangays: Record<string, string[]>
}

type LocationData = Record<string, Record<string, ProvinceLocationData>>

type AnalysisRequestedOption = {
  category: 'chemist' | 'agriculturist'
  label: string
}

type TestRequestForm = {
  id: string
  sampleDescription: SampleDescription
  sampleType: SampleType
  soilDescription?: SoilDescription
  waterDescription?: WaterDescription
  sampleId: string
  analysisRequested: string[]
  topography: string
  longitude: string
  latitude: string
  region: string
  province: string
  municipality: string
  barangay: string
  farmArea: string
  crops: string
  remarks: string
  subtotal: number
}

const props = defineProps<{
  client_id?: string | number
  from_existing?: boolean
}>()

const page = usePage()

const currentUserFullName = computed(() => {
  const auth = (page.props as Record<string, any>).auth?.user

  if (!auth) return 'Current User'
  if (auth.name) return auth.name

  const parts = [
    auth.first_name,
    auth.firstName,
    auth.givenname,
    auth.given_name,
    auth.surname,
    auth.last_name,
    auth.lastName,
  ].filter(Boolean)

  return parts.length ? parts.join(' ') : 'Current User'
})

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Reports', href: '/reports' },
  { title: 'Customer Information', href: '/test-reports/create/page-1' },
  { title: 'Test Request Details', href: '/test-reports/create/page-2' },
]

const LOCATION_DATA: LocationData = {
  'Region III': {
    'Nueva Ecija': {
      municipalities: ['Cabanatuan City', 'Gapan', 'San Jose City', 'Palayan City', 'Science City of Muñoz'],
      barangays: {
        'Cabanatuan City': ['Aduas Centro', 'Bakero', 'Banbanaba', 'Barrera', 'Bagong Buhay'],
        Gapan: ['Bayanihan', 'Bungo', 'Buliran', 'Kapalangan', 'Macabaclay'],
        'San Jose City': ['Abar Ist', 'Abar 2nd', 'Bagong Sikat', 'Calaocan', 'Camanacsacan'],
        'Palayan City': ['Atate', 'Caballero', 'Caimito', 'Doña Josefa', 'Langka'],
        'Science City of Muñoz': ['Bagong Sikat', 'Balante', 'Bantug', 'Catalanacan', 'Gabaldon'],
      },
    },
    Pampanga: {
      municipalities: ['Angeles City', 'San Fernando', 'Mabalacat', 'Porac', 'Magalang'],
      barangays: {
        'Angeles City': ['Agapito del Rosario', 'Amsic', 'Balibago', 'Claro M. Recto', 'Cutcut'],
        'San Fernando': ['Alasas', 'Baliti', 'Bulaon', 'Calulut', 'Dela Paz Norte'],
        Mabalacat: ['Atlu Bola', 'Bical', 'Bundagul', 'Camachiles', 'Dau'],
        Porac: ['Babo Pangulo', 'Babo Sacan', 'Balubad', 'Cangatba', 'Jalung'],
        Magalang: ['Ayala', 'Bucanan', 'Camias', 'San Agustin', 'San Ildefonso'],
      },
    },
    Bulacan: {
      municipalities: ['Malolos', 'Meycauayan', 'San Jose del Monte', 'Marilao', 'Bocaue'],
      barangays: {
        Malolos: ['Anilao', 'Atlag', 'Babatnin', 'Bagna', 'Bagong Bayan'],
        Meycauayan: ['Bagbaguin', 'Bahay Pare', 'Bancal', 'Banga', 'Bayugo'],
        'San Jose del Monte': ['Assumption', 'Bagong Buhay I', 'Bagong Buhay II', 'Ciudad Real', 'Dulong Bayan'],
        Marilao: ['Abangan Norte', 'Abangan Sur', 'Ibayo', 'Lambakin', 'Loma de Gato'],
        Bocaue: ['Antipona', 'Bagumbayan', 'Bambang', 'Batia', 'Biñang 1st'],
      },
    },
  },
  'Region IV-A': {
    Laguna: {
      municipalities: ['Santa Rosa', 'Biñan', 'San Pedro', 'Cabuyao', 'Calamba'],
      barangays: {
        'Santa Rosa': ['Aplaya', 'Balibago', 'Caingin', 'Dila', 'Dita'],
        Biñan: ['Biñan', 'Bungahan', 'Canlalay', 'Casile', 'De La Paz'],
        'San Pedro': ['Bagong Silang', 'Calendola', 'Chrysanthemum', 'Estrella', 'Fatima'],
        Cabuyao: ['Baclaran', 'Banaybanay', 'Banlic', 'Bigaa', 'Butong'],
        Calamba: ['Bagong Kalsada', 'Banadero', 'Banlic', 'Barandal', 'Batino'],
      },
    },
    Cavite: {
      municipalities: ['Bacoor', 'Imus', 'Dasmariñas', 'General Trias', 'Kawit'],
      barangays: {
        Bacoor: ['Alima', 'Aniban I', 'Aniban II', 'Banalo', 'Bayanan'],
        Imus: ['Alapan I-A', 'Alapan I-B', 'Alapan II-A', 'Anabu I-A', 'Bayan Luma I'],
        'Dasmariñas': ['Burol', 'Datu Esmael', 'E. Aguinaldo', 'Langkaan I', 'Paliparan I'],
        'General Trias': ['Alingaro', 'Arnaldo', 'Bacao I', 'Biclatan', 'Buenavista I'],
        Kawit: ['Balsahan', 'Binakayan-Aplaya', 'Binakayan-Kanluran', 'Congbalay-Legaspi', 'Gahak'],
      },
    },
  },
  NCR: {
    'Metro Manila': {
      municipalities: ['Manila', 'Quezon City', 'Makati', 'Pasig', 'Taguig'],
      barangays: {
        Manila: ['Ermita', 'Intramuros', 'Malate', 'Paco', 'Pandacan'],
        'Quezon City': ['Bagong Pag-asa', 'Bahay Toro', 'Balingasa', 'Batasan Hills', 'Central'],
        Makati: ['Bangkal', 'Bel-Air', 'Carmona', 'Dasmariñas', 'Forbes Park'],
        Pasig: ['Bagong Ilog', 'Bagong Katipunan', 'Bambang', 'Buting', 'Caniogan'],
        Taguig: ['Bagumbayan', 'Bambang', 'Calzada', 'Central Bicutan', 'Fort Bonifacio'],
      },
    },
  },
}

const cropOptions = ['Corn', 'Rice', 'Okra', 'String Beans', 'Squash']

const analysisRequestedOptions: AnalysisRequestedOption[] = [
  { category: 'chemist', label: 'pH' },
  { category: 'chemist', label: 'EC Analysis' },
  { category: 'chemist', label: 'Organic Matter Analysis' },
  { category: 'chemist', label: 'Available Phosphorus' },
  { category: 'chemist', label: 'Potassium' },
  { category: 'chemist', label: 'Calcium' },
  { category: 'chemist', label: 'Magnesium' },
  { category: 'chemist', label: 'Sodium' },
  { category: 'chemist', label: 'Zinc' },
  { category: 'chemist', label: 'Copper' },
  { category: 'chemist', label: 'Iron' },
  { category: 'chemist', label: 'Manganese' },
  { category: 'agriculturist', label: 'Soil Moisture' },
  { category: 'agriculturist', label: 'Particle Size Analysis' },
  { category: 'agriculturist', label: 'Soil Texture' },
  { category: 'agriculturist', label: 'Fertilizer Recommendation' },
]

const sampleInfo = ref({
  number_of_samples: '1',
  date_received: new Date().toISOString().split('T')[0],
  received_by: currentUserFullName.value,
  mode_of_release: '' as ModeOfRelease,
  retrieve_sample: '' as RetrieveSample,
  agreed_release_date: '',
  deposit: '',
  or_no: '',
  payment_date: '',
  balance: '',
})

const paymentStatus = ref<'pending' | 'paid'>('pending')
const openAnalysisDropdownIndex = ref<number | null>(null)
const showMismatchModal = ref(false)
const showConfirmProceed = ref(false)

const DRAFT_KEY = 'test-request-draft'
const savedDraft = JSON.parse(localStorage.getItem(DRAFT_KEY) || '{}')

function createEmptyTestRequestForm(id: string): TestRequestForm {
  return {
    id,
    sampleDescription: '',
    sampleType: '',
    sampleId: '',
    analysisRequested: [],
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
    subtotal: 0,
  }
}

const requestBlocks = ref<TestRequestForm[]>([
  createEmptyTestRequestForm('1'),
])

if (savedDraft.page2) {
  sampleInfo.value = savedDraft.page2.sampleInfo ?? sampleInfo.value
  paymentStatus.value = savedDraft.page2.paymentStatus ?? paymentStatus.value
  requestBlocks.value = savedDraft.page2.requestBlocks ?? requestBlocks.value
}

const declaredSampleCount = computed(() => Number(sampleInfo.value.number_of_samples || 0))
const currentFormCount = computed(() => requestBlocks.value.length)

const subtotalSummary = computed(() =>
  requestBlocks.value.map((block, index) => ({
    label: `Sample ${index + 1}`,
    subtotal: Number(block.subtotal || 0),
  })),
)

function goBack() {
  if (props.client_id) {
    router.visit(`/test-reports/create/page-1?client_id=${props.client_id}`)
    return
  }

  router.visit('/test-reports/create/page-1')
}

function savePage2() {
  const draft = JSON.parse(localStorage.getItem(DRAFT_KEY) || '{}')

  localStorage.setItem(DRAFT_KEY, JSON.stringify({
    ...draft,
    page2: {
      sampleInfo: sampleInfo.value,
      paymentStatus: paymentStatus.value,
      requestBlocks: requestBlocks.value,
    },
  }))
}

function proceedToNextPage() {
  savePage2()
  router.visit('/test-reports/create/page-3')
}

function goNext() {
  handleNextWithValidation()
}

function handleNextWithValidation() {
  if (declaredSampleCount.value !== currentFormCount.value) {
    showMismatchModal.value = true
    return
  }

  proceedToNextPage()
}

function forceProceed() {
  showMismatchModal.value = false
  showConfirmProceed.value = true
}

function handleConfirmProceed() {
  showConfirmProceed.value = false
  proceedToNextPage()
}

function handleCancelMismatch() {
  showMismatchModal.value = false
}

function handleCancelProceedConfirmation() {
  showConfirmProceed.value = false
}

function handleAddTestRequest() {
  const newId = String(requestBlocks.value.length + 1)
  requestBlocks.value.push(createEmptyTestRequestForm(newId))
}

function handleDuplicateTestRequest(index: number) {
  const blockToDuplicate = requestBlocks.value[index]
  const newId = String(requestBlocks.value.length + 1)

  requestBlocks.value.push({
    ...blockToDuplicate,
    id: newId,
    sampleId: '',
    analysisRequested: [...blockToDuplicate.analysisRequested],
  })
}

function removeTestRequest(index: number) {
  requestBlocks.value = requestBlocks.value.filter((_, i) => i !== index)
}

function updateSampleInfo(field: keyof typeof sampleInfo.value, value: string) {
  sampleInfo.value[field] = value as never
}

function updatePaymentStatus(value: 'pending' | 'paid') {
  paymentStatus.value = value
}

function updateTestRequest(index: number, field: string, value: unknown) {
  const updated = [...requestBlocks.value]
  const testRequest = { ...updated[index] }

  if (field.includes('.')) {
    const [parent, child] = field.split('.')
    testRequest[parent as keyof typeof testRequest] = {
      ...(testRequest[parent as keyof typeof testRequest] as Record<string, unknown>),
      [child]: value,
    } as never
  } else {
    testRequest[field as keyof typeof testRequest] = value as never
  }

  updated[index] = testRequest
  requestBlocks.value = updated
}

function handleSampleDescriptionChange(index: number, value: SampleDescription) {
  const updated = [...requestBlocks.value]

  updated[index] = {
    ...updated[index],
    sampleDescription: value,
    sampleType: '',
    soilDescription:
      value === 'soil'
        ? { condition: '', color: '', depth: '', others: '' }
        : undefined,
    waterDescription:
      value === 'water'
        ? { filtered: '', temperature: '', others: '' }
        : undefined,
  }

  requestBlocks.value = updated
}

function toggleAnalysisDropdown(index: number) {
  openAnalysisDropdownIndex.value = openAnalysisDropdownIndex.value === index ? null : index
}

function closeAnalysisDropdown() {
  openAnalysisDropdownIndex.value = null
}

function toggleAnalysisRequested(index: number, analysis: string) {
  const current = requestBlocks.value[index].analysisRequested
  const updated = current.includes(analysis)
    ? current.filter((item) => item !== analysis)
    : [...current, analysis]

  updateTestRequest(index, 'analysisRequested', updated)
}

function getAnalysisRequested(block: any): string[] {
  const value =
    block.analysisRequested ??
    block.analysis_requested ??
    block.analysisRequestedOptions ??
    block.analysis_requested_options ??
    block.analysis ??
    block.tests ??
    block.test_requested ??
    block.testRequested ??
    []

  if (Array.isArray(value)) {
    return value.map((item) => {
      if (typeof item === 'string') return item
      return item.label ?? item.name ?? item.value ?? ''
    }).filter(Boolean)
  }

  if (typeof value === 'string') {
    return value
      .split(',')
      .map((item) => item.trim())
      .filter(Boolean)
  }

  return []
}

function isSelected(block: any, label: string) {
  return getAnalysisRequested(block).some(
    (item) => item.toLowerCase() === label.toLowerCase(),
  )
}

function getProvinces(region: string): string[] {
  if (!region) return []

  const regionData = LOCATION_DATA[region]
  return regionData ? Object.keys(regionData) : []
}

function getMunicipalities(region: string, province: string): string[] {
  if (!region || !province) return []

  const regionData = LOCATION_DATA[region]
  if (!regionData) return []

  const provinceData = regionData[province]
  return provinceData ? provinceData.municipalities : []
}

function getBarangays(region: string, province: string, municipality: string): string[] {
  if (!region || !province || !municipality) return []

  const regionData = LOCATION_DATA[region]
  if (!regionData) return []

  const provinceData = regionData[province]
  if (!provinceData) return []

  return provinceData.barangays[municipality] ?? []
}
</script>

<template>
  <Head title="Create Test Request - Page 2" />

  <AppSidebarLayout :breadcrumbs="breadcrumbs">
    <div class="relative p-4 md:p-6">
      <div class="mx-auto max-w-7xl">
        <div class="rounded-2xl border border-zinc-200 bg-white shadow-sm">
          <div class="border-b border-zinc-200 px-6 py-5">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
              <div class="text-center md:text-left">
                <h1 class="text-2xl font-bold text-zinc-900">
                  Create Test Request
                </h1>
                <p class="mt-1 text-sm text-zinc-500">
                  Test Request Details
                </p>
              </div>
            </div>
          </div>

          <div class="px-6 py-6 md:px-8 md:py-8">
            <div class="grid gap-6 xl:grid-cols-[1fr_320px]">
              <div class="space-y-6">
                <div class="rounded-xl border-1 border-[#F09816] p-5 light:border-[#F09816]">
                  <div class="grid gap-6 md:grid-cols-2">
                    <div>
                      <label class="mb-2 block text-sm font-medium text-zinc-700">
                        Mode of Release <span class="text-rose-500">*</span>
                      </label>
                      <div class="flex gap-4">
                        <label class="flex items-center gap-2 text-sm text-zinc-700">
                          <input
                            :checked="sampleInfo.mode_of_release === 'courier'"
                            type="radio"
                            name="mode_of_release"
                            class="h-4 w-4 border-zinc-300 text-[#0E3D1A] focus:ring-0"
                            @change="updateSampleInfo('mode_of_release', 'courier')"
                          />
                          Courier
                        </label>

                        <label class="flex items-center gap-2 text-sm text-zinc-700">
                          <input
                            :checked="sampleInfo.mode_of_release === 'online'"
                            type="radio"
                            name="mode_of_release"
                            class="h-4 w-4 border-zinc-300 text-[#0E3D1A] focus:ring-0"
                            @change="updateSampleInfo('mode_of_release', 'online')"
                          />
                          Online
                        </label>
                      </div>
                    </div>

                    <div>
                      <label class="mb-2 block text-sm font-medium text-zinc-700">
                        Retrieve retained sample after analysis <span class="text-rose-500">*</span>
                      </label>
                      <div class="flex gap-4">
                        <label class="flex items-center gap-2 text-sm text-zinc-700">
                          <input
                            :checked="sampleInfo.retrieve_sample === 'yes'"
                            type="radio"
                            name="retrieve_sample"
                            class="h-4 w-4 border-zinc-300 text-[#0E3D1A] focus:ring-0"
                            @change="updateSampleInfo('retrieve_sample', 'yes')"
                          />
                          Yes
                        </label>

                        <label class="flex items-center gap-2 text-sm text-zinc-700">
                          <input
                            :checked="sampleInfo.retrieve_sample === 'no'"
                            type="radio"
                            name="retrieve_sample"
                            class="h-4 w-4 border-zinc-300 text-[#0E3D1A] focus:ring-0"
                            @change="updateSampleInfo('retrieve_sample', 'no')"
                          />
                          No
                        </label>
                      </div>
                    </div>
                  </div>

                  <div class="mt-6 max-w-xs">
                    <label class="mb-2 block text-sm font-medium text-zinc-700">
                      Agreed Date of Release Results <span class="text-rose-500">*</span>
                    </label>
                    <input
                      :value="sampleInfo.agreed_release_date"
                      type="date"
                      class="h-11 w-full rounded-lg border border-zinc-200 bg-white px-3 text-sm text-zinc-900 outline-none focus:border-[#0E3D1A]"
                      @input="updateSampleInfo('agreed_release_date', ($event.target as HTMLInputElement).value)"
                    />
                  </div>
                </div>

                <div class="rounded-xl border-1 border-[#F09816] p-5 light:border-[#F09816]">
                  <div class="grid gap-4 md:grid-cols-3">
                    <div>
                      <label class="mb-2 block text-sm font-medium text-zinc-700">
                        No. of Samples Submitted <span class="text-rose-500">*</span>
                      </label>
                      <input
                        :value="sampleInfo.number_of_samples"
                        type="number"
                        min="1"
                        class="h-11 w-full rounded-lg border border-zinc-200 bg-white px-3 text-sm text-zinc-900 outline-none focus:border-[#0E3D1A]"
                        @input="updateSampleInfo('number_of_samples', ($event.target as HTMLInputElement).value)"
                      />
                    </div>

                    <div>
                      <label class="mb-2 block text-sm font-medium text-zinc-700">
                        Date Received
                      </label>
                      <div class="relative">
                        <CalendarDays class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-400" />
                        <input
                          :value="sampleInfo.date_received"
                          type="date"
                          disabled
                          class="h-11 w-full rounded-lg border border-zinc-200 bg-zinc-50 pl-10 pr-3 text-sm text-zinc-700 outline-none"
                        />
                      </div>
                    </div>

                    <div>
                      <label class="mb-2 block text-sm font-medium text-zinc-700">
                        Received By
                      </label>
                      <div class="relative">
                        <UserRoundCheck class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-400" />
                        <input
                          :value="sampleInfo.received_by"
                          type="text"
                          disabled
                          class="h-11 w-full rounded-lg border border-zinc-200 bg-zinc-50 pl-10 pr-3 text-sm text-zinc-700 outline-none"
                        />
                      </div>
                    </div>
                  </div>
                </div>

                <div
                  v-for="(block, index) in requestBlocks"
                  :key="block.id"
                  class="rounded-xl border-1 border-[#1BBE55] p-5"
                >
                  <div class="mb-5 flex items-center justify-between gap-4">
                    <div class="inline-flex items-center rounded-full border border-zinc-200 bg-zinc-50 px-4 py-1.5 text-sm font-semibold text-zinc-700">
                      Sample {{ index + 1 }}
                    </div>

                    <div class="text-sm font-medium text-zinc-500">
                      {{ index + 1 }} of {{ sampleInfo.number_of_samples || 0 }}
                    </div>
                  </div>

                  <div class="space-y-4">
                    <div class="grid gap-4 md:grid-cols-3">
                      <div>
                        <label class="mb-2 block text-sm font-medium text-zinc-700">
                          Sample Description <span class="text-rose-500">*</span>
                        </label>
                        <select
                          :value="block.sampleDescription"
                          class="h-11 w-full rounded-lg border border-zinc-200 bg-white px-3 text-sm text-zinc-900 outline-none focus:border-[#0E3D1A]"
                          @change="handleSampleDescriptionChange(index, ($event.target as HTMLSelectElement).value as SampleDescription)"
                        >
                          <option value="">Select</option>
                          <option value="soil">Soil</option>
                          <option value="water">Water</option>
                          <option value="fertilizer">Fertilizer</option>
                        </select>
                      </div>

                      <div>
                        <label class="mb-2 block text-sm font-medium text-zinc-700">
                          Sample Type <span class="text-rose-500">*</span>
                        </label>
                        <select
                          :value="block.sampleType"
                          class="h-11 w-full rounded-lg border border-zinc-200 bg-white px-3 text-sm text-zinc-900 outline-none focus:border-[#0E3D1A]"
                          @change="updateTestRequest(index, 'sampleType', ($event.target as HTMLSelectElement).value)"
                        >
                          <option value="">Select type</option>
                          <template v-if="block.sampleDescription === 'soil'">
                            <option value="regular">Regular</option>
                            <option value="analytical">Analytical</option>
                            <option value="fertility">Fertility mapping</option>
                          </template>
                          <template v-if="block.sampleDescription === 'water'">
                            <option value="physical">Physical</option>
                            <option value="chemical">Chemical</option>
                            <option value="biological">Biological</option>
                          </template>
                          <template v-if="block.sampleDescription === 'fertilizer'">
                            <option value="organic">Organic</option>
                            <option value="inorganic">Inorganic</option>
                          </template>
                        </select>
                      </div>

                      <div>
                        <label class="mb-2 block text-sm font-medium text-zinc-700">
                          Sample ID <span class="text-rose-500">*</span>
                        </label>
                        <input
                          :value="block.sampleId"
                          type="text"
                          placeholder="Enter sample ID"
                          class="h-11 w-full rounded-lg border border-zinc-200 bg-white px-3 text-sm text-zinc-900 outline-none focus:border-[#0E3D1A]"
                          @input="updateTestRequest(index, 'sampleId', ($event.target as HTMLInputElement).value)"
                        />
                      </div>
                    </div>

                    <div
                      v-if="block.sampleDescription === 'soil'"
                      class="rounded-xl border-1 border-[#1BBE55] bg-zinc-50 p-4"
                    >
                      <label class="mb-3 block font-semibold text-zinc-900">
                        Soil Description <span class="text-rose-500">*</span>
                      </label>

                      <div class="grid gap-4 md:grid-cols-4">
                        <div>
                          <div class="mb-2 flex gap-4">
                            <label class="flex items-center gap-2 text-sm text-zinc-700">
                              <input
                                :checked="block.soilDescription?.condition === 'dry'"
                                type="radio"
                                :name="`soilCondition-${block.id}`"
                                class="h-4 w-4 border-zinc-300 text-[#0E3D1A] focus:ring-0"
                                @change="updateTestRequest(index, 'soilDescription.condition', 'dry')"
                              />
                              Dry
                            </label>

                            <label class="flex items-center gap-2 text-sm text-zinc-700">
                              <input
                                :checked="block.soilDescription?.condition === 'wet'"
                                type="radio"
                                :name="`soilCondition-${block.id}`"
                                class="h-4 w-4 border-zinc-300 text-[#0E3D1A] focus:ring-0"
                                @change="updateTestRequest(index, 'soilDescription.condition', 'wet')"
                              />
                              Wet
                            </label>
                          </div>
                        </div>

                        <div>
                          <label class="mb-2 block text-sm font-medium text-zinc-700">
                            Color <span class="text-rose-500">*</span>
                          </label>
                          <input
                            :value="block.soilDescription?.color || ''"
                            type="text"
                            class="h-11 w-full rounded-lg border border-zinc-200 bg-white px-3 text-sm text-zinc-900 outline-none focus:border-[#0E3D1A]"
                            @input="updateTestRequest(index, 'soilDescription.color', ($event.target as HTMLInputElement).value)"
                          />
                        </div>

                        <div>
                          <label class="mb-2 block text-sm font-medium text-zinc-700">
                            Soil Depth <span class="text-rose-500">*</span>
                          </label>
                          <input
                            :value="block.soilDescription?.depth || ''"
                            type="text"
                            class="h-11 w-full rounded-lg border border-zinc-200 bg-white px-3 text-sm text-zinc-900 outline-none focus:border-[#0E3D1A]"
                            @input="updateTestRequest(index, 'soilDescription.depth', ($event.target as HTMLInputElement).value)"
                          />
                        </div>

                        <div>
                          <label class="mb-2 block text-sm font-medium text-zinc-700">
                            Others
                          </label>
                          <input
                            :value="block.soilDescription?.others || ''"
                            type="text"
                            class="h-11 w-full rounded-lg border border-zinc-200 bg-white px-3 text-sm text-zinc-900 outline-none focus:border-[#0E3D1A]"
                            @input="updateTestRequest(index, 'soilDescription.others', ($event.target as HTMLInputElement).value)"
                          />
                        </div>
                      </div>
                    </div>

                    <div
                      v-if="block.sampleDescription === 'water'"
                      class="rounded-xl border-1 border-[#border-1 border-[#1BBE55] p-5 bg-zinc-50 p-4"
                    >
                      <label class="mb-3 block font-semibold text-zinc-900">
                        Water Description <span class="text-rose-500">*</span>
                      </label>

                      <div class="grid gap-4 md:grid-cols-3">
                        <div>
                          <label class="mb-2 block text-sm font-medium text-zinc-700">
                            Filtered <span class="text-rose-500">*</span>
                          </label>
                          <div class="flex gap-4">
                            <label class="flex items-center gap-2 text-sm text-zinc-700">
                              <input
                                :checked="block.waterDescription?.filtered === 'yes'"
                                type="radio"
                                :name="`filtered-${block.id}`"
                                class="h-4 w-4 border-zinc-300 text-[#0E3D1A] focus:ring-0"
                                @change="updateTestRequest(index, 'waterDescription.filtered', 'yes')"
                              />
                              Yes
                            </label>

                            <label class="flex items-center gap-2 text-sm text-zinc-700">
                              <input
                                :checked="block.waterDescription?.filtered === 'no'"
                                type="radio"
                                :name="`filtered-${block.id}`"
                                class="h-4 w-4 border-zinc-300 text-[#0E3D1A] focus:ring-0"
                                @change="updateTestRequest(index, 'waterDescription.filtered', 'no')"
                              />
                              No
                            </label>
                          </div>
                        </div>

                        <div>
                          <label class="mb-2 block text-sm font-medium text-zinc-700">
                            Temperature <span class="text-rose-500">*</span>
                          </label>
                          <input
                            :value="block.waterDescription?.temperature || ''"
                            type="text"
                            class="h-11 w-full rounded-lg border border-zinc-200 bg-white px-3 text-sm text-zinc-900 outline-none focus:border-[#0E3D1A]"
                            @input="updateTestRequest(index, 'waterDescription.temperature', ($event.target as HTMLInputElement).value)"
                          />
                        </div>

                        <div>
                          <label class="mb-2 block text-sm font-medium text-zinc-700">
                            Others
                          </label>
                          <input
                            :value="block.waterDescription?.others || ''"
                            type="text"
                            class="h-11 w-full rounded-lg border border-zinc-200 bg-white px-3 text-sm text-zinc-900 outline-none focus:border-[#0E3D1A]"
                            @input="updateTestRequest(index, 'waterDescription.others', ($event.target as HTMLInputElement).value)"
                          />
                        </div>
                      </div>
                    </div>

                    <div class="border-t-2 border-[#CF8A16] pt-7">
                      <div class="grid gap-4 md:grid-cols-3">
                        <div>
                          <label class="mb-2 block text-sm font-medium text-zinc-700">
                            Topography
                          </label>
                          <input
                            :value="block.topography"
                            type="text"
                            class="h-11 w-full rounded-lg border border-zinc-200 bg-white px-3 text-sm text-zinc-900 outline-none focus:border-[#0E3D1A]"
                            @input="updateTestRequest(index, 'topography', ($event.target as HTMLInputElement).value)"
                          />
                        </div>

                        <div>
                          <label class="mb-2 block text-sm font-medium text-zinc-700">
                            Coordinates
                          </label>
                          <input
                            :value="block.longitude"
                            type="text"
                            placeholder="Longitude"
                            class="h-11 w-full rounded-lg border border-zinc-200 bg-white px-3 text-sm text-zinc-900 outline-none focus:border-[#0E3D1A]"
                            @input="updateTestRequest(index, 'longitude', ($event.target as HTMLInputElement).value)"
                          />
                        </div>

                        <div>
                          <label class="mb-2 block text-sm font-medium text-zinc-700">
                            &nbsp;
                          </label>
                          <input
                            :value="block.latitude"
                            type="text"
                            placeholder="Latitude"
                            class="h-11 w-full rounded-lg border border-zinc-200 bg-white px-3 text-sm text-zinc-900 outline-none focus:border-[#0E3D1A]"
                            @input="updateTestRequest(index, 'latitude', ($event.target as HTMLInputElement).value)"
                          />
                        </div>
                      </div>
                    </div>

                    <div class="border-t-2 border-[#CF8A16] pt-7">
                      <div class="grid gap-4 md:grid-cols-4">
                        <div>
                          <label class="mb-2 block text-sm font-medium text-zinc-700">
                            Region
                          </label>
                          <select
                            :value="block.region"
                            class="h-11 w-full rounded-lg border border-zinc-200 bg-white px-3 text-sm text-zinc-900 outline-none focus:border-[#0E3D1A]"
                            @change="
                              updateTestRequest(index, 'region', ($event.target as HTMLSelectElement).value);
                              updateTestRequest(index, 'province', '');
                              updateTestRequest(index, 'municipality', '');
                              updateTestRequest(index, 'barangay', '');
                            "
                          >
                            <option value="">Select region</option>
                            <option v-for="region in Object.keys(LOCATION_DATA)" :key="region" :value="region">
                              {{ region }}
                            </option>
                          </select>
                        </div>

                        <div>
                          <label class="mb-2 block text-sm font-medium text-zinc-700">
                            Province
                          </label>
                          <select
                            :value="block.province"
                            :disabled="!block.region"
                            class="h-11 w-full rounded-lg border border-zinc-200 bg-white px-3 text-sm text-zinc-900 outline-none focus:border-[#0E3D1A] disabled:bg-zinc-50"
                            @change="
                              updateTestRequest(index, 'province', ($event.target as HTMLSelectElement).value);
                              updateTestRequest(index, 'municipality', '');
                              updateTestRequest(index, 'barangay', '');
                            "
                          >
                            <option value="">Select province</option>
                            <option v-for="province in getProvinces(block.region)" :key="province" :value="province">
                              {{ province }}
                            </option>
                          </select>
                        </div>

                        <div>
                          <label class="mb-2 block text-sm font-medium text-zinc-700">
                            Municipality
                          </label>
                          <select
                            :value="block.municipality"
                            :disabled="!block.province"
                            class="h-11 w-full rounded-lg border border-zinc-200 bg-white px-3 text-sm text-zinc-900 outline-none focus:border-[#0E3D1A] disabled:bg-zinc-50"
                            @change="
                              updateTestRequest(index, 'municipality', ($event.target as HTMLSelectElement).value);
                              updateTestRequest(index, 'barangay', '');
                            "
                          >
                            <option value="">Select municipality</option>
                            <option v-for="municipality in getMunicipalities(block.region, block.province)" :key="municipality" :value="municipality">
                              {{ municipality }}
                            </option>
                          </select>
                        </div>

                        <div>
                          <label class="mb-2 block text-sm font-medium text-zinc-700">
                            Barangay
                          </label>
                          <select
                            :value="block.barangay"
                            :disabled="!block.municipality"
                            class="h-11 w-full rounded-lg border border-zinc-200 bg-white px-3 text-sm text-zinc-900 outline-none focus:border-[#0E3D1A] disabled:bg-zinc-50"
                            @change="updateTestRequest(index, 'barangay', ($event.target as HTMLSelectElement).value)"
                          >
                            <option value="">Select barangay</option>
                            <option v-for="barangay in getBarangays(block.region, block.province, block.municipality)" :key="barangay" :value="barangay">
                              {{ barangay }}
                            </option>
                          </select>
                        </div>
                      </div>
                    </div>

                    <div class="border-t-2 border-[#CF8A16] pt-7">
                      <div class="grid gap-4 md:grid-cols-2">
                        <div>
                          <label class="mb-2 block text-sm font-medium text-zinc-700">
                            Farm Area
                          </label>
                          <input
                            :value="block.farmArea"
                            type="text"
                            placeholder="in hectares"
                            class="h-11 w-full rounded-lg border border-zinc-200 bg-white px-3 text-sm text-zinc-900 outline-none focus:border-[#0E3D1A]"
                            @input="updateTestRequest(index, 'farmArea', ($event.target as HTMLInputElement).value)"
                          />
                        </div>

                        <div>
                          <label class="mb-2 block text-sm font-medium text-zinc-700">
                            Crops
                          </label>
                          <select
                            :value="block.crops"
                            class="h-11 w-full rounded-lg border border-zinc-200 bg-white px-3 text-sm text-zinc-900 outline-none focus:border-[#0E3D1A]"
                            @change="updateTestRequest(index, 'crops', ($event.target as HTMLSelectElement).value)"
                          >
                            <option value="">Select crop</option>
                            <option v-for="crop in cropOptions" :key="crop" :value="crop">
                              {{ crop }}
                            </option>
                          </select>
                        </div>
                      </div>

                      <div class="mt-4">
                        <label class="mb-2 block text-sm font-medium text-zinc-700">
                          Remarks
                        </label>
                        <textarea
                          :value="block.remarks"
                          rows="3"
                          class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 outline-none focus:border-[#0E3D1A]"
                          @input="updateTestRequest(index, 'remarks', ($event.target as HTMLTextAreaElement).value)"
                        />
                      </div>
                    </div>

                    <div class="border-t-2 border-[#CF8A16] pt-7">
                      <h3 class="mb-2 block text-sm font-medium text-zinc-700">
                        Analysis Requested
                      </h3>

                      <div class="space-y-6">
                        <div class="rounded-xl border border-zinc-200 p-4">
                          <div class="mb-2 block text-sm font-medium text-zinc-700">
                            Chemist
                          </div>

                          <div class="grid gap-4 lg:grid-cols-2">
                            <div class="space-y-3">
                              <label
                                v-for="opt in analysisRequestedOptions.filter(o =>
                                  o.category === 'chemist' &&
                                  ['pH', 'EC Analysis', 'Organic Matter Analysis', 'Available Phosphorus'].includes(o.label)
                                )"
                                :key="opt.label"
                                class="flex items-center gap-3 rounded-xl border border-zinc-200 px-4 py-3 text-sm font-medium text-zinc-700"
                              >
                                <input
                                  type="checkbox"
                                  class="h-4 w-4 rounded border-zinc-300 text-[#0E3D1A] focus:ring-0"
                                  :checked="block.analysisRequested.includes(opt.label)"
                                  @change="toggleAnalysisRequested(index, opt.label)"
                                />
                                {{ opt.label }}
                              </label>
                            </div>

                            <div class="space-y-4">
                              <div class="rounded-xl border border-zinc-200 p-4">
                                <div class="border-b border-zinc-200 pb-2 text-sm font-medium text-zinc-700">
                                  Exchangeable Bases
                                </div>

                                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                  <label
                                    v-for="opt in ['Potassium', 'Calcium', 'Magnesium', 'Sodium']"
                                    :key="opt"
                                    class="flex items-center gap-3 rounded-xl border border-zinc-200 px-4 py-3 text-sm text-zinc-700"
                                  >
                                    <input
                                      type="checkbox"
                                      class="h-4 w-4 rounded border-zinc-300 text-[#0E3D1A] focus:ring-0"
                                      :checked="block.analysisRequested.includes(opt)"
                                      @change="toggleAnalysisRequested(index, opt)"
                                    />
                                    {{ opt }}
                                  </label>
                                </div>
                              </div>

                              <div class="rounded-xl border border-zinc-200 p-4">
                                <div class="border-b border-zinc-200 pb-2 text-sm font-medium text-zinc-700">
                                  Micronutrients
                                </div>

                                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                  <label
                                    v-for="opt in ['Zinc', 'Copper', 'Iron', 'Manganese']"
                                    :key="opt"
                                    class="flex items-center gap-3 rounded-xl border border-zinc-200 px-4 py-3 text-sm text-zinc-700"
                                  >
                                    <input
                                      type="checkbox"
                                      class="h-4 w-4 rounded border-zinc-300 text-[#0E3D1A] focus:ring-0"
                                      :checked="block.analysisRequested.includes(opt)"
                                      @change="toggleAnalysisRequested(index, opt)"
                                    />
                                    {{ opt }}
                                  </label>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>

                        <div class="rounded-xl border border-zinc-200 p-4">
                          <div class="mb-2 block text-sm font-medium text-zinc-700">
                            Agriculturist
                          </div>

                          <div class="max-w-md space-y-3">
                            <label
                              v-for="opt in analysisRequestedOptions.filter(o => o.category === 'agriculturist')"
                              :key="opt.label"
                              class="flex items-center gap-3 rounded-xl border border-zinc-200 px-4 py-3 text-sm font-medium text-zinc-700"
                            >
                              <input
                                type="checkbox"
                                class="h-4 w-4 rounded border-zinc-300 text-[#0E3D1A] focus:ring-0"
                                :checked="block.analysisRequested.includes(opt.label)"
                                @change="toggleAnalysisRequested(index, opt.label)"
                              />
                              {{ opt.label }}
                            </label>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <button
                      type="button"
                      @click="handleAddTestRequest"
                      class="inline-flex items-center justify-center gap-2 rounded-lg border border-[#0E3D1A] bg-white px-4 py-2.5 text-sm font-semibold text-[#0E3D1A] transition hover:bg-[#0E3D1A] hover:text-white"
                    >
                      <Plus class="h-4 w-4" />
                      Add New Form
                    </button>

                    <div class="flex gap-3">
                      <button
                        type="button"
                        @click="handleDuplicateTestRequest(index)"
                        class="inline-flex items-center justify-center gap-2 rounded-lg border border-[#0E3D1A] bg-white px-4 py-2.5 text-sm font-semibold text-[#0E3D1A] transition hover:bg-[#0E3D1A] hover:text-white"
                      >
                        <Copy class="h-4 w-4" />
                        Duplicate Form
                      </button>

                      <button
                        v-if="requestBlocks.length > 1"
                        type="button"
                        @click="removeTestRequest(index)"
                        class="inline-flex items-center justify-center gap-2 rounded-lg border border-red-600 bg-white px-4 py-2.5 text-sm font-semibold text-red-600 transition hover:bg-red-50"
                      >
                        Remove
                      </button>
                    </div>
                  </div>
                </div>

                <div class="flex items-center justify-between border-t border-zinc-200 pt-6">
                  <button
                    type="button"
                    @click="goBack"
                    class="inline-flex items-center gap-2 rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50"
                  >
                    <ArrowLeft class="h-4 w-4" />
                    Back
                  </button>

                  <div class="text-sm text-zinc-500">
                    Step 2 of 3
                  </div>

                  <button
                    type="button"
                    @click="handleNextWithValidation"
                    class="inline-flex items-center gap-2 rounded-lg bg-[#0E3D1A] px-5 py-2.5 text-sm font-semibold text-white transition hover:opacity-90"
                  >
                    Next
                    <ArrowRight class="h-4 w-4" />
                  </button>
                </div>
              </div>

              <div class="space-y-5">
                <div class="h-fit rounded-xl border-1 border-[#F09816] p-5 light:border-[#F09816]">
                  <div class="mb-5 flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#0E3D1A] text-white">
                      <CreditCard class="h-5 w-5" />
                    </div>

                    <div>
                      <h2 class="text-lg font-semibold text-zinc-9  00">
                        Payment Status
                      </h2>
                    </div>
                  </div>

                  <div class="space-y-4">
                    <div>
                      <label class="mb-2 block text-sm font-medium text-zinc-700">
                        Status
                      </label>
                      <select
                        :value="paymentStatus"
                        class="h-11 w-full rounded-lg border border-zinc-200 bg-white px-3 text-sm text-zinc-900 outline-none focus:border-[#0E3D1A]"
                        @change="updatePaymentStatus(($event.target as HTMLSelectElement).value as 'pending' | 'paid')"
                      >
                        <option value="pending">Pending</option>
                        <option value="paid">Paid</option>
                      </select>
                    </div>

                    <div>
                      <label class="mb-2 block text-sm font-medium text-zinc-700">
                        Deposit
                      </label>
                      <input
                        :value="sampleInfo.deposit"
                        type="number"
                        min="0"
                        class="h-11 w-full rounded-lg border border-zinc-200 bg-white px-3 text-sm text-zinc-900 outline-none focus:border-[#0E3D1A]"
                        @input="updateSampleInfo('deposit', ($event.target as HTMLInputElement).value)"
                      />
                    </div>

                    <div>
                      <label class="mb-2 block text-sm font-medium text-zinc-700">
                        O.R. No.
                      </label>
                      <input
                        :value="sampleInfo.or_no"
                        type="text"
                        class="h-11 w-full rounded-lg border border-zinc-200 bg-white px-3 text-sm text-zinc-900 outline-none focus:border-[#0E3D1A]"
                        @input="updateSampleInfo('or_no', ($event.target as HTMLInputElement).value)"
                      />
                    </div>

                    <div>
                      <label class="mb-2 block text-sm font-medium text-zinc-700">
                        Date
                      </label>
                      <input
                        :value="sampleInfo.payment_date"
                        type="date"
                        class="h-11 w-full rounded-lg border border-zinc-200 bg-white px-3 text-sm text-zinc-900 outline-none focus:border-[#0E3D1A]"
                        @input="updateSampleInfo('payment_date', ($event.target as HTMLInputElement).value)"
                      />
                    </div>

                    <div>
                      <label class="mb-2 block text-sm font-medium text-zinc-700">
                        Balance
                      </label>
                      <input
                        :value="sampleInfo.balance"
                        type="number"
                        disabled
                        placeholder="Auto-computed later"
                        class="h-11 w-full rounded-lg border border-zinc-200 bg-zinc-50 px-3 text-sm text-zinc-700 outline-none"
                      />
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
                      v-for="item in subtotalSummary"
                      :key="item.label"
                      class="rounded-xl border border-zinc-200 p-4"
                    >
                      <div class="flex items-center justify-between gap-3">
                        <span class="text-sm font-medium text-zinc-700">
                          {{ item.label }}
                        </span>
                        <span class="text-sm font-semibold text-zinc-900">
                          ₱{{ item.subtotal.toLocaleString() }}
                        </span>
                      </div>
                    </div>

                    <div
                      v-if="subtotalSummary.length === 0"
                      class="rounded-xl border border-dashed border-zinc-300 p-4 text-sm text-zinc-500"
                    >
                      No samples yet
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div
  v-if="showMismatchModal"
  class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4"
>
  <div class="w-full max-w-lg rounded-2xl border border-zinc-200 bg-white shadow-xl">

    <!-- Header -->
    <div
  class="flex items-center justify-left gap-3 border-b border-zinc-200 px-6 py-5"
>
  <div
    class="flex h-10 w-10 items-center justify-center rounded-full
           bg-amber-100 text-amber-600
          "
  >
    <TriangleAlert class="h-5 w-5" />
  </div>

  <h2 class="text-xl font-semibold text-zinc-900">
    Sample Count Mismatch
  </h2>
</div>

    <!-- Body -->
    <div class="px-6 py-5">
      <p class="text-sm leading-6 text-zinc-700">
        The number of completed sample forms does not match the declared
        number of samples.
      </p>

      <p class="mt-3 text-sm leading-6 text-zinc-700">
        Please review your entries before proceeding.
      </p>
      
    </div>

    <!-- Footer -->
    <div class="flex justify-end gap-2 border-t border-zinc-200 px-6 py-4">
      <button
          type="button"
          @click="handleCancelMismatch"
          class="rounded-lg border border-zinc-300 bg-white px-5 py-2.5
                text-sm font-medium text-zinc-700 transition
                hover:bg-zinc-50
               
               "
        >
          Go Back
        </button>

      <button
        type="button"
        @click="forceProceed"
        class="rounded-lg bg-[#0E3D1A] px-4 py-2 text-sm font-semibold text-white transition hover:opacity-90"
      >
        Proceed
      </button>
    </div>
  </div>
</div>

         
<div
  v-if="showConfirmProceed"
  class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4"
>
  <div
    class="w-full max-w-lg overflow-hidden rounded-2xl border-2 border-[#0E3D1A]
           bg-white shadow-2xl"
  >
   <!-- Header -->
<div
  class="flex items-center gap-3 border-b border-zinc-200 px-6 py-5
        "
>
  <div
    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full
           bg-emerald-100 text-[#0E3D1A]
          "
  >
    <CircleCheckBig class="h-5 w-5" />
  </div>

  <h2 class="text-lg font-semibold text-zinc-900">
    Confirm Submission
  </h2>
</div>

    <!-- Body -->
    <div class="px-6 py-6">
      <p class="text-center text-sm leading-7 text-zinc-600">
        Are you sure you want to proceed even though the number of completed
        sample forms does not match the declared number of samples?
      </p>
    </div>

    <!-- Footer -->
    <div
      class="flex justify-end gap-3 border-t border-zinc-200 px-6 py-5
            "
    >
      <button
        type="button"
        @click="handleCancelProceedConfirmation"
        class="rounded-lg border border-zinc-300 bg-white px-5 py-2.5
               text-sm font-semibold text-zinc-700 transition
               hover:bg-zinc-50
              
              "
      >
        No
      </button>

      <button
        type="button"
        @click="handleConfirmProceed"
        class="rounded-lg border border-[#0E3D1A] bg-[#0E3D1A]
               px-5 py-2.5 text-sm font-semibold text-white
               transition hover:opacity-90"
      >
        Yes
      </button>
    </div>
  </div>
</div>

        </div>
      </div>
    </div>
  </AppSidebarLayout>
</template>