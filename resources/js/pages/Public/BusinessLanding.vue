<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { ExternalLink, MapPin, Instagram, MessageCircle, Star, Menu, Globe, ArrowLeft, Share2 } from 'lucide-vue-next';

defineOptions({
    layout: false, // No layout - standalone public page
});

const props = defineProps<{
    business: {
        nanoid: string;
        name: string;
        address: string | null;
        logo: string | null;
        color: string;
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
    categories: Array<{
        id: number;
        name: string;
        order: number;
        items: Array<{
            id: number;
            name: string;
            description: string | null;
            price: number;
            image: string | null;
        }>;
        subcategories: Array<{
            id: number;
            name: string;
            items: Array<{
                id: number;
                name: string;
                description: string | null;
                price: number;
                image: string | null;
            }>;
        }>;
    }>;
}>();

// Menu page view toggle
const showMenuPage = ref(false);
const menuView = ref<'categories' | 'items'>('categories'); // categories selection or items view
const activeCategory = ref<number | null>(null);

const openMenu = () => {
    showMenuPage.value = true;
    menuView.value = 'categories'; // Start with category selection
    activeCategory.value = null;
};

const closeMenu = () => {
    showMenuPage.value = false;
    menuView.value = 'categories';
    activeCategory.value = null;
};

const selectCategory = (categoryId: number) => {
    activeCategory.value = categoryId;
    menuView.value = 'items';
};

const activeCategoryData = computed(() => {
    return props.categories.find(cat => cat.id === activeCategory.value);
});

// Open Google Maps for directions
const openMaps = () => {
    if (props.business.lat && props.business.lng) {
        window.open(`https://www.google.com/maps/dir/?api=1&destination=${props.business.lat},${props.business.lng}`, '_blank');
    } else if (props.business.address) {
        window.open(`https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(props.business.address)}`, '_blank');
    }
};

// Get icon for link type
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

// Apply brand color as CSS variable
const brandColor = computed(() => props.business.color || '#3b82f6');

// Format price in MAD
const formatPrice = (price: number) => {
    return new Intl.NumberFormat('fr-MA', {
        style: 'currency',
        currency: 'MAD',
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    }).format(price);
};

// Share menu
const shareMenu = async () => {
    const url = window.location.href;
    if (navigator.share) {
        try {
            await navigator.share({
                title: `${props.business.name} - Menu`,
                text: `Check out the menu at ${props.business.name}`,
                url: url,
            });
        } catch (err) {
            console.log('Share cancelled or failed');
        }
    } else if (navigator.clipboard && navigator.clipboard.writeText) {
        // Fallback: copy to clipboard if available
        try {
            await navigator.clipboard.writeText(url);
            alert('Lien copié dans le presse-papiers!');
        } catch (err) {
            console.error('Failed to copy to clipboard:', err);
            alert('Impossible de copier le lien. Veuillez le copier manuellement: ' + url);
        }
    } else {
        // Final fallback: show URL to copy manually
        alert('Copiez ce lien: ' + url);
    }
};
</script>

<template>
    <Head>
        <title>{{ business.seo_title || business.name }}</title>
        <meta name="description" :content="business.seo_description || `Visit ${business.name}`" />
        <meta name="keywords" :content="business.seo_keywords || business.name" />

        <!-- Open Graph / Facebook -->
        <meta property="og:type" content="business.business" />
        <meta property="og:title" :content="business.seo_title || business.name" />
        <meta property="og:description" :content="business.seo_description || `Visit ${business.name}`" />
        <meta property="og:image" :content="business.logo || ''" />

        <!-- Twitter -->
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" :content="business.seo_title || business.name" />
        <meta name="twitter:description" :content="business.seo_description || `Visit ${business.name}`" />
        <meta name="twitter:image" :content="business.logo || ''" />
    </Head>

    <!-- Landing Page View -->
    <div
        v-if="!showMenuPage"
        class="min-h-screen flex items-center justify-center p-4 bg-gradient-to-br from-gray-50 to-gray-100"
        :style="{ '--brand-color': brandColor }"
    >
        <div class="w-full max-w-md">
            <!-- Business Card -->
            <div class="bg-white rounded-3xl shadow-xl overflow-hidden">
                <!-- Header with Brand Color -->
                <div
                    class="h-32 relative"
                    :style="{ background: `linear-gradient(135deg, ${brandColor}, ${brandColor}dd)` }"
                >
                    <div class="absolute inset-0 bg-black/10"></div>
                </div>

                <!-- Logo & Business Info -->
                <div class="px-6 pb-6">
                    <div class="flex flex-col items-center -mt-16 mb-6">
                        <!-- Logo -->
                        <div
                            v-if="business.logo"
                            class="w-32 h-32 rounded-full bg-white shadow-lg ring-4 ring-white overflow-hidden mb-4 relative z-[9999]"
                        >
                            <img
                                :src="business.logo"
                                :alt="business.name"
                                class="w-full h-full object-cover"
                                loading="eager"
                                decoding="async"
                            />
                        </div>
                        <div
                            v-else
                            class="w-32 h-32 rounded-full bg-white shadow-lg ring-4 ring-white flex items-center justify-center text-4xl font-bold mb-4"
                            :style="{ color: brandColor }"
                        >
                            {{ business.name.charAt(0).toUpperCase() }}
                        </div>

                        <!-- Business Name -->
                        <h1 class="text-3xl font-bold text-gray-900 text-center mb-2">
                            {{ business.name }}
                        </h1>

                        <!-- Address -->
                        <button
                            v-if="business.address"
                            @click="openMaps"
                            class="text-sm text-gray-600 text-center flex items-center gap-1 hover:text-primary transition-colors cursor-pointer mx-auto"
                        >
                            <MapPin class="w-4 h-4" />
                            {{ business.address }}
                        </button>
                    </div>

                    <!-- Links -->
                    <div class="space-y-3">
                        <a
                            v-for="link in links"
                            :key="link.id"
                            :href="link.url"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="group block w-full px-6 py-4 rounded-2xl border-2 transition-all duration-200 hover:shadow-lg hover:scale-[1.02] active:scale-[0.98]"
                            :style="{
                                borderColor: brandColor,
                                color: brandColor
                            }"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <component
                                        :is="getLinkIcon(link.type)"
                                        class="w-5 h-5"
                                        :style="{ color: brandColor }"
                                    />
                                    <span class="font-semibold text-gray-900">{{ link.label }}</span>
                                </div>
                                <ExternalLink
                                    class="w-4 h-4 opacity-0 group-hover:opacity-100 transition-opacity"
                                    :style="{ color: brandColor }"
                                />
                            </div>
                        </a>

                        <!-- Menu Button -->
                        <button
                            v-if="hasMenu"
                            @click="openMenu"
                            class="group block w-full px-6 py-4 rounded-2xl border-2 transition-all duration-200 hover:shadow-lg hover:scale-[1.02] active:scale-[0.98]"
                            :style="{
                                borderColor: brandColor,
                                color: brandColor
                            }"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <Menu
                                        class="w-5 h-5"
                                        :style="{ color: brandColor }"
                                    />
                                    <span class="font-semibold text-gray-900">Voir le Menu</span>
                                </div>
                                <ExternalLink
                                    class="w-4 h-4 opacity-0 group-hover:opacity-100 transition-opacity"
                                    :style="{ color: brandColor }"
                                />
                            </div>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Powered By -->
            <div class="text-center mt-6">
                <p class="text-sm text-gray-500">
                    Powered by <span class="font-semibold">UP1</span>
                </p>
            </div>
        </div>
    </div>

    <!-- Menu Page View -->
    <div
        v-if="showMenuPage"
        class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100"
        :style="{ '--brand-color': brandColor }"
    >
        <!-- Header -->
        <div class="sticky top-0 z-10 backdrop-blur-lg bg-white/90 shadow-sm">
            <div class="max-w-4xl mx-auto px-4 py-4">
                <div class="flex items-center justify-between">
                    <!-- Back Button -->
                    <button
                        @click="closeMenu"
                        class="flex items-center gap-2 text-gray-600 hover:text-gray-900 transition-colors"
                    >
                        <ArrowLeft class="w-5 h-5" />
                        <span class="font-medium">Retour</span>
                    </button>

                    <!-- Business Name -->
                    <div class="flex items-center gap-3">
                        <div
                            v-if="business.logo"
                            class="w-10 h-10 rounded-full overflow-hidden ring-2 ring-white shadow-md"
                        >
                            <img :src="business.logo" :alt="business.name" class="w-full h-full object-cover" loading="lazy" decoding="async" />
                        </div>
                        <div>
                            <h1 class="text-lg font-bold text-gray-900">{{ business.name }}</h1>
                        </div>
                    </div>

                    <!-- Share Button -->
                    <button
                        @click="shareMenu"
                        class="flex items-center gap-2 px-4 py-2 rounded-full text-sm font-medium transition-colors"
                        :style="{
                            backgroundColor: brandColor,
                            color: 'white'
                        }"
                    >
                        <Share2 class="w-4 h-4" />
                        <span class="hidden sm:inline">Partager</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Menu Content -->
        <div class="max-w-6xl mx-auto px-4 py-6">
            <!-- Address -->
            <div v-if="business.address" class="mb-6 text-center">
                <button
                    @click="openMaps"
                    class="text-sm text-gray-600 flex items-center justify-center gap-1 hover:text-primary transition-colors cursor-pointer mx-auto"
                >
                    <MapPin class="w-4 h-4" />
                    {{ business.address }}
                </button>
            </div>

            <!-- Empty State -->
            <div v-if="categories.length === 0" class="text-center py-16">
                <div class="bg-white rounded-2xl shadow-md p-12">
                    <p class="text-xl font-semibold text-gray-900 mb-2">Aucun menu disponible</p>
                    <p class="text-gray-600">Le menu est en cours de mise à jour. Veuillez revenir plus tard.</p>
                </div>
            </div>

            <!-- Categories Selection View -->
            <div v-else-if="menuView === 'categories'" class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <!-- Category Buttons -->
                <button
                    v-for="category in categories"
                    :key="category.id"
                    @click="selectCategory(category.id)"
                    class="bg-white rounded-2xl shadow-md p-6 hover:shadow-lg transition-all hover:scale-[1.02] active:scale-[0.98] flex flex-col items-center justify-center gap-3 min-h-[140px]"
                    :style="{ borderTop: `4px solid ${brandColor}` }"
                >
                    <div
                        class="w-16 h-16 rounded-full flex items-center justify-center"
                        :style="{ backgroundColor: `${brandColor}20` }"
                    >
                        <span class="text-2xl font-bold" :style="{ color: brandColor }">
                            {{ category.name.charAt(0).toUpperCase() }}
                        </span>
                    </div>
                    <span class="font-bold text-center text-gray-900">{{ category.name }}</span>
                </button>
            </div>

            <!-- Items View with Tabs -->
            <div v-else-if="menuView === 'items'" class="flex flex-col md:flex-row gap-4">
                <!-- Tabs Sidebar (Horizontal on mobile, Vertical on desktop) -->
                <div class="md:w-64 flex-shrink-0">
                    <div class="bg-white rounded-2xl shadow-md overflow-hidden relative">
                        <!-- Scroll Indicators -->
                        <div class="absolute left-0 top-0 bottom-0 w-8 bg-gradient-to-r from-white to-transparent pointer-events-none md:hidden z-10"></div>
                        <div class="absolute right-0 top-0 bottom-0 w-8 bg-gradient-to-l from-white to-transparent pointer-events-none md:hidden z-10"></div>

                        <!-- Tabs: Horizontal scroll on mobile, Vertical on desktop -->
                        <div class="flex md:flex-col overflow-x-auto md:overflow-x-visible scroll-smooth snap-x md:snap-none">
                            <!-- Category Tabs -->
                            <button
                                v-for="category in categories"
                                :key="category.id"
                                @click="selectCategory(category.id)"
                                class="flex-shrink-0 px-6 py-4 text-left font-semibold transition-all border-b md:border-b-0 md:border-l-4 snap-start"
                                :class="{
                                    'bg-white text-gray-900': activeCategory === category.id,
                                    'bg-gray-50 text-gray-600 hover:bg-gray-100': activeCategory !== category.id
                                }"
                                :style="{
                                    borderColor: activeCategory === category.id ? brandColor : 'transparent'
                                }"
                            >
                                {{ category.name }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Active Category Content -->
                <div class="flex-1">
                    <!-- Single Category View -->
                    <div v-if="activeCategoryData" class="bg-white rounded-2xl shadow-md overflow-hidden">
                        <!-- Category Header -->
                        <div
                            class="px-6 py-4 font-bold text-lg text-white"
                            :style="{ backgroundColor: brandColor }"
                        >
                            {{ activeCategoryData.name }}
                        </div>

                        <!-- Category Items (direct items without subcategory) -->
                        <div v-if="activeCategoryData.items.length > 0" class="divide-y divide-gray-100">
                            <div
                                v-for="item in activeCategoryData.items"
                                :key="item.id"
                                class="p-6 hover:bg-gray-50 transition-colors"
                            >
                                <div class="flex gap-4">
                                    <!-- Item Image -->
                                    <div
                                        v-if="item.image"
                                        class="w-24 h-24 rounded-lg overflow-hidden flex-shrink-0 bg-gray-100"
                                    >
                                        <img
                                            :src="item.image"
                                            :alt="item.name"
                                            class="w-full h-full object-cover"
                                        />
                                    </div>

                                    <!-- Item Info -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-start justify-between gap-4 mb-2">
                                            <h3 class="font-semibold text-gray-900 text-lg">{{ item.name }}</h3>
                                            <span
                                                class="font-bold text-lg whitespace-nowrap"
                                                :style="{ color: brandColor }"
                                            >
                                                {{ formatPrice(item.price) }}
                                            </span>
                                        </div>
                                        <p v-if="item.description" class="text-gray-600 text-sm leading-relaxed">
                                            {{ item.description }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Subcategories -->
                        <div v-if="activeCategoryData.subcategories.length > 0" class="divide-y divide-gray-100">
                            <div
                                v-for="subcategory in activeCategoryData.subcategories"
                                :key="subcategory.id"
                                class="border-t border-gray-200"
                            >
                                <!-- Subcategory Header -->
                                <div class="px-6 py-3 bg-gray-50 font-semibold text-gray-700">
                                    {{ subcategory.name }}
                                </div>

                                <!-- Subcategory Items -->
                                <div class="divide-y divide-gray-100">
                                    <div
                                        v-for="item in subcategory.items"
                                        :key="item.id"
                                        class="p-6 hover:bg-gray-50 transition-colors"
                                    >
                                        <div class="flex gap-4">
                                            <!-- Item Image -->
                                            <div
                                                v-if="item.image"
                                                class="w-24 h-24 rounded-lg overflow-hidden flex-shrink-0 bg-gray-100"
                                            >
                                                <img
                                                    :src="item.image"
                                                    :alt="item.name"
                                                    class="w-full h-full object-cover"
                                                />
                                            </div>

                                            <!-- Item Info -->
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-start justify-between gap-4 mb-2">
                                                    <h3 class="font-semibold text-gray-900 text-lg">{{ item.name }}</h3>
                                                    <span
                                                        class="font-bold text-lg whitespace-nowrap"
                                                        :style="{ color: brandColor }"
                                                    >
                                                        {{ formatPrice(item.price) }}
                                                    </span>
                                                </div>
                                                <p v-if="item.description" class="text-gray-600 text-sm leading-relaxed">
                                                    {{ item.description }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Empty Subcategory -->
                                    <div v-if="subcategory.items.length === 0" class="p-6 text-center text-gray-500 text-sm">
                                        Aucun article dans cette sous-catégorie
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Empty Category (no items and no subcategories) -->
                        <div v-if="activeCategoryData.items.length === 0 && activeCategoryData.subcategories.length === 0" class="p-8 text-center text-gray-500">
                            Aucun article dans cette catégorie
                        </div>
                    </div>
                </div>
            </div>

            <!-- Powered By -->
            <div class="text-center mt-12">
                <p class="text-sm text-gray-500">
                    Powered by <span class="font-semibold">UP1</span>
                </p>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Custom hover effects with brand color */
a:hover {
    background: linear-gradient(135deg, var(--brand-color)08, var(--brand-color)12);
}
</style>
