<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Tabs, TabsList, TabsTrigger, TabsContent } from '@/components/ui/tabs';
import { update, index, show } from '@/actions/App/Http/Controllers/Admin/BusinessController';
import {
    storeCategory,
    updateCategory,
    destroyCategory,
    reorderCategories,
    storeItem,
    updateItem,
    destroyItem,
    reorderItems,
} from '@/actions/App/Http/Controllers/Admin/MenuController';
import { onMounted, ref, computed, nextTick } from 'vue';
import { X, Plus, Edit, Trash2, Save, ChevronUp, ChevronDown } from 'lucide-vue-next';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/adminos/dashboard' },
            { title: 'Businesses', href: index.url() },
            { title: 'Edit Business' },
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
        logo: string | null;
        seo_title: string | null;
        seo_description: string | null;
        seo_keywords: string | null;
    };
    categories: Array<{
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
}>();

const activeTab = ref('info');

// Business form
const form = useForm({
    name: props.business.name,
    address: props.business.address || '',
    lat: props.business.lat,
    lng: props.business.lng,
    logo: null as File | null,
    color: props.business.color,
    seo_title: props.business.seo_title || '',
    seo_description: props.business.seo_description || '',
    seo_keywords: props.business.seo_keywords || '',
    _method: 'PUT',
});

const logoPreview = ref<string | null>(props.business.logo);
const mapInput = ref<HTMLInputElement | null>(null);
const mapContainer = ref<HTMLDivElement | null>(null);
const googleMapsApiKey = import.meta.env.VITE_GOOGLE_MAPS_API_KEY;

let map: google.maps.Map | null = null;
let marker: google.maps.Marker | null = null;

// Menu management state
const showAddCategoryForm = ref(false);
const showAddItemForm = ref<number | null>(null);
const editingCategoryId = ref<number | null>(null);
const editingItemId = ref<number | null>(null);
const editingItemCategoryId = ref<number | null>(null);

const addCategoryForm = useForm({
    name: '',
});

const editCategoryForm = useForm({
    name: '',
});

const addItemForm = useForm({
    name: '',
    description: '',
    price: '',
    image: null as File | null,
});

const editItemForm = useForm({
    name: '',
    description: '',
    price: '',
    image: null as File | null,
    _method: 'PUT',
});

const itemImagePreview = ref<string | null>(null);

const sortedCategories = computed(() => {
    return [...props.categories].sort((a, b) => a.order - b.order);
});

const sortedItems = (categoryId: number) => {
    const category = props.categories.find((c) => c.id === categoryId);
    if (!category) return [];
    return [...category.items].sort((a, b) => a.order - b.order);
};

// Logo handlers
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

// Google Maps
const initGoogleMaps = async () => {
    if (!window.google || !mapContainer.value) {
        return;
    }

    // Wait for input element to be available
    await nextTick();

    const inputElement = mapInput.value;
    if (!inputElement || !(inputElement instanceof HTMLInputElement)) {
        console.warn('Map input element not found or invalid');
        return;
    }

    // Initialize autocomplete on input
    const autocomplete = new window.google.maps.places.Autocomplete(inputElement, {
        types: ['establishment', 'geocode'],
        componentRestrictions: { country: 'ma' },
    });

    autocomplete.addListener('place_changed', () => {
        const place = autocomplete.getPlace();

        if (place.geometry && place.geometry.location) {
            form.address = place.formatted_address || '';
            form.lat = place.geometry.location.lat();
            form.lng = place.geometry.location.lng();

            // Update map and marker
            if (map && marker) {
                map.setCenter(place.geometry.location);
                map.setZoom(15);
                marker.setPosition(place.geometry.location);
                marker.setVisible(true);
            }
        }
    });

    // Initialize map
    const defaultCenter = props.business.lat && props.business.lng
        ? { lat: props.business.lat, lng: props.business.lng }
        : { lat: 33.5731, lng: -7.5898 }; // Casablanca

    map = new google.maps.Map(mapContainer.value, {
        center: defaultCenter,
        zoom: props.business.lat && props.business.lng ? 15 : 11,
        mapTypeControl: false,
        streetViewControl: false,
        fullscreenControl: false,
    });

    // Initialize marker
    marker = new google.maps.Marker({
        map: map,
        draggable: true,
        position: props.business.lat && props.business.lng ? defaultCenter : undefined,
        visible: props.business.lat && props.business.lng ? true : false,
    });

    // Update form when marker is dragged
    marker.addListener('dragend', () => {
        const position = marker!.getPosition();
        if (position) {
            form.lat = position.lat();
            form.lng = position.lng();

            // Reverse geocode to get address
            const geocoder = new google.maps.Geocoder();
            geocoder.geocode({ location: position }, (results, status) => {
                if (status === 'OK' && results && results[0]) {
                    form.address = results[0].formatted_address;
                }
            });
        }
    });

    // Allow clicking on map to place marker
    map.addListener('click', (e: google.maps.MapMouseEvent) => {
        if (e.latLng) {
            form.lat = e.latLng.lat();
            form.lng = e.latLng.lng();
            marker!.setPosition(e.latLng);
            marker!.setVisible(true);

            // Reverse geocode
            const geocoder = new google.maps.Geocoder();
            geocoder.geocode({ location: e.latLng }, (results, status) => {
                if (status === 'OK' && results && results[0]) {
                    form.address = results[0].formatted_address;
                }
            });
        }
    });
};

onMounted(() => {
    if (googleMapsApiKey && !window.google) {
        const script = document.createElement('script');
        script.src = `https://maps.googleapis.com/maps/api/js?key=${googleMapsApiKey}&libraries=places&loading=async`;
        script.async = true;
        script.defer = true;
        script.onload = () => {
            nextTick(() => initGoogleMaps());
        };
        document.head.appendChild(script);
    } else if (window.google) {
        nextTick(() => initGoogleMaps());
    }
});

const submit = () => {
    form.post(update.url(props.business.nanoid), {
        preserveScroll: true,
        onSuccess: () => {
            form.logo = null;
        },
    });
};

// Category handlers
const handleAddCategory = () => {
    addCategoryForm.post(storeCategory.url(props.business.nanoid), {
        preserveScroll: true,
        onSuccess: () => {
            addCategoryForm.reset();
            showAddCategoryForm.value = false;
        },
    });
};

const startEditCategory = (category: typeof props.categories[0]) => {
    editingCategoryId.value = category.id;
    editCategoryForm.name = category.name;
};

const cancelEditCategory = () => {
    editingCategoryId.value = null;
    editCategoryForm.reset();
};

const handleUpdateCategory = (categoryId: number) => {
    editCategoryForm.put(updateCategory.url(props.business.nanoid, categoryId), {
        preserveScroll: true,
        onSuccess: () => {
            editingCategoryId.value = null;
            editCategoryForm.reset();
        },
    });
};

const handleDeleteCategory = (categoryId: number, categoryName: string) => {
    if (confirm(`Are you sure you want to delete "${categoryName}"? This will also delete all items in this category.`)) {
        router.delete(destroyCategory.url(props.business.nanoid, categoryId));
    }
};

const moveCategoryUp = (category: typeof props.categories[0]) => {
    const currentIndex = sortedCategories.value.findIndex((c) => c.id === category.id);
    if (currentIndex === 0) return;

    const newCategories = sortedCategories.value.map((c, index) => {
        if (index === currentIndex - 1) {
            return { id: c.id, order: currentIndex };
        }
        if (index === currentIndex) {
            return { id: c.id, order: currentIndex - 1 };
        }
        return { id: c.id, order: index };
    });

    router.post(reorderCategories.url(props.business.nanoid), { categories: newCategories });
};

const moveCategoryDown = (category: typeof props.categories[0]) => {
    const currentIndex = sortedCategories.value.findIndex((c) => c.id === category.id);
    if (currentIndex === sortedCategories.value.length - 1) return;

    const newCategories = sortedCategories.value.map((c, index) => {
        if (index === currentIndex) {
            return { id: c.id, order: currentIndex + 1 };
        }
        if (index === currentIndex + 1) {
            return { id: c.id, order: currentIndex };
        }
        return { id: c.id, order: index };
    });

    router.post(reorderCategories.url(props.business.nanoid), { categories: newCategories });
};

// Item handlers
const handleImageChange = (event: Event, formType: 'add' | 'edit') => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];

    if (file) {
        const targetForm = formType === 'add' ? addItemForm : editItemForm;
        targetForm.image = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            itemImagePreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
};

const removeItemImage = (formType: 'add' | 'edit') => {
    const targetForm = formType === 'add' ? addItemForm : editItemForm;
    targetForm.image = null;
    itemImagePreview.value = null;
};

const handleAddItem = (categoryId: number) => {
    addItemForm.post(storeItem.url(props.business.nanoid, categoryId), {
        preserveScroll: true,
        onSuccess: () => {
            addItemForm.reset();
            showAddItemForm.value = null;
            itemImagePreview.value = null;
        },
    });
};

const startEditItem = (item: typeof props.categories[0]['items'][0], categoryId: number) => {
    editingItemId.value = item.id;
    editingItemCategoryId.value = categoryId;
    editItemForm.name = item.name;
    editItemForm.description = item.description || '';
    editItemForm.price = item.price;
    itemImagePreview.value = item.image;
};

const cancelEditItem = () => {
    editingItemId.value = null;
    editingItemCategoryId.value = null;
    editItemForm.reset();
    itemImagePreview.value = null;
};

const handleUpdateItem = (categoryId: number, itemId: number) => {
    // Use the stored category ID if available (for safety)
    const targetCategoryId = editingItemCategoryId.value ?? categoryId;

    editItemForm.post(updateItem.url(props.business.nanoid, targetCategoryId, itemId), {
        preserveScroll: true,
        onSuccess: () => {
            editingItemId.value = null;
            editingItemCategoryId.value = null;
            editItemForm.reset();
            itemImagePreview.value = null;
        },
    });
};

const handleDeleteItem = (categoryId: number, itemId: number, itemName: string) => {
    if (confirm(`Are you sure you want to delete "${itemName}"?`)) {
        router.delete(destroyItem.url(props.business.nanoid, categoryId, itemId));
    }
};

const moveItemUp = (categoryId: number, item: typeof props.categories[0]['items'][0]) => {
    const items = sortedItems(categoryId);
    const currentIndex = items.findIndex((i) => i.id === item.id);
    if (currentIndex === 0) return;

    const newItems = items.map((i, index) => {
        if (index === currentIndex - 1) {
            return { id: i.id, order: currentIndex };
        }
        if (index === currentIndex) {
            return { id: i.id, order: currentIndex - 1 };
        }
        return { id: i.id, order: index };
    });

    router.post(reorderItems.url(props.business.nanoid, categoryId), { items: newItems });
};

const moveItemDown = (categoryId: number, item: typeof props.categories[0]['items'][0]) => {
    const items = sortedItems(categoryId);
    const currentIndex = items.findIndex((i) => i.id === item.id);
    if (currentIndex === items.length - 1) return;

    const newItems = items.map((i, index) => {
        if (index === currentIndex) {
            return { id: i.id, order: currentIndex + 1 };
        }
        if (index === currentIndex + 1) {
            return { id: i.id, order: currentIndex };
        }
        return { id: i.id, order: index };
    });

    router.post(reorderItems.url(props.business.nanoid, categoryId), { items: newItems });
};
</script>

<template>
    <Head :title="`Edit ${business.name}`" />

    <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
        <!-- Header -->
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-semibold">Edit Business</h1>
                <p class="text-sm text-muted-foreground">
                    Update information for {{ business.name }}
                </p>
            </div>
            <Button as-child variant="outline">
                <Link :href="show.url(business.nanoid)">
                    View Business
                </Link>
            </Button>
        </div>

        <!-- Tabs -->
        <Tabs v-model="activeTab" class="w-full">
            <TabsList class="grid w-full grid-cols-4">
                <TabsTrigger value="info">Business Info</TabsTrigger>
                <TabsTrigger value="location">Location</TabsTrigger>
                <TabsTrigger value="menu">Menu</TabsTrigger>
                <TabsTrigger value="seo">SEO</TabsTrigger>
            </TabsList>

            <!-- Business Info Tab -->
            <TabsContent value="info" class="space-y-6">
                <form @submit.prevent="submit" class="space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>Basic Information</CardTitle>
                            <CardDescription>Update the business name and branding</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="space-y-2">
                                <Label for="name">Business Name *</Label>
                                <Input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    :class="{ 'border-destructive': form.errors.name }"
                                    required
                                />
                                <p v-if="form.errors.name" class="text-sm text-destructive">
                                    {{ form.errors.name }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="logo">Logo</Label>
                                <div v-if="logoPreview" class="relative inline-block mb-2">
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
                                        class="flex-1"
                                        :class="{ 'border-destructive': form.errors.color }"
                                    />
                                </div>
                                <p v-if="form.errors.color" class="text-sm text-destructive">
                                    {{ form.errors.color }}
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <div class="flex gap-3">
                        <Button type="submit" :disabled="form.processing">
                            {{ form.processing ? 'Updating...' : 'Save Changes' }}
                        </Button>
                    </div>
                </form>
            </TabsContent>

            <!-- Location Tab -->
            <TabsContent value="location" class="space-y-6">
                <form @submit.prevent="submit" class="space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>Business Location</CardTitle>
                            <CardDescription>Set the address and coordinates using Google Maps</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="space-y-2">
                                <Label for="address">Address</Label>
                                <Input
                                    id="address"
                                    ref="mapInput"
                                    v-model="form.address"
                                    type="text"
                                    placeholder="Rechercher sur Google Places..."
                                    :class="{ 'border-destructive': form.errors.address }"
                                />
                                <p class="text-xs text-muted-foreground">
                                    Recherchez une adresse ou cliquez sur la carte pour placer le marqueur
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label>Carte</Label>
                                <div
                                    ref="mapContainer"
                                    class="h-[400px] w-full rounded-lg border bg-muted"
                                />
                                <p class="text-xs text-muted-foreground">
                                    Cliquez sur la carte ou déplacez le marqueur pour définir l'emplacement exact
                                </p>
                            </div>

                            <div v-if="form.lat && form.lng" class="rounded-lg border bg-muted/30 p-3">
                                <p class="text-sm font-medium">Coordonnées sélectionnées:</p>
                                <p class="text-sm text-muted-foreground">{{ form.lat }}, {{ form.lng }}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <div class="flex gap-3">
                        <Button type="submit" :disabled="form.processing">
                            {{ form.processing ? 'Updating...' : 'Save Changes' }}
                        </Button>
                    </div>
                </form>
            </TabsContent>

            <!-- Menu Tab -->
            <TabsContent value="menu" class="space-y-6">
                <!-- Add Category Form -->
                <Card v-if="showAddCategoryForm">
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <CardTitle>Add New Category</CardTitle>
                            <Button size="icon" variant="ghost" @click="showAddCategoryForm = false">
                                <X class="h-4 w-4" />
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="handleAddCategory" class="space-y-4">
                            <div class="space-y-2">
                                <Label for="add-category-name">Category Name *</Label>
                                <Input
                                    id="add-category-name"
                                    v-model="addCategoryForm.name"
                                    type="text"
                                    placeholder="Appetizers"
                                    required
                                />
                            </div>
                            <div class="flex gap-2">
                                <Button type="submit" :disabled="addCategoryForm.processing">
                                    <Save class="mr-2 h-4 w-4" />
                                    {{ addCategoryForm.processing ? 'Adding...' : 'Add Category' }}
                                </Button>
                                <Button type="button" variant="outline" @click="showAddCategoryForm = false">
                                    Cancel
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>

                <!-- Add Category Button -->
                <Button v-if="!showAddCategoryForm" @click="showAddCategoryForm = true" class="w-fit">
                    <Plus class="mr-2 h-4 w-4" />
                    Add Category
                </Button>

                <!-- Categories List -->
                <div v-if="sortedCategories.length > 0" class="space-y-6">
                    <Card v-for="category in sortedCategories" :key="category.id">
                        <CardHeader>
                            <!-- Category View Mode -->
                            <div v-if="editingCategoryId !== category.id" class="flex items-start justify-between">
                                <div>
                                    <CardTitle>{{ category.name }}</CardTitle>
                                    <CardDescription>{{ category.items.length }} items</CardDescription>
                                </div>
                                <div class="flex gap-1">
                                    <Button
                                        size="icon"
                                        variant="ghost"
                                        @click="moveCategoryUp(category)"
                                        :disabled="sortedCategories.findIndex((c) => c.id === category.id) === 0"
                                    >
                                        <ChevronUp class="h-4 w-4" />
                                    </Button>
                                    <Button
                                        size="icon"
                                        variant="ghost"
                                        @click="moveCategoryDown(category)"
                                        :disabled="sortedCategories.findIndex((c) => c.id === category.id) === sortedCategories.length - 1"
                                    >
                                        <ChevronDown class="h-4 w-4" />
                                    </Button>
                                    <Button size="icon" variant="ghost" @click="startEditCategory(category)">
                                        <Edit class="h-4 w-4" />
                                    </Button>
                                    <Button size="icon" variant="ghost" @click="handleDeleteCategory(category.id, category.name)">
                                        <Trash2 class="h-4 w-4 text-destructive" />
                                    </Button>
                                </div>
                            </div>

                            <!-- Category Edit Mode -->
                            <form v-else @submit.prevent="handleUpdateCategory(category.id)" class="space-y-4">
                                <div class="space-y-2">
                                    <Label>Category Name *</Label>
                                    <Input v-model="editCategoryForm.name" type="text" required />
                                </div>
                                <div class="flex gap-2">
                                    <Button type="submit" :disabled="editCategoryForm.processing" size="sm">
                                        <Save class="mr-2 h-4 w-4" />
                                        {{ editCategoryForm.processing ? 'Saving...' : 'Save Changes' }}
                                    </Button>
                                    <Button type="button" variant="outline" size="sm" @click="cancelEditCategory">
                                        Cancel
                                    </Button>
                                </div>
                            </form>
                        </CardHeader>

                        <CardContent>
                            <!-- Add Item Form -->
                            <Card v-if="showAddItemForm === category.id" class="mb-4">
                                <CardHeader>
                                    <div class="flex items-center justify-between">
                                        <CardTitle class="text-base">Add New Item</CardTitle>
                                        <Button size="icon" variant="ghost" @click="showAddItemForm = null">
                                            <X class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </CardHeader>
                                <CardContent>
                                    <form @submit.prevent="handleAddItem(category.id)" class="space-y-4">
                                        <div class="grid gap-4 md:grid-cols-2">
                                            <div class="space-y-2">
                                                <Label>Item Name *</Label>
                                                <Input v-model="addItemForm.name" type="text" placeholder="Caesar Salad" required />
                                            </div>
                                            <div class="space-y-2">
                                                <Label>Price (MAD) *</Label>
                                                <Input v-model="addItemForm.price" type="number" step="0.01" placeholder="45.00" required />
                                            </div>
                                        </div>
                                        <div class="space-y-2">
                                            <Label>Description</Label>
                                            <Textarea v-model="addItemForm.description" rows="2" />
                                        </div>
                                        <div class="space-y-2">
                                            <Label>Image</Label>
                                            <Input type="file" accept="image/*" @change="handleImageChange($event, 'add')" />
                                        </div>
                                        <div class="flex gap-2">
                                            <Button type="submit" :disabled="addItemForm.processing">
                                                <Save class="mr-2 h-4 w-4" />
                                                {{ addItemForm.processing ? 'Adding...' : 'Add Item' }}
                                            </Button>
                                            <Button type="button" variant="outline" @click="showAddItemForm = null">
                                                Cancel
                                            </Button>
                                        </div>
                                    </form>
                                </CardContent>
                            </Card>

                            <!-- Add Item Button -->
                            <Button
                                v-if="showAddItemForm !== category.id && editingCategoryId !== category.id"
                                @click="showAddItemForm = category.id"
                                size="sm"
                                variant="outline"
                                class="mb-4"
                            >
                                <Plus class="mr-2 h-4 w-4" />
                                Add Item
                            </Button>

                            <!-- Items List -->
                            <div v-if="sortedItems(category.id).length > 0" class="space-y-3">
                                <div
                                    v-for="item in sortedItems(category.id)"
                                    :key="item.id"
                                    class="rounded-lg border p-4"
                                >
                                    <!-- Item View Mode -->
                                    <div v-if="editingItemId !== item.id" class="flex items-start justify-between gap-4">
                                        <div class="flex gap-3 flex-1">
                                            <img
                                                v-if="item.image"
                                                :src="item.image"
                                                :alt="item.name"
                                                class="h-16 w-16 rounded-lg object-cover"
                                            />
                                            <div class="flex-1">
                                                <p class="font-medium">{{ item.name }}</p>
                                                <p v-if="item.description" class="text-sm text-muted-foreground">
                                                    {{ item.description }}
                                                </p>
                                                <p class="mt-1 font-semibold text-primary">{{ item.price }} MAD</p>
                                            </div>
                                        </div>
                                        <div class="flex gap-1">
                                            <Button
                                                size="icon"
                                                variant="ghost"
                                                @click="moveItemUp(category.id, item)"
                                                :disabled="sortedItems(category.id).findIndex((i) => i.id === item.id) === 0"
                                            >
                                                <ChevronUp class="h-4 w-4" />
                                            </Button>
                                            <Button
                                                size="icon"
                                                variant="ghost"
                                                @click="moveItemDown(category.id, item)"
                                                :disabled="sortedItems(category.id).findIndex((i) => i.id === item.id) === sortedItems(category.id).length - 1"
                                            >
                                                <ChevronDown class="h-4 w-4" />
                                            </Button>
                                            <Button size="icon" variant="ghost" @click="startEditItem(item, category.id)">
                                                <Edit class="h-4 w-4" />
                                            </Button>
                                            <Button size="icon" variant="ghost" @click="handleDeleteItem(category.id, item.id, item.name)">
                                                <Trash2 class="h-4 w-4 text-destructive" />
                                            </Button>
                                        </div>
                                    </div>

                                    <!-- Item Edit Mode -->
                                    <form v-else @submit.prevent="handleUpdateItem(category.id, item.id)" class="space-y-4">
                                        <div class="grid gap-4 md:grid-cols-2">
                                            <div class="space-y-2">
                                                <Label>Item Name *</Label>
                                                <Input v-model="editItemForm.name" type="text" required />
                                            </div>
                                            <div class="space-y-2">
                                                <Label>Price (MAD) *</Label>
                                                <Input v-model="editItemForm.price" type="number" step="0.01" required />
                                            </div>
                                        </div>
                                        <div class="space-y-2">
                                            <Label>Description</Label>
                                            <Textarea v-model="editItemForm.description" rows="2" />
                                        </div>
                                        <div class="space-y-2">
                                            <Label>Image</Label>
                                            <div v-if="itemImagePreview" class="relative inline-block mb-2">
                                                <img :src="itemImagePreview" alt="Item preview" class="h-24 w-24 rounded-lg border object-cover" />
                                                <Button
                                                    type="button"
                                                    size="icon"
                                                    variant="destructive"
                                                    class="absolute -right-2 -top-2 h-6 w-6 rounded-full"
                                                    @click="removeItemImage('edit')"
                                                >
                                                    <X class="h-3 w-3" />
                                                </Button>
                                            </div>
                                            <Input type="file" accept="image/*" @change="handleImageChange($event, 'edit')" />
                                        </div>
                                        <div class="flex gap-2">
                                            <Button type="submit" :disabled="editItemForm.processing" size="sm">
                                                <Save class="mr-2 h-4 w-4" />
                                                {{ editItemForm.processing ? 'Saving...' : 'Save Changes' }}
                                            </Button>
                                            <Button type="button" variant="outline" size="sm" @click="cancelEditItem">
                                                Cancel
                                            </Button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <p v-else class="text-center text-sm text-muted-foreground py-8">
                                No items in this category yet.
                            </p>
                        </CardContent>
                    </Card>
                </div>

                <!-- Empty State -->
                <Card v-else>
                    <CardContent class="py-12">
                        <p class="text-center text-sm text-muted-foreground">
                            No categories added yet. Click "Add Category" to create your first menu category.
                        </p>
                    </CardContent>
                </Card>
            </TabsContent>

            <!-- SEO Tab -->
            <TabsContent value="seo" class="space-y-6">
                <form @submit.prevent="submit" class="space-y-6">
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
                                    :class="{ 'border-destructive': form.errors.seo_title }"
                                />
                            </div>

                            <div class="space-y-2">
                                <Label for="seo_description">Meta Description</Label>
                                <Textarea
                                    id="seo_description"
                                    v-model="form.seo_description"
                                    rows="3"
                                    :class="{ 'border-destructive': form.errors.seo_description }"
                                />
                            </div>

                            <div class="space-y-2">
                                <Label for="seo_keywords">Meta Keywords</Label>
                                <Input
                                    id="seo_keywords"
                                    v-model="form.seo_keywords"
                                    type="text"
                                    :class="{ 'border-destructive': form.errors.seo_keywords }"
                                />
                            </div>
                        </CardContent>
                    </Card>

                    <div class="flex gap-3">
                        <Button type="submit" :disabled="form.processing">
                            {{ form.processing ? 'Updating...' : 'Save Changes' }}
                        </Button>
                    </div>
                </form>
            </TabsContent>
        </Tabs>
    </div>
</template>
