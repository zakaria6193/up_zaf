<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import { Toaster } from '@/components/ui/sonner';
import { toast } from 'vue-sonner';
import type { BreadcrumbItem } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { watch } from 'vue';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage();

// Watch for flash messages and display them as toasts
watch(
    () => page.props.flash,
    (flash) => {
        if (flash?.success) {
            toast.success(flash.success as string);
        }
        if (flash?.error) {
            toast.error(flash.error as string);
        }
        if (flash?.info) {
            toast.info(flash.info as string);
        }
    },
    { deep: true }
);
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar />
        <AppContent variant="sidebar" class="overflow-x-hidden">
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
            <slot />
        </AppContent>
        <Toaster />
    </AppShell>
</template>
