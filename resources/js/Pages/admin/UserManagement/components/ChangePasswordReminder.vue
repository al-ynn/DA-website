<script setup lang="ts">
import { usePage, router } from '@inertiajs/vue3'
import { ref, onMounted, computed } from 'vue'

const page = usePage<{
    mustChangePassword: boolean
    passwordReminderDismissed: boolean
    passwordReminderHidden: boolean
}>()

const mustChangePassword = computed(
    () => page.props.mustChangePassword
)

const reminderHidden = computed(
    () => page.props.passwordReminderHidden
)

const showModal = ref(false)

onMounted(() => {
    if (
        mustChangePassword.value &&
        !reminderHidden.value
    ) {
        showModal.value = true
    }
})

function remindLater() {
    router.patch(
        '/password-reminder/dismiss',
        {},
        {
            preserveScroll: true,
            preserveState: true,

            onSuccess: () => {
                showModal.value = false

                window.dispatchEvent(
                    new CustomEvent('password-reminder-dismissed')
                )
            },
        }
    )
}

function goToChangePassword() {
    router.visit('/profile')
}
</script>

<template>
    <Transition
        enter-active-class="transition duration-200"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition duration-150"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="showModal"
            class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 px-4"
        >
            <div
                class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-full bg-red-100 text-red-600 text-xl"
                    >
                        ⚠️
                    </div>

                    <div>
                        <h2 class="text-xl font-bold text-zinc-900">
                            Default Password Detected
                        </h2>

                        <p class="text-sm text-zinc-500">
                            Your account is still using the default password.
                        </p>
                    </div>
                </div>

                <div class="mt-5 space-y-3 text-sm text-zinc-700">
                    <p>
                        For security reasons, you should change your password as
                        soon as possible.
                    </p>

                    <p>
                        You may continue using the system, but this reminder
                        will appear every time you log in until you update your
                        password.
                    </p>
                </div>

                <div class="mt-8 flex justify-end gap-3">
                    <button
                        type="button"
                        @click="remindLater"
                        class="rounded-lg border border-zinc-300 px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-100"
                    >
                        Remind Me Later
                    </button>

                    <button
                        type="button"
                        @click="goToChangePassword"
                        class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700"
                    >
                        Change Password
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>
