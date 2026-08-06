<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { update, index, show } from '@/actions/App/Http/Controllers/Admin/UserController';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/adminos/dashboard' },
            { title: 'Users', href: index.url() },
            { title: 'Edit' },
        ],
    },
});

const props = defineProps<{
    user: {
        id: number;
        name: string;
        email: string;
        phone: string | null;
    };
}>();

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    phone: props.user.phone || '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.put(update.url(props.user.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="`Edit: ${user.name}`" />

    <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
        <!-- Header -->
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-semibold">Edit user</h1>
                <p class="text-sm text-muted-foreground">
                    Update information for {{ user.name }}
                </p>
            </div>
            <div class="flex gap-2">
                <Button as-child variant="outline">
                    <Link :href="show.url(user.id)">Back</Link>
                </Button>
            </div>
        </div>

        <!-- Form -->
        <form @submit.prevent="submit" class="space-y-6">
            <Card>
                <CardHeader>
                    <CardTitle>User information</CardTitle>
                    <CardDescription>
                        Edit the user's details
                    </CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    <!-- Name -->
                    <div class="space-y-2">
                        <Label for="name">Full name *</Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            type="text"
                            :class="{ 'border-destructive': form.errors.name }"
                            required
                            autofocus
                        />
                        <p v-if="form.errors.name" class="text-sm text-destructive">
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <!-- Email -->
                    <div class="space-y-2">
                        <Label for="email">Email *</Label>
                        <Input
                            id="email"
                            v-model="form.email"
                            type="email"
                            :class="{ 'border-destructive': form.errors.email }"
                            required
                        />
                        <p v-if="form.errors.email" class="text-sm text-destructive">
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <!-- Phone -->
                    <div class="space-y-2">
                        <Label for="phone">Phone</Label>
                        <Input
                            id="phone"
                            v-model="form.phone"
                            type="tel"
                            placeholder="+212 6XX XX XX XX"
                            :class="{ 'border-destructive': form.errors.phone }"
                        />
                        <p v-if="form.errors.phone" class="text-sm text-destructive">
                            {{ form.errors.phone }}
                        </p>
                        <p class="text-xs text-muted-foreground">Optional</p>
                    </div>
                </CardContent>
            </Card>

            <!-- Password Change Section -->
            <Card>
                <CardHeader>
                    <CardTitle>Change password</CardTitle>
                    <CardDescription>
                        Leave blank to keep the current password
                    </CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    <!-- Password -->
                    <div class="space-y-2">
                        <Label for="password">New password</Label>
                        <Input
                            id="password"
                            v-model="form.password"
                            type="password"
                            :class="{ 'border-destructive': form.errors.password }"
                        />
                        <p v-if="form.errors.password" class="text-sm text-destructive">
                            {{ form.errors.password }}
                        </p>
                        <p class="text-xs text-muted-foreground">
                            Minimum 8 characters
                        </p>
                    </div>

                    <!-- Password Confirmation -->
                    <div class="space-y-2">
                        <Label for="password_confirmation">Confirm new password</Label>
                        <Input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                        />
                    </div>
                </CardContent>
            </Card>

            <!-- Actions -->
            <div class="flex gap-3">
                <Button type="submit" :disabled="form.processing">
                    {{ form.processing ? 'Saving...' : 'Save changes' }}
                </Button>
                <Button type="button" variant="outline" as-child>
                    <Link :href="show.url(user.id)">Cancel</Link>
                </Button>
            </div>
        </form>
    </div>
</template>
