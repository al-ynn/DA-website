<script setup>
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Welcome">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <div class="flex min-h-screen flex-col items-center bg-[#FDFDFC]
            px-4 py-6 text-[#1b1b18]
            sm:px-6 lg:justify-center lg:px-8
            dark:bg-[#0a0a0a]">

        <header class="mb-6 w-full max-w-md text-sm sm:max-w-2xl lg:max-w-4xl">
            <nav class="flex items-center justify-end gap-4">

                <Link
                    v-if="$page.props.auth?.user"
                    href="/dashboard"
                    class="inline-block rounded-sm border border-[#19140035] px-5 py-1.5
                           text-sm text-[#1b1b18] hover:border-[#1915014a]
                           dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
                >
                    Dashboard
                </Link>

                <Link
                    v-else
                    href="/login"
                    class="inline-block rounded-sm border border-transparent px-5 py-1.5
                           text-sm text-[#1b1b18] hover:border-[#19140035]
                           dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]"
                >
                    Log in
                </Link>

            </nav>
        </header>

        <div class="flex w-full items-center justify-center opacity-100 transition-opacity duration-700
                    lg:grow starting:opacity-0">

            <main class="flex w-full max-w-md flex-col-reverse overflow-hidden rounded-lg
                        sm:max-w-2xl lg:max-w-4xl lg:flex-row">

                <div class="flex-1 rounded-bl-lg rounded-br-lg bg-white
                            p-6 pb-10 text-[13px] leading-5
                            shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)]
                            sm:p-8
                            lg:rounded-tl-lg lg:rounded-br-none lg:p-20
                            dark:bg-[#161615] dark:text-[#EDEDEC]
                            dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]">

                    <h1 class="mb-1 text-2xl font-medium sm:text-3xl">Department of Agriculture</h1>

                    <p class="mb-2 text-[#706f6c] dark:text-[#A1A09A] leading-5">
                        Regional Field Office <br />
                        Cordillera Administrative Region (CAR) <br />
                        Regional Soils Laboratory
                    </p>

                    <p class="mb-6 text-[#706f6c] dark:text-[#A1A09A]">
                        "Your Partner in Soil Analysis and Agricultural Excellence"
                    </p>

                    <Link
                        href="/results/request"
                        class="inline-flex w-full sm:w-auto items-center justify-center gap-2
                            rounded-lg border border-[#0E3D1A]
                            bg-white px-4 py-2.5 text-sm font-semibold text-[#0E3D1A]
                            transition hover:bg-[#0E3D1A] hover:text-white"
                    >
                    Request for Laboratory Results
                    </Link>

                </div>

                <div class="flex items-center justify-center lg:w-1/2 mb-6 lg:mb-0">
                    <img
                        src="/images/RSL%20CAR%20logo.png"
                        alt="Soil Testing"
                        class="w-44 sm:w-56 md:w-64 lg:w-[320px] dark:hidden"
                    />

                    <img
                        src="/images/RSL%20CAR%20logo%20DM.png"
                        alt="Soil Testing (Dark Mode)"
                        class="hidden w-44 sm:w-56 md:w-64 lg:w-[320px] dark:block"
                    />
                </div>

            </main>
        </div>

        <div class="hidden h-14.5 lg:block"></div>
    </div>
</template>