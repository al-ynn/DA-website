<script setup lang="ts">
import { ref, computed, onMounted, nextTick } from 'vue'
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue'
import { Head } from '@inertiajs/vue3'
import type { BreadcrumbItem } from '@/types'
import { Users, MessageCircle, Send, UserPlus } from 'lucide-vue-next'

type ContactStatus = 'client' | 'unknown' | 'employee'

interface Conversation {
  id: number | string
  name: string | null
  phone: string
  unread: boolean
  unreadCount: number
  lastMessage: string
  time: string | null
  isClient: boolean
  status?: ContactStatus
}

interface Contact {
  id: number | string
  name: string
  phone: string
  status: ContactStatus
}

interface Message {
  id: number | string
  direction: 'in' | 'out'
  text: string
  time: string
  rawTime?: string | null
  status?: 'queued' | 'sending' | 'delivered' | 'resending' | 'failed'
  seenBy?: string | null
  repliedBy?: string | null
  byWho?: number | string | null
  senderRole?: string | null
  senderName?: string | null
}

interface MessageNotification {
  id: string
  messageId: number | string
  phone: string
  senderName: string
  preview: string
  avatarText: string
  timestamp: string
  createdAt: number
  timer?: ReturnType<typeof setTimeout> | null
  hovering?: boolean
}

const bywholist = [
  { id: 1, name: 'V. Quitos', phone: '+639111111111' },
  { id: 2, name: 'V. Quitos', phone: '+639122222222' },
  { id: 3, name: 'E. Amurao', phone: '+639133333333' },
]

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Two-way SMS', href: '/sms' }]

const adminDisplayName = ref('Admin')

const staticContacts = ref<Contact[]>([
  { id: 1, name: 'Juan Dela Cruz', phone: '+639171111111', status: 'client' },
  { id: 2, name: 'Maria Santos', phone: '+639181234567', status: 'client' },
  { id: 3, name: 'Pedro Reyes', phone: '+639199998888', status: 'client' },
  ...bywholist.map(user => ({
    id: `employee-${user.id}`,
    name: user.name,
    phone: user.phone,
    status: 'employee' as ContactStatus,
  })),
])

const localConversations = ref<Conversation[]>([
  {
    id: 1,
    name: 'Juan Dela Cruz',
    phone: '+639171111111',
    unread: true,
    unreadCount: 2,
    lastMessage: 'Pwede po ba follow up sa request?',
    time: 'Mar 30, 2026, 10:25 AM',
    isClient: true,
    status: 'client',
  },
  {
    id: 2,
    name: null,
    phone: '+639181234567',
    unread: false,
    unreadCount: 0,
    lastMessage: 'Hello po',
    time: 'Mar 30, 2026, 9:10 AM',
    isClient: false,
    status: 'unknown',
  },
  {
    id: 3,
    name: 'Pedro Reyes',
    phone: '+639199998888',
    unread: false,
    unreadCount: 0,
    lastMessage: 'Salamat po',
    time: 'Mar 29, 2026, 4:30 PM',
    isClient: true,
    status: 'client',
  },
])

const localMessagesByPhone = ref<Record<string, Message[]>>({
  '+639171111111': [
    {
      id: 1,
      direction: 'in',
      text: 'Good morning po',
      time: 'Mar 30, 2026, 9:55 AM',
      rawTime: '2026-03-30T09:55:00+08:00',
      status: 'delivered',
      byWho: 1,
      senderRole: 'client',
      senderName: 'Juan Dela Cruz',
    },
    {
      id: 2,
      direction: 'out',
      text: 'Good morning. Paano po kami makakatulong?',
      time: 'Mar 30, 2026, 10:00 AM',
      rawTime: '2026-03-30T10:00:00+08:00',
      status: 'delivered',
      byWho: 3,
      senderRole: 'admin',
      senderName: 'Admin',
    },
    {
      id: 3,
      direction: 'in',
      text: 'Pwede po ba follow up sa request?',
      time: 'Mar 30, 2026, 10:25 AM',
      rawTime: '2026-03-30T10:25:00+08:00',
      status: 'delivered',
      byWho: 1,
      senderRole: 'client',
      senderName: 'Juan Dela Cruz',
    },
  ],
  '+639181234567': [
    {
      id: 4,
      direction: 'in',
      text: 'Hello po',
      time: 'Mar 30, 2026, 9:10 AM',
      rawTime: '2026-03-30T09:10:00+08:00',
      status: 'delivered',
      byWho: 1,
      senderRole: 'unknown',
      senderName: null,
    },
  ],
  '+639199998888': [
    {
      id: 5,
      direction: 'out',
      text: 'Na-process na po ang request ninyo.',
      time: 'Mar 29, 2026, 4:20 PM',
      rawTime: '2026-03-29T16:20:00+08:00',
      status: 'delivered',
      byWho: 3,
      senderRole: 'admin',
      senderName: 'Admin',
    },
    {
      id: 6,
      direction: 'in',
      text: 'Salamat po',
      time: 'Mar 29, 2026, 4:30 PM',
      rawTime: '2026-03-29T16:30:00+08:00',
      status: 'delivered',
      byWho: 1,
      senderRole: 'client',
      senderName: 'Pedro Reyes',
    },
  ],
})

const activePhone = ref<string | null>(null)
const selectedMessageId = ref<number | string | null>(null)
const notifications = ref<MessageNotification[]>([])

const MAX_NOTIFICATIONS = 1
const NOTIFICATION_DURATION = 4000

function getInitials(name: string): string {
  const clean = String(name || '').trim()
  if (!clean) return '?'
  const parts = clean.split(/\s+/).filter(Boolean)
  if (parts.length === 1) return parts[0].charAt(0).toUpperCase()
  return (parts[0].charAt(0) + parts[1].charAt(0)).toUpperCase()
}

function truncateNotificationText(text: string, max = 90): string {
  const clean = String(text ?? '').replace(/\s+/g, ' ').trim()
  if (clean.length <= max) return clean
  return clean.slice(0, max).trim() + '...'
}

function formatRelativeNotificationTime(timestamp: any): string {
  if (!timestamp) return 'now'
  const date = new Date(timestamp)
  if (isNaN(date.getTime())) return 'now'
  const diffMs = Date.now() - date.getTime()
  const diffSec = Math.floor(diffMs / 1000)
  const diffMin = Math.floor(diffSec / 60)
  const diffHr = Math.floor(diffMin / 60)
  const diffDay = Math.floor(diffHr / 24)
  if (diffSec < 10) return 'now'
  if (diffSec < 60) return `${diffSec}s ago`
  if (diffMin < 60) return `${diffMin}m ago`
  if (diffHr < 24) return `${diffHr}h ago`
  if (diffDay === 1) return 'Yesterday'
  if (diffDay < 7) return `${diffDay}d ago`
  return formatDateOnly(timestamp)
}

function startNotificationTimer(notificationId: string) {
  const notification = notifications.value.find(n => n.id === notificationId)
  if (!notification) return
  if (notification.timer) clearTimeout(notification.timer)
  notification.timer = setTimeout(() => removeNotification(notificationId), NOTIFICATION_DURATION)
}

function formatMessengerDivider(timestamp: any): string {
  if (!timestamp) return ''
  const date = new Date(timestamp)
  if (isNaN(date.getTime())) return ''
  const timeText = formatTimeOnly(timestamp)
  if (isTodayTimestamp(timestamp)) return timeText
  return `${formatDateOnly(timestamp)} at ${timeText}`
}

function pauseNotificationTimer(notificationId: string) {
  const notification = notifications.value.find(n => n.id === notificationId)
  if (!notification) return
  notification.hovering = true
  if (notification.timer) {
    clearTimeout(notification.timer)
    notification.timer = null
  }
}

function resumeNotificationTimer(notificationId: string) {
  const notification = notifications.value.find(n => n.id === notificationId)
  if (!notification) return
  notification.hovering = false
  startNotificationTimer(notificationId)
}

function removeNotification(notificationId: string) {
  const index = notifications.value.findIndex(n => n.id === notificationId)
  if (index === -1) return
  const notification = notifications.value[index]
  if (notification.timer) clearTimeout(notification.timer)
  notifications.value.splice(index, 1)
}

function clearAllNotificationTimers() {
  notifications.value.forEach(item => {
    if (item.timer) {
      clearTimeout(item.timer)
      item.timer = null
    }
  })
}

function addNotification(
  notification: Omit<MessageNotification, 'id' | 'timer' | 'createdAt' | 'hovering' | 'avatarText'>
) {
  const duplicate = notifications.value.find(
    item => String(item.messageId) === String(notification.messageId) && item.phone === notification.phone,
  )
  if (duplicate) return

  clearAllNotificationTimers()

  const id = `${notification.phone}-${notification.messageId}-${Date.now()}`
  const newNotification: MessageNotification = {
    id,
    ...notification,
    avatarText: getInitials(notification.senderName),
    createdAt: Date.now(),
    hovering: false,
    timer: null,
  }

  notifications.value = [newNotification].slice(0, MAX_NOTIFICATIONS)
  startNotificationTimer(id)
}

async function openNotificationConversation(notification: MessageNotification) {
  removeNotification(notification.id)
  const existingConv = localConversations.value.find(c => c.phone === notification.phone) || null
  if (existingConv) await selectConversation(existingConv)
}

function getDisplayName(byWho: any): string {
  if (!byWho) return adminDisplayName.value
  const user = bywholist.find(u => String(u.id) === String(byWho))
  return user?.name ?? adminDisplayName.value
}

function getConversationStatus(conv: Conversation | null | undefined): ContactStatus {
  if (!conv) return 'unknown'
  if (conv.status) return conv.status
  const contact = staticContacts.value.find(c => c.phone === conv.phone)
  if (contact?.status) return contact.status
  return conv.isClient ? 'client' : 'unknown'
}

function getStatusClass(status: ContactStatus): string {
  if (status === 'employee') return 'text-[#F09816]'
  if (status === 'client') return 'text-green-700'
  return 'text-red-500'
}

function formatTimestampToLocal(timestamp: any): string {
  if (!timestamp) return ''
  const date = new Date(timestamp)
  if (isNaN(date.getTime())) return String(timestamp)
  return date.toLocaleString('en-PH', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: 'numeric',
    minute: '2-digit',
    hour12: true,
    timeZone: 'Asia/Manila',
  })
}

function formatDateOnly(timestamp: any): string {
  if (!timestamp) return ''
  const date = new Date(timestamp)
  if (isNaN(date.getTime())) return ''
  return date.toLocaleDateString('en-PH', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    timeZone: 'Asia/Manila',
  })
}

function formatFullDateTime(timestamp: any): string {
  if (!timestamp) return ''
  const date = new Date(timestamp)
  if (isNaN(date.getTime())) return ''
  return date.toLocaleString('en-PH', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: 'numeric',
    minute: '2-digit',
    hour12: true,
    timeZone: 'Asia/Manila',
  })
}

function formatTimeOnly(timestamp: any): string {
  if (!timestamp) return ''
  const date = new Date(timestamp)
  if (isNaN(date.getTime())) return ''
  return date.toLocaleTimeString('en-PH', {
    hour: 'numeric',
    minute: '2-digit',
    hour12: true,
    timeZone: 'Asia/Manila',
  })
}

function isTodayTimestamp(timestamp: any): boolean {
  if (!timestamp) return false
  const date = new Date(timestamp)
  if (isNaN(date.getTime())) return false
  const today = new Date()
  const localDate = new Date(date.toLocaleString('en-US', { timeZone: 'Asia/Manila' }))
  const localToday = new Date(today.toLocaleString('en-US', { timeZone: 'Asia/Manila' }))
  return (
    localDate.getFullYear() === localToday.getFullYear() &&
    localDate.getMonth() === localToday.getMonth() &&
    localDate.getDate() === localToday.getDate()
  )
}

function isSameDay(a: any, b: any): boolean {
  if (!a || !b) return false
  const da = new Date(a)
  const db = new Date(b)
  if (isNaN(da.getTime()) || isNaN(db.getTime())) return false
  const aLocal = new Date(da.toLocaleString('en-US', { timeZone: 'Asia/Manila' }))
  const bLocal = new Date(db.toLocaleString('en-US', { timeZone: 'Asia/Manila' }))
  return (
    aLocal.getFullYear() === bLocal.getFullYear() &&
    aLocal.getMonth() === bLocal.getMonth() &&
    aLocal.getDate() === bLocal.getDate()
  )
}

function getMinutesDiff(a: any, b: any): number {
  if (!a || !b) return Infinity
  const da = new Date(a)
  const db = new Date(b)
  if (isNaN(da.getTime()) || isNaN(db.getTime())) return Infinity
  return Math.abs(da.getTime() - db.getTime()) / (1000 * 60)
}

const activeConversation = computed<Conversation | null>(() => {
  if (!activePhone.value) return null
  return localConversations.value.find(c => c.phone === activePhone.value) || null
})

const messages = computed<Message[]>(() => {
  if (!activePhone.value) return []
  return localMessagesByPhone.value[activePhone.value] || []
})

const timelineMessages = computed(() => {
  return messages.value.map((msg, index, arr) => {
    const currentRaw = msg.rawTime ?? msg.time
    const prev = index > 0 ? arr[index - 1] : null
    const prevRaw = prev ? (prev.rawTime ?? prev.time) : null
    const senderChanged =
      !!prev &&
      (prev.direction !== msg.direction ||
        (prev.senderRole ?? 'admin') !== (msg.senderRole ?? 'admin') ||
        (prev.senderName ?? '') !== (msg.senderName ?? ''))
    const dayChanged = !prev || !isSameDay(prevRaw, currentRaw)
    const gapExceeded = !prev || getMinutesDiff(prevRaw, currentRaw) > 10
    const showDivider = !prev || dayChanged || senderChanged || gapExceeded

    return {
      ...msg,
      showDivider,
      dividerLabel: showDivider ? formatMessengerDivider(currentRaw) : '',
    }
  })
})

const chatSearch = ref('')
const filteredConversations = computed(() => {
  const q = chatSearch.value.trim().toLowerCase()
  if (!q) return localConversations.value
  return localConversations.value.filter(c => {
    const name = (c.name ?? '').toLowerCase()
    const phone = (c.phone ?? '').toLowerCase()
    const last = (c.lastMessage ?? '').toLowerCase()
    return name.includes(q) || phone.includes(q) || last.includes(q)
  })
})

const unreadNotificationCount = computed(() => {
  return localConversations.value.filter(conv => conv.unread).length
})

const showContactsModal = ref(false)
const contactSearch = ref('')
const contacts = computed(() => staticContacts.value)

const filteredContacts = computed(() => {
  if (!contactSearch.value) return contacts.value
  const q = contactSearch.value.toLowerCase()
  return contacts.value.filter(
    c =>
      (c.name && c.name.toLowerCase().includes(q)) ||
      (c.phone && String(c.phone).includes(q)) ||
      c.status.includes(q),
  )
})

const showAddContactModal = ref(false)
const addContactMode = ref<'save-active' | 'manual'>('manual')
const newContact = ref({
  contact_number: '',
  surname: '',
  middle_name: '',
  given_name: '',
  status: 'client' as ContactStatus,
})

function openSaveActiveContact() {
  if (!activeConversation.value) return
  addContactMode.value = 'save-active'
  newContact.value = {
    contact_number: activeConversation.value.phone,
    surname: '',
    middle_name: '',
    given_name: '',
    status: 'client',
  }
  showAddContactModal.value = true
}

function openManualAddContact() {
  addContactMode.value = 'manual'
  newContact.value = {
    contact_number: '',
    surname: '',
    middle_name: '',
    given_name: '',
    status: 'client',
  }
  showAddContactModal.value = true
}

function openAddContact() {
  if (activeConversation.value && !activeConversation.value.name) {
    openSaveActiveContact()
    return
  }

  openManualAddContact()
}

async function saveContact() {
  const given = newContact.value.given_name.trim()
  const surname = newContact.value.surname.trim()
  const fullName = [given, surname].filter(Boolean).join(' ').trim()
  const phone = newContact.value.contact_number.trim()
  const status = newContact.value.status

  if (!phone) return

  const existingContact = staticContacts.value.find(c => c.phone === phone)

  if (existingContact) {
    existingContact.name = fullName || existingContact.name || phone
    existingContact.status = status
  } else {
    staticContacts.value.unshift({
      id: Date.now(),
      name: fullName || phone,
      phone,
      status,
    })
  }

  let conv = localConversations.value.find(c => c.phone === phone)

  if (conv) {
    conv.name = fullName || conv.name || phone
    conv.status = status
    conv.isClient = status === 'client'
  } else {
    conv = {
      id: Date.now(),
      name: fullName || phone,
      phone,
      unread: false,
      unreadCount: 0,
      lastMessage: '',
      time: null,
      isClient: status === 'client',
      status,
    }

    localConversations.value.unshift(conv)
    localMessagesByPhone.value[phone] = []
  }

  showAddContactModal.value = false
  showContactsModal.value = false
  await selectConversation(conv)
}

const loadingConversations = ref(false)
const loadingMessages = ref(false)
const newMessage = ref('')

async function selectConversation(conv: Conversation) {
  activePhone.value = conv.phone
  conv.unread = false
  conv.unreadCount = 0
  notifications.value = notifications.value.filter(n => n.phone !== conv.phone)
  await scrollToBottom()
}

async function sendMessage() {
  if (!newMessage.value.trim() || !activeConversation.value) return

  const phone = activeConversation.value.phone
  const text = newMessage.value.trim()
  const now = new Date().toISOString()
  const tempId = `msg-${Date.now()}`

  if (!localMessagesByPhone.value[phone]) {
    localMessagesByPhone.value[phone] = []
  }

  localMessagesByPhone.value[phone].push({
    id: tempId,
    direction: 'out',
    text,
    time: formatTimestampToLocal(now),
    rawTime: now,
    status: 'sending',
    repliedBy: adminDisplayName.value,
    byWho: 3,
    senderRole: 'admin',
    senderName: adminDisplayName.value,
  })

  const index = localConversations.value.findIndex(c => c.phone === phone)
  if (index !== -1) {
    const conv = localConversations.value[index]
    conv.lastMessage = text
    conv.time = formatTimestampToLocal(now)
    conv.unread = false
    conv.unreadCount = 0
    localConversations.value.splice(index, 1)
    localConversations.value.unshift(conv)
  }

  newMessage.value = ''
  await scrollToBottom()

  setTimeout(() => {
    const msg = localMessagesByPhone.value[phone]?.find(m => String(m.id) === String(tempId))
    if (msg) msg.status = 'delivered'
  }, 800)
}

async function scrollToBottom() {
  await nextTick()
  const container = document.querySelector('.message-container') as HTMLElement | null
  if (container) container.scrollTop = container.scrollHeight
}

function toggleMessageMeta(messageId: number | string) {
  selectedMessageId.value =
    selectedMessageId.value === messageId ? null : messageId
}

function simulateIncomingMessage() {
  const unknownConversation = localConversations.value.find(c => c.phone === '+639181234567')
  if (!unknownConversation) return

  const now = new Date().toISOString()
  const text = 'May update po ba?'

  if (!localMessagesByPhone.value[unknownConversation.phone]) {
    localMessagesByPhone.value[unknownConversation.phone] = []
  }

  localMessagesByPhone.value[unknownConversation.phone].push({
    id: Date.now(),
    direction: 'in',
    text,
    time: formatTimestampToLocal(now),
    rawTime: now,
    status: 'delivered',
    byWho: 1,
    senderRole: getConversationStatus(unknownConversation),
    senderName: unknownConversation.name ?? unknownConversation.phone,
  })

  const index = localConversations.value.findIndex(c => c.phone === unknownConversation.phone)
  if (index !== -1) {
    const conv = localConversations.value[index]
    conv.lastMessage = text
    conv.time = formatTimestampToLocal(now)

    if (activePhone.value !== conv.phone) {
      conv.unread = true
      conv.unreadCount = (conv.unreadCount ?? 0) + 1
    } else {
      conv.unread = false
      conv.unreadCount = 0
    }

    localConversations.value.splice(index, 1)
    localConversations.value.unshift(conv)

    addNotification({
      messageId: Date.now(),
      phone: conv.phone,
      senderName: conv.name ?? conv.phone,
      preview: truncateNotificationText(text),
      timestamp: formatRelativeNotificationTime(now),
    })
  }
}

onMounted(async () => {
  if (localConversations.value.length) {
    activePhone.value = localConversations.value[0].phone
    localConversations.value[0].unread = false
    localConversations.value[0].unreadCount = 0
    await scrollToBottom()
  }

  setTimeout(() => {
    simulateIncomingMessage()
  }, 4000)
})
</script>

<template>
  <AppSidebarLayout :breadcrumbs="breadcrumbs">
    <Head title="Two-way SMS" />

    <Transition name="msg-notification">
      <div
        v-if="notifications.length"
        class="fixed top-5 left-1/2 -translate-x-1/2 z-100 pointer-events-none"
      >
        <div
          class="pointer-events-auto w-85 max-w-[calc(100vw-2rem)] rounded-xl border shadow-xl bg-white border-[#00B572] text-gray-900 overflow-hidden cursor-pointer"
          @click="openNotificationConversation(notifications[0])"
          @mouseenter="pauseNotificationTimer(notifications[0].id)"
          @mouseleave="resumeNotificationTimer(notifications[0].id)"
        >
          <div class="flex items-start gap-4 px-5 py-4">
            <div class="w-10 h-10 rounded-full bg-[#2a2a2a] border border-[#00B572] flex items-center justify-center text-[#00B572] shrink-0">
              <MessageCircle class="w-5 h-5 text-[#00B572]" />
            </div>

            <div class="min-w-0 flex-1">
              <div class="flex items-center justify-between gap-3">
                <div class="font-semibold text-sm text-gray-900 truncate">
                  {{ notifications[0].senderName }}
                </div>
                <div class="text-xs text-gray-400 shrink-0">
                  {{ notifications[0].timestamp }}
                </div>
              </div>

              <div class="mt-1 text-sm text-gray-500 line-clamp-2 wrap-break-words">
                {{ notifications[0].preview }}
              </div>
            </div>

            <button
              class="shrink-0 text-gray-400 hover:text-gray-900 text-xl leading-none"
              @click.stop="removeNotification(notifications[0].id)"
            >
              ✕
            </button>
          </div>
        </div>
      </div>
    </Transition>

    <div class="relative p-4 md:p-6">
      <div class="h-[calc(100vh-8rem)] overflow-hidden">
        <div class="grid grid-cols-12 gap-5 h-full overflow-hidden min-h-0">
          <aside class="col-span-12 md:col-span-4 lg:col-span-4 h-full min-h-0">
            <div class="rounded-2xl bg-white border border-[#00B572] shadow-sm overflow-hidden h-full flex flex-col min-h-0">
              <div class="px-4 py-3 border-b border-[#00B572] flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <div class="relative w-10 h-10 rounded-full bg-white border border-[#00B572] flex items-center justify-center shrink-0">
                    <MessageCircle class="w-5 h-5 text-[#00B572]" />
                    <span
                      v-if="unreadNotificationCount > 0"
                      class="absolute -top-2 -right-1.5 min-w-4 h-4.5 px-1 rounded-full bg-red-500 text-white text-[10px] font-bold flex items-center justify-center leading-none"
                    >
                      {{ unreadNotificationCount > 99 ? '99+' : unreadNotificationCount }}
                    </span>
                  </div>
                  <div class="font-semibold text-sm text-gray-900">Chats</div>
                </div>

                <button
                  @click="showContactsModal = true"
                  class="w-9 h-9 rounded-lg border border-[#00B572] bg-white text-[#00B572] transition hover:bg-[#0E3D1A] hover:text-white flex items-center justify-center"
                  title="View contacts"
                >
                  <Users class="w-4 h-4" />
                </button>
              </div>

              <div class="px-4 py-3 border-b border-gray-200">
                <div class="relative">
                  <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">⌕</span>
                  <input
                    v-model="chatSearch"
                    placeholder="Search"
                    class="w-full pl-9 pr-3 py-2 rounded-lg border border-[#00B572] bg-white text-sm focus:outline-none focus:border-[#00B572] text-gray-900 placeholder:text-gray-400 caret-gray-900"
                  />
                </div>
              </div>

              <div class="flex-1 min-h-0 overflow-y-auto hide-scrollbar divide-y divide-gray-200">
                <div
                  v-if="loadingConversations && !localConversations.length"
                  class="p-6 text-center text-sm text-gray-400"
                >
                  Loading chats...
                </div>

                <button
                  v-else
                  v-for="conv in filteredConversations"
                  :key="conv.phone || conv.id"
                  @click="selectConversation(conv)"
                  class="w-full text-left px-4 py-3 hover:bg-[#e5e5e5] transition-colors duration-200"
                  :class="activeConversation?.phone === conv.phone ? 'bg-gray-50' : ''"
                >
                  <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                      <div class="flex items-center gap-2">
                        <div
                          class="text-sm text-gray-900 truncate"
                          :class="conv.unread ? 'font-bold' : 'font-normal'"
                        >
                          {{ conv.name && conv.name !== 'unknown' ? conv.name : conv.phone }}
                        </div>

                        <span
                          v-if="conv.unreadCount > 0"
                          class="inline-flex min-w-4.5 h-4.5 px-1 rounded-full bg-red-500 text-white text-[10px] font-bold items-center justify-center leading-none"
                        >
                          {{ conv.unreadCount > 99 ? '99+' : conv.unreadCount }}
                        </span>

                        <span
                          class="text-[11px] font-semibold"
                          :class="getStatusClass(getConversationStatus(conv))"
                        >
                          {{ getConversationStatus(conv) }}
                        </span>
                      </div>

                      <div
                        class="text-xs truncate mt-0.5"
                        :class="conv.unread ? 'font-semibold text-gray-900' : 'text-gray-400'"
                      >
                        {{ conv.lastMessage || 'No messages yet' }}
                      </div>
                    </div>

                    <div
                      class="text-[11px] whitespace-nowrap"
                      :class="conv.unread ? 'font-semibold text-gray-700' : 'text-gray-400'"
                    >
                      {{ conv.time }}
                    </div>
                  </div>
                </button>

                <div
                  v-if="!loadingConversations && !filteredConversations.length"
                  class="p-6 text-center text-sm text-gray-500"
                >
                  No chats found.
                </div>
              </div>
            </div>
          </aside>

          <section class="col-span-12 md:col-span-8 lg:col-span-8 h-full min-h-0">
            <div class="rounded-2xl bg-white border border-[#00B572] shadow-sm overflow-hidden h-full flex flex-col min-h-0">
              <div class="px-5 py-4 border-b border-[#00B572] flex items-center justify-between gap-3">
                <div class="flex items-center gap-3 min-w-0">
                  <div class="font-semibold text-sm text-gray-900 truncate">
                    {{ activeConversation?.name ?? activeConversation?.phone ?? '' }}
                  </div>

                  <span
                    v-if="activeConversation"
                    class="text-[11px] font-semibold"
                    :class="getStatusClass(getConversationStatus(activeConversation))"
                  >
                    {{ getConversationStatus(activeConversation) }}
                  </span>
                </div>

                <button
                  v-if="activeConversation && getConversationStatus(activeConversation) === 'unknown'"
                  @click="openSaveActiveContact"
                  class="inline-flex items-center justify-center gap-2 rounded-lg border border-[#0E3D1A] bg-white px-4 py-2 text-xs font-semibold text-[#0E3D1A] transition hover:bg-[#0E3D1A] hover:text-white"
                >
                  <UserPlus class="h-4 w-4" />
                  Save to Contacts
                </button>
              </div>

              <div class="flex-1 min-h-0 overflow-y-auto px-6 py-5 bg-white message-container hide-scrollbar">
                <div
                  v-if="!activeConversation"
                  class="h-full flex items-center justify-center text-sm text-gray-400"
                >
                  Select a chat to view messages
                </div>

                <div
                  v-else-if="loadingMessages"
                  class="h-full flex items-center justify-center text-sm text-gray-400"
                >
                  Loading messages...
                </div>

                <div v-else class="space-y-6">
                  <div v-for="msg in timelineMessages" :key="msg.id">
                    <div v-if="msg.showDivider" class="text-center text-xs text-gray-400 my-4">
                      {{ msg.dividerLabel }}
                    </div>

                    <div class="flex" :class="msg.direction === 'out' ? 'justify-end' : 'justify-start'">
                      <div
                        class="flex flex-col max-w-[70%] w-fit"
                        :class="msg.direction === 'out' ? 'items-end' : 'items-start'"
                        @click="toggleMessageMeta(msg.id)"
                      >
                        <div
                          class="inline-block w-fit max-w-full px-4 py-2 rounded-2xl text-sm border cursor-pointer whitespace-pre-wrap wrap-break-words"
                          :class="msg.direction === 'out'
                            ? 'bg-gray-100 border-[#00B572] text-gray-900'
                            : 'bg-white border-[#00B572] text-gray-900'"
                        >
                          {{ msg.text }}
                        </div>

                        <div
                          v-if="selectedMessageId === msg.id"
                          class="mt-1 text-[11px] text-gray-400 text-center"
                        >
                          {{ formatFullDateTime(msg.rawTime ?? msg.time) }}
                        </div>

                        <div
                          v-if="msg.direction === 'in'"
                          class="mt-1 w-fit text-[11px] font-normal text-gray-500"
                        >
                          seen by {{ getDisplayName(msg.byWho) }}
                        </div>

                        <div
                          v-if="msg.direction === 'out'"
                          class="mt-1 text-[11px] font-normal text-right"
                          :class="msg.status === 'failed' ? 'text-red-400' : 'text-gray-500'"
                        >
                          {{
                            msg.status === 'queued' ? 'queued...'
                              : msg.status === 'sending' ? 'sending...'
                              : msg.status === 'resending' ? 'resending...'
                              : msg.status === 'failed' ? 'failed'
                              : msg.status === 'delivered' ? 'delivered'
                              : ''
                          }}
                          by {{ getDisplayName(msg.byWho) }}
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="px-5 py-4 border-t border-[#00B572] bg-inherit">
                <div class="flex items-center gap-3">
                  <input
                    v-model="newMessage"
                    placeholder="Message"
                    class="flex-1 rounded-full border border-[#00B572] bg-white px-4 py-2 text-sm focus:outline-none focus:border-[#00B572] text-gray-900 placeholder:text-gray-400 caret-gray-900"
                    @keyup.enter="sendMessage"
                  />

                  <button
                    @click="sendMessage"
                    class="w-10 h-10 rounded-lg border border-[#00B572] bg-white text-[#00B572] hover:bg-[#0E3D1A] hover:text-white flex items-center justify-center transition-colors"
                    title="Send"
                  >
                    <Send class="w-4 h-4" />
                  </button>
                </div>
              </div>
            </div>
          </section>
        </div>
      </div>
    </div>

    <div v-if="showContactsModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4">
      <div class="w-full max-w-2xl rounded-2xl bg-white shadow-2xl border border-gray-200 overflow-hidden text-gray-900">
        <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
          <h3 class="text-sm font-semibold">Contacts</h3>

          <div class="flex items-center gap-2">
            <button
              @click="openManualAddContact"
              class="inline-flex items-center justify-center gap-2 rounded-lg border border-[#0E3D1A] bg-white px-3 py-2 text-xs font-semibold text-[#0E3D1A] transition hover:bg-[#0E3D1A] hover:text-white"
            >
              <UserPlus class="h-4 w-4" />
              Add Contact
            </button>

            <button
              @click="showContactsModal = false"
              class="text-gray-500 hover:text-gray-900"
            >
              ✕
            </button>
          </div>
        </div>

        <div class="px-5 py-4 border-b border-gray-200">
          <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">⌕</span>
            <input
              v-model="contactSearch"
              placeholder="Search"
              class="w-full pl-9 pr-3 py-2 rounded-lg border border-[#00B572] bg-white text-sm focus:outline-none focus:border-[#00B572] text-gray-900 placeholder:text-gray-400 caret-gray-900"
            />
          </div>
        </div>

        <div class="max-h-[60vh] overflow-y-auto hide-scrollbar divide-y divide-gray-200">
          <button
            v-for="contact in filteredContacts"
            :key="contact.id"
            type="button"
            @click="saveContact"
            class="w-full px-5 py-4 text-left hover:bg-gray-50 transition"
          >
            <div class="text-sm font-medium text-gray-900">{{ contact.name }}</div>
            <div class="mt-1 flex items-center gap-2 text-xs text-gray-500">
              <span>{{ contact.phone }}</span>
              <span class="font-semibold" :class="getStatusClass(contact.status)">
                {{ contact.status }}
              </span>
            </div>
          </button>

          <div
            v-if="!filteredContacts.length"
            class="px-5 py-8 text-center text-sm text-gray-500"
          >
            No contacts found.
          </div>
        </div>
      </div>
    </div>

    <div v-if="showAddContactModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
      <div class="w-full max-w-lg rounded-3xl bg-white text-gray-900 shadow-2xl overflow-hidden border border-gray-200">
        <div class="px-6 py-5 border-b border-gray-200 flex items-center justify-between">
          <h3 class="text-base font-semibold">
            {{ addContactMode === 'save-active' ? 'Save to Contacts' : 'Add Contact' }}
          </h3>
          <button
            @click="showAddContactModal = false"
            class="text-gray-500 hover:text-gray-900 text-lg leading-none"
          >
            ✕
          </button>
        </div>

        <div class="px-6 py-5 space-y-4">
          <div>
            <div class="text-xs text-gray-500 mb-1">Contact Number</div>
            <input
              v-model="newContact.contact_number"
              class="w-full rounded-lg border border-gray-300 bg-white text-gray-900 px-3 py-2 text-sm outline-none focus:border-[#00B572]"
            />
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <div class="text-xs text-gray-500 mb-1">Surname</div>
              <input
                v-model="newContact.surname"
                class="w-full rounded-lg border border-gray-300 bg-white text-gray-900 px-3 py-2 text-sm outline-none focus:border-[#00B572]"
              />
            </div>

            <div>
              <div class="text-xs text-gray-500 mb-1">Middle Name</div>
              <input
                v-model="newContact.middle_name"
                class="w-full rounded-lg border border-gray-300 bg-white text-gray-900 px-3 py-2 text-sm outline-none focus:border-[#00B572]"
              />
            </div>
          </div>

          <div>
            <div class="text-xs text-gray-500 mb-1">Given Name</div>
            <input
              v-model="newContact.given_name"
              class="w-full rounded-lg border border-gray-300 bg-white text-gray-900 px-3 py-2 text-sm outline-none focus:border-[#00B572]"
            />
          </div>

          <div>
            <div class="text-xs text-gray-500 mb-1">Status</div>
            <select
              v-model="newContact.status"
              class="w-full rounded-lg border border-gray-300 bg-white text-gray-900 px-3 py-2 text-sm outline-none focus:border-[#00B572]"
            >
              <option value="client">Client</option>
              <option value="employee">Employee</option>
              <option value="unknown">Unknown</option>
            </select>
          </div>

          <div class="flex items-center justify-between pt-2">
            <button
              @click="showAddContactModal = false"
              class="px-6 py-2 rounded-lg border border-gray-300 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
            >
              Cancel
            </button>

            <button
              @click="saveContact"
              class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#0E3D1A] px-6 py-2 text-sm font-semibold text-white transition hover:opacity-90"
            >
              <UserPlus class="h-4 w-4" />
              {{ addContactMode === 'save-active' ? 'Save to Contacts' : 'Add Contact' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </AppSidebarLayout>
</template>

<style scoped>
.msg-notification-enter-active,
.msg-notification-leave-active {
  transition: all 0.25s ease;
}
.msg-notification-enter-from {
  opacity: 0;
  transform: translateY(-14px);
}
.msg-notification-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
.msg-notification-move {
  transition: transform 0.25s ease;
}
:deep(html),
:deep(body) {
  height: 100%;
  overflow: hidden;
}
.hide-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
.hide-scrollbar::-webkit-scrollbar {
  width: 0;
  height: 0;
  display: none;
}
</style>