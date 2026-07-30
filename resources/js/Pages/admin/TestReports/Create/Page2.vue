<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3'
import {
  CalendarDays,
  UserRoundCheck,
  ArrowLeft,
  ArrowRight,
  Plus,
  Copy,
  CreditCard,
  TriangleAlert,
  CircleCheckBig,
} from 'lucide-vue-next'
import { computed, ref } from 'vue'
import AppSidebarLayout from '@/Layouts/app/AppSidebarLayout.vue'
import type { BreadcrumbItem } from '@/types'

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

type AnalysisGroup = {
  label: string
  category: 'chemist' | 'agriculturist'
  tasks: string[]
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
  draftReport?: Record<string, any> | null
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

const analysisGroups: AnalysisGroup[] = [
  {
    category: 'chemist',
    label: 'pH and EC Analysis',
    tasks: ['pH', 'EC Analysis'],
  },
  {
    category: 'chemist',
    label: 'Organic Matter Analysis',
    tasks: ['Organic Matter Analysis'],
  },
  {
    category: 'chemist',
    label: 'Available Phosphorus',
    tasks: ['Available Phosphorus'],
  },
  {
    category: 'chemist',
    label: 'Exchangeable Bases',
    tasks: ['Potassium', 'Calcium', 'Magnesium', 'Sodium'],
  },
  {
    category: 'chemist',
    label: 'Micronutrients',
    tasks: ['Zinc', 'Copper', 'Iron', 'Manganese'],
  },
  {
    category: 'agriculturist',
    label: 'Soil Moisture',
    tasks: ['Soil Moisture'],
  },
  {
    category: 'agriculturist',
    label: 'Particle Size Analysis and Soil Texture',
    tasks: ['Particle Size Analysis', 'Soil Texture'],
  },
  {
    category: 'agriculturist',
    label: 'Fertilizer Recommendation',
    tasks: ['Fertilizer Recommendation'],
  },
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
const showMismatchModal = ref(false)
const showConfirmProceed = ref(false)
const validationError = ref('')

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

if (props.draftReport) {
  sampleInfo.value = {
    number_of_samples: String(props.draftReport.number_of_samples ?? sampleInfo.value.number_of_samples),
    date_received: String(props.draftReport.date_received ?? sampleInfo.value.date_received).slice(0, 10),
    received_by: props.draftReport.received_by ?? sampleInfo.value.received_by,
    mode_of_release: props.draftReport.mode_of_release ?? sampleInfo.value.mode_of_release,
    retrieve_sample: (props.draftReport.retrieve_sample ? 'yes' : 'no') as RetrieveSample,
    agreed_release_date: String(props.draftReport.agreed_release_date ?? '').slice(0, 10),
    deposit: String(props.draftReport.deposit ?? ''),
    or_no: props.draftReport.or_no ?? '',
    payment_date: String(props.draftReport.payment_date ?? '').slice(0, 10),
    balance: String(props.draftReport.balance ?? ''),
  }
  paymentStatus.value = (props.draftReport.payment_status ?? 'pending') as 'pending' | 'paid'
  requestBlocks.value = Array.isArray(props.draftReport.samples) && props.draftReport.samples.length
    ? props.draftReport.samples.map((sample: any, index: number) => ({
        id: String(index + 1),
        sampleDescription: sample.sample_description ?? '',
        sampleType: sample.sample_type ?? '',
        sampleId: sample.sample_id ?? '',
        soilDescription: sample.sample_description === 'soil'
          ? {
              condition: sample.soil_condition ?? '',
              color: sample.soil_color ?? '',
              depth: sample.soil_depth ?? '',
              others: sample.soil_others ?? '',
            }
          : undefined,
        waterDescription: sample.sample_description === 'water'
          ? {
              filtered: sample.water_filtered ?? '',
              temperature: sample.water_temperature ?? '',
              others: sample.water_others ?? '',
            }
          : undefined,
        topography: sample.topography ?? '',
        longitude: sample.longitude ?? '',
        latitude: sample.latitude ?? '',
        region: sample.region ?? '',
        province: sample.province ?? '',
        municipality: sample.municipality ?? '',
        barangay: sample.barangay ?? '',
        farmArea: sample.farm_area ?? '',
        crops: sample.crops ?? '',
        remarks: sample.remarks ?? '',
        analysisRequested: String(sample.analysis_requested ?? '').split(',').map((item: string) => item.trim()).filter(Boolean),
        subtotal: Number(sample.subtotal ?? 0),
      }))
    : requestBlocks.value
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
  const draftQuery = props.draftReport?.id ? `&draft_id=${props.draftReport.id}` : ''

  if (props.client_id) {
    router.visit(`/test-reports/create/page-1?client_id=${props.client_id}${draftQuery}`)
    return
  }

  router.visit(`/test-reports/create/page-1${draftQuery ? `?${draftQuery.slice(1)}` : ''}`)
}

function getCsrfToken() {
  return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? ''
}

function savePage2() {
  return {
    draft_id: props.draftReport?.id ?? null,
    number_of_samples: sampleInfo.value.number_of_samples,
    date_received: sampleInfo.value.date_received,
    received_by: sampleInfo.value.received_by,
    mode_of_release: sampleInfo.value.mode_of_release,
    retrieve_sample: sampleInfo.value.retrieve_sample,
    agreed_release_date: sampleInfo.value.agreed_release_date,
    deposit: sampleInfo.value.deposit,
    or_no: sampleInfo.value.or_no,
    payment_date: sampleInfo.value.payment_date,
    balance: sampleInfo.value.balance,
  }
}

async function proceedToNextPage() {
  let response: Response

  try {
    response = await fetch('/reports/draft', {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': getCsrfToken(),
      },
      body: JSON.stringify({
        ...props.draftReport,
        ...savePage2(),
        is_draft: true,
        status: 'draft',
        samples: requestBlocks.value.map((block) => ({
          laboratory_code: block.id,
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
          analysis_requested: block.analysisRequested.join(', '),
          subtotal: Number(block.subtotal || 0),
        })),
      }),
    })
  } catch {
    validationError.value = 'Unable to save your information. Please check your connection and try again.'
    return
  }

  if (!response.ok) {
    let message = 'Unable to save your information. Please try again.'

    try {
      const data = await response.json() as { message?: string; errors?: Record<string, string[]> }
      const firstError = data.errors ? Object.values(data.errors).flat().find(Boolean) : ''
      message = firstError || data.message || message
    } catch {
      // Keep the user-friendly fallback.
    }

    validationError.value = message
    return
  }

  const data = await response.json() as { report?: { id?: number } }
  const draftId = data.report?.id ?? props.draftReport?.id
  router.visit(`/test-reports/create/page-3${draftId ? `?draft_id=${draftId}` : ''}`)
}

function handleNextWithValidation() {
  const requiredSampleInfoField = [
    [!sampleInfo.value.mode_of_release, 'Mode of Release'],
    [!sampleInfo.value.retrieve_sample, 'Retrieve retained sample after analysis'],
    [!sampleInfo.value.agreed_release_date, 'Agreed Date of Release Results'],
    [!sampleInfo.value.number_of_samples, 'No. of Samples Submitted'],
    [!sampleInfo.value.received_by, 'Received By'],
  ].find(([missing]) => missing)?.[1]

  if (requiredSampleInfoField) {
    validationError.value = `${requiredSampleInfoField} is required.`
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

function hasAllTasks(block: TestRequestForm, tasks: string[]) {
  return tasks.every((task) => block.analysisRequested.includes(task))
}

function toggleAnalysisGroup(index: number, group: AnalysisGroup) {
  const current = requestBlocks.value[index].analysisRequested
  const allSelected = group.tasks.every((task) => current.includes(task))
  const next = allSelected
    ? current.filter((item) => !group.tasks.includes(item))
    : Array.from(new Set([...current, ...group.tasks]))

  updateTestRequest(index, 'analysisRequested', next)
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

                      <div class="space-y-4">
                        <div
                          v-for="group in analysisGroups.filter((group) => group.category === 'chemist')"
                          :key="group.label"
                          class="rounded-xl border border-zinc-200 p-4"
                        >
                          <label class="flex cursor-pointer items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                              <input
                                type="checkbox"
                                class="h-4 w-4 rounded border-zinc-300 text-[#0E3D1A] focus:ring-0"
                                :checked="hasAllTasks(block, group.tasks)"
                                @change="toggleAnalysisGroup(index, group)"
                              />
                              <span class="text-sm font-medium text-zinc-700">
                                {{ group.label }}
                              </span>
                            </div>

                            <span class="text-xs text-zinc-400">
                              {{ group.tasks.length }} task<span v-if="group.tasks.length !== 1">s</span>
                            </span>
                          </label>
                        </div>

                        <div
                          v-for="group in analysisGroups.filter((group) => group.category === 'agriculturist')"
                          :key="group.label"
                          class="rounded-xl border border-zinc-200 p-4"
                        >
                          <label class="flex cursor-pointer items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                              <input
                                type="checkbox"
                                class="h-4 w-4 rounded border-zinc-300 text-[#0E3D1A] focus:ring-0"
                                :checked="hasAllTasks(block, group.tasks)"
                                @change="toggleAnalysisGroup(index, group)"
                              />
                              <span class="text-sm font-medium text-zinc-700">
                                {{ group.label }}
                              </span>
                            </div>

                            <span class="text-xs text-zinc-400">
                              {{ group.tasks.length }} task<span v-if="group.tasks.length !== 1">s</span>
                            </span>
                          </label>
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
