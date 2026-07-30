<script setup lang="ts">
import { computed } from 'vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/Components/ui/avatar';
import { useInitials } from '@/composables/useInitials';
import type { User } from '@/types';

type Props = {
    user: User;
    showEmail?: boolean;
};

const props = withDefaults(defineProps<Props>(), {
    showEmail: false,
});

const { getInitials } = useInitials();

const showAvatar = computed(
    () => props.user.avatar && props.user.avatar !== '',
);
</script>

<template>
    <Avatar class="h-7 w-7 shrink-0 overflow-hidden rounded-md">
        <AvatarImage v-if="showAvatar" :src="user.avatar!" :alt="user.name" />
        <AvatarFallback
            class="rounded-md bg-[#1b1b1b] text-[11px] font-semibold text-white"
        >
            {{ getInitials(user.name) }}
        </AvatarFallback>
    </Avatar>

    <div class="grid min-w-0 flex-1 text-left leading-tight">
        <span class="truncate text-[13px] font-medium text-white">
            {{ user.name }}
        </span>

        <span
            v-if="showEmail"
            class="truncate text-[11px] text-white/60"
        >
            {{ user.email }}
        </span>
    </div>
</template>
