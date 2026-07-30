<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { FileText } from 'lucide-vue-next';
import Heading from '@/Components/Heading.vue';
import AppSidebarLayout from '@/Layouts/app/AppSidebarLayout.vue';
import SettingsLayout from '@/Layouts/settings/Layout.vue';
import type { BreadcrumbItem } from '@/types';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Templates settings',
        href: '/settings/templates',
    },
];
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbItems">
        <Head title="Templates settings" />

        <h1 class="sr-only">Templates Settings</h1>

        <SettingsLayout>
            <div class="rounded-xl border bg-card p-5 shadow-sm md:p-6">
                <Heading
                    variant="small"
                    title="Templates"
                    description="Manage reusable document templates"
                />

                <div class="mt-6 rounded-lg border border-dashed bg-muted/30 p-8 text-center">
                    <div class="mx-auto flex h-11 w-11 items-center justify-center rounded-xl bg-primary/10 text-primary">
                        <FileText class="h-5 w-5" />
                    </div>
                    <p class="mt-4 text-sm font-medium">
                        Template management is not yet available
                    </p>
                    <p class="mt-1 text-sm text-muted-foreground">
                        This page is ready for the future template management
                        workflow.
                    </p>
                </div>
            </div>
        </SettingsLayout>
    </AppSidebarLayout>
</template>
