<script setup lang="ts">
import { ref, onMounted, computed, nextTick } from 'vue';
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

const props = defineProps<{
    businessUsers: Array<{ id: number; name: string; email: string }>;
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
    seo_title: '',
});

const logoPreview = ref<string | null>(null);

const processing = ref(false);
const mapContainer = ref<HTMLDivElement | null>(null);
const mapInput = ref<HTMLInputElement | null>(null);
const googleMapsApiKey = import.meta.env.VITE_GOOGLE_MAPS_API_KEY;

let map: google.maps.Map | null = null;
let marker: google.maps.Marker | null = null;
let autocomplete: google.maps.places.Autocomplete | null = null;

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
    autocomplete = new window.google.maps.places.Autocomplete(inputElement, {
        types: ['establishment', 'geocode'],
        componentRestrictions: { country: 'ma' },
    });

    autocomplete.addListener('place_changed', () => {
        const place = autocomplete!.getPlace();

        if (place.geometry && place.geometry.location) {
            form.value.address = place.formatted_address || '';
            form.value.lat = place.geometry.location.lat();
            form.value.lng = place.geometry.location.lng();

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
    const defaultCenter = { lat: 33.5731, lng: -7.5898 }; // Casablanca
    map = new google.maps.Map(mapContainer.value, {
        center: defaultCenter,
        zoom: 11,
        mapTypeControl: false,
        streetViewControl: false,
        fullscreenControl: false,
    });

    // Initialize marker
    marker = new google.maps.Marker({
        map: map,
        draggable: true,
        visible: false,
    });

    // Update form when marker is dragged
    marker.addListener('dragend', () => {
        const position = marker!.getPosition();
        if (position) {
            form.value.lat = position.lat();
            form.value.lng = position.lng();

            // Reverse geocode to get address
            const geocoder = new google.maps.Geocoder();
            geocoder.geocode({ location: position }, (results, status) => {
                if (status === 'OK' && results && results[0]) {
                    form.value.address = results[0].formatted_address;
                }
            });
        }
    });

    // Allow clicking on map to place marker
    map.addListener('click', (e: google.maps.MapMouseEvent) => {
        if (e.latLng) {
            form.value.lat = e.latLng.lat();
            form.value.lng = e.latLng.lng();
            marker!.setPosition(e.latLng);
            marker!.setVisible(true);

            // Reverse geocode
            const geocoder = new google.maps.Geocoder();
            geocoder.geocode({ location: e.latLng }, (results, status) => {
                if (status === 'OK' && results && results[0]) {
                    form.value.address = results[0].formatted_address;
                }
            });
        }
    });
};

// Load Google Maps script when modal opens
onMounted(() => {
    if (googleMapsApiKey && !window.google) {
        const script = document.createElement('script');
        script.src = `https://maps.googleapis.com/maps/api/js?key=${googleMapsApiKey}&libraries=places&loading=async`;
        script.async = true;
        script.defer = true;
        document.head.appendChild(script);
    }
});

const nextStep = () => {
    if (currentStep.value < totalSteps && canGoNext.value) {
        currentStep.value++;
        if (currentStep.value === 2) {
            // Initialize map when step 2 is shown
            nextTick(() => initGoogleMaps());
        }
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
        seo_title: '',
    };
    userSearch.value = '';
    logoPreview.value = null;
    if (marker) marker.setVisible(false);
};
</script>

<template>
    <Dialog v-model:open="open" @update:open="(val) => !val && resetForm()">
        <DialogContent class="sm:max-w-3xl max-h-[90vh] overflow-y-auto">
            <DialogHeader>
                <DialogTitle>Créer une nouvelle entreprise</DialogTitle>
                <DialogDescription>
                    Étape {{ currentStep }} sur {{ totalSteps }}
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
                        <Label for="name">Nom de l'entreprise *</Label>
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
                                        <span class="font-semibold">Cliquez pour télécharger</span> ou glissez-déposez
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
                        <Label for="business_user_id">Propriétaire (Optionnel)</Label>
                        <div class="relative">
                            <div class="relative">
                                <Input
                                    v-model="userSearch"
                                    type="text"
                                    placeholder="Rechercher un utilisateur..."
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
                                        Aucun (Entreprise orpheline)
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
                                        Aucun utilisateur trouvé
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p class="text-xs text-muted-foreground">
                            Sélectionnez un utilisateur ou laissez vide pour une entreprise orpheline
                        </p>
                    </div>
                </div>

                <!-- Step 2: Location with Google Maps -->
                <div v-if="currentStep === 2" class="space-y-4">
                    <div class="space-y-2">
                        <Label for="address">Adresse</Label>
                        <Input
                            id="address"
                            ref="mapInput"
                            v-model="form.address"
                            type="text"
                            placeholder="Rechercher sur Google Places..."
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
                </div>

                <!-- Step 3: Branding -->
                <div v-if="currentStep === 3" class="space-y-4">
                    <div class="space-y-2">
                        <Label for="seo_title">Titre de la page (SEO)</Label>
                        <Input
                            id="seo_title"
                            v-model="form.seo_title"
                            type="text"
                            placeholder="Titre personnalisé (optionnel)"
                        />
                        <p class="text-xs text-muted-foreground">
                            Si vide, le nom de l'entreprise sera utilisé
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="color">Couleur de marque</Label>
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

                    <!-- Summary -->
                    <div class="rounded-lg border bg-muted/30 p-4 space-y-3">
                        <h3 class="font-semibold">Récapitulatif</h3>

                        <div class="space-y-2 text-sm">
                            <div>
                                <span class="text-muted-foreground">Nom:</span>
                                <span class="ml-2 font-medium">{{ form.name }}</span>
                            </div>

                            <div v-if="selectedUserName">
                                <span class="text-muted-foreground">Propriétaire:</span>
                                <span class="ml-2 font-medium">{{ selectedUserName }}</span>
                            </div>

                            <div v-if="form.address">
                                <span class="text-muted-foreground">Adresse:</span>
                                <span class="ml-2 font-medium">{{ form.address }}</span>
                            </div>

                            <div>
                                <span class="text-muted-foreground">Couleur:</span>
                                <span class="ml-2 inline-block h-4 w-8 rounded border" :style="{ backgroundColor: form.color }"></span>
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
                        Précédent
                    </Button>

                    <div class="flex-1"></div>

                    <Button
                        v-if="currentStep < totalSteps"
                        type="button"
                        @click="nextStep"
                        :disabled="!canGoNext"
                    >
                        Suivant
                        <ChevronRight class="ml-2 h-4 w-4" />
                    </Button>

                    <Button
                        v-if="currentStep === totalSteps"
                        type="button"
                        @click="submit"
                        :disabled="processing"
                    >
                        {{ processing ? 'Création...' : 'Créer l\'entreprise' }}
                    </Button>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>
