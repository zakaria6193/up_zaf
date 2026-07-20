<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Download, ExternalLink, Copy, Check } from 'lucide-vue-next';
import { ref } from 'vue';
import BusinessSwitcher from '@/components/BusinessSwitcher.vue';

const props = defineProps<{
    business: {
        nanoid: string;
        name: string;
        qr_code: string | null;
        public_url: string;
    };
    userBusinesses: Array<{
        nanoid: string;
        name: string;
    }>;
}>();

const copied = ref(false);

const downloadQR = () => {
    if (!props.business.qr_code) return;

    const link = document.createElement('a');
    link.href = props.business.qr_code;
    link.download = `${props.business.name}-QR-Code.png`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};

const copyUrl = async () => {
    try {
        await navigator.clipboard.writeText(props.business.public_url);
        copied.value = true;
        setTimeout(() => {
            copied.value = false;
        }, 2000);
    } catch (err) {
        console.error('Failed to copy:', err);
    }
};

const printQR = () => {
    if (!props.business.qr_code) return;

    const printWindow = window.open('', '', 'width=600,height=600');
    if (!printWindow) return;

    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>${props.business.name} - QR Code</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    justify-content: center;
                    min-height: 100vh;
                    margin: 0;
                    padding: 20px;
                }
                h1 {
                    margin-bottom: 10px;
                    font-size: 24px;
                }
                p {
                    margin-bottom: 20px;
                    color: #666;
                }
                img {
                    max-width: 400px;
                    border: 2px solid #000;
                    padding: 20px;
                }
                @media print {
                    body { padding: 0; }
                }
            </style>
        </head>
        <body>
            <h1>${props.business.name}</h1>
            <p>Scan to view our business</p>
            <img src="${props.business.qr_code}" alt="QR Code" />
            <p style="margin-top: 20px; font-size: 12px;">${props.business.public_url}</p>
        </body>
        </html>
    `);

    printWindow.document.close();
    printWindow.focus();

    setTimeout(() => {
        printWindow.print();
        printWindow.close();
    }, 250);
};
</script>

<template>
    <Head title="QR Code" />

    <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
        <!-- Header -->
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-3xl font-bold">QR Code</h1>
                <p class="text-sm text-muted-foreground">
                    View and download your business QR code
                </p>
            </div>

            <!-- Business Selector (if multiple businesses) -->
            <BusinessSwitcher
                :businesses="userBusinesses"
                :current-nanoid="business.nanoid"
                label="Viewing:"
            />
        </div>

        <div class="grid gap-6 md:grid-cols-2">
            <!-- QR Code Display -->
            <Card>
                <CardHeader>
                    <CardTitle>Your QR Code</CardTitle>
                    <CardDescription>Share this code to direct customers to your page</CardDescription>
                </CardHeader>
                <CardContent class="flex flex-col items-center gap-6">
                    <div v-if="business.qr_code" class="bg-white p-8 rounded-lg shadow-md border-2 border-gray-200">
                        <img
                            :src="business.qr_code"
                            :alt="`${business.name} QR Code`"
                            class="w-64 h-64 object-contain"
                        />
                    </div>
                    <div v-else class="bg-gray-100 p-8 rounded-lg w-64 h-64 flex items-center justify-center">
                        <p class="text-gray-500 text-center">No QR code generated</p>
                    </div>

                    <div class="text-center">
                        <p class="font-semibold text-lg mb-1">{{ business.name }}</p>
                        <p class="text-sm text-muted-foreground">Scan to visit</p>
                    </div>
                </CardContent>
            </Card>

            <!-- Actions & Information -->
            <div class="space-y-6">
                <!-- Download Options -->
                <Card>
                    <CardHeader>
                        <CardTitle>Download Options</CardTitle>
                        <CardDescription>Save or print your QR code</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <Button @click="downloadQR" class="w-full" :disabled="!business.qr_code">
                            <Download class="mr-2 h-4 w-4" />
                            Download QR Code (PNG)
                        </Button>
                        <Button @click="printQR" variant="outline" class="w-full" :disabled="!business.qr_code">
                            <ExternalLink class="mr-2 h-4 w-4" />
                            Print QR Code
                        </Button>
                    </CardContent>
                </Card>

                <!-- Public URL -->
                <Card>
                    <CardHeader>
                        <CardTitle>Public URL</CardTitle>
                        <CardDescription>Share this link directly</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div class="flex gap-2">
                            <input
                                type="text"
                                :value="business.public_url"
                                readonly
                                class="flex-1 px-3 py-2 rounded-md border border-gray-300 bg-gray-50 text-sm font-mono"
                            />
                            <Button @click="copyUrl" variant="outline" size="icon">
                                <Check v-if="copied" class="h-4 w-4 text-green-600" />
                                <Copy v-else class="h-4 w-4" />
                            </Button>
                        </div>
                        <p v-if="copied" class="text-sm text-green-600">Copied to clipboard!</p>
                    </CardContent>
                </Card>

                <!-- Usage Tips -->
                <Card>
                    <CardHeader>
                        <CardTitle>Usage Tips</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <ul class="space-y-2 text-sm text-muted-foreground">
                            <li class="flex gap-2">
                                <span class="text-primary">•</span>
                                <span>Print and display the QR code at your business location</span>
                            </li>
                            <li class="flex gap-2">
                                <span class="text-primary">•</span>
                                <span>Add it to your marketing materials and menus</span>
                            </li>
                            <li class="flex gap-2">
                                <span class="text-primary">•</span>
                                <span>Share the QR code on social media</span>
                            </li>
                            <li class="flex gap-2">
                                <span class="text-primary">•</span>
                                <span>Include it in email signatures and receipts</span>
                            </li>
                        </ul>
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>
</template>
