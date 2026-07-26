<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Download, ExternalLink, Copy, Check } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import BusinessSwitcher from '@/components/BusinessSwitcher.vue';
import QrStylePicker from '@/components/QrStylePicker.vue';
import { updateStyle, updateText } from '@/actions/App/Http/Controllers/Business/QRCodeController';

const props = defineProps<{
    business: {
        nanoid: string;
        name: string;
        color: string;
        logo: string | null;
        qr_code: string | null;
        qr_style: string;
        qr_label: string | null;
        qr_headline: string | null;
        qr_label_default: string;
        qr_headline_default: string;
        public_url: string;
    };
    qrStyles: Array<{
        id: string;
        name: string;
        tagline: string;
        subtitle: string;
        description: string;
        requires_logo?: boolean;
    }>;
    userBusinesses: Array<{
        nanoid: string;
        name: string;
    }>;
}>();

const copied = ref(false);
const selectedStyle = ref(props.business.qr_style || 'pulse');
const savingStyle = ref(false);
const savingText = ref(false);
const qrLabel = ref(props.business.qr_label || '');
const qrHeadline = ref(props.business.qr_headline || '');

watch(
    () => props.business.qr_style,
    (style) => {
        selectedStyle.value = style || 'pulse';
    },
);

watch(
    () => [props.business.qr_label, props.business.qr_headline] as const,
    ([label, headline]) => {
        qrLabel.value = label || '';
        qrHeadline.value = headline || '';
    },
);

const qrPreviewSrc = computed(() => props.business.qr_code);

const selectedStyleMeta = computed(
    () => props.qrStyles.find((style) => style.id === selectedStyle.value) ?? props.qrStyles[0],
);

const headlinePlaceholder = computed(
    () => selectedStyleMeta.value?.tagline || props.business.qr_headline_default || 'Discover the menu',
);

const applyStyle = (style: string) => {
    selectedStyle.value = style;
    if (style === props.business.qr_style) {
        return;
    }

    savingStyle.value = true;
    router.patch(updateStyle.url(props.business.nanoid), { qr_style: style }, {
        preserveScroll: true,
        only: ['business', 'qrStyles'],
        onFinish: () => {
            savingStyle.value = false;
        },
    });
};

const saveText = () => {
    savingText.value = true;
    router.patch(updateText.url(props.business.nanoid), {
        qr_label: qrLabel.value.trim() || null,
        qr_headline: qrHeadline.value.trim() || null,
    }, {
        preserveScroll: true,
        only: ['business'],
        onFinish: () => {
            savingText.value = false;
        },
    });
};

const resetText = () => {
    qrLabel.value = '';
    qrHeadline.value = '';
    saveText();
};

const downloadQR = () => {
    if (!qrPreviewSrc.value) return;

    const link = document.createElement('a');
    link.href = qrPreviewSrc.value;
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
    if (!qrPreviewSrc.value) return;

    const printWindow = window.open('', '', 'width=700,height=900');
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
                    background: #fff;
                }
                img {
                    max-width: 520px;
                    width: 100%;
                }
                @media print {
                    body { padding: 0; }
                }
            </style>
        </head>
        <body>
            <img src="${qrPreviewSrc.value}" alt="QR Code" />
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
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold">QR Code</h1>
                <p class="text-sm text-muted-foreground">
                    Print or download your QR
                </p>
            </div>
            <BusinessSwitcher
                :businesses="userBusinesses"
                :current-nanoid="business.nanoid"
                label="Managing:"
            />
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <Card>
                <CardHeader>
                    <CardTitle>Your QR</CardTitle>
                    <CardDescription>Ready to print or share</CardDescription>
                </CardHeader>
                <CardContent class="flex flex-col items-center gap-4">
                    <div v-if="qrPreviewSrc" class="bg-muted/30 p-4 rounded-lg border">
                        <img
                            :key="qrPreviewSrc"
                            :src="qrPreviewSrc"
                            :alt="`${business.name} QR Code`"
                            class="w-full max-w-sm rounded-md"
                        />
                    </div>
                    <p v-else class="text-gray-500 text-center">No QR code generated</p>

                    <div class="flex w-full flex-col gap-2">
                        <Button @click="downloadQR" class="w-full" :disabled="!qrPreviewSrc">
                            <Download class="mr-2 h-4 w-4" />
                            Download PNG
                        </Button>
                        <Button @click="printQR" variant="outline" class="w-full" :disabled="!qrPreviewSrc">
                            Print
                        </Button>
                        <Button as-child variant="secondary" class="w-full">
                            <a :href="business.public_url" target="_blank">
                                <ExternalLink class="mr-2 h-4 w-4" />
                                Open page
                            </a>
                        </Button>
                        <Button @click="copyUrl" variant="ghost" class="w-full">
                            <Check v-if="copied" class="mr-2 h-4 w-4" />
                            <Copy v-else class="mr-2 h-4 w-4" />
                            {{ copied ? 'Copied' : 'Copy link' }}
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <div class="space-y-6">
                <Card>
                    <CardHeader>
                        <CardTitle>Design</CardTitle>
                        <CardDescription>
                            Pick a style — QR updates right away
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <Label>Style</Label>
                        <QrStylePicker
                            :model-value="selectedStyle"
                            :styles="qrStyles"
                            :brand-color="business.color"
                            :has-logo="!!business.logo"
                            @update:model-value="applyStyle"
                        />
                        <p v-if="savingStyle" class="text-sm text-muted-foreground">Saving…</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Card text</CardTitle>
                        <CardDescription>
                            What guests read on the table card
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-2">
                            <Label for="qr_label">Top label</Label>
                            <Input
                                id="qr_label"
                                v-model="qrLabel"
                                maxlength="24"
                                :placeholder="business.qr_label_default"
                            />
                            <p class="text-xs text-muted-foreground">
                                Short word above the headline. Leave blank for “MENU”.
                            </p>
                        </div>
                        <div class="space-y-2">
                            <Label for="qr_headline">Headline</Label>
                            <Input
                                id="qr_headline"
                                v-model="qrHeadline"
                                maxlength="48"
                                :placeholder="headlinePlaceholder"
                            />
                            <p class="text-xs text-muted-foreground">
                                Main line under the label. Leave blank to use the style default.
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <Button @click="saveText" :disabled="savingText">
                                {{ savingText ? 'Updating…' : 'Update text' }}
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                :disabled="savingText"
                                @click="resetText"
                            >
                                Use defaults
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>
</template>
