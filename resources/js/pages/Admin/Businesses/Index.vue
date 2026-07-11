<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Switch } from '@/components/ui/switch';
import { Badge } from '@/components/ui/badge';
import QRCodeModal from '@/components/QRCodeModal.vue';
import ReviewCardModal from '@/components/ReviewCardModal.vue';
import CreateBusinessModal from '@/components/CreateBusinessModal.vue';
import DeleteConfirmDialog from '@/components/DeleteConfirmDialog.vue';
import { ref, watch } from 'vue';
import { show, edit, destroy } from '@/actions/App/Http/Controllers/Admin/BusinessController';
import { debounce } from 'lodash-es';
import { QrCode, Pencil, Trash2, ExternalLink, Star } from 'lucide-vue-next';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Tableau de bord', href: '/adminos/dashboard' },
            { title: 'Entreprises' },
        ],
    },
});

const props = defineProps<{
    businesses: {
        data: Array<{
            nanoid: string;
            name: string;
            address: string | null;
            color: string;
            logo: string | null;
            public_url: string;
            qr_code: string | null;
            is_active: boolean;
            created_at: string;
            total_views: number;
            views_this_week: number;
            growth_percentage: number;
        }>;
        links: {
            first: string | null;
            last: string | null;
            prev: string | null;
            next: string | null;
        };
        meta: {
            current_page: number;
            from: number | null;
            last_page: number;
            per_page: number;
            to: number | null;
            total: number;
        };
    };
    filters: {
        search?: string;
    };
    businessUsers: Array<{
        id: number;
        name: string;
        email: string;
    }>;
}>();

const search = ref(props.filters.search || '');
const showQRModal = ref(false);
const showReviewCardModal = ref(false);
const showCreateModal = ref(false);
const showDeleteDialog = ref(false);
const selectedBusiness = ref<typeof props.businesses.data[0] | null>(null);

const debouncedSearch = debounce((value: string) => {
    router.get(
        window.location.pathname,
        { search: value },
        {
            preserveState: true,
            replace: true,
        }
    );
}, 300);

watch(search, (value) => {
    debouncedSearch(value);
});

const openQRModal = (business: typeof props.businesses.data[0]) => {
    selectedBusiness.value = business;
    showQRModal.value = true;
};

const openReviewCardModal = (business: typeof props.businesses.data[0]) => {
    selectedBusiness.value = business;
    showReviewCardModal.value = true;
};

const openDeleteDialog = (business: typeof props.businesses.data[0]) => {
    selectedBusiness.value = business;
    showDeleteDialog.value = true;
};

const handleDelete = () => {
    if (selectedBusiness.value) {
        router.delete(destroy.url(selectedBusiness.value.nanoid), {
            onFinish: () => {
                showDeleteDialog.value = false;
                selectedBusiness.value = null;
            },
        });
    }
};

const toggleActive = (business: typeof props.businesses.data[0]) => {
    router.patch(`/adminos/businesses/${business.nanoid}/toggle-active`, {}, {
        preserveScroll: true,
    });
};

// Generate random mini chart data
const generateMiniChartData = () => {
    return Array.from({ length: 7 }, () => Math.floor(Math.random() * 100));
};
</script>

<template>
    <Head title="Entreprises" />

    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold">Entreprises</h1>
                <p class="text-sm text-muted-foreground">
                    Gérer vos entreprises et codes QR
                </p>
            </div>
            <Button @click="showCreateModal = true">
                Ajouter une entreprise
            </Button>
        </div>

        <!-- Search -->
        <div class="flex items-center gap-4">
            <Input
                v-model="search"
                type="search"
                placeholder="Rechercher des entreprises..."
                class="max-w-sm"
            />
        </div>

        <!-- Businesses List -->
        <div v-if="businesses.data.length > 0" class="space-y-3">
            <div
                v-for="business in businesses.data"
                :key="business.nanoid"
                class="group relative overflow-hidden rounded-lg border border-sidebar-border/70 bg-card transition-all hover:border-sidebar-border hover:shadow-md"
            >
                <div class="flex items-center gap-4 p-4">
                    <!-- Logo or Color Block -->
                    <Link :href="show.url(business.nanoid)" class="shrink-0">
                        <div
                            v-if="business.logo"
                            class="h-16 w-16 overflow-hidden rounded-lg ring-2 ring-sidebar-border/50"
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
                            class="h-16 w-16 rounded-lg ring-2 ring-sidebar-border/50"
                        />
                    </Link>

                    <!-- Business Info -->
                    <Link :href="show.url(business.nanoid)" class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <h3 class="font-semibold text-lg group-hover:text-primary transition-colors truncate">
                                {{ business.name }}
                            </h3>
                            <Badge :variant="business.is_active ? 'default' : 'secondary'" class="shrink-0">
                                {{ business.is_active ? 'Actif' : 'Inactif' }}
                            </Badge>
                        </div>
                        <p class="text-sm text-muted-foreground mb-1">
                            ID: {{ business.nanoid }} • Créé le {{ business.created_at }}
                        </p>
                        <p v-if="business.address" class="text-sm text-muted-foreground truncate">
                            {{ business.address }}
                        </p>
                    </Link>

                    <!-- Stats & Chart -->
                    <div class="hidden lg:flex items-center gap-6 shrink-0">
                        <!-- Quick Stats -->
                        <div class="text-right">
                            <div class="flex items-baseline gap-1 justify-end">
                                <span class="text-xl font-bold">{{ business.views_this_week }}</span>
                                <span class="text-xs text-muted-foreground">vues</span>
                            </div>
                            <div
                                class="text-xs font-medium"
                                :class="business.growth_percentage >= 0 ? 'text-green-600' : 'text-red-600'"
                            >
                                {{ business.growth_percentage >= 0 ? '+' : '' }}{{ business.growth_percentage.toFixed(1) }}%
                            </div>
                        </div>

                        <!-- Mini Chart -->
                        <div class="flex items-end gap-0.5 h-10">
                            <div
                                v-for="(value, index) in generateMiniChartData()"
                                :key="index"
                                class="w-1 rounded-t bg-primary/70"
                                :style="{ height: `${value}%` }"
                            />
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center gap-1 shrink-0">
                        <!-- QR Code Button -->
                        <Button
                            size="icon"
                            variant="ghost"
                            @click.prevent="openQRModal(business)"
                            title="Voir le QR Code"
                        >
                            <QrCode class="h-4 w-4" />
                        </Button>

                        <!-- Review Card Button -->
                        <Button
                            size="icon"
                            variant="ghost"
                            @click.prevent="openReviewCardModal(business)"
                            title="Générer carte de visite"
                        >
                            <Star class="h-4 w-4" />
                        </Button>

                        <!-- Edit Button -->
                        <Button
                            size="icon"
                            variant="ghost"
                            as-child
                            title="Modifier"
                        >
                            <Link :href="edit.url(business.nanoid)">
                                <Pencil class="h-4 w-4" />
                            </Link>
                        </Button>

                        <!-- View Public Page -->
                        <Button
                            size="icon"
                            variant="ghost"
                            as="a"
                            :href="business.public_url"
                            target="_blank"
                            title="Voir la page publique"
                        >
                            <ExternalLink class="h-4 w-4" />
                        </Button>

                        <!-- Active Toggle -->
                        <div class="px-2">
                            <Switch
                                :checked="business.is_active"
                                @update:checked="() => toggleActive(business)"
                                title="Activer/Désactiver"
                            />
                        </div>

                        <!-- Delete Button -->
                        <Button
                            size="icon"
                            variant="ghost"
                            class="text-destructive hover:text-destructive hover:bg-destructive/10"
                            @click.prevent="openDeleteDialog(business)"
                            title="Supprimer"
                        >
                            <Trash2 class="h-4 w-4" />
                        </Button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="flex min-h-[400px] flex-col items-center justify-center rounded-lg border border-dashed border-sidebar-border/70 p-8 text-center">
            <svg
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="1.5"
                stroke="currentColor"
                class="mb-4 h-12 w-12 text-muted-foreground"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72m-13.5 8.65h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .415.336.75.75.75z"
                />
            </svg>
            <h3 class="mb-2 text-lg font-semibold">Aucune entreprise pour le moment</h3>
            <p class="mb-4 text-sm text-muted-foreground">
                Commencez par créer votre première entreprise
            </p>
            <Button @click="showCreateModal = true">
                Ajouter votre première entreprise
            </Button>
        </div>

        <!-- Pagination -->
        <div
            v-if="businesses.data.length > 0 && businesses.meta?.last_page > 1"
            class="flex items-center justify-between"
        >
            <p class="text-sm text-muted-foreground">
                Affichage de {{ businesses.meta?.from }} à {{ businesses.meta?.to }} sur
                {{ businesses.meta?.total }} entreprises
            </p>
            <div class="flex gap-2">
                <Button
                    v-if="businesses.links.prev"
                    as-child
                    variant="outline"
                    size="sm"
                >
                    <Link :href="businesses.links.prev">Précédent</Link>
                </Button>
                <Button
                    v-if="businesses.links.next"
                    as-child
                    variant="outline"
                    size="sm"
                >
                    <Link :href="businesses.links.next">Suivant</Link>
                </Button>
            </div>
        </div>

        <!-- QR Code Modal -->
        <QRCodeModal
            v-if="selectedBusiness"
            v-model:open="showQRModal"
            :business-name="selectedBusiness.name"
            :qr-code-url="selectedBusiness.qr_code!"
            :public-url="selectedBusiness.public_url"
            :nanoid="selectedBusiness.nanoid"
        />

        <!-- Review Card Modal -->
        <ReviewCardModal
            v-if="selectedBusiness"
            v-model:open="showReviewCardModal"
            :business-name="selectedBusiness.name"
            :business-color="selectedBusiness.color"
            :qr-code-url="selectedBusiness.qr_code!"
        />

        <!-- Delete Confirmation Dialog -->
        <DeleteConfirmDialog
            v-if="selectedBusiness"
            v-model:open="showDeleteDialog"
            title="Supprimer l'entreprise"
            description="Êtes-vous sûr de vouloir supprimer"
            :item-name="selectedBusiness.name"
            @confirm="handleDelete"
        />

        <!-- Create Business Modal -->
        <CreateBusinessModal v-model:open="showCreateModal" :business-users="businessUsers" />
    </div>
</template>
