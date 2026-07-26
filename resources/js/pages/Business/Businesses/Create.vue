<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { store } from '@/actions/App/Http/Controllers/Business/BusinessController';
import { index as dashboard } from '@/actions/App/Http/Controllers/Business/DashboardController';
import { ref } from 'vue';
import { X } from 'lucide-vue-next';
import LocationPicker from '@/components/LocationPicker.vue';
import QrStylePicker from '@/components/QrStylePicker.vue';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/business/dashboard' },
            { title: 'Create business' },
        ],
    },
});

defineProps<{
    currencies: Array<{ code: string; name: string; symbol: string; label: string }>;
    qrStyles: Array<{
        id: string;
        name: string;
        tagline: string;
        subtitle: string;
        description: string;
        requires_logo?: boolean;
    }>;
    subscription: {
        is_premium: boolean;
        can_create_business: boolean;
        free_business_limit: number;
    };
}>();

const form = useForm({
    name: '',
    address: '',
    lat: null as number | null,
    lng: null as number | null,
    logo: null as File | null,
    color: '#e4572e',
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
        forceFormData: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Create business" />

    <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
        <div>
            <h1 class="text-2xl font-semibold">Create business</h1>
            <p class="text-sm text-muted-foreground">
                Add your restaurant to get a public page, menu, and QR code.
            </p>
        </div>

        <form class="space-y-6" @submit.prevent="submit">
            <Card>
                <CardHeader>
                    <CardTitle>Basic information</CardTitle>
                    <CardDescription>Enter the business name and location</CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="space-y-2">
                        <Label for="name">Business name *</Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            type="text"
                            placeholder="Restaurant Casa Blanca"
                            :class="{ 'border-destructive': form.errors.name }"
                            required
                        />
                        <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
                    </div>

                    <LocationPicker
                        v-model:address="form.address"
                        v-model:lat="form.lat"
                        v-model:lng="form.lng"
                    />
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Branding</CardTitle>
                    <CardDescription>Customize the look of your public page</CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="space-y-2">
                        <Label for="logo">Logo</Label>
                        <div v-if="logoPreview" class="relative inline-block">
                            <img :src="logoPreview" alt="Logo preview" class="h-24 w-24 rounded-lg border object-cover" />
                            <Button
                                type="button"
                                size="icon"
                                variant="destructive"
                                class="absolute -top-2 -right-2 h-6 w-6 rounded-full"
                                @click="removeLogo"
                            >
                                <X class="h-3 w-3" />
                            </Button>
                        </div>
                        <Input
                            id="logo-upload"
                            type="file"
                            accept="image/*"
                            :class="{ 'border-destructive': form.errors.logo }"
                            @change="handleLogoChange"
                        />
                        <p v-if="form.errors.logo" class="text-sm text-destructive">{{ form.errors.logo }}</p>
                    </div>

                    <div class="space-y-2">
                        <Label for="color">Brand color</Label>
                        <div class="flex items-center gap-3">
                            <Input id="color" v-model="form.color" type="color" class="h-10 w-20 cursor-pointer" />
                            <Input v-model="form.color" type="text" placeholder="#e4572e" class="flex-1" />
                        </div>
                        <p v-if="form.errors.color" class="text-sm text-destructive">{{ form.errors.color }}</p>
                    </div>

                    <div class="space-y-2">
                        <Label for="currency">Currency *</Label>
                        <select
                            id="currency"
                            v-model="form.currency"
                            class="border-input bg-background focus-visible:border-ring focus-visible:ring-ring/50 h-9 w-full rounded-md border px-3 text-sm shadow-xs outline-none focus-visible:ring-[3px]"
                            required
                        >
                            <option v-for="currency in currencies" :key="currency.code" :value="currency.code">
                                {{ currency.label }}
                            </option>
                        </select>
                        <p v-if="form.errors.currency" class="text-sm text-destructive">{{ form.errors.currency }}</p>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>QR card design *</CardTitle>
                    <CardDescription>Pick a printable QR design</CardDescription>
                </CardHeader>
                <CardContent>
                    <QrStylePicker
                        v-model="form.qr_style"
                        :styles="qrStyles"
                        :brand-color="form.color"
                        :has-logo="!!logoPreview || !!form.logo"
                    />
                    <p v-if="form.errors.qr_style" class="mt-2 text-sm text-destructive">{{ form.errors.qr_style }}</p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>SEO settings</CardTitle>
                    <CardDescription>Optional search engine fields</CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="space-y-2">
                        <Label for="seo_title">SEO title</Label>
                        <Input id="seo_title" v-model="form.seo_title" type="text" />
                    </div>
                    <div class="space-y-2">
                        <Label for="seo_description">SEO description</Label>
                        <Textarea id="seo_description" v-model="form.seo_description" rows="3" />
                    </div>
                    <div class="space-y-2">
                        <Label for="seo_keywords">SEO keywords</Label>
                        <Input id="seo_keywords" v-model="form.seo_keywords" type="text" />
                    </div>
                </CardContent>
            </Card>

            <div class="flex gap-3">
                <Button type="submit" :disabled="form.processing">
                    {{ form.processing ? 'Creating...' : 'Create business' }}
                </Button>
                <Button as-child type="button" variant="outline">
                    <Link :href="dashboard.url()">Cancel</Link>
                </Button>
            </div>
        </form>
    </div>
</template>
