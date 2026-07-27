<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { AlertCircle } from 'lucide-vue-next';

defineOptions({
    layout: false,
});
</script>

<template>
    <Head title="Business Not Found" />

    <div class="flex min-h-screen items-center justify-center bg-background p-4">
        <div class="w-full max-w-md border border-border bg-card p-10 text-center">
            <div class="mb-6 inline-flex h-16 w-16 items-center justify-center rounded-full bg-destructive/10">
                <AlertCircle class="h-8 w-8 text-destructive" />
            </div>

            <h1 class="mb-3 font-serif text-3xl font-semibold tracking-tight text-foreground">
                Business Not Found
            </h1>

            <p class="mb-8 text-muted-foreground">
                This business page is currently unavailable or does not exist. Please check the link and try again.
            </p>

            <p class="text-sm text-muted-foreground">
                Powered by <span class="font-semibold text-foreground">UP1</span>
            </p>
        </div>
    </div>
</template>
