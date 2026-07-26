<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { store, index } from '@/actions/App/Http/Controllers/Admin/BusinessController';
import { ref } from 'vue';
import { X } from 'lucide-vue-next';
import LocationPicker from '@/components/LocationPicker.vue';
import QrStylePicker from '@/components/QrStylePicker.vue';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/adminos/dashboard' },
            { title: 'Businesses', href: index.url() },
            { title: 'Create Business' },
        ],
    },
});

const props = defineProps<{
    businessUsers: Array<{ id: number; name: string; email: string }>;
    currencies: Array<{ code: string; name: string; symbol: string; label: string }>;
    qrStyles: Array<{ id: string; name: string; tagline: string; subtitle: string; description: string; requires_logo?: boolean }>;
}>();

const form = useForm({
    name: '',
    address: '',
    lat: null as number | null,
    lng: null as number | null,
    logo: null as File | null,
    color: '#4d54d9',
    currency: 'MAD',
    qr_style: 'pulse',
    seo_title: '',
    seo_description: '',
    seo_keywords: '',
});

const logoPreview = ref<string | null>(null);

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

                    <LocationPicker
                        v-model:address="form.address"
                        v-model:lat="form.lat"
                        v-model:lng="form.lng"
                    />
                    <p v-if="form.errors.address" class="text-sm text-destructive">
                        {{ form.errors.address }}
                    </p>
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
                    </div>

                    <div class="space-y-2">
                        <Label for="color">Brand Color</Label>
                        <div class="flex gap-3 items-center">
                            <Input
                                id="color"
                                v-model="form.color"
                                type="color"
                                class="h-10 w-20 cursor-pointer"
                            />
                            <Input
                                v-model="form.color"
                                type="text"
                                placeholder="#4d54d9"
                                class="flex-1"
                            />
                        </div>
                        <p v-if="form.errors.color" class="text-sm text-destructive">
                            {{ form.errors.color }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="currency">Currency *</Label>
                        <select
                            id="currency"
                            v-model="form.currency"
                            class="border-input bg-background h-9 w-full rounded-md border px-3 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]"
                            required
                        >
                            <option v-for="currency in currencies" :key="currency.code" :value="currency.code">
                                {{ currency.label }}
                            </option>
                        </select>
                        <p v-if="form.errors.currency" class="text-sm text-destructive">
                            {{ form.errors.currency }}
                        </p>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>QR Card Design *</CardTitle>
                    <CardDescription>Pick one of three catchy designs for the printable QR image</CardDescription>
                </CardHeader>
                <CardContent>
                    <QrStylePicker
                        v-model="form.qr_style"
                        :styles="qrStyles"
                        :brand-color="form.color"
                        :has-logo="!!logoPreview || !!form.logo"
                    />
                    <p v-if="form.errors.qr_style" class="mt-2 text-sm text-destructive">
                        {{ form.errors.qr_style }}
                    </p>
                </CardContent>
            </Card>

            <!-- SEO -->
            <Card>
                <CardHeader>
                    <CardTitle>SEO Settings</CardTitle>
                    <CardDescription>Optional search engine optimization fields</CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="space-y-2">
                        <Label for="seo_title">SEO Title</Label>
                        <Input
                            id="seo_title"
                            v-model="form.seo_title"
                            type="text"
                            placeholder="Leave empty to use business name"
                        />
                    </div>

                    <div class="space-y-2">
                        <Label for="seo_description">SEO Description</Label>
                        <Textarea
                            id="seo_description"
                            v-model="form.seo_description"
                            placeholder="Brief description for search engines"
                            rows="3"
                        />
                    </div>

                    <div class="space-y-2">
                        <Label for="seo_keywords">SEO Keywords</Label>
                        <Input
                            id="seo_keywords"
                            v-model="form.seo_keywords"
                            type="text"
                            placeholder="restaurant, casablanca, food"
                        />
                    </div>
                </CardContent>
            </Card>

            <div class="flex gap-3">
                <Button type="submit" :disabled="form.processing">
                    {{ form.processing ? 'Creating...' : 'Create Business' }}
                </Button>
                <Button as-child type="button" variant="outline">
                    <Link :href="index.url()">Cancel</Link>
                </Button>
            </div>
        </form>
    </div>
</template>
