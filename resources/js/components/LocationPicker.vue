<script setup lang="ts">
import { nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { Search, MapPin, Loader2 } from 'lucide-vue-next';

const address = defineModel<string>('address', { default: '' });
const lat = defineModel<number | null>('lat', { default: null });
const lng = defineModel<number | null>('lng', { default: null });

const props = withDefaults(
    defineProps<{
        height?: string;
        countryCode?: string;
        /** Set true when the map becomes visible (e.g. tab/step change) */
        active?: boolean;
    }>(),
    {
        height: '400px',
        countryCode: 'ma',
        active: true,
    },
);

const mapEl = ref<HTMLDivElement | null>(null);
const searchQuery = ref('');
const searching = ref(false);
const searchResults = ref<Array<{ display_name: string; lat: string; lon: string }>>([]);
const showResults = ref(false);

type LeafletModule = typeof import('leaflet');
type LeafletMap = import('leaflet').Map;
type LeafletMarker = import('leaflet').Marker;
type LeafletLatLngExpression = import('leaflet').LatLngExpression;

let L: LeafletModule | null = null;
let map: LeafletMap | null = null;
let marker: LeafletMarker | null = null;
let searchTimeout: ReturnType<typeof setTimeout> | null = null;

const defaultCenter: [number, number] = [33.5731, -7.5898]; // Casablanca

const loadLeaflet = async (): Promise<LeafletModule> => {
    if (L) {
        return L;
    }

    const leaflet = await import('leaflet');
    await import('leaflet/dist/leaflet.css');

    const markerIcon2x = (await import('leaflet/dist/images/marker-icon-2x.png')).default;
    const markerIcon = (await import('leaflet/dist/images/marker-icon.png')).default;
    const markerShadow = (await import('leaflet/dist/images/marker-shadow.png')).default;

    // Fix Vite broken default marker paths
    delete (leaflet.Icon.Default.prototype as unknown as { _getIconUrl?: unknown })._getIconUrl;
    leaflet.Icon.Default.mergeOptions({
        iconRetinaUrl: markerIcon2x,
        iconUrl: markerIcon,
        shadowUrl: markerShadow,
    });

    L = leaflet;
    return leaflet;
};

const setMarker = (position: LeafletLatLngExpression, pan = true) => {
    if (!map || !L) {
        return;
    }

    if (marker) {
        marker.setLatLng(position);
    } else {
        marker = L.marker(position, { draggable: true }).addTo(map);
        marker.on('dragend', () => {
            const pos = marker?.getLatLng();
            if (!pos) {
                return;
            }
            lat.value = Number(pos.lat.toFixed(7));
            lng.value = Number(pos.lng.toFixed(7));
            reverseGeocode(pos.lat, pos.lng);
        });
    }

    if (pan) {
        map.setView(position, Math.max(map.getZoom(), 15));
    }
};

const reverseGeocode = async (latitude: number, longitude: number) => {
    try {
        const response = await fetch(
            `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${latitude}&lon=${longitude}`,
            {
                headers: {
                    Accept: 'application/json',
                },
            },
        );

        if (!response.ok) {
            return;
        }

        const data = await response.json();
        if (data?.display_name) {
            address.value = data.display_name;
            searchQuery.value = data.display_name;
        }
    } catch {
        // Keep manual address if reverse geocoding fails
    }
};

const searchAddress = async () => {
    const query = searchQuery.value.trim();
    if (query.length < 3) {
        searchResults.value = [];
        showResults.value = false;
        return;
    }

    searching.value = true;
    try {
        const params = new URLSearchParams({
            format: 'jsonv2',
            q: query,
            addressdetails: '1',
            limit: '5',
            countrycodes: props.countryCode,
        });

        const response = await fetch(`https://nominatim.openstreetmap.org/search?${params.toString()}`, {
            headers: {
                Accept: 'application/json',
            },
        });

        if (!response.ok) {
            searchResults.value = [];
            return;
        }

        searchResults.value = await response.json();
        showResults.value = searchResults.value.length > 0;
    } catch {
        searchResults.value = [];
        showResults.value = false;
    } finally {
        searching.value = false;
    }
};

const onSearchInput = () => {
    address.value = searchQuery.value;
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
    searchTimeout = setTimeout(() => {
        void searchAddress();
    }, 400);
};

const selectResult = (result: { display_name: string; lat: string; lon: string }) => {
    const latitude = Number(result.lat);
    const longitude = Number(result.lon);

    address.value = result.display_name;
    searchQuery.value = result.display_name;
    lat.value = Number(latitude.toFixed(7));
    lng.value = Number(longitude.toFixed(7));
    showResults.value = false;
    setMarker([latitude, longitude]);
};

const initMap = async () => {
    await nextTick();

    if (!mapEl.value) {
        return;
    }

    if (map) {
        map.invalidateSize();
        return;
    }

    const leaflet = await loadLeaflet();
    const hasCoords = lat.value !== null && lng.value !== null;
    const center: [number, number] = hasCoords ? [lat.value!, lng.value!] : defaultCenter;

    map = leaflet.map(mapEl.value, {
        center,
        zoom: hasCoords ? 15 : 11,
    });

    leaflet.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 19,
    }).addTo(map);

    if (hasCoords) {
        setMarker(center, false);
    }

    map.on('click', (event: import('leaflet').LeafletMouseEvent) => {
        lat.value = Number(event.latlng.lat.toFixed(7));
        lng.value = Number(event.latlng.lng.toFixed(7));
        setMarker(event.latlng, false);
        reverseGeocode(event.latlng.lat, event.latlng.lng);
    });

    // Ensure tiles render correctly after layout
    setTimeout(() => map?.invalidateSize(), 50);
};

const refresh = async () => {
    await nextTick();
    if (!map) {
        await initMap();
        return;
    }
    map.invalidateSize();
};

watch(
    () => props.active,
    (isActive) => {
        if (isActive) {
            void refresh();
        }
    },
);

onMounted(() => {
    searchQuery.value = address.value || '';
    if (props.active) {
        void initMap();
    }
});

onBeforeUnmount(() => {
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
    map?.remove();
    map = null;
    marker = null;
});

defineExpose({ refresh });
</script>

<template>
    <div class="space-y-4">
        <div class="space-y-2">
            <Label for="location-search">Address</Label>
            <div class="relative">
                <div class="flex gap-2">
                    <div class="relative flex-1">
                        <Input
                            id="location-search"
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search for an address..."
                            autocomplete="off"
                            @input="onSearchInput"
                            @focus="showResults = searchResults.length > 0"
                            @keydown.enter.prevent="searchAddress"
                        />
                        <div
                            v-if="showResults"
                            class="absolute z-50 mt-1 max-h-60 w-full overflow-auto rounded-md border bg-popover text-popover-foreground shadow-md"
                        >
                            <button
                                v-for="result in searchResults"
                                :key="`${result.lat}-${result.lon}`"
                                type="button"
                                class="block w-full px-3 py-2 text-left text-sm hover:bg-accent"
                                @click="selectResult(result)"
                            >
                                {{ result.display_name }}
                            </button>
                        </div>
                    </div>
                    <Button type="button" variant="outline" :disabled="searching" @click="searchAddress">
                        <Loader2 v-if="searching" class="h-4 w-4 animate-spin" />
                        <Search v-else class="h-4 w-4" />
                    </Button>
                </div>
                <p class="mt-1 text-xs text-muted-foreground">
                    Search an address, or click the map / drag the marker to set the exact location
                </p>
            </div>
        </div>

        <div class="space-y-2">
            <Label>Map</Label>
            <div
                ref="mapEl"
                class="w-full overflow-hidden rounded-lg border bg-muted"
                :style="{ height }"
            />
        </div>

        <div v-if="lat !== null && lng !== null" class="rounded-lg border bg-muted/30 p-3">
            <p class="text-sm font-medium">
                <MapPin class="mr-1 inline h-4 w-4" />
                Selected coordinates
            </p>
            <p class="text-sm text-muted-foreground">{{ lat }}, {{ lng }}</p>
        </div>
    </div>
</template>
