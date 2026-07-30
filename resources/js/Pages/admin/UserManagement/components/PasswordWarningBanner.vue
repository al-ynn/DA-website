<script setup lang="ts">
import { usePage, router } from '@inertiajs/vue3'
import { ref, onMounted, onBeforeUnmount } from 'vue'

const page = usePage<{
    mustChangePassword: boolean
    passwordReminderDismissed: boolean
    passwordReminderHidden: boolean
}>()

const visible = ref(false)

function goToChangePassword() {
    router.visit('/profile')
}

function showBanner() {
    visible.value = true
}

function closeBanner() {
    visible.value = false
}

onMounted(() => {
    if (
        page.props.mustChangePassword &&
        page.props.passwordReminderHidden
    ) {
        visible.value = true
    }

    window.addEventListener(
        'password-reminder-dismissed',
        showBanner
    )
})

onBeforeUnmount(() => {
    window.removeEventListener(
        'password-reminder-dismissed',
        showBanner
    )
})
</script>

<template>
    <Transition
        enter-active-class="transition-all duration-200"
        enter-from-class="-translate-y-2 opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition-all duration-150"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="visible"
            class="mx-5 mt-4 flex items-center justify-between rounded-lg border border-red-200 bg-red-50/70 px-4 py-2.5 text-sm text-red-700 shadow-sm"
        >
            <div class="flex items-center gap-2">
                <span class="text-base">⚠️</span>

                <span>
                    You're still using the
                    <span class="font-semibold">default password.</span>
                    Please change it for better account security.
                </span>
            </div>

            <div class="flex items-center gap-2">
                <button
                    type="button"
                    @click="goToChangePassword"
                    class="rounded-md bg-red-100 px-3 py-1 text-xs font-medium text-red-700 transition hover:bg-red-200"
                >
                    Change Password
                </button>

                <button
                    type="button"
                    @click="closeBanner"
                    class="rounded p-1 text-red-500 transition hover:bg-red-100"
                >
                    ✕
                </button>
            </div>
        </div>
    </Transition>
</template>
