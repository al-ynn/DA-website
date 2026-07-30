<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import {
  Search,
  FileText,
  Clock,
  AlertCircle,
  CheckCircle,
  Eye,
  Pencil,
  Plus,
  SlidersHorizontal,
} from 'lucide-vue-next'
import { computed, onMounted, onUnmounted, ref } from 'vue'
import CreateTestRequestEntryModal from './components/CreateTestRequestEntryModal.vue'
import CustomizeTableViewModal from './components/CustomizeTableViewModal.vue'

type PageMode = 'test-request' | 'sample'

type ReportStatus =
  | 'Test Request submitted'
  | 'Under Analyzation'
  | 'Analyzed'
  | 'Under Recommendation'
  | 'Recommended'
  | 'Reviewed'
  | 'Certified'
  | 'Noted'
  | 'Ready'

type DateRangeFilter = 'all' | 'today' | 'week' | 'month' | 'quarter' | 'year'

type SortFilter =
  | 'id-desc'
  | 'id-asc'
  | 'date-desc'
  | 'date-asc'
  | 'client-asc'
  | 'client-desc'
  | 'company-asc'
  | 'company-desc'

type FilterState = {
  status: 'all' | ReportStatus
  dateRange: DateRangeFilter
  sortBy: SortFilter
}

type SampleRecord = {
  laboratoryCode: string
  sampleId: string
  sampleDescription?: string
  sampleType?: string
  topography?: string

  coordinates?: string
  longitude?: string
  latitude?: string

  region?: string
  province?: string
  municipality?: string
  barangay?: string
  farmArea?: string
  crops?: string
  remarks?: string

  analysisRequested?: string
  analysisRequestedChemist?: string
  analysisRequestedAgriculturist?: string

  subtotal: number
}

type Report = {
  id: number
  requestCode?: string
  date: string
  status: ReportStatus
  surname: string
  firstName: string
  middleName?: string
  fullName: string
  rsbsaNo?: string
  companyName: string
  classification?: string
  sex?: string
  age?: string
  address?: string
  contactNumber?: string
  emailAddress?: string
  samplingDate?: string
  samplingTime?: string
  modeOfRelease?: string
  retrieveSample?: string
  agreedReleaseDate?: string
  numberOfSamples?: string
  dateReceived?: string
  receivedBy?: string
  paymentStatus?: string
  deposit?: number
  orNo?: string
  paymentDate?: string
  balance?: number
  totalAmountDue: number
  totalAmount?: number
  samples: SampleRecord[]
}

type FlattenedSample = SampleRecord & Omit<Report, 'id' | 'samples'> & {
  requestId: string
  reportId: number
}

type ActiveRow = Report | FlattenedSample

type TableColumn = {
  key: string
  label: string
  default: boolean
  category: string
  locked?: boolean
  align?: 'left' | 'center' | 'right'
}

const REQUEST_COLUMN_STORAGE_KEY = 'reports-test-request-visible-columns'
const SAMPLE_COLUMN_STORAGE_KEY = 'reports-sample-visible-columns'

const props = defineProps<{
  reports?: {
    data?: any[]
  }
  summary?: Record<string, number>
  filters?: {
    search?: string
    status?: string
    date_range?: string
    sort_by?: string
  }
}>()

const pageMode = ref<PageMode>('test-request')

const sampleInformationColumns: TableColumn[] = [
  { key: 'sampleDescription', label: 'Sample Description', default: false, category: 'Sample Information' },
  { key: 'sampleType', label: 'Sample Type', default: false, category: 'Sample Information' },
  { key: 'sampleId', label: 'Sample ID', default: false, category: 'Sample Information' },
  { key: 'topography', label: 'Topography', default: false, category: 'Sample Information' },
  { key: 'coordinates', label: 'Coordinates', default: false, category: 'Sample Information' },
  { key: 'longitude', label: 'Longitude', default: false, category: 'Sample Information' },
  { key: 'latitude', label: 'Latitude', default: false, category: 'Sample Information' },
  { key: 'region', label: 'Region', default: false, category: 'Sample Information' },
  { key: 'province', label: 'Province', default: false, category: 'Sample Information' },
  { key: 'municipality', label: 'Municipality', default: false, category: 'Sample Information' },
  { key: 'barangay', label: 'Barangay', default: false, category: 'Sample Information' },
  { key: 'farmArea', label: 'Farm Area', default: false, category: 'Sample Information' },
  { key: 'crops', label: 'Crops', default: false, category: 'Sample Information' },
  { key: 'analysisRequestedChemist', label: 'Analysis Requested (Chemist)', default: false, category: 'Sample Information' },
  { key: 'analysisRequestedAgriculturist', label: 'Analysis Requested (Agriculturist)', default: false, category: 'Sample Information' },
]

const requestColumns: TableColumn[] = [
  // Customer Information
  { key: 'rsbsaNo', label: 'RSBSA No.', default: false, category: 'Customer Information' },
  { key: 'fullName', label: 'Full Name', default: true, category: 'Customer Information' },
  { key: 'firstName', label: 'First Name', default: false, category: 'Customer Information' },
  { key: 'middleName', label: 'Middle Name', default: false, category: 'Customer Information' },
  { key: 'surname', label: 'Last Name', default: false, category: 'Customer Information' },
  { key: 'sex', label: 'Sex', default: false, category: 'Customer Information' },
  { key: 'age', label: 'Age', default: false, category: 'Customer Information' },
  { key: 'companyName', label: 'Company Name', default: true, category: 'Customer Information' },
  { key: 'address', label: 'Address', default: false, category: 'Customer Information' },
  { key: 'contactNumber', label: 'Contact No.', default: false, category: 'Customer Information' },
  { key: 'emailAddress', label: 'Email Address', default: false, category: 'Customer Information' },
  { key: 'classification', label: 'Client Classification', default: false, category: 'Customer Information' },
  { key: 'samplingDate', label: 'Date of Sampling', default: false, category: 'Customer Information' },
  { key: 'samplingTime', label: 'Time of Sampling', default: false, category: 'Customer Information' },

  // Other Details
  { key: 'modeOfRelease', label: 'Mode of Release of Test Result', default: false, category: 'Other Details' },
  { key: 'retrieveSample', label: 'Retrieve retained sample after analysis', default: false, category: 'Other Details' },
  { key: 'agreedReleaseDate', label: 'Agreed Date of Release of results', default: false, category: 'Other Details' },

  // Test Request
  { key: 'requestId', label: 'Test Request No.', default: true, locked: true, category: 'Test Request Information' },
  { key: 'labCodes', label: 'Lab Codes', default: true, category: 'Test Request Information' },
  { key: 'status', label: 'Status (of the test Request)', default: true, category: 'Test Request Information' },

  // Payment
  { key: 'paymentStatus', label: 'Status', default: true, category: 'Payment Status' },
  { key: 'deposit', label: 'Deposit', default: false, category: 'Payment Status', align: 'right' },
  { key: 'orNo', label: 'O.R No.', default: false, category: 'Payment Status' },
  { key: 'paymentDate', label: 'Date', default: false, category: 'Payment Status' },
  { key: 'balance', label: 'Balance', default: false, category: 'Payment Status', align: 'right' },
  { key: 'totalAmountDue', label: 'Total Amount Due', default: true, category: 'Payment Status', align: 'right' },
  { key: 'totalAmount', label: 'Total Amount', default: false, category: 'Payment Status', align: 'right' },

  { key: 'actions', label: 'Actions', default: true, locked: true, category: 'System', align: 'center' },
]

const sampleColumns: TableColumn[] = [
  // Customer
  { key: 'rsbsaNo', label: 'RSBSA No.', default: false, category: 'Customer Information' },
  { key: 'fullName', label: 'Full Name', default: false, category: 'Customer Information' },
  { key: 'firstName', label: 'First Name', default: false, category: 'Customer Information' },
  { key: 'middleName', label: 'Middle Name', default: false, category: 'Customer Information' },
  { key: 'surname', label: 'Last Name', default: false, category: 'Customer Information' },
  { key: 'sex', label: 'Sex', default: false, category: 'Customer Information' },
  { key: 'age', label: 'Age', default: false, category: 'Customer Information' },
  { key: 'companyName', label: 'Company Name', default: false, category: 'Customer Information' },
  { key: 'address', label: 'Address', default: false, category: 'Customer Information' },
  { key: 'contactNumber', label: 'Contact No.', default: false, category: 'Customer Information' },
  { key: 'emailAddress', label: 'Email Address', default: false, category: 'Customer Information' },
  { key: 'classification', label: 'Client Classification', default: false, category: 'Customer Information' },
  { key: 'samplingDate', label: 'Date of Sampling', default: false, category: 'Customer Information' },
  { key: 'samplingTime', label: 'Time of Sampling', default: false, category: 'Customer Information' },

  // Other Details
  { key: 'modeOfRelease', label: 'Mode of Release of Test Result', default: false, category: 'Other Details' },
  { key: 'retrieveSample', label: 'Retrieve retained sample after analysis', default: false, category: 'Other Details' },
  { key: 'agreedReleaseDate', label: 'Agreed Date of Release of results', default: false, category: 'Other Details' },

  // Request
  { key: 'requestId', label: 'Test Request No.', default: true, locked: true, category: 'Test Request Information' },
  { key: 'laboratoryCode', label: 'Lab Codes', default: true, category: 'Test Request Information' },
  { key: 'status', label: 'Status (of the test Request)', default: true, category: 'Test Request Information' },

  // Sample
  ...sampleInformationColumns.map((column) =>
    column.key === 'sampleDescription' || column.key === 'sampleId'
      ? { ...column, default: true }
      : column,
  ),

  // Payment
  { key: 'paymentStatus', label: 'Status', default: false, category: 'Payment Status' },
  { key: 'deposit', label: 'Deposit', default: false, category: 'Payment Status' },
  { key: 'orNo', label: 'O.R No.', default: false, category: 'Payment Status' },
  { key: 'paymentDate', label: 'Date', default: false, category: 'Payment Status' },
  { key: 'balance', label: 'Balance', default: false, category: 'Payment Status' },
  { key: 'totalAmountDue', label: 'Total Amount Due', default: false, category: 'Payment Status' },
  { key: 'totalAmount', label: 'Total Amount', default: false, category: 'Payment Status' },

  { key: 'actions', label: 'Actions', default: true, locked: true, align: 'center', category: 'System' },
]

const filters = ref<FilterState>({
  status: 'all',
  dateRange: 'all',
  sortBy: 'id-desc',
})

const searchQuery = ref('')
const showCreateModal = ref(false)
const showCustomizeModal = ref(false)
const showPaymentModal = ref(false)
const pageRoot = ref<HTMLElement | null>(null)
const editingPaymentReport = ref<Report | null>(null)
const paymentDraft = ref({
  paymentStatus: 'Pending',
  deposit: '',
  orNo: '',
  paymentDate: '',
  balance: '',
})
let reportsScrollContainer: HTMLElement | null = null

function getScrollableParent(element: HTMLElement | null) {
  let current = element?.parentElement || null

  while (current) {
    const overflowY = window.getComputedStyle(current).overflowY

    if (overflowY === 'auto' || overflowY === 'scroll') {
      return current
    }

    current = current.parentElement
  }

  return null
}

onMounted(() => {
  reportsScrollContainer = getScrollableParent(pageRoot.value)
  reportsScrollContainer?.classList.add('reports-hidden-scrollbar')
})

onUnmounted(() => {
  reportsScrollContainer?.classList.remove('reports-hidden-scrollbar')
})

function normalizeStatus(value: unknown): ReportStatus {
  return String(value || 'Test Request submitted') as ReportStatus
}

function normalizeSample(item: any, index: number): SampleRecord {
  const fallbackPrefix = String(item.sampleDescription || item.sample_description || '')
    .toLowerCase() === 'water'
    ? 'W'
    : String(item.sampleDescription || item.sample_description || '')
        .toLowerCase() === 'fertilizer'
      ? 'F'
      : 'S'
  const fallbackYear = String(new Date().getFullYear()).slice(-2)

  return {
    laboratoryCode: item.laboratoryCode || item.labCode || item.lab_code || `${fallbackPrefix}${fallbackYear}-${String(index + 1).padStart(3, '0')}`,
    sampleId: item.sampleId || item.sampleID || item.sample_id || '',
    sampleDescription: item.sampleDescription || item.description || item.sample_description || '',
    sampleType: item.sampleType || item.sample_type || '',

    topography: item.topography || '',
    coordinates:
      item.coordinates ||
      `${item.latitude || ''}, ${item.longitude || ''}`,

    longitude: item.longitude || '',
    latitude: item.latitude || '',

    region: item.region || '',
    province: item.province || '',
    municipality: item.municipality || '',
    barangay: item.barangay || '',

    farmArea: item.farmArea || item.farm_area || '',
    crops: item.crops || '',
    remarks: item.remarks || '',

    analysisRequested:
      item.analysisRequested ||
      item.analysis ||
      item.analysis_requested ||
      '',

    analysisRequestedChemist:
      item.analysisRequestedChemist || '',

    analysisRequestedAgriculturist:
      item.analysisRequestedAgriculturist || '',

    subtotal: Number(item.subtotal || item.amount || 0),
  }
}

function normalizeReport(item: any, index: number): Report {
  const samples = Array.isArray(item.samples)
    ? item.samples.map(normalizeSample)
    : Array.isArray(item.sampleRows)
      ? item.sampleRows.map(normalizeSample)
      : []

  const firstName = item.firstName || item.first_name || ''
  const middleName = item.middleName || item.middle_name || ''
  const surname = item.surname || item.lastName || item.last_name || ''
  const fullName = item.fullName || [firstName, middleName, surname].filter(Boolean).join(' ') || item.clientName || '—'

  const totalAmountDue =
    Number(item.totalAmountDue || item.total_amount_due || item.total || 0) ||
    samples.reduce((sum: number, sample: SampleRecord) => sum + sample.subtotal, 0)

  return {
    id: Number(item.id || index + 1),
    requestCode: item.requestCode || item.request_code || '',
    date: item.date || item.dateReceived || item.createdAt || new Date().toISOString().slice(0, 10),
    status: normalizeStatus(item.status),
    rsbsaNo: item.rsbsaNo || item.rsbsa_no || '',
    fullName,
    surname,
    firstName,
    middleName,
    companyName: item.companyName || item.company_name || '',
    sex: item.sex || '',
    age: item.age || '',
    classification: item.classification || '',
    address: item.address || '',
    contactNumber: item.contactNumber || item.contact_number || '',
    emailAddress: item.emailAddress || item.email_address || '',
    samplingDate: item.samplingDate || item.sampling_date || '',
    samplingTime: item.samplingTime || item.sampling_time || '',
    modeOfRelease: item.modeOfRelease || item.mode_of_release || '',
    retrieveSample: item.retrieveSample || item.retrieve_sample || '',
    agreedReleaseDate: item.agreedReleaseDate || item.agreed_release_date || '',
    numberOfSamples: item.numberOfSamples || item.number_of_samples || String(samples.length),
    dateReceived: item.dateReceived || item.date_received || '',
    receivedBy: item.receivedBy || item.received_by || '',
    paymentStatus: item.paymentStatus || item.payment_status || 'Pending',
    deposit: Number(item.deposit || 0),
    orNo: item.orNo || item.or_no || '',
    paymentDate: item.paymentDate || item.payment_date || '',
    balance: Number(item.balance || Math.max(totalAmountDue - Number(item.deposit || 0), 0)),
    totalAmountDue,
    totalAmount: Number(item.totalAmount || item.total_amount || totalAmountDue),
    samples,
  }
}

const allReports = computed<Report[]>(() =>
  (props.reports?.data ?? []).map((item, index) => normalizeReport(item, index)),
)

const createRequestClients = computed(() =>
  allReports.value.map((report) => ({
    id: String(report.id),
    code: report.rsbsaNo || getReportId(report),
    name: report.fullName || '—',
    type: report.classification || report.companyName || 'Client',
  })),
)

const currentColumns = computed(() => {
  console.log('PAGE MODE:', pageMode.value)
  console.log(
    'Returning',
    pageMode.value === 'test-request'
      ? 'requestColumns'
      : 'sampleColumns'
  )

  return pageMode.value === 'test-request'
    ? requestColumns
    : sampleColumns
})

const currentStorageKey = computed(() =>
  pageMode.value === 'test-request' ? REQUEST_COLUMN_STORAGE_KEY : SAMPLE_COLUMN_STORAGE_KEY,
)

const defaultColumnKeys = computed(() =>
  currentColumns.value.filter((column) => column.default).map((column) => column.key),
)

function getSavedColumnKeys() {
  if (typeof window === 'undefined') return defaultColumnKeys.value

  try {
    const saved = localStorage.getItem(currentStorageKey.value)
    if (!saved) return defaultColumnKeys.value

    const parsed = JSON.parse(saved)
    if (!Array.isArray(parsed)) return defaultColumnKeys.value

    return parsed.filter((key) =>
      currentColumns.value.some((column) => column.key === key),
    )
  } catch {
    return defaultColumnKeys.value
  }
}

const visibleColumnKeys = ref<string[]>(getSavedColumnKeys())
const draftColumnKeys = ref<string[]>([...visibleColumnKeys.value])

function setPageMode(mode: PageMode) {
  console.log('Changing page mode to:', mode)

  pageMode.value = mode

  console.log('Current columns after switch:')
  console.table(
    currentColumns.value.map(c => ({
      label: c.label,
      category: c.category,
    })),
  )

  visibleColumnKeys.value = getSavedColumnKeys()
  draftColumnKeys.value = [...visibleColumnKeys.value]
}

const activeColumns = computed(() => {
  const visible = currentColumns.value.filter((column) =>
    visibleColumnKeys.value.includes(column.key),
  )

  return [
    ...visible.filter((column) => column.key === 'requestId'),
    ...visible.filter((column) => column.key !== 'requestId'),
  ]
})

const tableMinWidth = computed(() => {
  if (activeColumns.value.length <= 7) return '100%'
  return `${activeColumns.value.length * 150}px`
})

function getReportId(report: Report) {
  if (report.requestCode) return report.requestCode

  const year = new Date(report.date).getFullYear()
  return `RSL-${year}-${String(report.id).padStart(3, '0')}`
}

function getLabCodeDisplay(report: Report) {
  const codes = report.samples.map((sample) => sample.laboratoryCode)
  if (codes.length <= 3) return codes.join(', ')
  return `${codes.slice(0, 3).join(', ')}, ...`
}

function formatMoney(value?: number) {
  if (value === null || value === undefined) return '—'
  return `₱ ${value.toLocaleString()}`
}

function isReport(row: ActiveRow): row is Report {
  return 'samples' in row
}

function formatRequestValue(report: Report, key: string) {
  if (key === 'requestId') return getReportId(report)
  if (key === 'labCodes') return getLabCodeDisplay(report)
  if (key === 'deposit') return formatMoney(report.deposit)
  if (key === 'balance') return formatMoney(report.balance)
  if (key === 'totalAmountDue') return formatMoney(report.totalAmountDue)
  if (key === 'actions') return ''

  const value = report[key as keyof Report]
  if (value === null || value === undefined || value === '') return '—'

  return String(value)
}

const sampleRows = computed<FlattenedSample[]>(() =>
  allReports.value.flatMap((report) => {
    const { id, samples, ...reportFields } = report

    return samples.map((sample) => ({
      ...reportFields,
      ...sample,
      requestId: getReportId(report),
      reportId: id,
    }))
  }),
)

console.table(
  sampleRows.value.map(s => ({
    request: s.requestId,
    lab: s.laboratoryCode,
    sample: s.sampleId,
  })),
)

function formatSampleValue(sample: FlattenedSample, key: string) {
  if (key === 'subtotal') return formatMoney(sample.subtotal)
  if (key === 'actions') return ''

  const value = sample[key as keyof FlattenedSample]
  if (value === null || value === undefined || value === '') return '—'

  return String(value)
}

const filteredReports = computed(() => {
  const today = new Date()

  return [...allReports.value]
    .filter((report) => {
      if (searchQuery.value.trim()) {
        const searchLower = searchQuery.value.toLowerCase()
        const reportId = getReportId(report).toLowerCase()
        const labCodes = getLabCodeDisplay(report).toLowerCase()

        const matchesSearch =
          reportId.includes(searchLower) ||
          labCodes.includes(searchLower) ||
          report.companyName.toLowerCase().includes(searchLower) ||
          report.fullName.toLowerCase().includes(searchLower)

        if (!matchesSearch) return false
      }

      if (filters.value.status !== 'all' && report.status !== filters.value.status) return false

      if (filters.value.dateRange !== 'all') {
        const reportDate = new Date(report.date)

        switch (filters.value.dateRange) {
          case 'today':
            if (reportDate.toDateString() !== today.toDateString()) return false
            break
          case 'week': {
            const weekAgo = new Date(today)
            weekAgo.setDate(today.getDate() - 7)
            if (reportDate < weekAgo) return false
            break
          }
          case 'month':
            if (reportDate.getMonth() !== today.getMonth() || reportDate.getFullYear() !== today.getFullYear()) return false
            break
          case 'quarter': {
            const quarterStart = new Date(today.getFullYear(), Math.floor(today.getMonth() / 3) * 3, 1)
            if (reportDate < quarterStart) return false
            break
          }
          case 'year':
            if (reportDate.getFullYear() !== today.getFullYear()) return false
            break
        }
      }

      return true
    })
    .sort((a, b) => {
      switch (filters.value.sortBy) {
        case 'id-desc': return b.id - a.id
        case 'id-asc': return a.id - b.id
        case 'date-desc': return new Date(b.date).getTime() - new Date(a.date).getTime()
        case 'date-asc': return new Date(a.date).getTime() - new Date(b.date).getTime()
        case 'client-asc': return a.fullName.localeCompare(b.fullName)
        case 'client-desc': return b.fullName.localeCompare(a.fullName)
        case 'company-asc': return a.companyName.localeCompare(b.companyName)
        case 'company-desc': return b.companyName.localeCompare(a.companyName)
        default: return b.id - a.id
      }
    })
})

const filteredSamples = computed(() =>
  sampleRows.value.filter((sample) => {
    if (searchQuery.value.trim()) {
      const searchLower = searchQuery.value.toLowerCase()

      const matchesSearch =
        sample.requestId.toLowerCase().includes(searchLower) ||
        sample.laboratoryCode.toLowerCase().includes(searchLower) ||
        sample.sampleId.toLowerCase().includes(searchLower) ||
        sample.companyName.toLowerCase().includes(searchLower) ||
        sample.fullName.toLowerCase().includes(searchLower)

      if (!matchesSearch) return false
    }

    if (filters.value.status !== 'all' && sample.status !== filters.value.status) return false

    return true
  }),
)

const activeRows = computed<ActiveRow[]>(() =>
  pageMode.value === 'test-request' ? filteredReports.value : filteredSamples.value,
)

const summaryCards = computed(() => {
  const rows = activeRows.value

  return [
    {
      title: 'Filtered Total',
      value: rows.length,
      icon: FileText,
      borderClass: 'border-l-blue-500',
      iconClass: 'text-blue-600',
      iconBgClass: 'bg-blue-100',
    },
    {
      title: 'Active',
      value: rows.filter((item) => item.status === 'Under Analyzation' || item.status === 'Under Recommendation').length,
      icon: Clock,
      borderClass: 'border-l-purple-500',
      iconClass: 'text-purple-600',
      iconBgClass: 'bg-purple-100',
    },
    {
      title: 'Pending',
      value: rows.filter((item) => item.status === 'Test Request submitted').length,
      icon: AlertCircle,
      borderClass: 'border-l-amber-500',
      iconClass: 'text-amber-600',
      iconBgClass: 'bg-amber-100',
    },
    {
      title: 'Completed',
      value: rows.filter((item) => item.status === 'Ready').length,
      icon: CheckCircle,
      borderClass: 'border-l-green-500',
      iconClass: 'text-green-600',
      iconBgClass: 'bg-green-100',
    },
  ]
})

function getRowKey(row: ActiveRow) {
  if (isReport(row)) {
    return `request-${row.id}`
  }

  return `sample-${row.reportId}-${row.laboratoryCode}-${row.sampleId}`
}

function getRequestIdFromRow(row: ActiveRow) {
  return isReport(row) ? getReportId(row) : row.requestId
}

const mergedCustomerColumns = [
  'rsbsaNo',
  'fullName',
  'firstName',
  'middleName',
  'surname',
  'sex',
  'age',
  'companyName',
  'address',
  'contactNumber',
  'emailAddress',
  'classification',
]

function isFirstSampleOfRequest(row: ActiveRow, index: number) {
  if (pageMode.value !== 'sample') return true
  if (isReport(row)) return true
  if (index === 0) return true

  const previous = activeRows.value[index - 1]
  if (isReport(previous)) return true

  return previous.requestId !== row.requestId
}

function getSampleRequestRowspan(row: ActiveRow) {
  if (isReport(row)) return 1

  return filteredSamples.value.filter((sample) => sample.requestId === row.requestId).length
}

function isNewSampleGroup(row: ActiveRow, index: number) {
  if (pageMode.value !== 'sample') return false
  if (index === 0) return false
  if (isReport(row)) return false

  const previous = activeRows.value[index - 1]
  if (isReport(previous)) return false

  return previous.requestId !== row.requestId
}

function formatCellValue(row: ActiveRow, key: string) {
  if (isReport(row)) return formatRequestValue(row, key)
  return formatSampleValue(row, key)
}

function clearFilters() {
  filters.value = {
    status: 'all',
    dateRange: 'all',
    sortBy: 'id-desc',
  }
  searchQuery.value = ''
}

function openCustomizeModal() {
  console.clear()

  console.log('PAGE MODE:', pageMode.value)

  console.table(
    currentColumns.value.map(c => ({
      label: c.label,
      category: c.category
    }))
  )

  draftColumnKeys.value = [...visibleColumnKeys.value]
  showCustomizeModal.value = true
}

function toggleDraftColumn(key: string) {
  const column = currentColumns.value.find((item) => item.key === key)
  if (column?.locked) return

  if (draftColumnKeys.value.includes(key)) {
    draftColumnKeys.value = draftColumnKeys.value.filter((item) => item !== key)
    return
  }

  draftColumnKeys.value.push(key)
}

function handleToggleColumn(key: string) {
  toggleDraftColumn(key)
}

function applyDefaultView() {
  draftColumnKeys.value = [...defaultColumnKeys.value]
}

function applyDetailedView() {
  draftColumnKeys.value = currentColumns.value.map((column) => column.key)
}

function saveColumnView() {
  const lockedKeys = currentColumns.value
    .filter((column) => column.locked)
    .map((column) => column.key)

  visibleColumnKeys.value = Array.from(new Set([
    ...lockedKeys,
    ...draftColumnKeys.value,
  ]))

  localStorage.setItem(currentStorageKey.value, JSON.stringify(visibleColumnKeys.value))
  showCustomizeModal.value = false
}

function resetSavedView() {
  visibleColumnKeys.value = [...defaultColumnKeys.value]
  draftColumnKeys.value = [...defaultColumnKeys.value]
  localStorage.setItem(currentStorageKey.value, JSON.stringify(defaultColumnKeys.value))
}

function handleCreateNewClient() {
  showCreateModal.value = false
  router.visit('/test-reports/create/page-1?from=reports')
}

function handleAddRecord(clientId: string) {
  showCreateModal.value = false
  router.visit(`/test-reports/create/page-2?client_id=${clientId}&from_existing=true&from=reports`)
}

function handleViewReport(row: ActiveRow) {
  const reportId = isReport(row) ? row.id : row.reportId

  router.visit(`/reports/${reportId}`)
}

function handleEditReport(row: ActiveRow) {
  if (pageMode.value !== 'test-request') return
  if (!isReport(row)) return

  editingPaymentReport.value = row
  paymentDraft.value = {
    paymentStatus: row.paymentStatus || 'Pending',
    deposit: row.deposit !== undefined && row.deposit !== null ? String(row.deposit) : '',
    orNo: row.orNo || '',
    paymentDate: row.paymentDate || '',
    balance: row.balance !== undefined && row.balance !== null ? String(row.balance) : '',
  }
  showPaymentModal.value = true
}

function mapSamplesForSubmit(report: Report) {
  return report.samples.map((sample) => ({
    laboratory_code: sample.laboratoryCode,
    sample_id: sample.sampleId,
    sample_description: sample.sampleDescription,
    sample_type: sample.sampleType,
    topography: sample.topography,
    coordinates: sample.coordinates,
    longitude: sample.longitude,
    latitude: sample.latitude,
    region: sample.region,
    province: sample.province,
    municipality: sample.municipality,
    barangay: sample.barangay,
    farm_area: sample.farmArea,
    crops: sample.crops,
    remarks: sample.remarks,
    analysis_requested: sample.analysisRequested,
    analysis_requested_chemist: sample.analysisRequestedChemist,
    analysis_requested_agriculturist: sample.analysisRequestedAgriculturist,
    subtotal: sample.subtotal,
  }))
}

function savePaymentChanges() {
  if (!editingPaymentReport.value) return

  const report = editingPaymentReport.value
  const payload = {
    request_code: report.requestCode || getReportId(report),
    date: report.date,
    status: report.status,
    is_draft: false,
    surname: report.surname,
    first_name: report.firstName,
    middle_name: report.middleName,
    full_name: report.fullName,
    rsbsa_no: report.rsbsaNo,
    company_name: report.companyName,
    classification: report.classification,
    sex: report.sex,
    age: report.age,
    address: report.address,
    contact_number: report.contactNumber,
    email_address: report.emailAddress,
    sampling_date: report.samplingDate,
    sampling_time: report.samplingTime,
    mode_of_release: report.modeOfRelease,
    retrieve_sample: report.retrieveSample,
    agreed_release_date: report.agreedReleaseDate,
    number_of_samples: report.numberOfSamples,
    date_received: report.dateReceived,
    received_by: report.receivedBy,
    payment_status: paymentDraft.value.paymentStatus,
    deposit: Number(paymentDraft.value.deposit || 0),
    or_no: paymentDraft.value.orNo,
    payment_date: paymentDraft.value.paymentDate,
    balance: Number(paymentDraft.value.balance || 0),
    total_amount_due: report.totalAmountDue,
    total_amount: report.totalAmount,
    samples: mapSamplesForSubmit(report),
  }

  router.put(`/reports/${report.id}`, payload, {
    preserveScroll: true,
    onSuccess: () => {
      showPaymentModal.value = false
      editingPaymentReport.value = null
      router.reload({ preserveScroll: true })
    },
  })
}
</script>

<template>
  <div ref="pageRoot" class="relative p-3 sm:p-4 md:p-5">
    <div class="flex flex-col gap-5">
      <div
        class="sticky top-0 z-30 -mx-3 space-y-5 border-b border-border bg-background px-3 pb-4 pt-3 sm:-mx-4 sm:px-4 md:-mx-5 md:px-5 md:pt-5"
      >
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
          <h1 class="text-xl font-bold text-foreground sm:text-2xl">
            {{ pageMode === 'test-request' ? 'Test Request Records' : 'Sample Records' }}
          </h1>

          <button
            type="button"
            @click="showCreateModal = true"
            class="inline-flex items-center justify-center gap-2 rounded-lg border border-primary bg-card px-4 py-2.5 text-sm font-semibold text-primary transition hover:bg-primary hover:text-primary-foreground"
          >
            <Plus class="h-4 w-4" />
            Create Test Request
          </button>
        </div>

        <div class="flex flex-wrap gap-2">
          <button
            type="button"
            @click="setPageMode('test-request')"
            class="rounded-lg border px-4 py-2 text-sm font-semibold"
            :class="pageMode === 'test-request'
              ? 'border-primary text-primary'
              : 'border-border text-muted-foreground'"
          >
            Test Request List
          </button>

          <button
            type="button"
            @click="setPageMode('sample')"
            class="rounded-lg border px-4 py-2 text-sm font-semibold"
            :class="pageMode === 'sample'
              ? 'border-primary text-primary'
              : 'border-border text-muted-foreground'"
          >
            Sample List
          </button>
        </div>
      </div>

      <div class="rounded-xl border border-zinc-200 bg-white p-3 shadow-sm">
        <div class="grid gap-2 md:grid-cols-4">
          <div class="relative md:col-span-4">
            <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-400" />
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search by test request no., lab code, company, or client name..."
              class="h-10 w-full rounded-lg border border-zinc-200 bg-white pl-10 pr-3 text-sm text-zinc-900 outline-none focus:border-zinc-400"
            />
          </div>

          <select v-model="filters.status" class="h-10 rounded-lg border border-zinc-200 bg-white px-3 text-sm text-zinc-900 outline-none focus:border-zinc-400">
            <option value="all">All Statuses</option>
            <option value="Test Request submitted">Test Request submitted</option>
            <option value="Under Analyzation">Under Analyzation</option>
            <option value="Analyzed">Analyzed</option>
            <option value="Under Recommendation">Under Recommendation</option>
            <option value="Recommended">Recommended</option>
            <option value="Reviewed">Reviewed</option>
            <option value="Certified">Certified</option>
            <option value="Noted">Noted</option>
            <option value="Ready">Ready</option>
          </select>

          <select v-model="filters.dateRange" class="h-10 rounded-lg border border-zinc-200 bg-white px-3 text-sm text-zinc-900 outline-none focus:border-zinc-400">
            <option value="all">All Time</option>
            <option value="today">Today</option>
            <option value="week">This Week</option>
            <option value="month">This Month</option>
            <option value="quarter">This Quarter</option>
            <option value="year">This Year</option>
          </select>

          <select v-model="filters.sortBy" class="h-10 rounded-lg border border-zinc-200 bg-white px-3 text-sm text-zinc-900 outline-none focus:border-zinc-400">
            <option value="id-desc">Test Request No. (Newest First)</option>
            <option value="id-asc">Test Request No. (Oldest First)</option>
            <option value="date-desc">Date (Newest First)</option>
            <option value="date-asc">Date (Oldest First)</option>
            <option value="client-asc">Client Name (A-Z)</option>
            <option value="client-desc">Client Name (Z-A)</option>
            <option value="company-asc">Company Name (A-Z)</option>
            <option value="company-desc">Company Name (Z-A)</option>
          </select>
        </div>

        <div class="mt-2 flex flex-wrap justify-end gap-2">
          <button
            type="button"
            @click="openCustomizeModal"
            class="inline-flex items-center gap-2 rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-sm font-medium text-zinc-600 transition hover:bg-zinc-50 hover:text-zinc-900"
          >
            <SlidersHorizontal class="h-4 w-4" />
            Customize View
          </button>

          <button
            type="button"
            @click="clearFilters"
            class="rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-sm font-medium text-zinc-600 transition hover:bg-zinc-50 hover:text-zinc-900"
          >
            Clear All Filters
          </button>
        </div>
      </div>

      <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <div
          v-for="card in summaryCards"
          :key="card.title"
          class="rounded-xl border border-zinc-200 border-l-4 bg-white p-3 shadow-sm"
          :class="card.borderClass"
        >
          <div class="flex items-start justify-between">
            <div>
              <p class="text-xs text-zinc-500">
                {{ card.title }}
              </p>
              <p class="mt-1 text-xl font-semibold text-zinc-900">
                {{ card.value }}
              </p>
            </div>

            <div class="rounded-lg p-2" :class="card.iconBgClass">
              <component :is="card.icon" class="h-4 w-4" :class="card.iconClass" />
            </div>
          </div>
        </div>
      </div>

      <div class="rounded-xl border border-zinc-200 bg-white shadow-sm">
        <div class="border-b border-zinc-200 px-4 py-3 sm:px-5 sm:py-4">
          <h2 class="text-base font-semibold tracking-wide text-zinc-900">
            {{ pageMode === 'test-request' ? 'Test Request List' : 'Sample List' }}
          </h2>
          <p class="text-sm text-zinc-500">
            {{ activeRows.length }} record<span v-if="activeRows.length !== 1">s</span>
          </p>
        </div>

        <div class="grid gap-3 p-4 lg:hidden">
          <div
            v-for="(row, index) in activeRows"
            :key="`${getRowKey(row)}-card`"
            class="rounded-xl border border-zinc-200 bg-white p-4"
            :class="isNewSampleGroup(row, index) ? 'border-t-4 border-t-zinc-900' : ''"
          >
            <p class="text-sm font-semibold text-sky-600">
              {{ getRequestIdFromRow(row) }}
            </p>

            <div class="mt-3 space-y-2">
              <template
                v-for="column in activeColumns.filter((item) => item.key !== 'actions' && item.key !== 'requestId')"
                :key="`${getRowKey(row)}-${column.key}-card`"
              >
                <div>
                  <p class="text-[11px] font-medium text-zinc-500">
                    {{ column.label }}
                  </p>
                  <p class="wrap-break-words text-sm text-zinc-900">
                    {{ formatCellValue(row, column.key) }}
                  </p>
                </div>
              </template>
            </div>

            <div class="mt-4 flex flex-wrap gap-2">
              <button
                type="button"
                @click="handleViewReport(row)"
                class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-sky-200 bg-sky-50 text-sky-700 transition hover:bg-sky-100"
              >
                <Eye class="h-4 w-4" />
              </button>

              <button
                v-if="pageMode === 'test-request'"
                type="button"
                @click="handleEditReport(row)"
                class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-zinc-200 bg-white text-zinc-700 transition hover:bg-zinc-50"
              >
                <Pencil class="h-4 w-4" />
              </button>
            </div>
          </div>

          <div
            v-if="activeRows.length === 0"
            class="rounded-xl border border-dashed border-zinc-300 px-5 py-8 text-center text-sm text-zinc-500"
          >
            No records found for the selected filters.
          </div>
        </div>

        <div class="no-scrollbar hidden max-w-full overflow-x-auto lg:block">
          <table class="w-full text-sm" :style="{ minWidth: tableMinWidth }">
            <thead>
              <tr class="border-b border-zinc-200 bg-zinc-50">
                <th
                  v-for="column in activeColumns"
                  :key="column.key"
                  class="whitespace-nowrap px-3 py-2 font-semibold text-zinc-700"
                  :class="{
                    'text-left': !column.align || column.align === 'left',
                    'text-center': column.align === 'center',
                    'text-right': column.align === 'right',
                    'sticky right-0 z-20 border-l border-zinc-200 bg-zinc-50 shadow-[-8px_0_12px_-12px_rgba(0,0,0,0.45)]': column.key === 'actions',
                  }"
                >
                  {{ column.label }}
                </th>
              </tr>
            </thead>

            <tbody>
              <tr
                v-for="(row, index) in activeRows"
                :key="getRowKey(row)"
                :class="[
                  'border-b border-zinc-100 transition-colors hover:bg-zinc-50',
                  isNewSampleGroup(row, index) ? 'border-t-4 border-zinc-900' : '',
                ]"
              >
                <template
                  v-for="column in activeColumns"
                  :key="`${getRowKey(row)}-${column.key}`"
                >
                  <td
                    v-if="
                      !(
                        pageMode === 'sample' &&
                        ['requestId', ...mergedCustomerColumns].includes(column.key)
                      ) ||
                      isFirstSampleOfRequest(row, index)
                    "
                    :rowspan="
                    pageMode === 'sample' &&
                      ['requestId', ...mergedCustomerColumns].includes(column.key)
                        ? getSampleRequestRowspan(row)
                        : 1
                    "
                    class="whitespace-nowrap px-3 py-2.5 align-middle"
                    :class="{
                      'text-left': !column.align || column.align === 'left',
                      'text-center': column.align === 'center',
                      'text-right': column.align === 'right',
                      'sticky right-0 z-10 border-l border-zinc-100 bg-white shadow-[-8px_0_12px_-12px_rgba(0,0,0,0.45)]': column.key === 'actions',
                    }"
                  >
                    <span
                      v-if="column.key === 'requestId'"
                      class="font-medium text-sky-600"
                    >
                      {{ getRequestIdFromRow(row) }}
                    </span>

                    <div v-else-if="column.key === 'actions'" class="flex items-center justify-center gap-1">
                      <button
                        type="button"
                        @click="handleViewReport(row)"
                        class="inline-flex h-7 w-7 items-center justify-center rounded-md border border-sky-200 bg-sky-50 text-sky-700 transition hover:bg-sky-100"
                        title="View"
                      >
                        <Eye class="h-3.5 w-3.5" />
                      </button>

                      <button
                        v-if="pageMode === 'test-request'"
                        type="button"
                        @click="handleEditReport(row)"
                        class="inline-flex h-7 w-7 items-center justify-center rounded-md border border-zinc-200 bg-white text-zinc-700 transition hover:bg-zinc-50"
                        title="Edit Payment Status"
                      >
                        <Pencil class="h-3.5 w-3.5" />
                      </button>
                    </div>

                    <span v-else class="block max-w-55 truncate text-zinc-900">
                      {{ formatCellValue(row, column.key) }}
                    </span>
                  </td>
                </template>
              </tr>

              <tr v-if="activeRows.length === 0">
                <td :colspan="activeColumns.length" class="px-4 py-8 text-center text-sm text-zinc-500">
                  No records found for the selected filters.
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div
          v-if="activeRows.length > 0"
          class="border-t border-zinc-200 px-4 py-2.5 text-sm text-zinc-500"
        >
          Showing {{ activeRows.length }} record<span v-if="activeRows.length !== 1">s</span>
        </div>
      </div>
    </div>

    <CreateTestRequestEntryModal
      :open="showCreateModal"
      :clients="createRequestClients"
      @close="showCreateModal = false"
      @create-new="handleCreateNewClient"
      @add-record="handleAddRecord"
    />

    <CustomizeTableViewModal
      :open="showCustomizeModal"
      :columns="pageMode === 'sample' ? sampleColumns : requestColumns"
      :draft-column-keys="draftColumnKeys"
      @close="showCustomizeModal = false"
      @toggle-column="handleToggleColumn"
      @default-view="applyDefaultView"
      @detailed-view="applyDetailedView"
      @reset-saved-view="resetSavedView"
      @save-view="saveColumnView"
    />

    <div
      v-if="showPaymentModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4 py-6"
      @click.self="showPaymentModal = false"
    >
      <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl">
        <div class="mb-5 flex items-center justify-between gap-4">
          <h3 class="text-lg font-semibold text-zinc-900">
            Edit Payment Status
          </h3>

          <button
            type="button"
            class="text-2xl leading-none text-zinc-400 transition hover:text-zinc-700"
            @click="showPaymentModal = false"
          >
            &times;
          </button>
        </div>

        <div class="space-y-4">
          <div>
            <label class="mb-1 block text-sm font-medium text-zinc-700">Status</label>
            <select
              v-model="paymentDraft.paymentStatus"
              class="h-11 w-full rounded-lg border border-zinc-200 px-3 text-sm outline-none focus:border-zinc-400"
            >
              <option value="Pending">Pending</option>
              <option value="Paid">Paid</option>
              <option value="Partially Paid">Partially Paid</option>
              <option value="Unpaid">Unpaid</option>
            </select>
          </div>

          <div class="grid gap-4 md:grid-cols-2">
            <div>
              <label class="mb-1 block text-sm font-medium text-zinc-700">Deposit</label>
              <input
                v-model="paymentDraft.deposit"
                type="number"
                min="0"
                step="0.01"
                class="h-11 w-full rounded-lg border border-zinc-200 px-3 text-sm outline-none focus:border-zinc-400"
              >
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-zinc-700">O.R. No.</label>
              <input
                v-model="paymentDraft.orNo"
                type="text"
                class="h-11 w-full rounded-lg border border-zinc-200 px-3 text-sm outline-none focus:border-zinc-400"
              >
            </div>
          </div>

          <div class="grid gap-4 md:grid-cols-2">
            <div>
              <label class="mb-1 block text-sm font-medium text-zinc-700">Date</label>
              <input
                v-model="paymentDraft.paymentDate"
                type="date"
                class="h-11 w-full rounded-lg border border-zinc-200 px-3 text-sm outline-none focus:border-zinc-400"
              >
            </div>

            <div>
              <label class="mb-1 block text-sm font-medium text-zinc-700">Balance</label>
              <input
                v-model="paymentDraft.balance"
                type="number"
                min="0"
                step="0.01"
                class="h-11 w-full rounded-lg border border-zinc-200 px-3 text-sm outline-none focus:border-zinc-400"
              >
            </div>
          </div>
        </div>

        <div class="mt-6 flex items-center justify-end gap-3">
          <button
            type="button"
            class="rounded-lg border border-zinc-200 px-4 py-2.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50"
            @click="showPaymentModal = false"
          >
            Cancel
          </button>

          <button
            type="button"
            class="rounded-lg bg-[#0E3D1A] px-4 py-2.5 text-sm font-semibold text-white transition hover:opacity-90"
            @click="savePaymentChanges"
          >
            Save Changes
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.no-scrollbar {
  scrollbar-width: none;
}

.no-scrollbar::-webkit-scrollbar {
  display: none;
}

:global(.reports-hidden-scrollbar) {
  scrollbar-width: none;
}

:global(.reports-hidden-scrollbar::-webkit-scrollbar) {
  display: none;
}
</style>
