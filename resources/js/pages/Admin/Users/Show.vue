<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { index, edit } from '@/actions/App/Http/Controllers/Admin/UserController';
import { show as businessShow } from '@/actions/App/Http/Controllers/Admin/BusinessController';
import { User, Mail, Phone, Calendar, Building2 } from 'lucide-vue-next';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/adminos/dashboard' },
            { title: 'Users', href: index.url() },
            { title: 'Details' },
        ],
    },
});

const props = defineProps<{
    user: {
        id: number;
        name: string;
        email: string;
        phone: string | null;
        created_at: string;
    };
    businesses: Array<{
        nanoid: string;
        name: string;
        address: string | null;
        is_active: boolean;
        created_at: string;
    }>;
}>();
</script>

<template>
    <Head :title="`User: ${user.name}`" />

    <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
        <!-- Header -->
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-semibold">{{ user.name }}</h1>
                <p class="text-sm text-muted-foreground">
                    User details
                </p>
            </div>
            <div class="flex gap-2">
                <Button as-child variant="outline">
                    <Link :href="index.url()">Back</Link>
                </Button>
                <Button as-child>
                    <Link :href="edit.url(user.id)">Edit</Link>
                </Button>
            </div>
        </div>

        <!-- User Info Card -->
        <Card>
            <CardHeader>
                <div class="flex items-center gap-4">
                    <div class="h-16 w-16 rounded-full bg-primary text-primary-foreground flex items-center justify-center text-2xl font-semibold">
                        {{ user.name.charAt(0).toUpperCase() }}
                    </div>
                    <div>
                        <CardTitle>{{ user.name }}</CardTitle>
                        <CardDescription>Account information</CardDescription>
                    </div>
                </div>
            </CardHeader>
            <CardContent class="space-y-4">
                <div class="grid gap-4 md:grid-cols-2">
                    <!-- Email -->
                    <div class="flex items-start gap-3">
                        <Mail class="h-5 w-5 text-muted-foreground mt-0.5" />
                        <div>
                            <p class="text-sm font-medium">Email</p>
                            <p class="text-sm text-muted-foreground">{{ user.email }}</p>
                        </div>
                    </div>

                    <!-- Phone -->
                    <div class="flex items-start gap-3">
                        <Phone class="h-5 w-5 text-muted-foreground mt-0.5" />
                        <div>
                            <p class="text-sm font-medium">Phone</p>
                            <p class="text-sm text-muted-foreground">
                                {{ user.phone || 'Not provided' }}
                            </p>
                        </div>
                    </div>

                    <!-- Created At -->
                    <div class="flex items-start gap-3">
                        <Calendar class="h-5 w-5 text-muted-foreground mt-0.5" />
                        <div>
                            <p class="text-sm font-medium">Created</p>
                            <p class="text-sm text-muted-foreground">{{ user.created_at }}</p>
                        </div>
                    </div>

                    <!-- Businesses Count -->
                    <div class="flex items-start gap-3">
                        <Building2 class="h-5 w-5 text-muted-foreground mt-0.5" />
                        <div>
                            <p class="text-sm font-medium">Businesses</p>
                            <p class="text-sm text-muted-foreground">
                                {{ businesses.length }} {{ businesses.length === 1 ? 'business' : 'businesses' }}
                            </p>
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- Businesses Card -->
        <Card>
            <CardHeader>
                <CardTitle>Businesses ({{ businesses.length }})</CardTitle>
                <CardDescription>Businesses managed by this user</CardDescription>
            </CardHeader>
            <CardContent>
                <div v-if="businesses.length > 0" class="space-y-3">
                    <Link
                        v-for="business in businesses"
                        :key="business.nanoid"
                        :href="businessShow.url(business.nanoid)"
                        class="flex items-center justify-between p-4 rounded-lg border hover:bg-muted/50 transition-colors"
                    >
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <p class="font-medium truncate">{{ business.name }}</p>
                                <Badge :variant="business.is_active ? 'default' : 'outline'">
                                    {{ business.is_active ? 'Active' : 'Inactive' }}
                                </Badge>
                            </div>
                            <p v-if="business.address" class="text-sm text-muted-foreground truncate">
                                {{ business.address }}
                            </p>
                            <p class="text-xs text-muted-foreground mt-1">
                                Created {{ business.created_at }}
                            </p>
                        </div>
                    </Link>
                </div>
                <div v-else class="flex min-h-[200px] flex-col items-center justify-center text-center">
                    <Building2 class="h-12 w-12 text-muted-foreground mb-4" />
                    <p class="text-sm font-medium mb-1">No businesses</p>
                    <p class="text-sm text-muted-foreground">
                        This user has no businesses yet
                    </p>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
