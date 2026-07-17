<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { NavItem } from '@/types';

defineProps<{
    items: NavItem[];
}>();
</script>

<template>
    <nav class="mt-auto pb-4 border-none border-[#145F2B]">
        <ul class="flex flex-col gap-1 px-3">
            <li
                v-for="item in items"
                :key="item.title"
            >
                <Link
                    :href="item.href"
                    class="
                        flex items-center gap-3 w-full 
                        px-3 py-2 rounded-md 
                        text-white
                        bg-[#0E3D1A]
                        hover:bg-[#145F2B]
                        transition-colors duration-150
                    "
                >
                    <component
                        :is="item.icon"
                        class="h-4 w-4"
                    />
                    <span class="text-sm font-medium">{{ item.title }}</span>
                </Link>
            </li>
        </ul>
    </nav>
</template>
