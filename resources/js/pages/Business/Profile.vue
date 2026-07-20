<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, nextTick } from 'vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Upload, X, Save, Trash2 } from 'lucide-vue-next';
import BusinessSwitcher from '@/components/BusinessSwitcher.vue';
import LocationPicker from '@/components/LocationPicker.vue';
import { update, deleteLogo as deleteLogoRoute } from '@/actions/App/Http/Controllers/Business/ProfileController';

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
    googleMapsApiKey?: string;
}>();

const form = useForm({
    name: props.business.name,
    address: props.business.address || '',
    lat: props.business.lat !== null ? Number(props.business.lat) : null,
    lng: props.business.lng !== null ? Number(props.business.lng) : null,
    color: props.business.color,
    seo_title: props.business.seo_title,
    seo_description: props.business.seo_description,
    seo_keywords: props.business.seo_keywords,
    logo: null as File | null,
});

const logoPreview = ref<string | null>(props.business.logo);
const fileInput = ref<HTMLInputElement | null>(null);
const locationPicker = ref<{ refresh: () => Promise<void> } | null>(null);
const activeTab = ref('basic');

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
        router.delete(deleteLogoRoute.url(props.business.nanoid), {
            preserveScroll: true,
            onSuccess: () => {
                logoPreview.value = null;
            },
        });
    }
};

// Submit form
const submit = () => {
    form.put(update.url(props.business.nanoid), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            form.logo = null;
            if (fileInput.value) {
                fileInput.value.value = '';
            }
        },
    });
};

// Handle tab change - refresh map when Location tab is opened
const handleTabChange = (value: string | number) => {
    activeTab.value = String(value);
    if (value === 'location') {
        nextTick(() => locationPicker.value?.refresh());
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
                label="Editing:"
            />
        </div>

        <!-- Tabs -->
        <Tabs v-model="activeTab" @update:model-value="handleTabChange">
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
                        <LocationPicker
                            ref="locationPicker"
                            v-model:address="form.address"
                            v-model:lat="form.lat"
                            v-model:lng="form.lng"
                            :active="activeTab === 'location'"
                        />
                        <p v-if="form.errors.address" class="text-sm text-red-600">{{ form.errors.address }}</p>

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
