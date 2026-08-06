<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Tabs, TabsList, TabsTrigger, TabsContent } from '@/components/ui/tabs';
import { User, Lock } from 'lucide-vue-next';
import { ref } from 'vue';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/adminos/dashboard' },
            { title: 'Settings' },
        ],
    },
});

const props = defineProps<{
    user: {
        id: number;
        name: string;
        email: string;
    };
}>();

const activeTab = ref('profile');

// Profile Form
const profileForm = useForm({
    name: props.user.name,
    email: props.user.email,
});

const updateProfile = () => {
    profileForm.put('/adminos/settings/profile', {
        preserveScroll: true,
        onSuccess: () => {
            // Form will auto-update with new values from server
        },
    });
};

// Password Form
const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    passwordForm.put('/adminos/settings/password', {
        preserveScroll: true,
        onSuccess: () => {
            passwordForm.reset();
        },
    });
};
</script>

<template>
    <Head title="Settings" />

    <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
        <!-- Header -->
        <div>
            <h1 class="text-2xl font-semibold">Settings</h1>
            <p class="text-sm text-muted-foreground">
                Manage your account and preferences
            </p>
        </div>

        <!-- Settings Tabs -->
        <Tabs v-model="activeTab" class="w-full">
            <TabsList class="grid w-full grid-cols-2 max-w-md">
                <TabsTrigger value="profile">
                    <User class="mr-2 h-4 w-4" />
                    Profile
                </TabsTrigger>
                <TabsTrigger value="password">
                    <Lock class="mr-2 h-4 w-4" />
                    Password
                </TabsTrigger>
            </TabsList>

            <!-- Profile Tab -->
            <TabsContent value="profile" class="mt-6">
                <form @submit.prevent="updateProfile" class="space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>Profile Information</CardTitle>
                            <CardDescription>
                                Update your personal details
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <!-- User Avatar -->
                            <div class="flex items-center gap-4">
                                <div class="h-20 w-20 rounded-full bg-primary text-primary-foreground flex items-center justify-center text-3xl font-semibold">
                                    {{ user.name.charAt(0).toUpperCase() }}
                                </div>
                                <div>
                                    <p class="text-sm font-medium">Profile photo</p>
                                    <p class="text-xs text-muted-foreground">
                                        Initial of your name
                                    </p>
                                </div>
                            </div>

                            <!-- Name -->
                            <div class="space-y-2">
                                <Label for="profile-name">Full name *</Label>
                                <Input
                                    id="profile-name"
                                    v-model="profileForm.name"
                                    type="text"
                                    :class="{ 'border-destructive': profileForm.errors.name }"
                                    required
                                />
                                <p v-if="profileForm.errors.name" class="text-sm text-destructive">
                                    {{ profileForm.errors.name }}
                                </p>
                            </div>

                            <!-- Email -->
                            <div class="space-y-2">
                                <Label for="profile-email">Email *</Label>
                                <Input
                                    id="profile-email"
                                    v-model="profileForm.email"
                                    type="email"
                                    :class="{ 'border-destructive': profileForm.errors.email }"
                                    required
                                />
                                <p v-if="profileForm.errors.email" class="text-sm text-destructive">
                                    {{ profileForm.errors.email }}
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Actions -->
                    <div class="flex gap-3">
                        <Button type="submit" :disabled="profileForm.processing">
                            {{ profileForm.processing ? 'Saving...' : 'Save changes' }}
                        </Button>
                    </div>
                </form>
            </TabsContent>

            <!-- Password Tab -->
            <TabsContent value="password" class="mt-6">
                <form @submit.prevent="updatePassword" class="space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>Change Password</CardTitle>
                            <CardDescription>
                                Make sure to use a strong, secure password
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <!-- Current Password -->
                            <div class="space-y-2">
                                <Label for="current_password">Current password *</Label>
                                <Input
                                    id="current_password"
                                    v-model="passwordForm.current_password"
                                    type="password"
                                    :class="{ 'border-destructive': passwordForm.errors.current_password }"
                                    required
                                />
                                <p v-if="passwordForm.errors.current_password" class="text-sm text-destructive">
                                    {{ passwordForm.errors.current_password }}
                                </p>
                            </div>

                            <!-- New Password -->
                            <div class="space-y-2">
                                <Label for="password">New password *</Label>
                                <Input
                                    id="password"
                                    v-model="passwordForm.password"
                                    type="password"
                                    :class="{ 'border-destructive': passwordForm.errors.password }"
                                    required
                                />
                                <p v-if="passwordForm.errors.password" class="text-sm text-destructive">
                                    {{ passwordForm.errors.password }}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    Minimum 8 characters
                                </p>
                            </div>

                            <!-- Confirm Password -->
                            <div class="space-y-2">
                                <Label for="password_confirmation">Confirm new password *</Label>
                                <Input
                                    id="password_confirmation"
                                    v-model="passwordForm.password_confirmation"
                                    type="password"
                                    required
                                />
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Actions -->
                    <div class="flex gap-3">
                        <Button type="submit" :disabled="passwordForm.processing">
                            {{ passwordForm.processing ? 'Updating...' : 'Update password' }}
                        </Button>
                        <Button
                            type="button"
                            variant="outline"
                            @click="passwordForm.reset()"
                            :disabled="passwordForm.processing"
                        >
                            Reset
                        </Button>
                    </div>
                </form>
            </TabsContent>
        </Tabs>

        <!-- Additional Settings Sections (Future) -->
        <Card class="mt-6">
            <CardHeader>
                <CardTitle>Other Settings</CardTitle>
                <CardDescription>
                    Additional system configuration
                </CardDescription>
            </CardHeader>
            <CardContent>
                <div class="space-y-4">
                    <div class="flex items-center justify-between py-3 border-b">
                        <div>
                            <p class="text-sm font-medium">Email Notifications</p>
                            <p class="text-xs text-muted-foreground">
                                Receive notifications by email
                            </p>
                        </div>
                        <p class="text-xs text-muted-foreground">Coming soon</p>
                    </div>

                    <div class="flex items-center justify-between py-3 border-b">
                        <div>
                            <p class="text-sm font-medium">Active Sessions</p>
                            <p class="text-xs text-muted-foreground">
                                Manage signed-in sessions
                            </p>
                        </div>
                        <p class="text-xs text-muted-foreground">Coming soon</p>
                    </div>

                    <div class="flex items-center justify-between py-3">
                        <div>
                            <p class="text-sm font-medium">Two-Factor Authentication</p>
                            <p class="text-xs text-muted-foreground">
                                Secure your account with 2FA
                            </p>
                        </div>
                        <p class="text-xs text-muted-foreground">Coming soon</p>
                    </div>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
