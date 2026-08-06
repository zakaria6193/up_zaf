<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AppLogo from '@/components/AppLogo.vue';

defineOptions({ layout: false });

defineProps<{
    status?: string;
    googleEnabled?: boolean;
}>();

const page = usePage();
const flashError = computed(() => (page.props.flash as { error?: string } | undefined)?.error);

const form = useForm({
    phone: '',
    password: '',
    remember: false,
    login_type: 'business',
});

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Business Login" />

    <div class="flex min-h-screen items-center justify-center bg-background p-4">
        <Card class="w-full max-w-md border-border shadow-none">
            <CardHeader class="space-y-1 text-center">
                <div class="mb-4 flex justify-center">
                    <AppLogo class="h-12 w-auto" />
                </div>
                <CardTitle class="font-serif text-2xl font-semibold tracking-tight">Business Login</CardTitle>
                <CardDescription>
                    Enter your email or phone number and password
                </CardDescription>
            </CardHeader>
            <CardContent class="space-y-4">
                <div v-if="status" class="rounded-md bg-green-50 p-3 text-sm text-green-800">
                    {{ status }}
                </div>
                <div v-if="flashError" class="rounded-md bg-red-50 p-3 text-sm text-red-800">
                    {{ flashError }}
                </div>

                <a
                    v-if="googleEnabled"
                    href="/auth/google"
                    class="flex w-full items-center justify-center gap-2 rounded-md border border-input bg-card px-4 py-2 text-sm font-medium hover:bg-muted"
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" aria-hidden="true">
                        <path fill="#EA4335" d="M12 10.2v3.6h5.1c-.2 1.2-.9 2.2-1.9 2.9l3.1 2.4c1.8-1.7 2.8-4.1 2.8-7 0-.7-.1-1.4-.2-2H12z" />
                        <path fill="#34A853" d="M6.6 14.3l-.8.6-2.4 1.9C5.1 19.4 8.3 21.5 12 21.5c2.7 0 4.9-.9 6.5-2.4l-3.1-2.4c-.9.6-2 .9-3.4.9-2.6 0-4.8-1.8-5.6-4.1z" />
                        <path fill="#4A90E2" d="M3.4 7.2C2.7 8.6 2.3 10.2 2.3 12s.4 3.4 1.1 4.8l3.2-2.5c-.2-.6-.3-1.2-.3-2.3 0-1 .1-1.7.3-2.3L3.4 7.2z" />
                        <path fill="#FBBC05" d="M12 5.5c1.5 0 2.8.5 3.8 1.5l2.8-2.8C16.9 2.5 14.7 1.5 12 1.5 8.3 1.5 5.1 3.6 3.4 7.2l3.2 2.5C7.2 7.3 9.4 5.5 12 5.5z" />
                    </svg>
                    Continue with Google
                </a>

                <div v-if="googleEnabled" class="relative py-1 text-center text-xs text-muted-foreground">
                    <span class="bg-card relative z-10 px-2">or</span>
                    <span class="absolute inset-x-0 top-1/2 h-px -translate-y-1/2 bg-border" />
                </div>

                <form class="space-y-4" @submit.prevent="submit">
                    <div class="space-y-2">
                        <Label for="phone">Email or phone</Label>
                        <Input
                            id="phone"
                            v-model="form.phone"
                            type="text"
                            autocomplete="username"
                            placeholder="mohamed@example.com or +212600111111"
                            :class="{ 'border-destructive': form.errors.phone || form.errors.email }"
                            required
                            autofocus
                        />
                        <p v-if="form.errors.phone || form.errors.email" class="text-sm text-destructive">
                            {{ form.errors.phone || form.errors.email }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="password">Password</Label>
                        <Input
                            id="password"
                            v-model="form.password"
                            type="password"
                            autocomplete="current-password"
                            :class="{ 'border-destructive': form.errors.password }"
                            required
                        />
                        <p v-if="form.errors.password" class="text-sm text-destructive">
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <Button type="submit" class="w-full" :disabled="form.processing">
                        {{ form.processing ? 'Signing in...' : 'Log in' }}
                    </Button>
                </form>

                <p class="text-center text-sm text-muted-foreground">
                    Don't have an account?
                    <Link href="/register" class="font-medium text-foreground underline-offset-4 hover:underline">
                        Sign up
                    </Link>
                </p>

                <p class="text-center text-xs text-muted-foreground">
                    <Link href="/" class="hover:underline">← Back to home</Link>
                </p>
            </CardContent>
        </Card>
    </div>
</template>
