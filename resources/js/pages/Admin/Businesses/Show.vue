<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Tabs, TabsList, TabsTrigger, TabsContent } from '@/components/ui/tabs';
import { Edit, ExternalLink, QrCode, Trash2, Copy, Check } from 'lucide-vue-next';
import { edit, destroy, index } from '@/actions/App/Http/Controllers/Admin/BusinessController';
import { index as linksIndex } from '@/actions/App/Http/Controllers/Admin/BusinessLinkController';
import DeleteConfirmDialog from '@/components/DeleteConfirmDialog.vue';
import { formatMoney } from '@/lib/money';
import { ref, onMounted, watch } from 'vue';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/adminos/dashboard' },
            { title: 'Businesses', href: index.url() },
            { title: 'Business details' },
        ],
    },
});

const props = defineProps<{
    business: {
        nanoid: string;
        name: string;
        address: string | null;
        lat: number | null;
        lng: number | null;
        color: string;
        currency: string;
        logo: string | null;
        public_url: string;
        qr_code: string | null;
        seo_title: string | null;
        seo_description: string | null;
        seo_keywords: string | null;
        created_at: string;
        links: Array<{
            id: number;
            type: string;
            label: string;
            url: string;
            is_active: boolean;
            order: number;
        }>;
        menu_categories: Array<{
            id: number;
            name: string;
            order: number;
            items: Array<{
                id: number;
                name: string;
                description: string | null;
                price: string;
                image: string | null;
                order: number;
            }>;
        }>;
    };
}>();

const copied = ref(false);
const showDeleteDialog = ref(false);
const activeTab = ref('info');

const copyToClipboard = async (text: string) => {
    await navigator.clipboard.writeText(text);
    copied.value = true;
    setTimeout(() => {
        copied.value = false;
    }, 2000);
};

const handleDelete = () => {
    router.delete(destroy.url(props.business.nanoid), {
        onFinish: () => {
            showDeleteDialog.value = false;
        },
    });
};

const downloadQR = () => {
    if (props.business.qr_code) {
        const link = document.createElement('a');
        link.href = props.business.qr_code;
        link.download = `${props.business.nanoid}-qr-code.png`;
        link.click();
    }
};

// Hash-based tab navigation
const getHashTab = () => {
    const hash = window.location.hash.slice(1);
    return ['info', 'design', 'links', 'menu'].includes(hash) ? hash : 'info';
};

onMounted(() => {
    activeTab.value = getHashTab();

    window.addEventListener('hashchange', () => {
        activeTab.value = getHashTab();
    });
});

watch(activeTab, (newTab) => {
    window.location.hash = newTab;
});
</script>

<template>
    <Head :title="business.name" />

    <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
        <!-- Header -->
        <div class="flex items-start justify-between">
            <div class="flex items-center gap-4">
                <div
                    v-if="business.logo"
                    class="h-16 w-16 overflow-hidden rounded-lg"
                >
                    <img
                        :src="business.logo"
                        :alt="business.name"
                        class="h-full w-full object-cover"
                    />
                </div>
                <div
                    v-else
                    :style="{ backgroundColor: business.color }"
                    class="h-16 w-16 rounded-lg"
                />
                <div>
                    <h1 class="text-3xl font-bold">{{ business.name }}</h1>
                    <p class="text-sm text-muted-foreground">
                        ID: {{ business.nanoid }} • Created {{ business.created_at }}
                    </p>
                </div>
            </div>
            <div class="flex gap-2">
                <Button as-child variant="outline">
                    <Link :href="edit.url(business.nanoid)">
                        <Edit class="mr-2 h-4 w-4" />
                        Edit
                    </Link>
                </Button>
                <Button variant="destructive" @click="showDeleteDialog = true">
                    <Trash2 class="mr-2 h-4 w-4" />
                    Delete
                </Button>
            </div>
        </div>

        <!-- Tabs Navigation -->
        <Tabs v-model="activeTab" class="w-full">
            <TabsList class="grid w-full grid-cols-4">
                <TabsTrigger value="info">Information</TabsTrigger>
                <TabsTrigger value="design">Design</TabsTrigger>
                <TabsTrigger value="links">Links</TabsTrigger>
                <TabsTrigger value="menu">Menu</TabsTrigger>
            </TabsList>

            <!-- Info Tab -->
            <TabsContent value="info" class="space-y-6">
                <div class="grid gap-6 md:grid-cols-2">
                    <!-- QR Code Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <QrCode class="h-5 w-5" />
                                QR Code
                            </CardTitle>
                            <CardDescription>Scan to visit the public page</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div v-if="business.qr_code" class="flex justify-center">
                                <img
                                    :src="business.qr_code"
                                    :alt="`QR Code for ${business.name}`"
                                    class="h-48 w-48 rounded-lg border"
                                />
                            </div>
                            <div class="space-y-2">
                                <Button @click="downloadQR" class="w-full" variant="outline">
                                    Download QR Code
                                </Button>
                                <Button as-child class="w-full" variant="outline">
                                    <a :href="business.public_url" target="_blank">
                                        <ExternalLink class="mr-2 h-4 w-4" />
                                        View public page
                                    </a>
                                </Button>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Business Info Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Business information</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div>
                                <label class="text-sm font-medium">Public URL</label>
                                <div class="mt-1 flex gap-2">
                                    <code class="flex-1 rounded bg-muted px-3 py-2 text-sm">
                                        {{ business.public_url }}
                                    </code>
                                    <Button
                                        size="sm"
                                        variant="outline"
                                        @click="copyToClipboard(business.public_url)"
                                    >
                                        <Check v-if="copied" class="h-4 w-4" />
                                        <Copy v-else class="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>

                            <div v-if="business.address">
                                <label class="text-sm font-medium">Address</label>
                                <p class="mt-1 text-sm text-muted-foreground">
                                    {{ business.address }}
                                </p>
                            </div>

                            <div v-if="business.lat && business.lng">
                                <label class="text-sm font-medium">Coordinates</label>
                                <p class="mt-1 text-sm text-muted-foreground">
                                    {{ business.lat }}, {{ business.lng }}
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </TabsContent>

            <!-- Design Tab -->
            <TabsContent value="design" class="space-y-6">
                <div class="grid gap-6 md:grid-cols-2">
                    <!-- Branding Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Brand identity</CardTitle>
                            <CardDescription>Logo and brand color</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div>
                                <label class="text-sm font-medium">Logo</label>
                                <div class="mt-2">
                                    <div
                                        v-if="business.logo"
                                        class="h-24 w-24 overflow-hidden rounded-lg border"
                                    >
                                        <img
                                            :src="business.logo"
                                            :alt="business.name"
                                            class="h-full w-full object-cover"
                                        />
                                    </div>
                                    <p v-else class="text-sm text-muted-foreground">No logo</p>
                                </div>
                            </div>

                            <div>
                                <label class="text-sm font-medium">Brand color</label>
                                <div class="mt-2 flex items-center gap-3">
                                    <div
                                        :style="{ backgroundColor: business.color }"
                                        class="h-10 w-10 rounded-lg border"
                                    />
                                    <code class="text-sm">{{ business.color }}</code>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- SEO Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle>SEO</CardTitle>
                            <CardDescription>Metadata for search engines</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <div v-if="business.seo_title">
                                <label class="text-sm font-medium">Title</label>
                                <p class="mt-1 text-sm text-muted-foreground">{{ business.seo_title }}</p>
                            </div>
                            <div v-if="business.seo_description">
                                <label class="text-sm font-medium">Description</label>
                                <p class="mt-1 text-sm text-muted-foreground">{{ business.seo_description }}</p>
                            </div>
                            <div v-if="business.seo_keywords">
                                <label class="text-sm font-medium">Keywords</label>
                                <p class="mt-1 text-sm text-muted-foreground">{{ business.seo_keywords }}</p>
                            </div>
                            <p v-if="!business.seo_title && !business.seo_description && !business.seo_keywords" class="text-sm text-muted-foreground">
                                No SEO metadata configured
                            </p>
                        </CardContent>
                    </Card>
                </div>
            </TabsContent>

            <!-- Links Tab -->
            <TabsContent value="links" class="space-y-6">
                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div>
                                <CardTitle>Links ({{ business.links.length }})</CardTitle>
                                <CardDescription>Public links for this business</CardDescription>
                            </div>
                            <Button as-child size="sm" variant="outline">
                                <Link :href="linksIndex.url(business.nanoid)">
                                    Manage links
                                </Link>
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div v-if="business.links.length > 0" class="space-y-2">
                            <div
                                v-for="link in business.links"
                                :key="link.id"
                                class="flex items-center justify-between rounded-lg border p-3"
                            >
                                <div class="flex items-center gap-3">
                                    <Badge :variant="link.is_active ? 'default' : 'secondary'">
                                        {{ link.type.replace('_', ' ') }}
                                    </Badge>
                                    <div>
                                        <p class="font-medium">{{ link.label }}</p>
                                        <p class="text-xs text-muted-foreground">{{ link.url }}</p>
                                    </div>
                                </div>
                                <Badge v-if="!link.is_active" variant="outline">Inactive</Badge>
                            </div>
                        </div>
                        <p v-else class="text-center text-sm text-muted-foreground py-8">
                            No links added
                        </p>
                    </CardContent>
                </Card>
            </TabsContent>

            <!-- Menu Tab -->
            <TabsContent value="menu" class="space-y-6">
                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div>
                                <CardTitle>Menu ({{ business.menu_categories.length }} categories)</CardTitle>
                                <CardDescription>Menu categories and items</CardDescription>
                            </div>
                            <Button as-child size="sm" variant="outline">
                                <Link :href="`/adminos/businesses/${business.nanoid}/menu`">
                                    Manage menu
                                </Link>
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div v-if="business.menu_categories.length > 0" class="space-y-4">
                            <div
                                v-for="category in business.menu_categories"
                                :key="category.id"
                                class="rounded-lg border p-4"
                            >
                                <h3 class="mb-3 font-semibold">
                                    {{ category.name }}
                                    <span class="ml-2 text-sm font-normal text-muted-foreground">
                                        ({{ category.items.length }} items)
                                    </span>
                                </h3>
                                <div class="space-y-2">
                                    <div
                                        v-for="item in category.items"
                                        :key="item.id"
                                        class="flex items-start justify-between"
                                    >
                                        <div class="flex-1">
                                            <p class="font-medium">{{ item.name }}</p>
                                            <p v-if="item.description" class="text-sm text-muted-foreground">
                                                {{ item.description }}
                                            </p>
                                        </div>
                                        <p class="font-semibold">{{ formatMoney(item.price, business.currency) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p v-else class="text-center text-sm text-muted-foreground py-8">
                            No menu items
                        </p>
                    </CardContent>
                </Card>
            </TabsContent>
        </Tabs>

        <!-- Delete Confirmation Dialog -->
        <DeleteConfirmDialog
            v-model:open="showDeleteDialog"
            title="Delete business"
            description="Are you sure you want to delete"
            :item-name="business.name"
            @confirm="handleDelete"
        />
    </div>
</template>
