<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Link as LinkIcon, Menu, TrendingUp, Eye, Building2, ExternalLink, Plus, QrCode } from 'lucide-vue-next';
import { index as profile } from '@/actions/App/Http/Controllers/Business/ProfileController';
import { index as menu } from '@/actions/App/Http/Controllers/Business/MenuController';
import { index as qrCode } from '@/actions/App/Http/Controllers/Business/QRCodeController';
import { index as links } from '@/actions/App/Http/Controllers/Business/LinkController';
import { create as createBusiness } from '@/actions/App/Http/Controllers/Business/BusinessController';

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
    subscription: {
        is_premium: boolean;
        is_on_trial: boolean;
        trial_ends_at: string | null;
        trial_seconds_remaining: number;
        public_pages_accessible: boolean;
        can_create_business: boolean;
        free_business_limit: number;
    };
}>();

const remaining = ref(props.subscription.trial_seconds_remaining);
let timer: ReturnType<typeof setInterval> | null = null;

onMounted(() => {
    if (props.subscription.is_on_trial) {
        timer = setInterval(() => {
            remaining.value = Math.max(0, remaining.value - 1);
        }, 1000);
    }
});

onUnmounted(() => {
    if (timer) {
        clearInterval(timer);
    }
});

const trialClock = computed(() => {
    const total = remaining.value;
    const m = Math.floor(total / 60);
    const s = total % 60;
    return `${m}:${String(s).padStart(2, '0')}`;
});

const formatNumber = (num: number) => {
    return new Intl.NumberFormat('en-US').format(num);
};

const businessQuery = (nanoid: string) => ({ business: nanoid });
const manageUrl = (nanoid: string) => profile.url({ query: businessQuery(nanoid) });
const menuUrl = (nanoid: string) => menu.url({ query: businessQuery(nanoid) });
const qrUrl = (nanoid: string) => qrCode.url({ query: businessQuery(nanoid) });
const linksUrl = (nanoid: string) => links.url({ query: businessQuery(nanoid) });
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="text-3xl font-bold">Dashboard</h1>
                <p class="text-sm text-muted-foreground">
                    Welcome back! Here's an overview of your businesses.
                </p>
            </div>
            <Button v-if="subscription.can_create_business" as-child>
                <Link :href="createBusiness.url()">
                    <Plus class="mr-2 h-4 w-4" />
                    Create business
                </Link>
            </Button>
        </div>

        <div
            v-if="subscription.is_on_trial"
            class="rounded-xl border border-orange-200 bg-orange-50 px-4 py-3 text-sm text-orange-950"
        >
            <p class="font-semibold">Free trial active — {{ trialClock }} left</p>
            <p class="mt-1 text-orange-900/80">
                Your public client pages stay online during the trial. After that they lock until you go Premium.
                Dashboard and menu editing remain available.
            </p>
        </div>

        <div
            v-else-if="!subscription.is_premium && !subscription.public_pages_accessible"
            class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-950"
        >
            <p class="font-semibold">Trial ended — public pages are locked</p>
            <p class="mt-1 text-red-900/80">
                Guests can no longer open your public menu. Upgrade to Premium to reopen them
                {{ subscription.can_create_business ? '' : ' and create more businesses' }}.
            </p>
        </div>

        <div
            v-else-if="subscription.is_premium"
            class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-950"
        >
            <p class="font-semibold">Premium account</p>
            <p class="mt-1 text-emerald-900/80">Unlimited businesses and always-on public pages.</p>
        </div>

        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">Total Views</CardTitle>
                    <Eye class="text-muted-foreground h-4 w-4" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">{{ formatNumber(stats.total_views) }}</div>
                    <p class="text-muted-foreground text-xs">All time</p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">This Week</CardTitle>
                    <TrendingUp class="text-muted-foreground h-4 w-4" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">{{ formatNumber(stats.weekly_views) }}</div>
                    <p class="text-muted-foreground text-xs">
                        {{ stats.avg_growth >= 0 ? '+' : '' }}{{ stats.avg_growth.toFixed(1) }}% from last week
                    </p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">Businesses</CardTitle>
                    <Building2 class="text-muted-foreground h-4 w-4" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">{{ stats.total_businesses }}</div>
                    <p class="text-muted-foreground text-xs">{{ stats.active_businesses }} active</p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">Setup</CardTitle>
                    <QrCode class="text-muted-foreground h-4 w-4" />
                </CardHeader>
                <CardContent>
                    <p class="text-muted-foreground text-xs leading-relaxed">
                        Menu, QR code, and links stay inside each business. Use the shortcuts on a card below.
                    </p>
                </CardContent>
            </Card>
        </div>

        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold">Your Businesses</h2>
            </div>

            <div v-if="businesses.length > 0" class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <Card
                    v-for="business in businesses"
                    :key="business.nanoid"
                    class="overflow-hidden transition-shadow hover:shadow-lg"
                >
                    <CardHeader class="pb-3">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                <div
                                    v-if="business.logo"
                                    class="h-12 w-12 overflow-hidden rounded-lg shadow-md ring-2 ring-white"
                                >
                                    <img :src="business.logo" :alt="business.name" class="h-full w-full object-cover" />
                                </div>
                                <div
                                    v-else
                                    class="bg-primary text-primary-foreground flex h-12 w-12 items-center justify-center rounded-lg text-xl font-bold shadow-md ring-2 ring-white"
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
                        <div class="grid grid-cols-3 gap-2 text-center">
                            <div class="rounded-lg bg-gray-50 p-2">
                                <Eye class="text-muted-foreground mx-auto mb-1 h-4 w-4" />
                                <p class="text-sm font-bold">{{ formatNumber(business.views_this_week) }}</p>
                                <p class="text-muted-foreground text-xs">Week</p>
                            </div>
                            <div class="rounded-lg bg-gray-50 p-2">
                                <LinkIcon class="text-muted-foreground mx-auto mb-1 h-4 w-4" />
                                <p class="text-sm font-bold">{{ business.active_links_count }}</p>
                                <p class="text-muted-foreground text-xs">Links</p>
                            </div>
                            <div class="rounded-lg bg-gray-50 p-2">
                                <Menu class="text-muted-foreground mx-auto mb-1 h-4 w-4" />
                                <p class="text-sm font-bold">{{ business.menu_categories_count }}</p>
                                <p class="text-muted-foreground text-xs">Menu</p>
                            </div>
                        </div>

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

                        <div class="grid grid-cols-3 gap-2">
                            <Button size="sm" variant="outline" as-child>
                                <Link :href="menuUrl(business.nanoid)">
                                    <Menu class="mr-1.5 h-3.5 w-3.5" />
                                    Menu
                                </Link>
                            </Button>
                            <Button size="sm" variant="outline" as-child>
                                <Link :href="qrUrl(business.nanoid)">
                                    <QrCode class="mr-1.5 h-3.5 w-3.5" />
                                    QR
                                </Link>
                            </Button>
                            <Button size="sm" variant="outline" as-child>
                                <Link :href="linksUrl(business.nanoid)">
                                    <LinkIcon class="mr-1.5 h-3.5 w-3.5" />
                                    Links
                                </Link>
                            </Button>
                        </div>

                        <div class="flex gap-2">
                            <Button size="sm" variant="outline" as-child class="flex-1">
                                <a :href="business.public_url" target="_blank">
                                    <ExternalLink class="mr-2 h-3 w-3" />
                                    View
                                </a>
                            </Button>
                            <Button size="sm" as-child class="flex-1">
                                <Link :href="manageUrl(business.nanoid)">Setup</Link>
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <Card v-else>
                <CardContent class="py-12">
                    <div class="text-center">
                        <Building2 class="text-muted-foreground mx-auto mb-4 h-12 w-12" />
                        <p class="mb-1 text-sm font-medium">No businesses yet</p>
                        <p class="text-muted-foreground mb-4 text-sm">
                            Create your first enterprise to publish a menu and QR code.
                        </p>
                        <Button v-if="subscription.can_create_business" as-child>
                            <Link :href="createBusiness.url()">
                                <Plus class="mr-2 h-4 w-4" />
                                Create business
                            </Link>
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
