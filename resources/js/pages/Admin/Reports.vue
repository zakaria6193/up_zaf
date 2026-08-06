<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { show as businessShow } from '@/actions/App/Http/Controllers/Admin/BusinessController';
import { show as userShow } from '@/actions/App/Http/Controllers/Admin/UserController';
import { TrendingUp, TrendingDown, Building2, Users, Eye, BarChart3 } from 'lucide-vue-next';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/adminos/dashboard' },
            { title: 'Reports' },
        ],
    },
});

const props = defineProps<{
    overview: {
        total_businesses: number;
        active_businesses: number;
        total_users: number;
        orphan_businesses: number;
        total_views: number;
        weekly_views: number;
        avg_growth: number;
    };
    top_businesses: Array<{
        nanoid: string;
        name: string;
        views_this_week: number;
        total_views: number;
        growth_percentage: number;
        is_active: boolean;
    }>;
    recent_businesses: Array<{
        nanoid: string;
        name: string;
        created_at: string;
        is_active: boolean;
    }>;
    businesses_by_month: Array<{
        month: string;
        count: number;
    }>;
    top_users: Array<{
        id: number;
        name: string;
        email: string;
        businesses_count: number;
    }>;
}>();

const formatNumber = (num: number) => {
    return new Intl.NumberFormat('en-US').format(num);
};

const formatMonth = (month: string) => {
    const [year, monthNum] = month.split('-');
    const date = new Date(parseInt(year), parseInt(monthNum) - 1);
    return date.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
};
</script>

<template>
    <Head title="Reports & Analytics" />

    <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
        <!-- Header -->
        <div>
            <h1 class="text-2xl font-semibold">Reports & Analytics</h1>
            <p class="text-sm text-muted-foreground">
                Overview of performance and statistics
            </p>
        </div>

        <!-- Overview Stats Cards -->
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <!-- Total Businesses -->
            <Card>
                <CardHeader class="flex flex-row items-center justify-between pb-2">
                    <CardTitle class="text-sm font-medium">Total Businesses</CardTitle>
                    <Building2 class="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">{{ formatNumber(overview.total_businesses) }}</div>
                    <p class="text-xs text-muted-foreground mt-1">
                        {{ overview.active_businesses }} active
                    </p>
                </CardContent>
            </Card>

            <!-- Total Users -->
            <Card>
                <CardHeader class="flex flex-row items-center justify-between pb-2">
                    <CardTitle class="text-sm font-medium">Users</CardTitle>
                    <Users class="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">{{ formatNumber(overview.total_users) }}</div>
                    <p class="text-xs text-muted-foreground mt-1">
                        {{ overview.orphan_businesses }} orphan businesses
                    </p>
                </CardContent>
            </Card>

            <!-- Weekly Views -->
            <Card>
                <CardHeader class="flex flex-row items-center justify-between pb-2">
                    <CardTitle class="text-sm font-medium">Views This Week</CardTitle>
                    <Eye class="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">{{ formatNumber(overview.weekly_views) }}</div>
                    <p class="text-xs text-muted-foreground mt-1">
                        {{ formatNumber(overview.total_views) }} total views
                    </p>
                </CardContent>
            </Card>

            <!-- Average Growth -->
            <Card>
                <CardHeader class="flex flex-row items-center justify-between pb-2">
                    <CardTitle class="text-sm font-medium">Average Growth</CardTitle>
                    <BarChart3 class="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold flex items-center gap-1">
                        <span :class="overview.avg_growth >= 0 ? 'text-green-600' : 'text-red-600'">
                            {{ overview.avg_growth >= 0 ? '+' : '' }}{{ overview.avg_growth }}%
                        </span>
                        <TrendingUp v-if="overview.avg_growth >= 0" class="h-4 w-4 text-green-600" />
                        <TrendingDown v-else class="h-4 w-4 text-red-600" />
                    </div>
                    <p class="text-xs text-muted-foreground mt-1">
                        This week
                    </p>
                </CardContent>
            </Card>
        </div>

        <div class="grid gap-6 md:grid-cols-2">
            <!-- Top Performing Businesses -->
            <Card>
                <CardHeader>
                    <CardTitle>Top 10 Businesses</CardTitle>
                    <CardDescription>By views this week</CardDescription>
                </CardHeader>
                <CardContent>
                    <div v-if="top_businesses.length > 0" class="space-y-3">
                        <Link
                            v-for="(business, index) in top_businesses"
                            :key="business.nanoid"
                            :href="businessShow.url(business.nanoid)"
                            class="flex items-center justify-between p-3 rounded-lg hover:bg-muted/50 transition-colors"
                        >
                            <div class="flex items-center gap-3 flex-1 min-w-0">
                                <div class="h-8 w-8 rounded-full bg-primary/10 text-primary flex items-center justify-center text-sm font-semibold shrink-0">
                                    {{ index + 1 }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium truncate">{{ business.name }}</p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <Badge variant="secondary" class="text-xs">
                                            {{ formatNumber(business.views_this_week) }} views
                                        </Badge>
                                        <span
                                            v-if="business.growth_percentage !== null && business.growth_percentage !== undefined"
                                            class="text-xs font-medium"
                                            :class="business.growth_percentage >= 0 ? 'text-green-600' : 'text-red-600'"
                                        >
                                            {{ business.growth_percentage >= 0 ? '+' : '' }}{{ Number(business.growth_percentage).toFixed(1) }}%
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <Badge v-if="!business.is_active" variant="outline">
                                Inactive
                            </Badge>
                        </Link>
                    </div>
                    <p v-else class="text-center text-sm text-muted-foreground py-8">
                        No data available
                    </p>
                </CardContent>
            </Card>

            <!-- Recent Businesses -->
            <Card>
                <CardHeader>
                    <CardTitle>Recent Businesses</CardTitle>
                    <CardDescription>Created in the last 7 days</CardDescription>
                </CardHeader>
                <CardContent>
                    <div v-if="recent_businesses.length > 0" class="space-y-3">
                        <Link
                            v-for="business in recent_businesses"
                            :key="business.nanoid"
                            :href="businessShow.url(business.nanoid)"
                            class="flex items-center justify-between p-3 rounded-lg hover:bg-muted/50 transition-colors"
                        >
                            <div class="flex-1 min-w-0">
                                <p class="font-medium truncate">{{ business.name }}</p>
                                <p class="text-xs text-muted-foreground mt-1">
                                    {{ business.created_at }}
                                </p>
                            </div>
                            <Badge :variant="business.is_active ? 'default' : 'outline'">
                                {{ business.is_active ? 'Active' : 'Inactive' }}
                            </Badge>
                        </Link>
                    </div>
                    <p v-else class="text-center text-sm text-muted-foreground py-8">
                        No new businesses this week
                    </p>
                </CardContent>
            </Card>
        </div>

        <div class="grid gap-6 md:grid-cols-2">
            <!-- Businesses by Month -->
            <Card>
                <CardHeader>
                    <CardTitle>Business Growth</CardTitle>
                    <CardDescription>Businesses created per month (last 6 months)</CardDescription>
                </CardHeader>
                <CardContent>
                    <div v-if="businesses_by_month.length > 0" class="space-y-2">
                        <div
                            v-for="item in businesses_by_month"
                            :key="item.month"
                            class="flex items-center justify-between py-2"
                        >
                            <span class="text-sm font-medium">{{ formatMonth(item.month) }}</span>
                            <div class="flex items-center gap-3">
                                <div class="h-2 rounded-full bg-primary/20" :style="{ width: `${Math.max(item.count * 20, 20)}px` }">
                                    <div class="h-full rounded-full bg-primary" :style="{ width: '100%' }"></div>
                                </div>
                                <span class="text-sm font-semibold w-8 text-right">{{ item.count }}</span>
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-center text-sm text-muted-foreground py-8">
                        No data available
                    </p>
                </CardContent>
            </Card>

            <!-- Top Users -->
            <Card>
                <CardHeader>
                    <CardTitle>Active Users</CardTitle>
                    <CardDescription>By number of businesses</CardDescription>
                </CardHeader>
                <CardContent>
                    <div v-if="top_users.length > 0" class="space-y-3">
                        <Link
                            v-for="user in top_users"
                            :key="user.id"
                            :href="userShow.url(user.id)"
                            class="flex items-center justify-between p-3 rounded-lg hover:bg-muted/50 transition-colors"
                        >
                            <div class="flex items-center gap-3 flex-1 min-w-0">
                                <div class="h-10 w-10 rounded-full bg-primary text-primary-foreground flex items-center justify-center text-sm font-semibold shrink-0">
                                    {{ user.name.charAt(0).toUpperCase() }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium truncate">{{ user.name }}</p>
                                    <p class="text-xs text-muted-foreground truncate">{{ user.email }}</p>
                                </div>
                            </div>
                            <Badge variant="secondary">
                                {{ user.businesses_count }} {{ user.businesses_count === 1 ? 'business' : 'businesses' }}
                            </Badge>
                        </Link>
                    </div>
                    <p v-else class="text-center text-sm text-muted-foreground py-8">
                        No users with businesses
                    </p>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
