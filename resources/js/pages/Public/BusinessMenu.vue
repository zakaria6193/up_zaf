<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { ArrowLeft, MapPin, Share2 } from 'lucide-vue-next';

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
        lat: number | null;
        lng: number | null;
    };
    categories: Array<{
        id: number;
        name: string;
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

// Open Google Maps for directions
const openMaps = () => {
    if (props.business.lat && props.business.lng) {
        window.open(`https://www.google.com/maps/dir/?api=1&destination=${props.business.lat},${props.business.lng}`, '_blank');
    } else if (props.business.address) {
        window.open(`https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(props.business.address)}`, '_blank');
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
    } else {
        // Fallback: copy to clipboard
        navigator.clipboard.writeText(url);
        alert('Link copied to clipboard!');
    }
};
</script>

<template>
    <Head>
        <title>{{ business.seo_title || `${business.name} - Menu` }}</title>
        <meta name="description" :content="business.seo_description || `View the menu at ${business.name}`" />

        <!-- Open Graph / Facebook -->
        <meta property="og:type" content="restaurant.menu" />
        <meta property="og:title" :content="`${business.name} - Menu`" />
        <meta property="og:description" :content="`View the menu at ${business.name}`" />
        <meta property="og:image" :content="business.logo || ''" />

        <!-- Twitter -->
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" :content="`${business.name} - Menu`" />
        <meta name="twitter:description" :content="`View the menu at ${business.name}`" />
    </Head>

    <div
        class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100"
        :style="{ '--brand-color': brandColor }"
    >
        <!-- Header -->
        <div
            class="sticky top-0 z-10 backdrop-blur-lg bg-white/90 shadow-sm"
        >
            <div class="max-w-4xl mx-auto px-4 py-4">
                <div class="flex items-center justify-between">
                    <!-- Back Button -->
                    <Link
                        :href="`/p/${business.nanoid}`"
                        class="flex items-center gap-2 text-gray-600 hover:text-gray-900 transition-colors"
                    >
                        <ArrowLeft class="w-5 h-5" />
                        <span class="font-medium">Back</span>
                    </Link>

                    <!-- Business Name -->
                    <div class="flex items-center gap-3">
                        <div
                            v-if="business.logo"
                            class="w-10 h-10 rounded-full overflow-hidden ring-2 ring-white shadow-md"
                        >
                            <img :src="business.logo" :alt="business.name" class="w-full h-full object-cover" />
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
                        <span class="hidden sm:inline">Share</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Menu Content -->
        <div class="max-w-4xl mx-auto px-4 py-8">
            <!-- Address -->
            <div v-if="business.address" class="mb-8 text-center">
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
                    <p class="text-xl font-semibold text-gray-900 mb-2">No menu available</p>
                    <p class="text-gray-600">The menu is being updated. Please check back later.</p>
                </div>
            </div>

            <!-- Categories -->
            <div v-else class="space-y-8">
                <div
                    v-for="category in categories"
                    :key="category.id"
                    class="bg-white rounded-2xl shadow-md overflow-hidden"
                >
                    <!-- Category Header -->
                    <div
                        class="px-6 py-4 font-bold text-lg text-white"
                        :style="{ backgroundColor: brandColor }"
                    >
                        {{ category.name }}
                    </div>

                    <!-- Category Items (direct items without subcategory) -->
                    <div v-if="category.items.length > 0" class="divide-y divide-gray-100">
                        <div
                            v-for="item in category.items"
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
                    <div v-if="category.subcategories.length > 0" class="divide-y divide-gray-100">
                        <div
                            v-for="subcategory in category.subcategories"
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
                                    No items in this subcategory yet
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Empty Category (no items and no subcategories) -->
                    <div v-if="category.items.length === 0 && category.subcategories.length === 0" class="p-8 text-center text-gray-500">
                        No items in this category yet
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
