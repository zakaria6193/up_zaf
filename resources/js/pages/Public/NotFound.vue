<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { AlertCircle } from 'lucide-vue-next';

defineOptions({
    layout: false, // No layout - standalone public page
});
</script>

<template>
    <Head title="Business Not Found" />

    <div class="min-h-screen flex items-center justify-center p-4 bg-gradient-to-br from-gray-50 to-gray-100">
        <div class="w-full max-w-md text-center">
            <div class="bg-white rounded-3xl shadow-xl p-12">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-red-100 mb-6">
                    <AlertCircle class="w-10 h-10 text-red-600" />
                </div>

                <h1 class="text-3xl font-bold text-gray-900 mb-4">
                    Business Not Found
                </h1>

                <p class="text-gray-600 mb-8">
                    This business page is currently unavailable or does not exist. Please check the link and try again.
                </p>

                <div class="text-center">
                    <p class="text-sm text-gray-500">
                        Powered by <span class="font-semibold">UP1</span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
