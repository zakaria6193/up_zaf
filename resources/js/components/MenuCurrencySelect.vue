<script setup lang="ts">
import { Label } from '@/components/ui/label';

defineProps<{
    modelValue: string;
    currencies: Array<{ code: string; name: string; symbol: string; label: string }>;
    id?: string;
    label?: string;
    compact?: boolean;
}>();

defineEmits<{
    'update:modelValue': [value: string];
}>();
</script>

<template>
    <div :class="compact ? '' : 'space-y-2'">
        <Label v-if="label" :for="id || 'menu-currency'">{{ label }}</Label>
        <select
            :id="id || 'menu-currency'"
            :value="modelValue"
            class="border-input bg-background h-9 w-full min-w-[8.5rem] rounded-md border px-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]"
            :aria-label="label || 'Currency'"
            @change="$emit('update:modelValue', ($event.target as HTMLSelectElement).value)"
        >
            <option v-for="currency in currencies" :key="currency.code" :value="currency.code">
                {{ compact ? `${currency.code} (${currency.symbol})` : currency.label }}
            </option>
        </select>
    </div>
</template>
