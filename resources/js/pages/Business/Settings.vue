<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Lock } from 'lucide-vue-next';
import { updatePassword as updatePasswordRoute } from '@/actions/App/Http/Controllers/Business/SettingsController';

const props = defineProps<{
    user: {
        name: string;
        email: string;
    };
}>();

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    passwordForm.put(updatePasswordRoute.url(), {
        preserveScroll: true,
        onSuccess: () => {
            passwordForm.reset();
        },
    });
};
</script>

<template>
    <Head title="Account" />

    <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
        <!-- Header -->
        <div>
            <h1 class="text-3xl font-bold">Account</h1>
            <p class="text-sm text-muted-foreground">
                Your login details and password. Menu and QR live under each business on the dashboard.
            </p>
        </div>

        <div class="grid gap-6 md:grid-cols-2">
            <!-- Account Information -->
            <Card>
                <CardHeader>
                    <CardTitle>Account information</CardTitle>
                    <CardDescription>Your personal details</CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div>
                        <Label class="text-sm font-medium text-gray-700">Name</Label>
                        <p class="text-base">{{ user.name }}</p>
                    </div>
                    <div>
                        <Label class="text-sm font-medium text-gray-700">Email</Label>
                        <p class="text-base">{{ user.email }}</p>
                    </div>
                </CardContent>
            </Card>

            <!-- Password Change -->
            <Card>
                <CardHeader>
                    <CardTitle>Change password</CardTitle>
                    <CardDescription>Update your password</CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="updatePassword" class="space-y-4">
                        <div>
                            <Label for="current_password">Current password</Label>
                            <Input
                                id="current_password"
                                v-model="passwordForm.current_password"
                                type="password"
                                :disabled="passwordForm.processing"
                                :class="{ 'border-red-500': passwordForm.errors.current_password }"
                            />
                            <p v-if="passwordForm.errors.current_password" class="text-sm text-red-600 mt-1">
                                {{ passwordForm.errors.current_password }}
                            </p>
                        </div>

                        <div>
                            <Label for="password">New password</Label>
                            <Input
                                id="password"
                                v-model="passwordForm.password"
                                type="password"
                                :disabled="passwordForm.processing"
                                :class="{ 'border-red-500': passwordForm.errors.password }"
                            />
                            <p v-if="passwordForm.errors.password" class="text-sm text-red-600 mt-1">
                                {{ passwordForm.errors.password }}
                            </p>
                        </div>

                        <div>
                            <Label for="password_confirmation">Confirm password</Label>
                            <Input
                                id="password_confirmation"
                                v-model="passwordForm.password_confirmation"
                                type="password"
                                :disabled="passwordForm.processing"
                            />
                        </div>

                        <Button
                            type="submit"
                            class="w-full"
                            :disabled="passwordForm.processing"
                        >
                            <Lock class="mr-2 h-4 w-4" />
                            Update password
                        </Button>
                    </form>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
