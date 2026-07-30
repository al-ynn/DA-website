<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppearanceTabs from '@/Components/AppearanceTabs.vue';
import Heading from '@/Components/Heading.vue';
import AppSidebarLayout from '@/Layouts/app/AppSidebarLayout.vue';
import SettingsLayout from '@/Layouts/settings/Layout.vue';
import { edit } from '@/routes/appearance/index';
import { type BreadcrumbItem } from '@/types';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Appearance settings',
        href: edit().url,
    },
];
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbItems">
        <Head title="Appearance settings" />

        <h1 class="sr-only">Appearance Settings</h1>

        <SettingsLayout>
            <div class="rounded-xl border bg-card p-5 shadow-sm md:p-6">
                <div class="mb-6">
                    <Heading
                        variant="small"
                        title="Appearance settings"
                        description="Update your account's appearance settings"
                    />
                </div>
                <AppearanceTabs />
            </div>
        </SettingsLayout>
    </AppSidebarLayout>
</template>
