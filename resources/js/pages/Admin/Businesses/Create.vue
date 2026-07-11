<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { store, index } from '@/actions/App/Http/Controllers/Admin/BusinessController';
import { onMounted, ref } from 'vue';
import { X } from 'lucide-vue-next';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/adminos/dashboard' },
            { title: 'Businesses', href: index.url() },
            { title: 'Create Business' },
        ],
    },
});

const form = useForm({
    name: '',
    address: '',
    lat: null as number | null,
    lng: null as number | null,
    logo: null as File | null,
    color: '#4d54d9',
    seo_title: '',
    seo_description: '',
    seo_keywords: '',
});

const logoPreview = ref<string | null>(null);
const mapInput = ref<HTMLInputElement | null>(null);
const googleMapsApiKey = import.meta.env.VITE_GOOGLE_MAPS_API_KEY;

const handleLogoChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];

    if (file) {
        form.logo = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            logoPreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
};

const removeLogo = () => {
    form.logo = null;
    logoPreview.value = null;
    const input = document.getElementById('logo-upload') as HTMLInputElement;
    if (input) {
        input.value = '';
    }
};

const initGoogleMaps = () => {
    if (!window.google || !mapInput.value) {
        return;
    }

    const autocomplete = new window.google.maps.places.Autocomplete(mapInput.value, {
        types: ['establishment', 'geocode'],
        componentRestrictions: { country: 'ma' },
    });

    autocomplete.addListener('place_changed', () => {
        const place = autocomplete.getPlace();

        if (place.geometry) {
            form.address = place.formatted_address || '';
            form.lat = place.geometry.location?.lat() || null;
            form.lng = place.geometry.location?.lng() || null;
        }
    });
};

onMounted(() => {
    if (googleMapsApiKey) {
        const script = document.createElement('script');
        script.src = `https://maps.googleapis.com/maps/api/js?key=${googleMapsApiKey}&libraries=places`;
        script.async = true;
        script.onload = () => initGoogleMaps();
        document.head.appendChild(script);
    }
});

const submit = () => {
    form.post(store.url(), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Create Business" />

    <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
        <!-- Header -->
        <div>
            <h1 class="text-2xl font-semibold">Create Business</h1>
            <p class="text-sm text-muted-foreground">
                Add a new business to generate a QR code and public page
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <!-- Basic Information -->
            <Card>
                <CardHeader>
                    <CardTitle>Basic Information</CardTitle>
                    <CardDescription>Enter the business name and location</CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="space-y-2">
                        <Label for="name">Business Name *</Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            type="text"
                            placeholder="Restaurant Casa Blanca"
                            :class="{ 'border-destructive': form.errors.name }"
                            required
                        />
                        <p v-if="form.errors.name" class="text-sm text-destructive">
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="address">Address</Label>
                        <Input
                            id="address"
                            ref="mapInput"
                            v-model="form.address"
                            type="text"
                            placeholder="Start typing to search on Google Maps..."
                            :class="{ 'border-destructive': form.errors.address }"
                        />
                        <p v-if="form.errors.address" class="text-sm text-destructive">
                            {{ form.errors.address }}
                        </p>
                        <p v-else class="text-xs text-muted-foreground">
                            Use Google Maps autocomplete to set the exact location
                        </p>
                    </div>
                </CardContent>
            </Card>

            <!-- Branding -->
            <Card>
                <CardHeader>
                    <CardTitle>Branding</CardTitle>
                    <CardDescription>Customize the look of your business page</CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="space-y-2">
                        <Label for="logo">Logo</Label>
                        <div v-if="logoPreview" class="relative inline-block">
                            <img
                                :src="logoPreview"
                                alt="Logo preview"
                                class="h-24 w-24 rounded-lg border object-cover"
                            />
                            <Button
                                type="button"
                                size="icon"
                                variant="destructive"
                                class="absolute -right-2 -top-2 h-6 w-6 rounded-full"
                                @click="removeLogo"
                            >
                                <X class="h-3 w-3" />
                            </Button>
                        </div>
                        <Input
                            id="logo-upload"
                            type="file"
                            accept="image/*"
                            @change="handleLogoChange"
                            :class="{ 'border-destructive': form.errors.logo }"
                        />
                        <p v-if="form.errors.logo" class="text-sm text-destructive">
                            {{ form.errors.logo }}
                        </p>
                        <p v-else class="text-xs text-muted-foreground">
                            Maximum file size: 2MB. Recommended: Square image, at least 400x400px
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="color">Brand Color *</Label>
                        <div class="flex items-center gap-3">
                            <input
                                id="color"
                                v-model="form.color"
                                type="color"
                                class="h-10 w-20 cursor-pointer rounded border"
                            />
                            <Input
                                v-model="form.color"
                                type="text"
                                pattern="^#[0-9A-Fa-f]{6}$"
                                placeholder="#4d54d9"
                                class="flex-1"
                                :class="{ 'border-destructive': form.errors.color }"
                            />
                        </div>
                        <p v-if="form.errors.color" class="text-sm text-destructive">
                            {{ form.errors.color }}
                        </p>
                        <p v-else class="text-xs text-muted-foreground">
                            This color will be used for the public page theme
                        </p>
                    </div>
                </CardContent>
            </Card>

            <!-- SEO Settings -->
            <Card>
                <CardHeader>
                    <CardTitle>SEO Settings</CardTitle>
                    <CardDescription>Optimize your business page for search engines</CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="space-y-2">
                        <Label for="seo_title">Meta Title</Label>
                        <Input
                            id="seo_title"
                            v-model="form.seo_title"
                            type="text"
                            placeholder="Restaurant Casa Blanca - Authentic Moroccan Cuisine"
                            :class="{ 'border-destructive': form.errors.seo_title }"
                        />
                        <p v-if="form.errors.seo_title" class="text-sm text-destructive">
                            {{ form.errors.seo_title }}
                        </p>
                        <p v-else class="text-xs text-muted-foreground">
                            Optional. Will default to business name if not provided.
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="seo_description">Meta Description</Label>
                        <Textarea
                            id="seo_description"
                            v-model="form.seo_description"
                            placeholder="Discover the best Moroccan cuisine in Casablanca..."
                            rows="3"
                            :class="{ 'border-destructive': form.errors.seo_description }"
                        />
                        <p v-if="form.errors.seo_description" class="text-sm text-destructive">
                            {{ form.errors.seo_description }}
                        </p>
                        <p v-else class="text-xs text-muted-foreground">
                            Optional. Recommended length: 150-160 characters.
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="seo_keywords">Meta Keywords</Label>
                        <Input
                            id="seo_keywords"
                            v-model="form.seo_keywords"
                            type="text"
                            placeholder="restaurant, moroccan food, casablanca"
                            :class="{ 'border-destructive': form.errors.seo_keywords }"
                        />
                        <p v-if="form.errors.seo_keywords" class="text-sm text-destructive">
                            {{ form.errors.seo_keywords }}
                        </p>
                        <p v-else class="text-xs text-muted-foreground">
                            Optional. Comma-separated keywords.
                        </p>
                    </div>
                </CardContent>
            </Card>

            <!-- Form Actions -->
            <div class="flex gap-3">
                <Button type="submit" :disabled="form.processing">
                    {{ form.processing ? 'Creating...' : 'Create Business' }}
                </Button>
                <Button type="button" variant="outline" as-child>
                    <Link :href="index.url()">
                        Cancel
                    </Link>
                </Button>
            </div>
        </form>
    </div>
</template>
