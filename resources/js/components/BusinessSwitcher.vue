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
import { computed } from 'vue';

const props = defineProps<{
    businesses: Array<{
        nanoid: string;
        name: string;
    }>;
    currentNanoid: string;
    route: string;
    label?: string;
}>();

const handleChange = (nanoid: string) => {
    router.visit(route(props.route, { business: nanoid }));
};
</script>

<template>
    <div v-if="businesses.length > 1" class="flex flex-col sm:flex-row sm:items-center gap-2">
        <Label :for="`business-select-${route}`" class="text-sm text-muted-foreground whitespace-nowrap">
            {{ label || 'Business:' }}
        </Label>
        <Select :model-value="currentNanoid" @update:model-value="handleChange">
            <SelectTrigger :id="`business-select-${route}`" class="w-full sm:w-[200px]">
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
