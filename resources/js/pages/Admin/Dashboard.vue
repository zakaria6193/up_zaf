<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Building2, Users, BarChart3, Settings } from 'lucide-vue-next';

const stats = [
    { title: 'Total Businesses', value: '24', icon: Building2, change: '+12% from last month' },
    { title: 'Total Users', value: '156', icon: Users, change: '+8% from last month' },
    { title: 'QR Scans', value: '2,847', icon: BarChart3, change: '+23% from last month' },
    { title: 'Active Links', value: '89', icon: Settings, change: '+5% from last month' },
];
</script>

<template>
    <Head title="Admin Dashboard" />

    <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
        <!-- Header -->
        <div>
            <h1 class="text-3xl font-bold">Dashboard</h1>
            <p class="text-sm text-muted-foreground">
                Welcome back! Here's an overview of your system.
            </p>
        </div>

        <!-- Stats Grid -->
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <Card v-for="stat in stats" :key="stat.title">
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">
                        {{ stat.title }}
                    </CardTitle>
                    <component :is="stat.icon" class="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">{{ stat.value }}</div>
                    <p class="text-xs text-muted-foreground">
                        {{ stat.change }}
                    </p>
                </CardContent>
            </Card>
        </div>

        <!-- Quick Actions -->
        <div class="grid gap-4 md:grid-cols-2">
            <Card>
                <CardHeader>
                    <CardTitle>Recent Businesses</CardTitle>
                    <CardDescription>Latest businesses added to the system</CardDescription>
                </CardHeader>
                <CardContent>
                    <p class="text-sm text-muted-foreground text-center py-8">
                        No recent businesses to display
                    </p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>System Activity</CardTitle>
                    <CardDescription>Recent system events and activities</CardDescription>
                </CardHeader>
                <CardContent>
                    <p class="text-sm text-muted-foreground text-center py-8">
                        No recent activity to display
                    </p>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
