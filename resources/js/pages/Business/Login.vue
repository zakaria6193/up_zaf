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
    <Head title="Connexion Entreprise" />

    <div class="flex min-h-screen items-center justify-center bg-gradient-to-br from-indigo-50 via-white to-blue-50 p-4">
        <Card class="w-full max-w-md">
            <CardHeader class="space-y-1 text-center">
                <div class="mb-4 flex justify-center">
                    <AppLogo class="h-12 w-auto" />
                </div>
                <CardTitle class="text-2xl font-bold">Connexion Entreprise</CardTitle>
                <CardDescription>
                    Entrez votre e-mail ou numéro de téléphone et votre mot de passe
                </CardDescription>
            </CardHeader>
            <CardContent>
                <form @submit.prevent="submit" class="space-y-4">
                    <div v-if="status" class="rounded-md bg-green-50 p-3 text-sm text-green-800">
                        {{ status }}
                    </div>

                    <div class="space-y-2">
                        <Label for="phone">E-mail ou téléphone</Label>
                        <Input
                            id="phone"
                            v-model="form.phone"
                            type="text"
                            autocomplete="username"
                            placeholder="mohamed@example.com ou +212600111111"
                            :class="{ 'border-destructive': form.errors.phone || form.errors.email }"
                            required
                            autofocus
                        />
                        <p v-if="form.errors.phone || form.errors.email" class="text-sm text-destructive">
                            {{ form.errors.phone || form.errors.email }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="password">Mot de passe</Label>
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

                    <Button
                        type="submit"
                        class="w-full"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Connexion...' : 'Se connecter' }}
                    </Button>
                </form>
            </CardContent>
        </Card>
    </div>
</template>
