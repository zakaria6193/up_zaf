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
    <Head title="Connexion Administrateur" />

    <div class="flex min-h-screen items-center justify-center bg-gradient-to-br from-indigo-50 via-white to-blue-50 p-4">
        <Card class="w-full max-w-md">
            <CardHeader class="space-y-1 text-center">
                <div class="mb-4 flex justify-center">
                    <AppLogo class="h-12 w-auto" />
                </div>
                <CardTitle class="text-2xl font-bold">Connexion Administrateur</CardTitle>
                <CardDescription>
                    Entrez vos identifiants pour accéder au panneau d'administration
                </CardDescription>
            </CardHeader>
            <CardContent>
                <form @submit.prevent="submit" class="space-y-4">
                    <div v-if="status" class="rounded-md bg-green-50 p-3 text-sm text-green-800">
                        {{ status }}
                    </div>

                    <div class="space-y-2">
                        <Label for="email">Nom d'utilisateur</Label>
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
                        <Label for="password">Mot de passe</Label>
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
                        {{ form.processing ? 'Connexion...' : 'Se connecter' }}
                    </Button>
                </form>
            </CardContent>
        </Card>
    </div>
</template>
