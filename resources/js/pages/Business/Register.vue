<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AppLogo from '@/components/AppLogo.vue';
import { store } from '@/actions/App/Http/Controllers/Business/RegisterController';

defineOptions({ layout: false });

const props = defineProps<{
    googleEnabled?: boolean;
    trialMinutes?: number;
}>();

const page = usePage();
const flashError = computed(() => (page.props.flash as { error?: string } | undefined)?.error);

const form = useForm({
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(store.url());
};
</script>

<template>
    <Head title="Créer un compte" />

    <div class="flex min-h-screen items-center justify-center bg-gradient-to-br from-orange-50 via-stone-50 to-amber-50 p-4">
        <Card class="w-full max-w-md">
            <CardHeader class="space-y-1 text-center">
                <div class="mb-4 flex justify-center">
                    <AppLogo class="h-12 w-auto" />
                </div>
                <CardTitle class="text-2xl font-bold">Créer un compte</CardTitle>
                <CardDescription>
                    Essayez UP1 gratuitement pendant {{ trialMinutes ?? 10 }} minutes — puis passez Premium pour continuer.
                </CardDescription>
            </CardHeader>
            <CardContent class="space-y-4">
                <div v-if="flashError" class="rounded-md bg-red-50 p-3 text-sm text-red-800">
                    {{ flashError }}
                </div>

                <a
                    v-if="googleEnabled"
                    href="/auth/google"
                    class="flex w-full items-center justify-center gap-2 rounded-md border border-input bg-background px-4 py-2 text-sm font-medium hover:bg-accent"
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" aria-hidden="true">
                        <path fill="#EA4335" d="M12 10.2v3.6h5.1c-.2 1.2-.9 2.2-1.9 2.9l3.1 2.4c1.8-1.7 2.8-4.1 2.8-7 0-.7-.1-1.4-.2-2H12z" />
                        <path fill="#34A853" d="M6.6 14.3l-.8.6-2.4 1.9C5.1 19.4 8.3 21.5 12 21.5c2.7 0 4.9-.9 6.5-2.4l-3.1-2.4c-.9.6-2 .9-3.4.9-2.6 0-4.8-1.8-5.6-4.1z" />
                        <path fill="#4A90E2" d="M3.4 7.2C2.7 8.6 2.3 10.2 2.3 12s.4 3.4 1.1 4.8l3.2-2.5c-.2-.6-.3-1.2-.3-2.3 0-1 .1-1.7.3-2.3L3.4 7.2z" />
                        <path fill="#FBBC05" d="M12 5.5c1.5 0 2.8.5 3.8 1.5l2.8-2.8C16.9 2.5 14.7 1.5 12 1.5 8.3 1.5 5.1 3.6 3.4 7.2l3.2 2.5C7.2 7.3 9.4 5.5 12 5.5z" />
                    </svg>
                    Continuer avec Google
                </a>

                <div v-if="googleEnabled" class="relative py-1 text-center text-xs text-muted-foreground">
                    <span class="bg-card relative z-10 px-2">ou</span>
                    <span class="absolute inset-x-0 top-1/2 h-px -translate-y-1/2 bg-border" />
                </div>

                <form class="space-y-4" @submit.prevent="submit">
                    <div class="space-y-2">
                        <Label for="name">Nom</Label>
                        <Input id="name" v-model="form.name" required autofocus autocomplete="name" />
                        <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
                    </div>

                    <div class="space-y-2">
                        <Label for="email">E-mail</Label>
                        <Input id="email" v-model="form.email" type="email" required autocomplete="email" />
                        <p v-if="form.errors.email" class="text-sm text-destructive">{{ form.errors.email }}</p>
                    </div>

                    <div class="space-y-2">
                        <Label for="phone">Téléphone (optionnel)</Label>
                        <Input id="phone" v-model="form.phone" autocomplete="tel" placeholder="+2126..." />
                        <p v-if="form.errors.phone" class="text-sm text-destructive">{{ form.errors.phone }}</p>
                    </div>

                    <div class="space-y-2">
                        <Label for="password">Mot de passe</Label>
                        <Input id="password" v-model="form.password" type="password" required autocomplete="new-password" />
                        <p v-if="form.errors.password" class="text-sm text-destructive">{{ form.errors.password }}</p>
                    </div>

                    <div class="space-y-2">
                        <Label for="password_confirmation">Confirmer le mot de passe</Label>
                        <Input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            required
                            autocomplete="new-password"
                        />
                    </div>

                    <Button type="submit" class="w-full" :disabled="form.processing">
                        {{ form.processing ? 'Création...' : 'Créer mon compte' }}
                    </Button>
                </form>

                <p class="text-center text-sm text-muted-foreground">
                    Déjà un compte ?
                    <Link href="/login" class="font-medium text-foreground underline-offset-4 hover:underline">
                        Se connecter
                    </Link>
                </p>
            </CardContent>
        </Card>
    </div>
</template>
