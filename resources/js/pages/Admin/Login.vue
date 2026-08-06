<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AppLogo from '@/components/AppLogo.vue';

defineProps<{
    status?: string;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
    login_type: 'admin',
});

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Admin Login" />

    <div class="flex min-h-screen items-center justify-center bg-background p-4">
        <Card class="w-full max-w-md border-border shadow-none">
            <CardHeader class="space-y-1 text-center">
                <div class="mb-4 flex justify-center">
                    <AppLogo class="h-12 w-auto" />
                </div>
                <CardTitle class="font-serif text-2xl font-semibold tracking-tight">Admin Login</CardTitle>
                <CardDescription>
                    Enter your credentials to access the admin panel
                </CardDescription>
            </CardHeader>
            <CardContent>
                <form @submit.prevent="submit" class="space-y-4">
                    <div v-if="status" class="rounded-md bg-green-50 p-3 text-sm text-green-800">
                        {{ status }}
                    </div>

                    <div class="space-y-2">
                        <Label for="email">Username</Label>
                        <Input
                            id="email"
                            v-model="form.email"
                            type="text"
                            placeholder="up1"
                            :class="{ 'border-destructive': form.errors.email }"
                            required
                            autofocus
                        />
                        <p v-if="form.errors.email" class="text-sm text-destructive">
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="password">Password</Label>
                        <Input
                            id="password"
                            v-model="form.password"
                            type="password"
                            :class="{ 'border-destructive': form.errors.password }"
                            required
                        />
                        <p v-if="form.errors.password" class="text-sm text-destructive">
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <Button
                        type="submit"
                        class="w-full"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Signing in...' : 'Log in' }}
                    </Button>
                </form>
            </CardContent>
        </Card>
    </div>
</template>
