<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { MessageSquareText, ShieldCheck } from 'lucide-vue-next';
import { ref } from 'vue';
import Heading from '@/Components/Heading.vue';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import AppSidebarLayout from '@/Layouts/app/AppSidebarLayout.vue';
import SettingsLayout from '@/Layouts/settings/Layout.vue';
import { show } from '@/routes/two-factor/index';
import type { BreadcrumbItem } from '@/types';

type Props = {
    twoFactorEnabled?: boolean;
};

withDefaults(defineProps<Props>(), {
    twoFactorEnabled: false,
});

const user = usePage().props.auth.user as { contact_number?: string | null };
const verificationCode = ref('');

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Two-Factor Authentication',
        href: show.url(),
    },
];
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Two-Factor Authentication" />

        <h1 class="sr-only">Two-Factor Authentication Settings</h1>

        <SettingsLayout>
            <div class="rounded-xl border bg-card p-5 shadow-sm md:p-6">
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <Heading
                        variant="small"
                        title="Two-Factor Authentication"
                        description="Prepare your account for SMS verification"
                    />

                    <Badge
                        :variant="twoFactorEnabled ? 'default' : 'secondary'"
                        class="w-fit"
                    >
                        {{ twoFactorEnabled ? 'Enabled' : 'Disabled' }}
                    </Badge>
                </div>

                <div class="rounded-lg border bg-muted/30 p-4">
                    <div class="flex items-start gap-3">
                        <div class="rounded-lg bg-primary/10 p-2 text-primary">
                            <MessageSquareText class="h-5 w-5" />
                        </div>
                        <div>
                            <p class="text-sm font-medium">
                                SMS verification
                            </p>
                            <p class="mt-1 text-sm text-muted-foreground">
                                SMS delivery and OTP verification are not yet
                                connected. These controls prepare the interface
                                for the future integration.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mt-6 grid gap-5">
                    <div class="grid gap-2">
                        <Label for="two_factor_phone">Phone number</Label>
                        <Input
                            id="two_factor_phone"
                            :model-value="user.contact_number || 'No phone number on file'"
                            readonly
                        />
                    </div>

                    <div class="grid gap-2">
                        <Label for="verification_code">Verification code</Label>
                        <div class="flex flex-col gap-3 sm:flex-row">
                            <Input
                                id="verification_code"
                                v-model="verificationCode"
                                inputmode="numeric"
                                autocomplete="one-time-code"
                                placeholder="Enter verification code"
                                class="flex-1"
                            />
                            <Button type="button" variant="outline">
                                Send Code
                            </Button>
                            <Button type="button">
                                Verify
                            </Button>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex flex-col gap-3 border-t pt-5 sm:flex-row sm:justify-end">
                    <Button type="button" variant="outline">
                        <ShieldCheck class="h-4 w-4" />
                        Enable Two-Factor Authentication
                    </Button>
                    <Button type="button" variant="destructive">
                        Disable Two-Factor Authentication
                    </Button>
                </div>
            </div>
        </SettingsLayout>
    </AppSidebarLayout>
</template>
