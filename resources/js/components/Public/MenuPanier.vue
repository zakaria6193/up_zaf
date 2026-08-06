<script setup lang="ts">
import { computed } from 'vue';
import { Minus, Plus, ShoppingBag, Trash2, Copy, Check } from 'lucide-vue-next';
import { ref } from 'vue';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';
import type { PanierItem } from '@/composables/useMenuPanier';
import { formatMoney } from '@/lib/money';

const props = defineProps<{
    open: boolean;
    brandColor: string;
    currency?: string;
    items: PanierItem[];
    totalQuantity: number;
    totalPrice: number;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    increment: [id: number];
    decrement: [id: number];
    remove: [id: number];
    clear: [];
}>();

const copied = ref(false);

const formatPrice = (price: number) => formatMoney(price, props.currency || 'MAD');

const softBrand = computed(() => `${props.brandColor}18`);

const copyList = async () => {
    const lines = props.items.map(
        (item) => `${item.quantity}x ${item.name} — ${formatPrice(item.price * item.quantity)}`,
    );
    const text = [
        'My selection',
        ...lines,
        '',
        `Estimated total: ${formatPrice(props.totalPrice)}`,
    ].join('\n');

    try {
        await navigator.clipboard.writeText(text);
        copied.value = true;
        window.setTimeout(() => {
            copied.value = false;
        }, 2000);
    } catch {
        window.prompt('Copy this list:', text);
    }
};
</script>

<template>
    <!-- Persistent cart bar — thumb zone (research: bottom sticky cart) -->
    <button
        v-if="totalQuantity > 0"
        type="button"
        class="fixed inset-x-4 bottom-5 z-40 mx-auto flex max-w-md items-center justify-between gap-3 rounded-2xl px-5 py-3.5 text-white shadow-[0_16px_40px_-16px_rgba(0,0,0,0.5)] transition active:scale-[0.99]"
        :style="{ backgroundColor: brandColor }"
        @click="emit('update:open', true)"
    >
        <span class="flex items-center gap-3">
            <span class="relative flex h-10 w-10 items-center justify-center rounded-xl bg-white/15">
                <ShoppingBag class="h-5 w-5" />
                <span
                    class="absolute -right-1.5 -top-1.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-white px-1 text-[11px] font-bold"
                    :style="{ color: brandColor }"
                >
                    {{ totalQuantity }}
                </span>
            </span>
            <span class="text-left">
                <span class="block text-sm font-semibold">View cart</span>
                <span class="block text-xs text-white/80">{{ totalQuantity }} item{{ totalQuantity > 1 ? 's' : '' }}</span>
            </span>
        </span>
        <span class="text-sm font-semibold tabular-nums">
            {{ formatPrice(totalPrice) }}
        </span>
    </button>

    <Sheet :open="open" @update:open="emit('update:open', $event)">
        <SheetContent
            side="bottom"
            class="mx-auto max-h-[85vh] w-full max-w-lg rounded-t-3xl border-0 p-0 sm:right-6 sm:bottom-6 sm:left-auto sm:max-w-md sm:rounded-3xl sm:border"
        >
            <div class="flex max-h-[85vh] flex-col">
                <SheetHeader class="border-b border-gray-100 px-6 pb-4 pt-6 text-left">
                    <SheetTitle class="text-xl font-bold text-gray-900">
                        Your cart
                    </SheetTitle>
                    <SheetDescription class="text-sm text-gray-500">
                        Note what you want, then show the list to your server.
                    </SheetDescription>
                </SheetHeader>

                <div v-if="items.length === 0" class="px-6 py-12 text-center">
                    <div
                        class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full"
                        :style="{ backgroundColor: softBrand }"
                    >
                        <ShoppingBag class="h-6 w-6" :style="{ color: brandColor }" />
                    </div>
                    <p class="font-medium text-gray-900">Cart empty</p>
                    <p class="mt-1 text-sm text-gray-500">
                        Tap + next to a dish to add it.
                    </p>
                </div>

                <div v-else class="flex-1 space-y-3 overflow-y-auto px-4 py-4">
                    <div
                        v-for="item in items"
                        :key="item.id"
                        class="flex items-center gap-3 rounded-2xl bg-gray-50 px-3 py-3"
                    >
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-semibold text-gray-900">{{ item.name }}</p>
                            <p class="text-sm text-gray-500">
                                {{ formatPrice(item.price) }}
                                <span v-if="item.quantity > 1">
                                    · {{ formatPrice(item.price * item.quantity) }}
                                </span>
                            </p>
                        </div>

                        <div class="flex items-center gap-1.5">
                            <button
                                type="button"
                                class="flex h-8 w-8 items-center justify-center rounded-full bg-white text-gray-700 shadow-sm ring-1 ring-gray-200 transition hover:bg-gray-100"
                                @click="emit('decrement', item.id)"
                            >
                                <Minus class="h-3.5 w-3.5" />
                            </button>
                            <span class="w-6 text-center text-sm font-bold text-gray-900">
                                {{ item.quantity }}
                            </span>
                            <button
                                type="button"
                                class="flex h-8 w-8 items-center justify-center rounded-full text-white shadow-sm transition hover:opacity-90"
                                :style="{ backgroundColor: brandColor }"
                                @click="emit('increment', item.id)"
                            >
                                <Plus class="h-3.5 w-3.5" />
                            </button>
                            <button
                                type="button"
                                class="ml-1 flex h-8 w-8 items-center justify-center rounded-full text-gray-400 transition hover:bg-white hover:text-red-500"
                                @click="emit('remove', item.id)"
                            >
                                <Trash2 class="h-3.5 w-3.5" />
                            </button>
                        </div>
                    </div>
                </div>

                <div
                    v-if="items.length > 0"
                    class="space-y-3 border-t border-gray-100 bg-white px-6 py-5"
                >
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Estimated total</span>
                        <span class="text-lg font-bold" :style="{ color: brandColor }">
                            {{ formatPrice(totalPrice) }}
                        </span>
                    </div>

                    <div class="flex gap-2">
                        <button
                            type="button"
                            class="flex flex-1 items-center justify-center gap-2 rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
                            @click="copyList"
                        >
                            <Check v-if="copied" class="h-4 w-4 text-green-600" />
                            <Copy v-else class="h-4 w-4" />
                            {{ copied ? 'Copied' : 'Copy list' }}
                        </button>
                        <button
                            type="button"
                            class="rounded-2xl border border-gray-200 px-4 py-3 text-sm font-semibold text-gray-500 transition hover:bg-gray-50 hover:text-red-600"
                            @click="emit('clear')"
                        >
                            Clear
                        </button>
                    </div>
                </div>
            </div>
        </SheetContent>
    </Sheet>
</template>
