<script setup lang="ts">
import NavMain from '@/components/NavMain.vue'
import NavUser from '@/components/NavUser.vue'
import { SidebarTrigger } from '@/components/ui/sidebar'

import {
    SidebarProvider,
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar'

import { Link } from '@inertiajs/vue3'
import type { NavItem, BreadcrumbItem } from '@/types'

import {
    CheckSquare,
    LayoutDashboard,
    MessageCircle,
    Users,
    FileText,
    ClipboardList,
} from 'lucide-vue-next'

defineProps<{
    breadcrumbs?: BreadcrumbItem[]
}>()

const mainNavItems: NavItem[] = [
    {
        title: 'To do',
        href: '#',
        icon: CheckSquare,
    },
    {
        title: 'User Dashboard',
        href: '#',
        icon: LayoutDashboard,
    },
    {
        title: 'Two-way SMS',
        href: '#',
        icon: MessageCircle,
    },
    {
        title: 'User Management',
        href: '#',
        icon: Users,
    },
    {
        title: 'Reports',
        href: '#',
        icon: FileText,
    },
    {
        title: 'Task Management',
        href: '#',
        icon: ClipboardList,
    },
]
</script>

<template>
    <SidebarProvider>
        <div class="flex min-h-screen w-full bg-muted/40">
            <Sidebar
                variant="inset"
                class="border-none bg-[#0E3D1A] text-white"
            >
                <SidebarHeader>
                    <SidebarMenu>
                        <SidebarMenuItem>
                            <SidebarMenuButton size="lg" as-child>
                                <Link href="#" class="flex items-center gap-4">
                                    <img
                                        src="/images/RSL CAR logo DM.png"
                                        class="h-12 w-12"
                                        alt="DA RSL CAR Logo"
                                    />
                                    <span class="font-semibold">DA RSL CAR</span>
                                </Link>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                    </SidebarMenu>
                </SidebarHeader>

                <SidebarContent class="pt-6">
                    <div class="flex flex-col gap-4 px-3">

                        <Link href="#" class="flex items-center gap-3 text-white px-3 py-2 rounded-md hover:bg-[#145F2B]">
                            <CheckSquare class="h-5 w-5" />
                            <span>To do</span>
                        </Link>

                        <Link href="#" class="flex items-center gap-3 text-white px-3 py-2 rounded-md hover:bg-[#145F2B]">
                            <LayoutDashboard class="h-5 w-5" />
                            <span>User Dashboard</span>
                        </Link>

                        <Link href="#" class="flex items-center gap-3 text-white px-3 py-2 rounded-md hover:bg-[#145F2B]">
                            <MessageCircle class="h-5 w-5" />
                            <span>Two-way SMS</span>
                        </Link>

                        <Link href="#" class="flex items-center gap-3 text-white px-3 py-2 rounded-md hover:bg-[#145F2B]">
                            <Users class="h-5 w-5" />
                            <span>User Management</span>
                        </Link>

                        <Link href="#" class="flex items-center gap-3 text-white px-3 py-2 rounded-md hover:bg-[#145F2B]">
                            <FileText class="h-5 w-5" />
                            <span>Reports</span>
                        </Link>

                        <Link href="#" class="flex items-center gap-3 text-white px-3 py-2 rounded-md hover:bg-[#145F2B]">
                            <ClipboardList class="h-5 w-5" />
                            <span>Task Management</span>
                        </Link>

                    </div>
                </SidebarContent>

                <SidebarFooter>
                    <NavUser />
                </SidebarFooter>
            </Sidebar>

            <main class="flex-1 p-6">
                <div
                    class="mx-auto max-w-7xl rounded-xl bg-background shadow-lg shadow-black/5"
                >
                    <div class="flex items-center gap-3 border-b px-4 py-3">
                        <SidebarTrigger class="rounded-md p-2 transition hover:bg-muted" />
                        <h1 class="text-sm font-medium text-foreground">
                            {{ breadcrumbs?.[0]?.title }}
                        </h1>
                    </div>

                    <slot />
                </div>
            </main>
        </div>
    </SidebarProvider>
</template>