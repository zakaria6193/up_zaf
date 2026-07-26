<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

defineOptions({ layout: false });

defineProps<{
    businessName?: string;
}>();
</script>

<template>
    <Head :title="businessName ? `${businessName} unavailable` : 'Menu unavailable'" />

    <div class="flex min-h-screen items-center justify-center bg-stone-100 px-4">
        <div class="max-w-md text-center">
            <p class="mb-2 text-sm font-semibold tracking-wide text-orange-600 uppercase">UP1</p>
            <h1 class="mb-3 text-2xl font-bold text-stone-900">
                {{ businessName || 'This menu' }} is temporarily unavailable
            </h1>
            <p class="text-stone-600">
                The free trial for this restaurant has ended. Ask the owner to upgrade to Premium to reopen the public menu.
            </p>
        </div>
    </div>
</template>
