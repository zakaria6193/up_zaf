<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { LayoutGrid, Users, BarChart3, Building2, Settings } from 'lucide-vue-next';
import AppLogo from '@/components/AppLogo.vue';
import NavBusinesses from '@/components/NavBusinesses.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import type { NavItem } from '@/types';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);
const isAdmin = computed(() => user.value?.is_admin);

const adminNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/adminos/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Businesses',
        href: '/adminos/businesses',
        icon: Building2,
    },
    {
        title: 'Users',
        href: '/adminos/users',
        icon: Users,
    },
    {
        title: 'Reports',
        href: '/adminos/reports',
        icon: BarChart3,
    },
    {
        title: 'Settings',
        href: '/adminos/settings',
        icon: Settings,
    },
];

/**
 * Global account nav only. Menu, QR, links, and profile stay inside each
 * business's setup screens (opened from the dashboard), not the sidebar.
 */
const businessNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/business/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Account',
        href: '/business/settings',
        icon: Settings,
    },
];

const mainNavItems = computed(() => (isAdmin.value ? adminNavItems : businessNavItems));
const navGroupLabel = computed(() => (isAdmin.value ? 'Admin' : 'Main'));
const dashboardHref = computed(() => (isAdmin.value ? '/adminos/dashboard' : '/business/dashboard'));
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboardHref">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" :label="navGroupLabel" />
            <NavBusinesses v-if="!isAdmin" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
