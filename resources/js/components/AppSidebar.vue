<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Building2, QrCode, Link as LinkIcon, Menu, Settings, LayoutGrid, Users, BarChart3 } from 'lucide-vue-next';
import AppLogo from '@/components/AppLogo.vue';
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
        title: 'Tableau de bord',
        href: '/adminos/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Entreprises',
        href: '/adminos/businesses',
        icon: Building2,
    },
    {
        title: 'Utilisateurs',
        href: '/adminos/users',
        icon: Users,
    },
    {
        title: 'Rapports',
        href: '/adminos/reports',
        icon: BarChart3,
    },
    {
        title: 'Paramètres',
        href: '/adminos/settings',
        icon: Settings,
    },
];

const businessNavItems: NavItem[] = [
    {
        title: 'Tableau de bord',
        href: '/business/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Mon Entreprise',
        href: '/business/profile',
        icon: Building2,
    },
    {
        title: 'Code QR',
        href: '/business/qr-code',
        icon: QrCode,
    },
    {
        title: 'Liens',
        href: '/business/links',
        icon: LinkIcon,
    },
    {
        title: 'Menu',
        href: '/business/menu',
        icon: Menu,
    },
    {
        title: 'Paramètres',
        href: '/business/settings',
        icon: Settings,
    },
];

const mainNavItems = computed(() => isAdmin.value ? adminNavItems : businessNavItems);
const dashboardHref = computed(() => isAdmin.value ? '/adminos/dashboard' : '/business/dashboard');
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
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
