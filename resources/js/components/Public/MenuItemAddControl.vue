<script setup lang="ts">
import { computed } from 'vue';
import { Minus, Plus } from 'lucide-vue-next';

const props = defineProps<{
    quantity: number;
    brandColor: string;
}>();

const emit = defineEmits<{
    add: [];
    increment: [];
    decrement: [];
}>();

const softBrand = computed(() => `${props.brandColor}18`);
</script>

<template>
    <div class="flex shrink-0 items-center gap-1.5">
        <template v-if="quantity > 0">
            <button
                type="button"
                class="flex h-11 w-11 items-center justify-center rounded-full bg-white text-stone-700 shadow-sm ring-1 ring-stone-200 transition hover:bg-stone-50 active:scale-95"
                aria-label="Retirer"
                @click.stop="emit('decrement')"
            >
                <Minus class="h-4 w-4" />
            </button>
            <span class="min-w-6 text-center text-sm font-bold tabular-nums text-stone-900">
                {{ quantity }}
            </span>
            <button
                type="button"
                class="flex h-11 w-11 items-center justify-center rounded-full text-white shadow-sm transition hover:opacity-90 active:scale-95"
                :style="{ backgroundColor: brandColor }"
                aria-label="Ajouter"
                @click.stop="emit('increment')"
            >
                <Plus class="h-4 w-4" />
            </button>
        </template>
        <button
            v-else
            type="button"
            class="flex h-11 w-11 items-center justify-center rounded-full shadow-sm transition hover:scale-105 active:scale-95"
            :style="{
                backgroundColor: softBrand,
                color: brandColor,
                boxShadow: `inset 0 0 0 1px ${brandColor}40`,
            }"
            aria-label="Ajouter au panier"
            @click.stop="emit('add')"
        >
            <Plus class="h-5 w-5" stroke-width="2.5" />
        </button>
    </div>
</template>
