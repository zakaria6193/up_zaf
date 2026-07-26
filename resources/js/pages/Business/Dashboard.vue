<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { QrCode, Link as LinkIcon, Menu, TrendingUp, Eye, Building2, ExternalLink } from 'lucide-vue-next';
import { index as profile } from '@/actions/App/Http/Controllers/Business/ProfileController';

const props = defineProps<{
    businesses: Array<{
        nanoid: string;
        name: string;
        is_active: boolean;
        total_views: number;
        views_this_week: number;
        growth_percentage: number;
        active_links_count: number;
        menu_categories_count: number;
        logo: string | null;
        public_url: string;
    }>;
    stats: {
        total_views: number;
        weekly_views: number;
        avg_growth: number;
        total_businesses: number;
        active_businesses: number;
    };
}>();

const formatNumber = (num: number) => {
    return new Intl.NumberFormat('fr-FR').format(num);
};

const manageUrl = (nanoid: string) =>
    profile.url({ query: { business: nanoid } });
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
        <!-- Header -->
        <div>
            <h1 class="text-3xl font-bold">Dashboard</h1>
            <p class="text-sm text-muted-foreground">
                Welcome back! Here's an overview of your businesses.
            </p>
        </div>

        <!-- Overall Stats Grid -->
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">Total Views</CardTitle>
                    <Eye class="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">{{ formatNumber(stats.total_views) }}</div>
                    <p class="text-xs text-muted-foreground">All time</p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">This Week</CardTitle>
                    <TrendingUp class="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">{{ formatNumber(stats.weekly_views) }}</div>
                    <p class="text-xs text-muted-foreground">
                        {{ stats.avg_growth >= 0 ? '+' : '' }}{{ stats.avg_growth.toFixed(1) }}% from last week
                    </p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">Businesses</CardTitle>
                    <Building2 class="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">{{ stats.total_businesses }}</div>
                    <p class="text-xs text-muted-foreground">
                        {{ stats.active_businesses }} active
                    </p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">Quick Actions</CardTitle>
                    <QrCode class="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <div class="flex flex-col gap-2">
                        <Button size="sm" variant="outline" as-child class="w-full">
                            <Link href="/business/qr-code">
                                <QrCode class="mr-2 h-3 w-3" />
                                QR Codes
                            </Link>
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Businesses List -->
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold">Your Businesses</h2>
            </div>

            <div v-if="businesses.length > 0" class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <Card
                    v-for="business in businesses"
                    :key="business.nanoid"
                    class="overflow-hidden hover:shadow-lg transition-shadow"
                >
                    <CardHeader class="pb-3">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                <div
                                    v-if="business.logo"
                                    class="w-12 h-12 rounded-lg overflow-hidden ring-2 ring-white shadow-md"
                                >
                                    <img
                                        :src="business.logo"
                                        :alt="business.name"
                                        class="w-full h-full object-cover"
                                    />
                                </div>
                                <div
                                    v-else
                                    class="w-12 h-12 rounded-lg bg-primary text-primary-foreground flex items-center justify-center text-xl font-bold ring-2 ring-white shadow-md"
                                >
                                    {{ business.name.charAt(0).toUpperCase() }}
                                </div>
                                <div>
                                    <CardTitle class="text-base">{{ business.name }}</CardTitle>
                                    <Badge :variant="business.is_active ? 'default' : 'outline'" class="mt-1">
                                        {{ business.is_active ? 'Active' : 'Inactive' }}
                                    </Badge>
                                </div>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <!-- Stats -->
                        <div class="grid grid-cols-3 gap-2 text-center">
                            <div class="rounded-lg bg-gray-50 p-2">
                                <Eye class="h-4 w-4 mx-auto mb-1 text-muted-foreground" />
                                <p class="text-sm font-bold">{{ formatNumber(business.views_this_week) }}</p>
                                <p class="text-xs text-muted-foreground">Week</p>
                            </div>
                            <div class="rounded-lg bg-gray-50 p-2">
                                <LinkIcon class="h-4 w-4 mx-auto mb-1 text-muted-foreground" />
                                <p class="text-sm font-bold">{{ business.active_links_count }}</p>
                                <p class="text-xs text-muted-foreground">Links</p>
                            </div>
                            <div class="rounded-lg bg-gray-50 p-2">
                                <Menu class="h-4 w-4 mx-auto mb-1 text-muted-foreground" />
                                <p class="text-sm font-bold">{{ business.menu_categories_count }}</p>
                                <p class="text-xs text-muted-foreground">Menu</p>
                            </div>
                        </div>

                        <!-- Growth -->
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-muted-foreground">Growth</span>
                            <span
                                :class="{
                                    'text-green-600': business.growth_percentage >= 0,
                                    'text-red-600': business.growth_percentage < 0,
                                }"
                                class="font-semibold"
                            >
                                {{ business.growth_percentage >= 0 ? '+' : '' }}{{ business.growth_percentage.toFixed(1) }}%
                            </span>
                        </div>

                        <!-- Actions -->
                        <div class="flex gap-2">
                            <Button size="sm" variant="outline" as-child class="flex-1">
                                <a :href="business.public_url" target="_blank">
                                    <ExternalLink class="mr-2 h-3 w-3" />
                                    View
                                </a>
                            </Button>
                            <Button size="sm" as-child class="flex-1">
                                <Link :href="manageUrl(business.nanoid)">
                                    Manage
                                </Link>
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Empty State -->
            <Card v-else>
                <CardContent class="py-12">
                    <div class="text-center">
                        <Building2 class="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                        <p class="text-sm font-medium mb-1">No businesses yet</p>
                        <p class="text-sm text-muted-foreground">
                            Contact an administrator to get your business added.
                        </p>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
