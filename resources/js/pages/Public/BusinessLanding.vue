<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import {
    ExternalLink,
    MapPin,
    Instagram,
    MessageCircle,
    Star,
    Menu,
    Globe,
    ArrowLeft,
    Share2,
    ChevronRight,
} from 'lucide-vue-next';
import MenuPanier from '@/components/Public/MenuPanier.vue';
import MenuItemAddControl from '@/components/Public/MenuItemAddControl.vue';
import { useMenuPanier } from '@/composables/useMenuPanier';
import { formatMoney } from '@/lib/money';

defineOptions({
    layout: false,
});

type MenuItem = {
    id: number;
    name: string;
    description: string | null;
    price: number;
    image: string | null;
};

type Category = {
    id: number;
    name: string;
    order: number;
    items: MenuItem[];
    subcategories: Array<{
        id: number;
        name: string;
        items: MenuItem[];
    }>;
};

const props = defineProps<{
    business: {
        nanoid: string;
        name: string;
        address: string | null;
        logo: string | null;
        color: string;
        currency: string;
        seo_title: string | null;
        seo_description: string | null;
        seo_keywords: string | null;
        lat: number | null;
        lng: number | null;
    };
    links: Array<{
        id: number;
        type: string;
        label: string;
        url: string;
    }>;
    hasMenu: boolean;
    categories: Category[];
}>();

const {
    list: panierList,
    isOpen: panierOpen,
    totalQuantity,
    totalPrice,
    quantityOf,
    addItem,
    increment,
    decrement,
    removeItem,
    clear: clearPanier,
} = useMenuPanier(props.business.nanoid);

const showMenuPage = ref(false);
const activeCategory = ref<number | null>(null);
const isJumping = ref(false);
let observer: IntersectionObserver | null = null;

const brandColor = computed(() => props.business.color || '#0f766e');

const sortedCategories = computed(() =>
    [...props.categories].sort((a, b) => a.order - b.order),
);

const openMenu = async () => {
    showMenuPage.value = true;
    activeCategory.value = sortedCategories.value[0]?.id ?? null;
    await nextTick();
    setupObserver();
};

const closeMenu = () => {
    teardownObserver();
    showMenuPage.value = false;
    activeCategory.value = null;
};

const selectCategory = async (categoryId: number) => {
    activeCategory.value = categoryId;
    isJumping.value = true;
    await nextTick();
    const el = document.getElementById(`category-${categoryId}`);
    el?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    window.setTimeout(() => {
        isJumping.value = false;
    }, 700);
};

const setupObserver = () => {
    teardownObserver();

    if (typeof window === 'undefined' || sortedCategories.value.length === 0) {
        return;
    }

    observer = new IntersectionObserver(
        (entries) => {
            if (isJumping.value) {
                return;
            }

            const visible = entries
                .filter((entry) => entry.isIntersecting)
                .sort((a, b) => b.intersectionRatio - a.intersectionRatio);

            const top = visible[0];
            if (!top?.target.id.startsWith('category-')) {
                return;
            }

            activeCategory.value = Number(top.target.id.replace('category-', ''));
        },
        {
            root: null,
            rootMargin: '-120px 0px -55% 0px',
            threshold: [0.15, 0.35, 0.6],
        },
    );

    sortedCategories.value.forEach((category) => {
        const el = document.getElementById(`category-${category.id}`);
        if (el) {
            observer?.observe(el);
        }
    });
};

const teardownObserver = () => {
    observer?.disconnect();
    observer = null;
};

watch(showMenuPage, async (open) => {
    if (open) {
        await nextTick();
        setupObserver();
    } else {
        teardownObserver();
    }
});

onMounted(() => {
    if (showMenuPage.value) {
        setupObserver();
    }
});

onBeforeUnmount(() => {
    teardownObserver();
});

const openMaps = () => {
    if (props.business.lat && props.business.lng) {
        window.open(
            `https://www.google.com/maps/dir/?api=1&destination=${props.business.lat},${props.business.lng}`,
            '_blank',
        );
    } else if (props.business.address) {
        window.open(
            `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(props.business.address)}`,
            '_blank',
        );
    }
};

const getLinkIcon = (type: string) => {
    switch (type) {
        case 'google_reviews':
            return Star;
        case 'google_maps':
            return MapPin;
        case 'menu':
            return Menu;
        case 'instagram':
            return Instagram;
        case 'whatsapp':
            return MessageCircle;
        case 'website':
            return Globe;
        default:
            return ExternalLink;
    }
};

const formatPrice = (price: number) => formatMoney(price, props.business.currency || 'MAD');

const shareMenu = async () => {
    const url = window.location.href;
    if (navigator.share) {
        try {
            await navigator.share({
                title: `${props.business.name} - Menu`,
                text: `Découvrez le menu de ${props.business.name}`,
                url,
            });
        } catch {
            // cancelled
        }
    } else if (navigator.clipboard?.writeText) {
        try {
            await navigator.clipboard.writeText(url);
            alert('Lien copié dans le presse-papiers!');
        } catch {
            alert('Impossible de copier le lien. Veuillez le copier manuellement: ' + url);
        }
    } else {
        alert('Copiez ce lien: ' + url);
    }
};
</script>

<template>
    <Head>
        <title>{{ business.seo_title || business.name }}</title>
        <meta name="description" :content="business.seo_description || `Visit ${business.name}`" />
        <meta name="keywords" :content="business.seo_keywords || business.name" />
        <meta property="og:type" content="business.business" />
        <meta property="og:title" :content="business.seo_title || business.name" />
        <meta property="og:description" :content="business.seo_description || `Visit ${business.name}`" />
        <meta property="og:image" :content="business.logo || ''" />
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" :content="business.seo_title || business.name" />
        <meta name="twitter:description" :content="business.seo_description || `Visit ${business.name}`" />
        <meta name="twitter:image" :content="business.logo || ''" />
    </Head>

    <!-- ========== LANDING (link-in-bio hub) ========== -->
    <div
        v-if="!showMenuPage"
        class="min-h-screen bg-[#f7f7f5] text-stone-900"
        :style="{ '--brand-color': brandColor }"
    >
        <div
            class="pointer-events-none absolute inset-x-0 top-0 h-72 opacity-80"
            :style="{
                background: `radial-gradient(90% 70% at 50% -10%, ${brandColor}22 0%, transparent 70%)`,
            }"
        ></div>

        <div class="relative mx-auto flex min-h-screen w-full max-w-md flex-col px-5 pb-10 pt-12">
            <header class="mb-8 flex flex-col items-center text-center">
                <div
                    v-if="business.logo"
                    class="mb-5 h-24 w-24 overflow-hidden rounded-full bg-white shadow-[0_10px_30px_-12px_rgba(0,0,0,0.35)] ring-4 ring-white"
                >
                    <img
                        :src="business.logo"
                        :alt="business.name"
                        class="h-full w-full object-cover"
                        loading="eager"
                        decoding="async"
                    />
                </div>
                <div
                    v-else
                    class="mb-5 flex h-24 w-24 items-center justify-center rounded-full text-3xl font-semibold text-white shadow-[0_10px_30px_-12px_rgba(0,0,0,0.35)] ring-4 ring-white"
                    :style="{ backgroundColor: brandColor }"
                >
                    {{ business.name.charAt(0).toUpperCase() }}
                </div>

                <h1 class="text-[1.75rem] font-semibold tracking-tight text-stone-900">
                    {{ business.name }}
                </h1>

                <button
                    v-if="business.address"
                    type="button"
                    class="mt-3 inline-flex max-w-full items-center gap-1.5 rounded-full bg-white/80 px-3 py-1.5 text-sm text-stone-600 shadow-sm ring-1 ring-stone-200/80 backdrop-blur transition hover:text-stone-900"
                    @click="openMaps"
                >
                    <MapPin class="h-3.5 w-3.5 shrink-0" :style="{ color: brandColor }" />
                    <span class="truncate">{{ business.address }}</span>
                </button>
            </header>

            <div class="space-y-3">
                <!-- Primary CTA: Menu -->
                <button
                    v-if="hasMenu"
                    type="button"
                    class="group flex min-h-14 w-full items-center justify-between rounded-2xl px-5 py-4 text-left text-white shadow-[0_12px_28px_-14px_rgba(0,0,0,0.45)] transition active:scale-[0.99]"
                    :style="{ backgroundColor: brandColor }"
                    @click="openMenu"
                >
                    <span class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/15">
                            <Menu class="h-5 w-5" />
                        </span>
                        <span>
                            <span class="block text-base font-semibold">Voir le menu</span>
                            <span class="block text-xs font-medium text-white/80">Parcourir les plats</span>
                        </span>
                    </span>
                    <ChevronRight class="h-5 w-5 opacity-80 transition group-hover:translate-x-0.5" />
                </button>

                <!-- Secondary links -->
                <a
                    v-for="link in links"
                    :key="link.id"
                    :href="link.url"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="flex min-h-14 w-full items-center justify-between rounded-2xl bg-white px-4 py-3.5 shadow-sm ring-1 ring-stone-200/70 transition hover:bg-stone-50 active:scale-[0.99]"
                >
                    <span class="flex items-center gap-3">
                        <span
                            class="flex h-10 w-10 items-center justify-center rounded-xl"
                            :style="{ backgroundColor: `${brandColor}14`, color: brandColor }"
                        >
                            <component :is="getLinkIcon(link.type)" class="h-5 w-5" />
                        </span>
                        <span class="font-medium text-stone-800">{{ link.label }}</span>
                    </span>
                    <ExternalLink class="h-4 w-4 text-stone-300" />
                </a>
            </div>

            <p class="mt-auto pt-10 text-center text-xs text-stone-400">
                Powered by <span class="font-medium text-stone-500">UP1</span>
            </p>
        </div>
    </div>

    <!-- ========== MENU (continuous scroll + sticky pills) ========== -->
    <div
        v-else
        class="min-h-screen bg-[#f7f7f5] text-stone-900"
        :style="{ '--brand-color': brandColor }"
    >
        <!-- Top bar -->
        <div class="sticky top-0 z-30 border-b border-stone-200/70 bg-[#f7f7f5]/90 backdrop-blur-xl">
            <div class="mx-auto flex max-w-2xl items-center justify-between gap-2 px-4 py-3">
                <button
                    type="button"
                    class="inline-flex min-h-11 min-w-11 items-center justify-center gap-1 rounded-full text-stone-600 transition hover:bg-white hover:text-stone-900"
                    @click="closeMenu"
                >
                    <ArrowLeft class="h-5 w-5" />
                    <span class="sr-only sm:not-sr-only sm:pr-2 sm:text-sm sm:font-medium">Retour</span>
                </button>

                <div class="flex min-w-0 items-center gap-2">
                    <div
                        v-if="business.logo"
                        class="h-8 w-8 shrink-0 overflow-hidden rounded-full bg-white ring-1 ring-stone-200"
                    >
                        <img :src="business.logo" :alt="business.name" class="h-full w-full object-cover" loading="lazy" />
                    </div>
                    <p class="truncate text-sm font-semibold text-stone-900">{{ business.name }}</p>
                </div>

                <button
                    type="button"
                    class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-full text-stone-600 transition hover:bg-white hover:text-stone-900"
                    @click="shareMenu"
                >
                    <Share2 class="h-5 w-5" />
                    <span class="sr-only">Partager</span>
                </button>
            </div>

            <!-- Sticky category pills — strongest UX win from research -->
            <div
                v-if="sortedCategories.length > 0"
                class="mx-auto flex max-w-2xl gap-2 overflow-x-auto px-4 pb-3 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
            >
                <button
                    v-for="category in sortedCategories"
                    :key="category.id"
                    type="button"
                    class="shrink-0 rounded-full px-4 py-2 text-sm font-medium transition"
                    :class="
                        activeCategory === category.id
                            ? 'text-white shadow-sm'
                            : 'bg-white text-stone-600 ring-1 ring-stone-200 hover:text-stone-900'
                    "
                    :style="activeCategory === category.id ? { backgroundColor: brandColor } : undefined"
                    @click="selectCategory(category.id)"
                >
                    {{ category.name }}
                </button>
            </div>
        </div>

        <div class="mx-auto max-w-2xl px-4 pb-32 pt-5">
            <div v-if="sortedCategories.length === 0" class="rounded-3xl bg-white p-10 text-center shadow-sm ring-1 ring-stone-200/70">
                <p class="text-lg font-semibold text-stone-900">Aucun menu disponible</p>
                <p class="mt-2 text-sm text-stone-500">Le menu est en cours de mise à jour.</p>
            </div>

            <section
                v-for="category in sortedCategories"
                :id="`category-${category.id}`"
                :key="category.id"
                class="mb-8 scroll-mt-36"
            >
                <div class="mb-3 flex items-center gap-2 px-1">
                    <span class="h-5 w-1 rounded-full" :style="{ backgroundColor: brandColor }"></span>
                    <h2 class="text-lg font-semibold tracking-tight text-stone-900">{{ category.name }}</h2>
                </div>

                <div class="overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-stone-200/70">
                    <!-- Direct items -->
                    <div v-if="category.items.length > 0" class="divide-y divide-stone-100">
                        <article
                            v-for="item in category.items"
                            :key="item.id"
                            class="flex gap-3 p-4"
                        >
                            <div
                                v-if="item.image"
                                class="h-20 w-20 shrink-0 overflow-hidden rounded-2xl bg-stone-100"
                            >
                                <img :src="item.image" :alt="item.name" class="h-full w-full object-cover" loading="lazy" />
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-3">
                                    <h3 class="text-base font-semibold leading-snug text-stone-900">{{ item.name }}</h3>
                                    <span class="shrink-0 text-sm font-semibold tabular-nums" :style="{ color: brandColor }">
                                        {{ formatPrice(item.price) }}
                                    </span>
                                </div>
                                <p
                                    v-if="item.description"
                                    class="mt-1 line-clamp-2 text-sm leading-relaxed text-stone-500"
                                >
                                    {{ item.description }}
                                </p>
                            </div>

                            <div class="flex items-center self-center">
                                <MenuItemAddControl
                                    :quantity="quantityOf(item.id)"
                                    :brand-color="brandColor"
                                    @add="addItem(item)"
                                    @increment="increment(item.id)"
                                    @decrement="decrement(item.id)"
                                />
                            </div>
                        </article>
                    </div>

                    <!-- Subcategories -->
                    <div v-for="subcategory in category.subcategories" :key="subcategory.id">
                        <div class="bg-stone-50 px-4 py-2 text-xs font-semibold uppercase tracking-wider text-stone-500">
                            {{ subcategory.name }}
                        </div>
                        <div class="divide-y divide-stone-100">
                            <article
                                v-for="item in subcategory.items"
                                :key="item.id"
                                class="flex gap-3 p-4"
                            >
                                <div
                                    v-if="item.image"
                                    class="h-20 w-20 shrink-0 overflow-hidden rounded-2xl bg-stone-100"
                                >
                                    <img :src="item.image" :alt="item.name" class="h-full w-full object-cover" loading="lazy" />
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="flex items-start justify-between gap-3">
                                        <h3 class="text-base font-semibold leading-snug text-stone-900">{{ item.name }}</h3>
                                        <span class="shrink-0 text-sm font-semibold tabular-nums" :style="{ color: brandColor }">
                                            {{ formatPrice(item.price) }}
                                        </span>
                                    </div>
                                    <p
                                        v-if="item.description"
                                        class="mt-1 line-clamp-2 text-sm leading-relaxed text-stone-500"
                                    >
                                        {{ item.description }}
                                    </p>
                                </div>

                                <div class="flex items-center self-center">
                                    <MenuItemAddControl
                                        :quantity="quantityOf(item.id)"
                                        :brand-color="brandColor"
                                        @add="addItem(item)"
                                        @increment="increment(item.id)"
                                        @decrement="decrement(item.id)"
                                    />
                                </div>
                            </article>

                            <div
                                v-if="subcategory.items.length === 0"
                                class="p-4 text-center text-sm text-stone-400"
                            >
                                Aucun article
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="category.items.length === 0 && category.subcategories.length === 0"
                        class="p-8 text-center text-sm text-stone-400"
                    >
                        Aucun article dans cette catégorie
                    </div>
                </div>
            </section>

            <p class="pt-4 text-center text-xs text-stone-400">
                Powered by <span class="font-medium text-stone-500">UP1</span>
            </p>
        </div>

        <MenuPanier
            v-model:open="panierOpen"
            :brand-color="brandColor"
            :currency="business.currency"
            :items="panierList"
            :total-quantity="totalQuantity"
            :total-price="totalPrice"
            @increment="increment"
            @decrement="decrement"
            @remove="removeItem"
            @clear="clearPanier"
        />
    </div>
</template>
