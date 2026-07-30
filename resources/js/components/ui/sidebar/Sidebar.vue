<template>
  <aside class="w-[255px] min-h-screen bg-[#004d14] text-white flex flex-col px-5 py-7">
    <!-- TOP -->
    <div>
      <!-- Logo + Title -->
      <div class="flex items-center gap-4 mb-10">
        <img
          src="/images/RSL CAR logo DM.png"
          alt="DA RSL CAR Logo"
          class="w-14 h-14 object-contain"
        />

        <h1 class="text-[18px] font-semibold tracking-wide">
          DA RSL CAR
        </h1>
      </div>

      <!-- MENU -->
      <nav class="flex flex-col gap-7 mt-6">
        <a
          v-for="item in menuItems"
          :key="item.label"
          :href="item.href"
          class="flex items-center gap-4 text-[16px] text-white hover:opacity-90 transition"
        >
          <component :is="item.icon" class="w-5 h-5 stroke-[1.8]" />
          <span>{{ item.label }}</span>
        </a>
      </nav>
    </div>

    <!-- BOTTOM ACCOUNT / SETTINGS -->
    <div class="mt-auto pt-8">
      <div class="bg-black rounded-xl px-4 py-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-md bg-black flex items-center justify-center text-white font-semibold">
            SA
          </div>

          <span class="text-[15px] font-semibold text-white">super admin</span>
        </div>

        <ChevronsUpDown class="w-5 h-5 text-white" />
      </div>
    </div>
  </aside>
</template>

<script setup lang="ts">
import {
  ClipboardCheck,
  LayoutDashboard,
  MessageCircleMore,
  Users,
  FileText,
  ListTodo,
  ChevronsUpDown, 
} from 'lucide-vue-next'

const menuItems = [
  { label: 'To do', href: '/todo', icon: ClipboardCheck },
  { label: 'User Dashboard', href: '#', icon: LayoutDashboard },
  { label: 'Two-way SMS', href: '/sms', icon: MessageCircleMore },
  { label: 'User Management', href: '/user-management', icon: Users },
  { label: 'Reports', href: '#', icon: FileText },
  { label: 'Task Management', href: '#', icon: ListTodo },
]
</script>
