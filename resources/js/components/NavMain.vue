<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import {
    CheckSquare,
    LayoutDashboard,
    MessageCircle,
    Users,
    FileText,
    ClipboardList,
} from 'lucide-vue-next'
</script>

<template>
    <div class="px-2 py-2">
        <nav class="flex flex-col gap-4">
            <Link
                href="#"
                class="flex items-center gap-3 rounded-md px-3 py-2 text-white hover:bg-[#145F2B]"
            >
                <CheckSquare class="h-5 w-5 shrink-0" />
                <span class="text-[16px]">To do</span>
            </Link>

            <Link
                href="#"
                class="flex items-center gap-3 rounded-md px-3 py-2 text-white hover:bg-[#145F2B]"
            >
                <LayoutDashboard class="h-5 w-5 shrink-0" />
                <span class="text-[16px]">User Dashboard</span>
            </Link>

            <Link
                href="#"
                class="flex items-center gap-3 rounded-md px-3 py-2 text-white hover:bg-[#145F2B]"
            >
                <MessageCircle class="h-5 w-5 shrink-0" />
                <span class="text-[16px]">Two-way SMS</span>
            </Link>

            <Link
                href="#"
                class="flex items-center gap-3 rounded-md px-3 py-2 text-white hover:bg-[#145F2B]"
            >
                <Users class="h-5 w-5 shrink-0" />
                <span class="text-[16px]">User Management</span>
            </Link>

            <Link
                href="#"
                class="flex items-center gap-3 rounded-md px-3 py-2 text-white hover:bg-[#145F2B]"
            >
                <FileText class="h-5 w-5 shrink-0" />
                <span class="text-[16px]">Reports</span>
            </Link>

            <Link
                href="#"
                class="flex items-center gap-3 rounded-md px-3 py-2 text-white hover:bg-[#145F2B]"
            >
                <ClipboardList class="h-5 w-5 shrink-0" />
                <span class="text-[16px]">Task Management</span>
            </Link>
        </nav>
    </div>
</template>
