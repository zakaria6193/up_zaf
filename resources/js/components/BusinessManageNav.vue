<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Building2, Link as LinkIcon, Menu, QrCode } from 'lucide-vue-next';
import { computed } from 'vue';
import { index as profile } from '@/actions/App/Http/Controllers/Business/ProfileController';
import { index as menu } from '@/actions/App/Http/Controllers/Business/MenuController';
import { index as qrCode } from '@/actions/App/Http/Controllers/Business/QRCodeController';
import { index as links } from '@/actions/App/Http/Controllers/Business/LinkController';

const props = defineProps<{
    businessNanoid: string;
    active: 'profile' | 'menu' | 'qr' | 'links';
}>();

const query = computed(() => ({ business: props.businessNanoid }));

const items = computed(() => [
    {
        key: 'profile' as const,
        title: 'Profile',
        href: profile.url({ query: query.value }),
        icon: Building2,
    },
    {
        key: 'menu' as const,
        title: 'Menu',
        href: menu.url({ query: query.value }),
        icon: Menu,
    },
    {
        key: 'qr' as const,
        title: 'QR Code',
        href: qrCode.url({ query: query.value }),
        icon: QrCode,
    },
    {
        key: 'links' as const,
        title: 'Links',
        href: links.url({ query: query.value }),
        icon: LinkIcon,
    },
]);
</script>

<template>
    <nav
        class="bg-muted/50 flex flex-wrap gap-1 rounded-xl border p-1"
        aria-label="Business management"
    >
        <Link
            v-for="item in items"
            :key="item.key"
            :href="item.href"
            class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition-colors"
            :class="
                active === item.key
                    ? 'bg-background text-foreground shadow-sm'
                    : 'text-muted-foreground hover:bg-background/70 hover:text-foreground'
            "
        >
            <component :is="item.icon" class="h-4 w-4 shrink-0" />
            <span>{{ item.title }}</span>
        </Link>
    </nav>
</template>
