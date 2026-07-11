<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { store, index } from '@/actions/App/Http/Controllers/Admin/UserController';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Tableau de bord', href: '/adminos/dashboard' },
            { title: 'Utilisateurs', href: index.url() },
            { title: 'Créer' },
        ],
    },
});

const form = useForm({
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(store.url(), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Créer un utilisateur" />

    <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
        <!-- Header -->
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-semibold">Créer un utilisateur</h1>
                <p class="text-sm text-muted-foreground">
                    Ajouter un nouveau compte propriétaire d'entreprise
                </p>
            </div>
            <Button as-child variant="outline">
                <Link :href="index.url()">Retour</Link>
            </Button>
        </div>

        <!-- Form -->
        <form @submit.prevent="submit" class="space-y-6">
            <Card>
                <CardHeader>
                    <CardTitle>Informations de l'utilisateur</CardTitle>
                    <CardDescription>
                        Entrez les détails du nouvel utilisateur
                    </CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    <!-- Name -->
                    <div class="space-y-2">
                        <Label for="name">Nom complet *</Label>
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
                        <Label for="phone">Téléphone</Label>
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
                        <p class="text-xs text-muted-foreground">Optionnel</p>
                    </div>

                    <!-- Password -->
                    <div class="space-y-2">
                        <Label for="password">Mot de passe *</Label>
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
                        <p class="text-xs text-muted-foreground">
                            Minimum 8 caractères
                        </p>
                    </div>

                    <!-- Password Confirmation -->
                    <div class="space-y-2">
                        <Label for="password_confirmation">Confirmer le mot de passe *</Label>
                        <Input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            required
                        />
                    </div>
                </CardContent>
            </Card>

            <!-- Actions -->
            <div class="flex gap-3">
                <Button type="submit" :disabled="form.processing">
                    {{ form.processing ? 'Création...' : 'Créer l\'utilisateur' }}
                </Button>
                <Button type="button" variant="outline" as-child>
                    <Link :href="index.url()">Annuler</Link>
                </Button>
            </div>
        </form>
    </div>
</template>
