<script setup lang="ts">
import { computed, watch } from 'vue';

export type QrStyleOption = {
    id: string;
    name: string;
    tagline: string;
    subtitle: string;
    description: string;
    requires_logo?: boolean;
};

const props = defineProps<{
    modelValue: string;
    styles: QrStyleOption[];
    brandColor?: string;
    hasLogo?: boolean;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const color = computed(() => props.brandColor || '#4d54d9');

const availableStyles = computed(() =>
    props.styles.filter((style) => props.hasLogo || !style.requires_logo),
);

watch(
    availableStyles,
    (styles) => {
        if (!styles.some((style) => style.id === props.modelValue) && styles[0]) {
            emit('update:modelValue', styles[0].id);
        }
    },
    { immediate: true },
);

const gridClass = computed(() =>
    availableStyles.value.length >= 3 ? 'sm:grid-cols-3' : 'sm:grid-cols-2',
);
</script>

<template>
    <div class="space-y-3">
        <p v-if="!hasLogo" class="text-xs text-muted-foreground">
            Upload a logo to unlock Emblem.
        </p>
        <div class="grid gap-3" :class="gridClass">
            <button
                v-for="style in availableStyles"
                :key="style.id"
                type="button"
                class="rounded-xl border-2 p-3 text-left transition-all"
                :class="modelValue === style.id
                    ? 'border-primary ring-2 ring-primary/20'
                    : 'border-border hover:border-primary/40'"
                @click="emit('update:modelValue', style.id)"
            >
                <div
                    class="mb-3 overflow-hidden rounded-lg border"
                    :class="{
                        'bg-[#faf8f5]': style.id === 'pulse',
                        'bg-zinc-900': style.id === 'noir',
                        'bg-white': style.id === 'emblem',
                    }"
                >
                    <div
                        class="h-1.5 w-full"
                        :style="{ backgroundColor: color }"
                    />
                    <div class="space-y-2 p-3 text-center">
                        <p
                            class="text-[9px] font-semibold tracking-[0.22em] uppercase"
                            :class="style.id === 'noir' ? 'text-zinc-400' : 'text-stone-500'"
                            :style="style.id !== 'noir' ? { color } : undefined"
                        >
                            Menu
                        </p>
                        <p
                            class="text-[10px] font-semibold leading-tight"
                            :class="style.id === 'noir' ? 'text-white' : 'text-stone-900'"
                        >
                            {{ style.tagline }}
                        </p>
                        <div
                            class="mx-auto flex h-16 w-16 items-center justify-center rounded-sm relative"
                            :class="style.id === 'noir' ? 'bg-zinc-800' : 'bg-white border'"
                        >
                            <div
                                class="grid h-10 w-10 grid-cols-3 gap-0.5 opacity-80"
                            >
                                <span
                                    v-for="n in 9"
                                    :key="n"
                                    class="rounded-[1px]"
                                    :class="style.id === 'noir' ? 'bg-white' : ''"
                                    :style="style.id === 'noir' ? undefined : { backgroundColor: color }"
                                />
                            </div>
                            <div
                                v-if="style.id === 'emblem'"
                                class="absolute grid h-7 w-7 grid-cols-3 gap-px p-0.5"
                            >
                                <span
                                    v-for="n in 9"
                                    :key="`emblem-${n}`"
                                    class="rounded-[0.5px]"
                                    :class="[2, 4, 5, 6, 8].includes(n) ? '' : 'opacity-0'"
                                    :style="[2, 4, 5, 6, 8].includes(n) ? { backgroundColor: color } : undefined"
                                />
                            </div>
                        </div>
                        <p
                            v-if="style.subtitle"
                            class="text-[9px]"
                            :class="style.id === 'noir' ? 'text-zinc-400' : 'text-stone-500'"
                        >
                            {{ style.subtitle }}
                        </p>
                    </div>
                </div>

                <p class="text-sm font-semibold">{{ style.name }}</p>
                <p class="mt-0.5 text-xs text-muted-foreground">{{ style.description }}</p>
            </button>
        </div>
    </div>
</template>
