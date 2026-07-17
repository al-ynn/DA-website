<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { ref } from 'vue'
import AppSidebarHeader from '@/layouts/app/AppSidebarHeader.vue'

const page = usePage()
const showingNavigationDropdown = ref(false)
</script>

<template>
  <div class="min-h-screen flex bg-gray-100">
    <!-- LEFT SIDEBAR -->
    <aside class="w-[220px] bg-[#0E3D1A] text-white flex flex-col py-5 px-3">
      <!-- Logo + Title -->
      <div class="flex items-center gap-2 px-1 mb-6">
        
        <img
          src="/images/RSL CAR logo DM.png"
          class="w-12 h-12 hidden"
          alt="DA Logo Dark"
        />

        <div class="leading-tight">
          <h1 class="text-[13px] font-semibold tracking-wide">DA RSL CAR</h1>
        </div>
      </div>

      <!-- Platform label -->
      <p class="uppercase text-[10px] tracking-wider mb-3 opacity-80 px-1">
        Platform
      </p>

      <!-- Navigation Links -->
      <nav class="flex flex-col gap-2">
        <Link
          href="/dashboard"
          class="px-2 py-2 rounded-md text-[13px] hover:bg-[#0b3115] transition"
        >
          Dashboard
        </Link>

        <Link
          href="/admin/todo"
          class="px-2 py-2 rounded-md text-[13px] hover:bg-[#0b3115] transition"
        >
          To-do
        </Link>

        <Link
          v-if="['admin', 'superadmin'].includes(page.props.auth.user.role)"
          href="/sms"
          class="px-2 py-2 rounded-md text-[13px] hover:bg-[#0b3115] transition"
        >
          Two-Way SMS
        </Link>

        <Link
          v-if="page.props.auth.user.role === 'superadmin'"
          href="/users"
          class="px-2 py-2 rounded-md text-[13px] hover:bg-[#0b3115] transition"
        >
          Users
        </Link>
      </nav>

      <!-- Bottom User Menu -->
      <div class="mt-auto pt-4 border-t border-white/10">
        <div class="px-2 py-2 text-[12px] font-medium truncate">
          {{ page.props.auth.user.name }}
        </div>

        <Link
          href="/profile"
          class="px-2 py-2 text-[12px] hover:bg-[#0b3115] block rounded-md"
        >
          Settings
        </Link>

        <Link
          method="post"
          as="button"
          href="/logout"
          class="px-2 py-2 text-[12px] hover:bg-[#0b3115] block rounded-md text-left w-full"
        >
          Log out
        </Link>
      </div>
    </aside>

    <!-- MAIN CONTENT -->
    <div class="flex-1 min-w-0 flex flex-col">
      <AppSidebarHeader />
      <main class="flex-1 p-4 overflow-auto">
        <slot />
      </main>
    </div>
  </div>
</template>