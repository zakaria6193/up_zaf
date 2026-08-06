<script setup lang="ts">
import { ref, computed, nextTick, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { store } from '@/actions/App/Http/Controllers/Admin/BusinessController';
import { ChevronLeft, ChevronRight, Search, Upload, X } from 'lucide-vue-next';
import LocationPicker from '@/components/LocationPicker.vue';
import QrStylePicker from '@/components/QrStylePicker.vue';

const props = defineProps<{
    businessUsers: Array<{ id: number; name: string; email: string }>;
    currencies: Array<{ code: string; name: string; symbol: string; label: string }>;
    qrStyles?: Array<{ id: string; name: string; tagline: string; subtitle: string; description: string }>;
}>();

const open = defineModel<boolean>('open', { default: false });

const currentStep = ref(1);
const totalSteps = 3;

// Form data
const form = ref({
    business_user_id: null as number | null,
    name: '',
    address: '',
    lat: null as number | null,
    lng: null as number | null,
    logo: null as File | null,
    color: '#4d54d9',
    currency: 'MAD',
    qr_style: 'pulse',
    seo_title: '',
});

const defaultQrStyles = [
    {
        id: 'pulse',
        name: 'Pulse',
        tagline: 'Discover the menu',
        subtitle: '',
        description: 'Soft light card',
        requires_logo: false,
    },
    {
        id: 'noir',
        name: 'Noir',
        tagline: "See what's cooking",
        subtitle: '',
        description: 'Bold dark card',
        requires_logo: false,
    },
    {
        id: 'emblem',
        name: 'Emblem',
        tagline: 'Explore the menu',
        subtitle: '',
        description: 'Logo woven in',
        requires_logo: true,
    },
];

const qrStyleOptions = computed(() => props.qrStyles?.length ? props.qrStyles : defaultQrStyles);
const hasLogoSelected = computed(() => !!form.value.logo || !!logoPreview.value);

const logoPreview = ref<string | null>(null);
const processing = ref(false);
const locationPicker = ref<{ refresh: () => Promise<void> } | null>(null);

// User search
const userSearch = ref('');
const showUserDropdown = ref(false);
const selectedUserName = computed(() => {
    if (!form.value.business_user_id) return '';
    const user = props.businessUsers.find(u => u.id === form.value.business_user_id);
    return user ? `${user.name} (${user.email})` : '';
});

const filteredUsers = computed(() => {
    const search = userSearch.value.toLowerCase();
    if (!search) return props.businessUsers;
    return props.businessUsers.filter(user =>
        user.name.toLowerCase().includes(search) ||
        user.email.toLowerCase().includes(search)
    );
});

const selectUser = (userId: number | null) => {
    form.value.business_user_id = userId;
    showUserDropdown.value = false;
    if (userId === null) {
        userSearch.value = '';
    }
};

const handleLogoUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];
    if (file) {
        form.value.logo = file;
        // Create preview
        const reader = new FileReader();
        reader.onload = (e) => {
            logoPreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
};

const removeLogo = () => {
    form.value.logo = null;
    logoPreview.value = null;
};

const canGoNext = computed(() => {
    if (currentStep.value === 1) {
        return form.value.name.trim().length > 0;
    }
    return true;
});

watch(currentStep, async (step) => {
    if (step === 2) {
        await nextTick();
        await locationPicker.value?.refresh();
    }
});

const nextStep = () => {
    if (currentStep.value < totalSteps && canGoNext.value) {
        currentStep.value++;
    }
};

const prevStep = () => {
    if (currentStep.value > 1) {
        currentStep.value--;
    }
};

const submit = async () => {
    processing.value = true;

    // Prepare form data (use FormData for file upload)
    const formData = new FormData();
    if (form.value.business_user_id) {
        formData.append('business_user_id', form.value.business_user_id.toString());
    }
    formData.append('name', form.value.name);
    if (form.value.address) {
        formData.append('address', form.value.address);
    }
    if (form.value.lat) {
        formData.append('lat', form.value.lat.toString());
    }
    if (form.value.lng) {
        formData.append('lng', form.value.lng.toString());
    }
    if (form.value.logo) {
        formData.append('logo', form.value.logo);
    }
    formData.append('color', form.value.color);
    formData.append('currency', form.value.currency);
    formData.append('qr_style', form.value.qr_style);
    formData.append('seo_title', form.value.seo_title || form.value.name);

    // Submit form
    router.post(store.url(), formData, {
        onSuccess: () => {
            open.value = false;
            resetForm();
        },
        onFinish: () => {
            processing.value = false;
        },
    });
};

const resetForm = () => {
    currentStep.value = 1;
    form.value = {
        business_user_id: null,
        name: '',
        address: '',
        lat: null,
        lng: null,
        logo: null,
        color: '#4d54d9',
        currency: 'MAD',
        qr_style: 'pulse',
        seo_title: '',
    };
    userSearch.value = '';
    logoPreview.value = null;
};
</script>

<template>
    <Dialog v-model:open="open" @update:open="(val) => !val && resetForm()">
        <DialogContent class="sm:max-w-3xl max-h-[90vh] overflow-y-auto">
            <DialogHeader>
                <DialogTitle>Create new business</DialogTitle>
                <DialogDescription>
                    Step {{ currentStep }} of {{ totalSteps }}
                </DialogDescription>
            </DialogHeader>

            <div class="space-y-6">
                <!-- Step Progress -->
                <div class="flex gap-2">
                    <div
                        v-for="step in totalSteps"
                        :key="step"
                        class="h-2 flex-1 rounded-full transition-colors"
                        :class="step <= currentStep ? 'bg-primary' : 'bg-muted'"
                    />
                </div>

                <!-- Step 1: Business Name & Owner -->
                <div v-if="currentStep === 1" class="space-y-4">
                    <div class="space-y-2">
                        <Label for="name">Business name *</Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            type="text"
                            placeholder="Restaurant Casa Blanca"
                            required
                            autofocus
                        />
                    </div>

                    <div class="space-y-2">
                        <Label for="logo">Logo</Label>
                        <div v-if="!logoPreview" class="flex items-center justify-center w-full">
                            <label
                                for="logo"
                                class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-lg cursor-pointer bg-muted/30 hover:bg-muted/50 transition-colors"
                            >
                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <Upload class="w-8 h-8 mb-2 text-muted-foreground" />
                                    <p class="mb-1 text-sm text-muted-foreground">
                                        <span class="font-semibold">Click to upload</span> or drag and drop
                                    </p>
                                    <p class="text-xs text-muted-foreground">PNG, JPG (MAX. 2MB)</p>
                                </div>
                                <input
                                    id="logo"
                                    type="file"
                                    class="hidden"
                                    accept="image/*"
                                    @change="handleLogoUpload"
                                />
                            </label>
                        </div>
                        <div v-else class="relative">
                            <img
                                :src="logoPreview"
                                alt="Logo preview"
                                class="w-full h-32 object-cover rounded-lg border"
                            />
                            <Button
                                size="icon"
                                variant="destructive"
                                class="absolute top-2 right-2"
                                @click="removeLogo"
                                type="button"
                            >
                                <X class="h-4 w-4" />
                            </Button>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label for="business_user_id">Owner (Optional)</Label>
                        <div class="relative">
                            <div class="relative">
                                <Input
                                    v-model="userSearch"
                                    type="text"
                                    placeholder="Search for a user..."
                                    @focus="showUserDropdown = true"
                                    @blur="setTimeout(() => showUserDropdown = false, 200)"
                                    :value="selectedUserName || userSearch"
                                />
                                <Search class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                            </div>

                            <div
                                v-if="showUserDropdown"
                                class="absolute z-50 mt-1 w-full rounded-md border bg-popover shadow-md"
                            >
                                <div class="max-h-60 overflow-y-auto p-1">
                                    <div
                                        class="relative flex cursor-pointer select-none items-center rounded-sm px-2 py-1.5 text-sm outline-none hover:bg-accent hover:text-accent-foreground"
                                        @click="selectUser(null)"
                                    >
                                        None (orphan business)
                                    </div>
                                    <div
                                        v-for="user in filteredUsers"
                                        :key="user.id"
                                        class="relative flex cursor-pointer select-none items-center rounded-sm px-2 py-1.5 text-sm outline-none hover:bg-accent hover:text-accent-foreground"
                                        :class="{ 'bg-accent': form.business_user_id === user.id }"
                                        @click="selectUser(user.id)"
                                    >
                                        <div>
                                            <div class="font-medium">{{ user.name }}</div>
                                            <div class="text-xs text-muted-foreground">{{ user.email }}</div>
                                        </div>
                                    </div>
                                    <div
                                        v-if="filteredUsers.length === 0"
                                        class="px-2 py-6 text-center text-sm text-muted-foreground"
                                    >
                                        No users found
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p class="text-xs text-muted-foreground">
                            Select a user or leave empty for an orphan business
                        </p>
                    </div>
                </div>

                <!-- Step 2: Location -->
                <div v-if="currentStep === 2" class="space-y-4">
                    <LocationPicker
                        ref="locationPicker"
                        v-model:address="form.address"
                        v-model:lat="form.lat"
                        v-model:lng="form.lng"
                        :active="currentStep === 2"
                    />
                </div>

                <!-- Step 3: Branding -->
                <div v-if="currentStep === 3" class="space-y-4">
                    <div class="space-y-2">
                        <Label for="seo_title">Page title (SEO)</Label>
                        <Input
                            id="seo_title"
                            v-model="form.seo_title"
                            type="text"
                            placeholder="Custom title (optional)"
                        />
                        <p class="text-xs text-muted-foreground">
                            If empty, the business name will be used
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="color">Brand color</Label>
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
                            />
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label for="currency">Currency</Label>
                        <select
                            id="currency"
                            v-model="form.currency"
                            class="border-input bg-background h-9 w-full rounded-md border px-3 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]"
                        >
                            <option v-for="currency in currencies" :key="currency.code" :value="currency.code">
                                {{ currency.label }}
                            </option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <Label>QR design *</Label>
                        <QrStylePicker
                            v-model="form.qr_style"
                            :styles="qrStyleOptions"
                            :brand-color="form.color"
                            :has-logo="hasLogoSelected"
                        />
                    </div>

                    <!-- Summary -->
                    <div class="rounded-lg border bg-muted/30 p-4 space-y-3">
                        <h3 class="font-semibold">Summary</h3>

                        <div class="space-y-2 text-sm">
                            <div>
                                <span class="text-muted-foreground">Name:</span>
                                <span class="ml-2 font-medium">{{ form.name }}</span>
                            </div>

                            <div v-if="selectedUserName">
                                <span class="text-muted-foreground">Owner:</span>
                                <span class="ml-2 font-medium">{{ selectedUserName }}</span>
                            </div>

                            <div v-if="form.address">
                                <span class="text-muted-foreground">Address:</span>
                                <span class="ml-2 font-medium">{{ form.address }}</span>
                            </div>

                            <div>
                                <span class="text-muted-foreground">Color:</span>
                                <span class="ml-2 inline-block h-4 w-8 rounded border" :style="{ backgroundColor: form.color }"></span>
                            </div>

                            <div>
                                <span class="text-muted-foreground">QR:</span>
                                <span class="ml-2 font-medium">{{ qrStyleOptions.find(s => s.id === form.qr_style)?.name }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation Buttons -->
                <div class="flex justify-between gap-3 pt-4 border-t">
                    <Button
                        v-if="currentStep > 1"
                        type="button"
                        variant="outline"
                        @click="prevStep"
                    >
                        <ChevronLeft class="mr-2 h-4 w-4" />
                        Previous
                    </Button>

                    <div class="flex-1"></div>

                    <Button
                        v-if="currentStep < totalSteps"
                        type="button"
                        @click="nextStep"
                        :disabled="!canGoNext"
                    >
                        Next
                        <ChevronRight class="ml-2 h-4 w-4" />
                    </Button>

                    <Button
                        v-if="currentStep === totalSteps"
                        type="button"
                        @click="submit"
                        :disabled="processing"
                    >
                        {{ processing ? 'Creating...' : 'Create business' }}
                    </Button>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>
