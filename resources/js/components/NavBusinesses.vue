<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Building2 } from 'lucide-vue-next';
import { computed } from 'vue';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    useSidebar,
} from '@/components/ui/sidebar';
import { index as profile } from '@/actions/App/Http/Controllers/Business/ProfileController';

type SidebarBusiness = {
    nanoid: string;
    name: string;
};

const SETUP_PATHS = [
    '/business/profile',
    '/business/menu',
    '/business/qr-code',
    '/business/links',
] as const;

const page = usePage();
const { isMobile, setOpenMobile } = useSidebar();

const businesses = computed(() => {
    const list = page.props.auth.user?.businesses;

    return Array.isArray(list) ? (list as SidebarBusiness[]) : [];
});

const currentPath = computed(() => {
    try {
        return new URL(page.url, 'http://localhost').pathname;
    } catch {
        return page.url.split('?')[0] ?? '';
    }
});

const isOnSetupPage = computed(() =>
    SETUP_PATHS.some((path) => currentPath.value === path || currentPath.value.startsWith(`${path}/`)),
);

const activeNanoid = computed(() => {
    try {
        const fromQuery = new URL(page.url, 'http://localhost').searchParams.get('business');
        if (fromQuery) {
            return fromQuery;
        }
    } catch {
        // ignore
    }

    const business = page.props.business as { nanoid?: string } | undefined;

    return business?.nanoid ?? null;
});

const hrefFor = (nanoid: string): string => {
    if (isOnSetupPage.value) {
        const url = new URL(page.url, 'http://localhost');
        url.searchParams.set('business', nanoid);

        return `${url.pathname}?${url.searchParams.toString()}`;
    }

    return profile.url({ query: { business: nanoid } });
};

const closeMobileSidebar = () => {
    if (isMobile.value) {
        setOpenMobile(false);
    }
};
</script>

<template>
    <SidebarGroup v-if="businesses.length > 0" class="px-2 py-0">
        <SidebarGroupLabel>Businesses</SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="business in businesses" :key="business.nanoid">
                <SidebarMenuButton
                    as-child
                    :is-active="activeNanoid === business.nanoid"
                    :tooltip="business.name"
                >
                    <Link :href="hrefFor(business.nanoid)" @click="closeMobileSidebar">
                        <Building2 />
                        <span class="truncate">{{ business.name }}</span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
