<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import MenuCurrencySelect from '@/components/MenuCurrencySelect.vue';
import { Save, X } from 'lucide-vue-next';

defineProps<{
    name: string;
    price: string;
    description: string;
    currency: string;
    currencies: Array<{ code: string; name: string; symbol: string; label: string }>;
    imagePreview?: string | null;
    processing?: boolean;
    submitLabel?: string;
}>();

defineEmits<{
    'update:name': [value: string];
    'update:price': [value: string];
    'update:description': [value: string];
    'update:currency': [value: string];
    'image-change': [event: Event];
    'remove-image': [];
    submit: [];
    cancel: [];
}>();
</script>

<template>
    <form class="space-y-3" @submit.prevent="$emit('submit')">
        <div class="grid gap-3 sm:grid-cols-2">
            <div class="space-y-1.5">
                <Label>Item name *</Label>
                <Input
                    :model-value="name"
                    type="text"
                    placeholder="e.g. Classic Burger"
                    required
                    @update:model-value="$emit('update:name', String($event))"
                />
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div class="space-y-1.5">
                    <Label>Price *</Label>
                    <Input
                        :model-value="price"
                        type="number"
                        step="0.01"
                        min="0"
                        placeholder="0.00"
                        required
                        @update:model-value="$emit('update:price', String($event))"
                    />
                </div>
                <div class="space-y-1.5">
                    <Label>Currency *</Label>
                    <MenuCurrencySelect
                        :model-value="currency"
                        :currencies="currencies"
                        compact
                        @update:model-value="$emit('update:currency', $event)"
                    />
                </div>
            </div>
        </div>

        <div class="space-y-1.5">
            <Label>Description</Label>
            <Input
                :model-value="description"
                type="text"
                placeholder="Optional description"
                @update:model-value="$emit('update:description', String($event))"
            />
        </div>

        <div class="space-y-1.5">
            <Label>Photo</Label>
            <Input type="file" accept="image/*" @change="$emit('image-change', $event)" />
            <div v-if="imagePreview" class="mt-2 flex items-center gap-3">
                <img :src="imagePreview" alt="Preview" class="h-16 w-16 rounded-md object-cover border" />
                <Button type="button" size="sm" variant="outline" @click="$emit('remove-image')">
                    Remove photo
                </Button>
            </div>
        </div>

        <div class="flex gap-2">
            <Button type="submit" size="sm" :disabled="processing">
                <Save class="mr-1 h-3 w-3" />
                {{ submitLabel || 'Add' }}
            </Button>
            <Button type="button" size="sm" variant="ghost" @click="$emit('cancel')">
                <X class="h-3 w-3" />
            </Button>
        </div>
    </form>
</template>
