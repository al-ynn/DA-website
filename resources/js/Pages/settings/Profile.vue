<script setup lang="ts">
import { Form, Head, usePage } from '@inertiajs/vue3';
import { BadgeCheck, MailWarning } from 'lucide-vue-next';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import DeleteUser from '@/Components/DeleteUser.vue';
import Heading from '@/Components/Heading.vue';
import InputError from '@/Components/InputError.vue';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import AppSidebarLayout from '@/Layouts/app/AppSidebarLayout.vue';
import SettingsLayout from '@/Layouts/settings/Layout.vue';
import { edit } from '@/routes/profile/index';
import { type BreadcrumbItem } from '@/types';

type Props = {
    mustVerifyEmail: boolean;
    status?: string;
};

defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Profile settings',
        href: edit().url,
    },
];

const page = usePage();
const user = page.props.auth.user;
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbItems">
        <Head title="Profile settings" />

        <h1 class="sr-only">Profile Settings</h1>

        <SettingsLayout>
            <div class="rounded-xl border bg-card p-5 shadow-sm md:p-6">
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <Heading
                        variant="small"
                        title="Profile information"
                        description="Update your name and email address"
                    />

                    <Badge
                        v-if="user.email_verified_at"
                        variant="default"
                        class="w-fit gap-1.5"
                    >
                        <BadgeCheck class="h-3.5 w-3.5" />
                        Verified
                    </Badge>
                    <Badge
                        v-else
                        variant="destructive"
                        class="w-fit gap-1.5"
                    >
                        <MailWarning class="h-3.5 w-3.5" />
                        Email not verified
                    </Badge>
                </div>

                <Form
                    :action="ProfileController.update()"
                    class="space-y-5"
                    v-slot="{ errors, processing, recentlySuccessful }"
                >
                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input
                            id="name"
                            class="mt-1 block w-full"
                            name="name"
                            :default-value="user.name"
                            required
                            autocomplete="name"
                            placeholder="Full name"
                        />
                        <InputError class="mt-2" :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Email address</Label>
                        <Input
                            id="email"
                            type="email"
                            class="mt-1 block w-full"
                            name="email"
                            :default-value="user.email"
                            required
                            autocomplete="username"
                            placeholder="Email address"
                        />
                        <InputError class="mt-2" :message="errors.email" />
                    </div>

                    <div
                        v-if="!user.email_verified_at"
                        class="flex flex-col gap-3 rounded-lg border border-amber-200 bg-amber-50 p-4 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div>
                            <p class="text-sm font-medium text-amber-900">
                                Your email address has not been verified.
                            </p>
                            <p class="mt-1 text-xs text-amber-700">
                                Email verification will be available once the
                                mail service is connected.
                            </p>
                        </div>

                        <Button type="button" variant="outline">
                            Verify Email
                        </Button>
                    </div>

                    <div class="flex items-center justify-end gap-4 border-t pt-5">
                        <Button
                            :disabled="processing"
                            data-test="update-profile-button"
                            >Save</Button
                        >

                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p
                                v-show="recentlySuccessful"
                                class="text-sm text-neutral-600"
                            >
                                Saved.
                            </p>
                        </Transition>
                    </div>
                </Form>
            </div>

            <DeleteUser />
        </SettingsLayout>
    </AppSidebarLayout>
</template>
