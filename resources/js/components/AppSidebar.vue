<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3'
import { ref } from 'vue'
import {
  ClipboardCheck,
  LayoutDashboard,
  MessageCircleMore,
  Users,
  FileText,
  ListTodo,
  ChevronsUpDown,
  Settings,
  LogOut,
} from 'lucide-vue-next'

const props = defineProps<{
  collapsed: boolean
}>()

const page = usePage()
const userMenuOpen = ref(false)

const menuItems = [
  { label: 'To do', href: '/todo', icon: ClipboardCheck },
  { label: 'User Dashboard', href: '/user-dashboard', icon: LayoutDashboard },
  { label: 'Two-way SMS', href: '/sms', icon: MessageCircleMore },
  { label: 'User Management', href: '/user-management', icon: Users },
  { label: 'Reports', href: '/reports', icon: FileText },
  { label: 'Task Management', href: '/task-management', icon: ListTodo },
]

const toggleUserMenu = () => {
  userMenuOpen.value = !userMenuOpen.value
}
</script>

<template>
  <aside
    :class="[
      'bg-[#0E3D1A] text-white flex flex-col py-3 px-2 transition-all duration-200 shrink-0 min-h-screen',
      props.collapsed
        ? 'w-[60px] sm:w-[72px]'
        : 'w-[170px] sm:w-[230px]',
    ]"
  >
    <div class="flex items-center px-2 mb-5">
      <div
        :class="[
          'flex items-center min-w-0',
          props.collapsed ? 'justify-center w-full' : 'gap-2',
        ]"
      >
        <img
          src="/images/RSL CAR logo DM.png"
          :class="[
            'shrink-0 object-contain',
            props.collapsed ? 'h-9 w-9 sm:h-14 sm:w-14' : 'h-10 w-10 sm:h-14 sm:w-14',
          ]"
          alt="DA Logo"
        />

        <div v-if="!props.collapsed" class="leading-tight min-w-0">
          <h3 class="text-[10px] font-regular tracking-wide truncate">
            Department of Agriculture
          </h3>
          <h1 class="text-[13px] font-semibold tracking-wide truncate">
            RSL CAR
          </h1>
        </div>
      </div>
    </div>

    <nav class="flex flex-col gap-2">
      <Link
        v-for="item in menuItems"
        :key="item.label"
        :href="item.href"
        :class="[
          'rounded-md transition hover:bg-[#00572c] text-white',
          props.collapsed
            ? 'h-8 flex items-center justify-center'
            : 'h-8 px-2 flex items-center gap-2.5',
        ]"
      >
        <component :is="item.icon" class="h-4 w-4 shrink-0 sm:h-4 sm:w-4" />

        <span
          v-if="!props.collapsed"
          class="truncate text-[11px] font-medium sm:text-[13px]"
        >
          {{ item.label }}
        </span>
      </Link>
    </nav>

    <div class="mt-auto pt-3 relative">
      <button
        type="button"
        @click="toggleUserMenu"
        :class="[
          'w-full rounded-lg bg-[#111111] hover:bg-[#1a1a1a] transition text-white',
          props.collapsed
            ? 'h-14 flex items-center justify-center'
            : 'h-14 px-4 flex items-center gap-4',
        ]"
      >
        <div class="h-6 w-6 rounded-md bg-black flex items-center justify-center text-[10px] font-semibold shrink-0">
          {{ page.props.auth?.user?.name?.charAt(0)?.toUpperCase() || 'U' }}
        </div>

        <template v-if="!props.collapsed">
          <span class="min-w-0 flex-1 truncate text-left text-[12px] font-medium">
            {{ page.props.auth?.user?.name || 'Test User' }}
          </span>
          <ChevronsUpDown class="h-4 w-4 shrink-0" />
        </template>
      </button>

      <div
        v-if="userMenuOpen"
        :class="[
          'absolute bottom-[calc(100%+6px)] z-50 rounded-lg bg-[#111111] shadow-lg overflow-hidden',
          props.collapsed ? 'left-0 w-[180px]' : 'left-0 right-0',
        ]"
      >
        <Link
          href="/profile"
          class="flex items-center gap-2 px-3 py-2 text-[12px] hover:bg-[#1a1a1a]"
        >
          <Settings class="h-4 w-4" />
          <span>Settings</span>
        </Link>

        <Link
          method="post"
          as="button"
          href="/logout"
          class="w-full flex items-center gap-2 px-3 py-2 text-[12px] text-left hover:bg-[#1a1a1a]"
        >
          <LogOut class="h-4 w-4" />
          <span>Log out</span>
        </Link>
      </div>
    </div>
  </aside>
</template>