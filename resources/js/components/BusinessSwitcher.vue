<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

const props = defineProps<{
    businesses: Array<{
        nanoid: string;
        name: string;
    }>;
    currentNanoid: string;
    label?: string;
}>();

const handleChange = (nanoid: string | number | bigint | null | undefined) => {
    if (nanoid === null || nanoid === undefined || String(nanoid) === props.currentNanoid) {
        return;
    }

    const url = new URL(window.location.href);
    url.searchParams.set('business', String(nanoid));
    router.visit(`${url.pathname}?${url.searchParams.toString()}`);
};
</script>

<template>
    <div v-if="businesses.length > 1" class="flex flex-col sm:flex-row sm:items-center gap-2">
        <Label :for="`business-select-${currentNanoid}`" class="text-sm text-muted-foreground whitespace-nowrap">
            {{ label || 'Business:' }}
        </Label>
        <Select :model-value="currentNanoid" @update:model-value="handleChange">
            <SelectTrigger :id="`business-select-${currentNanoid}`" class="w-full sm:w-[200px]">
                <SelectValue />
            </SelectTrigger>
            <SelectContent>
                <SelectItem
                    v-for="business in businesses"
                    :key="business.nanoid"
                    :value="business.nanoid"
                >
                    {{ business.name }}
                </SelectItem>
            </SelectContent>
        </Select>
    </div>
</template>
