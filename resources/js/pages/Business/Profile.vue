<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, computed, nextTick, onMounted } from 'vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Upload, X, MapPin, Save, Trash2 } from 'lucide-vue-next';
import BusinessSwitcher from '@/components/BusinessSwitcher.vue';

const props = defineProps<{
    business: {
        id: number;
        nanoid: string;
        name: string;
        address: string | null;
        lat: number | null;
        lng: number | null;
        logo: string | null;
        color: string;
        is_active: boolean;
        seo_title: string | null;
        seo_description: string | null;
        seo_keywords: string | null;
        qr_code: string | null;
        public_url: string;
    };
    userBusinesses: Array<{
        nanoid: string;
        name: string;
    }>;
    googleMapsApiKey: string;
}>();

const form = useForm({
    name: props.business.name,
    address: props.business.address,
    lat: props.business.lat,
    lng: props.business.lng,
    color: props.business.color,
    seo_title: props.business.seo_title,
    seo_description: props.business.seo_description,
    seo_keywords: props.business.seo_keywords,
    logo: null as File | null,
});

const logoPreview = ref<string | null>(props.business.logo);
const fileInput = ref<HTMLInputElement | null>(null);
const mapContainer = ref<HTMLDivElement | null>(null);
const mapInput = ref<HTMLInputElement | null>(null);

let map: google.maps.Map | null = null;
let marker: google.maps.Marker | null = null;
let autocomplete: google.maps.places.Autocomplete | null = null;

// Handle logo file selection
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

// Trigger file input click
const triggerFileInput = () => {
    fileInput.value?.click();
};

// Remove logo
const removeLogo = () => {
    form.logo = null;
    logoPreview.value = null;
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

// Delete logo from server
const deleteLogo = () => {
    if (confirm('Are you sure you want to delete the logo?')) {
        router.delete(route('business.profile.deleteLogo', props.business.nanoid), {
            preserveScroll: true,
            onSuccess: () => {
                logoPreview.value = null;
            },
        });
    }
};

// Initialize Google Maps
const initGoogleMaps = async () => {
    if (!window.google || !mapContainer.value) return;

    await nextTick();

    const inputElement = mapInput.value;
    if (!inputElement || !(inputElement instanceof HTMLInputElement)) {
        console.warn('Map input element not found or invalid');
        return;
    }

    // Initialize autocomplete
    autocomplete = new window.google.maps.places.Autocomplete(inputElement, {
        types: ['establishment', 'geocode'],
        componentRestrictions: { country: 'ma' },
    });

    // Initialize map
    const defaultCenter = {
        lat: props.business.lat ?? 33.5731,
        lng: props.business.lng ?? -7.5898,
    };

    map = new google.maps.Map(mapContainer.value, {
        center: defaultCenter,
        zoom: props.business.lat && props.business.lng ? 15 : 11,
    });

    // Initialize marker
    marker = new google.maps.Marker({
        map: map,
        position: defaultCenter,
        draggable: true,
        visible: props.business.lat !== null && props.business.lng !== null,
    });

    // Listen to autocomplete place selection
    autocomplete.addListener('place_changed', () => {
        const place = autocomplete!.getPlace();

        if (!place.geometry || !place.geometry.location) {
            return;
        }

        const location = place.geometry.location;

        // Update form values
        form.address = place.formatted_address || '';
        form.lat = location.lat();
        form.lng = location.lng();

        // Update map and marker
        map!.setCenter(location);
        map!.setZoom(15);
        marker!.setPosition(location);
        marker!.setVisible(true);
    });

    // Listen to marker drag
    marker.addListener('dragend', async (event: google.maps.MapMouseEvent) => {
        const position = event.latLng;
        if (!position) return;

        form.lat = position.lat();
        form.lng = position.lng();

        // Reverse geocode to get address
        const geocoder = new google.maps.Geocoder();
        const response = await geocoder.geocode({ location: position });

        if (response.results[0]) {
            form.address = response.results[0].formatted_address;
            if (mapInput.value) {
                mapInput.value.value = form.address;
            }
        }
    });
};

// Load Google Maps script
onMounted(() => {
    if (props.googleMapsApiKey && !window.google) {
        const script = document.createElement('script');
        script.src = `https://maps.googleapis.com/maps/api/js?key=${props.googleMapsApiKey}&libraries=places&loading=async`;
        script.async = true;
        script.defer = true;
        script.onload = () => {
            initGoogleMaps();
        };
        document.head.appendChild(script);
    } else if (window.google) {
        initGoogleMaps();
    }
});

// Submit form
const submit = () => {
    form.post(route('business.profile.update', props.business.nanoid), {
        preserveScroll: true,
        onSuccess: () => {
            // Reset logo file input but keep preview
            form.logo = null;
            if (fileInput.value) {
                fileInput.value.value = '';
            }
        },
    });
};

// Handle tab change - initialize map when Location tab is opened
const handleTabChange = (value: string) => {
    if (value === 'location') {
        nextTick(() => initGoogleMaps());
    }
};
</script>

<template>
    <Head title="Business Profile" />

    <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold">Business Profile</h1>
                <p class="text-sm text-muted-foreground">
                    Manage your business information and settings
                </p>
            </div>

            <!-- Business Selector (if multiple businesses) -->
            <BusinessSwitcher
                :businesses="userBusinesses"
                :current-nanoid="business.nanoid"
                route="business.profile"
                label="Editing:"
            />
        </div>

        <!-- Tabs -->
        <Tabs default-value="basic" @update:model-value="handleTabChange">
            <TabsList class="grid w-full grid-cols-4">
                <TabsTrigger value="basic">Basic Info</TabsTrigger>
                <TabsTrigger value="location">Location</TabsTrigger>
                <TabsTrigger value="branding">Branding</TabsTrigger>
                <TabsTrigger value="seo">SEO</TabsTrigger>
            </TabsList>

            <!-- Basic Info Tab -->
            <TabsContent value="basic">
                <Card>
                    <CardHeader>
                        <CardTitle>Basic Information</CardTitle>
                        <CardDescription>Update your business basic details</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-2">
                            <Label for="name">Business Name *</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                :class="{ 'border-red-500': form.errors.name }"
                                required
                            />
                            <p v-if="form.errors.name" class="text-sm text-red-600">{{ form.errors.name }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label>Status</Label>
                            <div class="flex items-center gap-2">
                                <span
                                    class="px-3 py-1 rounded-full text-sm font-medium"
                                    :class="{
                                        'bg-green-100 text-green-800': business.is_active,
                                        'bg-gray-100 text-gray-800': !business.is_active,
                                    }"
                                >
                                    {{ business.is_active ? 'Active' : 'Inactive' }}
                                </span>
                                <p class="text-xs text-muted-foreground">
                                    Contact admin to change status
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 pt-4">
                            <Button @click="submit" :disabled="form.processing">
                                <Save class="mr-2 h-4 w-4" />
                                Save Changes
                            </Button>
                            <p v-if="form.recentlySuccessful" class="text-sm text-green-600">
                                Saved successfully!
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </TabsContent>

            <!-- Location Tab -->
            <TabsContent value="location">
                <Card>
                    <CardHeader>
                        <CardTitle>Location</CardTitle>
                        <CardDescription>Set your business location</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-2">
                            <Label for="address">Address</Label>
                            <Input
                                id="address"
                                ref="mapInput"
                                v-model="form.address"
                                placeholder="Search for your business address..."
                                :class="{ 'border-red-500': form.errors.address }"
                            />
                            <p v-if="form.errors.address" class="text-sm text-red-600">{{ form.errors.address }}</p>
                        </div>

                        <!-- Map Container -->
                        <div ref="mapContainer" class="h-96 rounded-lg border border-gray-300 bg-gray-100"></div>

                        <p class="text-sm text-muted-foreground">
                            <MapPin class="inline h-4 w-4" />
                            Search for your business or drag the marker to set the exact location
                        </p>

                        <div class="flex items-center gap-3 pt-4">
                            <Button @click="submit" :disabled="form.processing">
                                <Save class="mr-2 h-4 w-4" />
                                Save Changes
                            </Button>
                            <p v-if="form.recentlySuccessful" class="text-sm text-green-600">
                                Saved successfully!
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </TabsContent>

            <!-- Branding Tab -->
            <TabsContent value="branding">
                <Card>
                    <CardHeader>
                        <CardTitle>Branding</CardTitle>
                        <CardDescription>Customize your business appearance</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <!-- Logo Upload -->
                        <div class="space-y-2">
                            <Label>Business Logo</Label>
                            <div class="flex items-start gap-4">
                                <!-- Logo Preview -->
                                <div
                                    v-if="logoPreview"
                                    class="relative w-32 h-32 rounded-lg border-2 border-dashed border-gray-300 overflow-hidden group"
                                >
                                    <img :src="logoPreview" alt="Logo preview" class="w-full h-full object-cover" />
                                    <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                                        <Button size="sm" variant="ghost" @click="triggerFileInput" class="text-white">
                                            <Upload class="h-4 w-4" />
                                        </Button>
                                        <Button size="sm" variant="ghost" @click="business.logo ? deleteLogo() : removeLogo()" class="text-white">
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </div>

                                <!-- Upload Area -->
                                <button
                                    v-else
                                    @click="triggerFileInput"
                                    type="button"
                                    class="w-32 h-32 rounded-lg border-2 border-dashed border-gray-300 hover:border-primary transition-colors flex flex-col items-center justify-center gap-2 text-gray-500 hover:text-primary"
                                >
                                    <Upload class="h-6 w-6" />
                                    <span class="text-xs">Upload Logo</span>
                                </button>

                                <input
                                    ref="fileInput"
                                    type="file"
                                    accept="image/*"
                                    class="hidden"
                                    @change="handleLogoChange"
                                />

                                <div class="flex-1 text-sm text-muted-foreground">
                                    <p class="mb-1">Recommended: Square image, at least 400x400px</p>
                                    <p>Maximum file size: 2MB</p>
                                </div>
                            </div>
                            <p v-if="form.errors.logo" class="text-sm text-red-600">{{ form.errors.logo }}</p>
                        </div>

                        <!-- Brand Color -->
                        <div class="space-y-2">
                            <Label for="color">Brand Color</Label>
                            <div class="flex items-center gap-3">
                                <Input
                                    id="color"
                                    type="color"
                                    v-model="form.color"
                                    class="w-20 h-10 cursor-pointer"
                                />
                                <Input
                                    v-model="form.color"
                                    placeholder="#3b82f6"
                                    class="flex-1"
                                    :class="{ 'border-red-500': form.errors.color }"
                                />
                            </div>
                            <p v-if="form.errors.color" class="text-sm text-red-600">{{ form.errors.color }}</p>
                        </div>

                        <div class="flex items-center gap-3 pt-4">
                            <Button @click="submit" :disabled="form.processing">
                                <Save class="mr-2 h-4 w-4" />
                                Save Changes
                            </Button>
                            <p v-if="form.recentlySuccessful" class="text-sm text-green-600">
                                Saved successfully!
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </TabsContent>

            <!-- SEO Tab -->
            <TabsContent value="seo">
                <Card>
                    <CardHeader>
                        <CardTitle>SEO Settings</CardTitle>
                        <CardDescription>Optimize your business page for search engines</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-2">
                            <Label for="seo_title">SEO Title</Label>
                            <Input
                                id="seo_title"
                                v-model="form.seo_title"
                                placeholder="Leave empty to use business name"
                                :class="{ 'border-red-500': form.errors.seo_title }"
                            />
                            <p v-if="form.errors.seo_title" class="text-sm text-red-600">{{ form.errors.seo_title }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="seo_description">SEO Description</Label>
                            <Textarea
                                id="seo_description"
                                v-model="form.seo_description"
                                placeholder="Brief description of your business for search engines"
                                rows="3"
                                :class="{ 'border-red-500': form.errors.seo_description }"
                            />
                            <p v-if="form.errors.seo_description" class="text-sm text-red-600">{{ form.errors.seo_description }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="seo_keywords">SEO Keywords</Label>
                            <Input
                                id="seo_keywords"
                                v-model="form.seo_keywords"
                                placeholder="keyword1, keyword2, keyword3"
                                :class="{ 'border-red-500': form.errors.seo_keywords }"
                            />
                            <p v-if="form.errors.seo_keywords" class="text-sm text-red-600">{{ form.errors.seo_keywords }}</p>
                        </div>

                        <div class="flex items-center gap-3 pt-4">
                            <Button @click="submit" :disabled="form.processing">
                                <Save class="mr-2 h-4 w-4" />
                                Save Changes
                            </Button>
                            <p v-if="form.recentlySuccessful" class="text-sm text-green-600">
                                Saved successfully!
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </TabsContent>
        </Tabs>
    </div>
</template>
