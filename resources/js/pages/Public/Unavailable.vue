<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

defineOptions({ layout: false });

defineProps<{
    businessName?: string;
}>();
</script>

<template>
    <Head :title="businessName ? `${businessName} unavailable` : 'Menu unavailable'" />

    <div class="flex min-h-screen items-center justify-center bg-background px-4">
        <div class="max-w-md text-center">
            <p class="mb-2 text-sm font-semibold tracking-wide text-primary uppercase">UP1</p>
            <h1 class="mb-3 font-serif text-2xl font-semibold tracking-tight text-foreground">
                {{ businessName || 'This menu' }} is temporarily unavailable
            </h1>
            <p class="text-muted-foreground">
                The free trial for this restaurant has ended. Ask the owner to upgrade to Premium to reopen the public menu.
            </p>
        </div>
    </div>
</template>
