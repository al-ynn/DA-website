<script setup lang="ts">
import { Head, useForm } from "@inertiajs/vue3";
import PublicHeader from '@/components/PublicHeader.vue'

const form = useForm({
    email: "",
    password: "",
    remember: false,
});

function submit() {
    form.post("/login", {
        onFinish: () => form.reset("password"),
    });
}
</script>

<template>
    <Head title="Log in" />

    <div class="min-h-screen bg-[#f6f7f8] text-sm text-zinc-900 dark:bg-[#0a0a0a] dark:text-zinc-100">

        <PublicHeader />

        <div class="flex min-h-[calc(100vh-72px)] flex-col items-center justify-center gap-10 px-4 py-8 lg:flex-row lg:gap-20">

            <div class="flex flex-col items-center">

                <!-- Light Mode Logo -->
                <img
                    src="/images/RSL CAR logo.png"
                    class="w-56 md:w-64 lg:w-87.5 dark:hidden"
                    alt="RSL CAR Logo"
                />

                <!-- Dark Mode Logo -->
                <img
                    src="/images/RSL CAR logo DM.png"
                    class="hidden w-56 md:w-64 lg:w-87.5 dark:block"
                    alt="RSL CAR Logo Dark"
                />

            </div>

            <div
                class="w-full max-w-md rounded-xl border border-zinc-200 bg-white p-6 shadow-xl
                       md:p-10
                       dark:border-zinc-800 dark:bg-[#111111]"
            >

                <h2 class="mb-1 text-center text-lg font-semibold dark:text-white">
                    Log in to your account
                </h2>

                <p class="mb-6 text-center text-sm text-zinc-600 dark:text-zinc-400">
                    Enter your email and password below to log in
                </p>

                <form @submit.prevent="submit" class="space-y-4">

                    <div>
                        <label class="mb-1 block text-sm font-medium dark:text-zinc-200">
                            Email address
                        </label>

                        <input
                            v-model="form.email"
                            type="email"
                            class="w-full rounded-md border border-zinc-300 bg-white px-3 py-2 outline-none
                                   focus:border-[#0E3D1A]
                                   dark:border-zinc-700 dark:bg-[#0b0b0b]
                                   dark:text-white dark:placeholder:text-zinc-500"
                        />

                        <p
                            v-if="form.errors.email"
                            class="mt-1 text-xs text-red-500"
                        >
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium dark:text-zinc-200">
                            Password
                        </label>

                        <input
                            v-model="form.password"
                            type="password"
                            class="w-full rounded-md border border-zinc-300 bg-white px-3 py-2 outline-none
                                   focus:border-[#0E3D1A]
                                   dark:border-zinc-700 dark:bg-[#0b0b0b]
                                   dark:text-white dark:placeholder:text-zinc-500"
                        />

                        <p
                            v-if="form.errors.password"
                            class="mt-1 text-xs text-red-500"
                        >
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <label class="flex items-center gap-2 text-sm dark:text-zinc-300">
                        <input
                            v-model="form.remember"
                            type="checkbox"
                            class="accent-[#0E3D1A]"
                        />
                        Remember me
                    </label>

                    <button
                        type="submit"
                        class="w-full rounded-md bg-[#0E3D1A] py-3 font-semibold text-white transition
                               hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-60"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Logging in...' : 'Log in' }}
                    </button>

                    <p
                        v-if="form.errors.email === 'These credentials do not match our records.'"
                        class="mt-2 text-center text-sm text-red-500"
                    >
                        These credentials do not match our records.
                    </p>

                </form>
            </div>
        </div>
    </div>
</template>