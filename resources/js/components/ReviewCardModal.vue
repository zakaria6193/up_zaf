<script setup lang="ts">
import { ref, computed } from 'vue';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Instagram, Star, FileText } from 'lucide-vue-next';

const props = defineProps<{
    businessName: string;
    businessColor: string;
    qrCodeUrl: string;
}>();

const open = defineModel<boolean>('open', { default: false });

type CardType = 'instagram' | 'google' | 'general';
type CardFormat = '10x10' | '8x12';

const selectedType = ref<CardType>('google');
const selectedFormat = ref<CardFormat>('10x10');

// Customization
const bgColor = ref('#ffffff');
const textColor = ref('#000000');
const accentColor = ref(props.businessColor || '#4d54d9');
const customText = ref('');

// Predefined templates
const templates = {
    instagram: {
        bgColor: '#FAFAFA',
        textColor: '#262626',
        accentColor: '#E1306C',
        text: 'Suivez-nous sur Instagram\npour plus de contenu',
    },
    google: {
        bgColor: '#FFFFFF',
        textColor: '#202124',
        accentColor: '#4285F4',
        text: 'Votre avis compte !\nScannez pour laisser un avis',
    },
    general: {
        bgColor: '#FFFFFF',
        textColor: '#000000',
        accentColor: props.businessColor || '#4d54d9',
        text: 'Scannez pour découvrir\nnotre carte digitale',
    },
};

const selectType = (type: CardType) => {
    selectedType.value = type;
    const template = templates[type];
    bgColor.value = template.bgColor;
    textColor.value = template.textColor;
    accentColor.value = template.accentColor;
    customText.value = template.text;
};

const formatDimensions = computed(() => {
    return selectedFormat.value === '10x10'
        ? { cmWidth: 10, cmHeight: 10 }
        : { cmWidth: 8, cmHeight: 12 };
});

const generatePDF = () => {
    const printWindow = window.open('', '_blank');
    if (!printWindow) return;

    const { cmWidth, cmHeight } = formatDimensions.value;
    const isSquare = selectedFormat.value === '10x10';

    let cardHTML = '';

    if (selectedType.value === 'instagram') {
        cardHTML = `
            <div class="card instagram-card">
                <div class="gradient-bg"></div>
                <div class="content">
                    <svg class="icon-large" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                        <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
                        <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
                    </svg>
                    <h1 class="title">${props.businessName}</h1>
                    <p class="text">${customText.value}</p>
                    <div class="qr-container">
                        <img src="${props.qrCodeUrl}" alt="QR Code" class="qr-code" />
                    </div>
                </div>
            </div>
        `;
    } else if (selectedType.value === 'google') {
        cardHTML = `
            <div class="card google-card">
                <div class="header-section">
                    <h1 class="title">${props.businessName}</h1>
                    <div class="stars">
                        ${Array(5).fill(0).map(() => `
                            <svg class="star-icon" viewBox="0 0 24 24" fill="${accentColor.value}" stroke="${accentColor.value}">
                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                            </svg>
                        `).join('')}
                    </div>
                </div>
                <div class="qr-container-center">
                    <img src="${props.qrCodeUrl}" alt="QR Code" class="qr-code-large" />
                </div>
                <div class="divider"></div>
                <p class="text-center">${customText.value}</p>
            </div>
        `;
    } else {
        cardHTML = `
            <div class="card general-card">
                <div class="top-section">
                    <h1 class="title-large">${props.businessName}</h1>
                </div>
                <div class="qr-container-large">
                    <img src="${props.qrCodeUrl}" alt="QR Code" class="qr-code-xlarge" />
                </div>
                <p class="text-bottom">${customText.value}</p>
            </div>
        `;
    }

    const html = `
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Review Card - ${props.businessName}</title>
            <style>
                @page {
                    size: ${cmWidth}cm ${cmHeight}cm;
                    margin: 0;
                }
                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                }
                body {
                    width: ${cmWidth}cm;
                    height: ${cmHeight}cm;
                    margin: 0;
                    padding: 0;
                    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
                }
                .card {
                    width: 100%;
                    height: 100%;
                    background-color: ${bgColor.value};
                    color: ${textColor.value};
                    position: relative;
                    overflow: hidden;
                }

                /* Instagram Style */
                .instagram-card {
                    background: linear-gradient(135deg, #FAFAFA 0%, #F0F0F0 100%);
                }
                .instagram-card .gradient-bg {
                    position: absolute;
                    top: -20%;
                    right: -20%;
                    width: 60%;
                    height: 60%;
                    background: radial-gradient(circle, ${accentColor.value}22, transparent);
                    border-radius: 50%;
                }
                .instagram-card .content {
                    position: relative;
                    height: 100%;
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    justify-content: center;
                    padding: ${isSquare ? '1.5cm' : '2cm 1.5cm'};
                    text-align: center;
                }
                .icon-large {
                    width: ${isSquare ? '3cm' : '3.5cm'};
                    height: ${isSquare ? '3cm' : '3.5cm'};
                    color: ${accentColor.value};
                    margin-bottom: 1cm;
                }
                .title {
                    font-size: ${isSquare ? '1.3em' : '1.5em'};
                    font-weight: bold;
                    margin-bottom: 0.6cm;
                    color: ${accentColor.value};
                    line-height: 1.2;
                }
                .text {
                    font-size: ${isSquare ? '0.9em' : '1em'};
                    line-height: 1.6;
                    white-space: pre-line;
                    margin-bottom: 1cm;
                    opacity: 0.9;
                }

                /* Google Style */
                .google-card {
                    padding: ${isSquare ? '1.5cm' : '2cm 1.5cm'};
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    justify-content: space-between;
                }
                .header-section {
                    text-align: center;
                    width: 100%;
                }
                .stars {
                    display: flex;
                    justify-content: center;
                    gap: 0.2cm;
                    margin-top: 0.4cm;
                }
                .star-icon {
                    width: ${isSquare ? '0.8cm' : '1cm'};
                    height: ${isSquare ? '0.8cm' : '1cm'};
                }
                .divider {
                    width: 100%;
                    height: 2px;
                    background: ${accentColor.value};
                    margin: 0.5cm 0;
                }
                .text-center {
                    text-align: center;
                    font-size: ${isSquare ? '0.95em' : '1.1em'};
                    line-height: 1.6;
                    white-space: pre-line;
                }

                /* General Style */
                .general-card {
                    padding: ${isSquare ? '1.5cm' : '2.5cm 1.5cm'};
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    justify-content: space-between;
                }
                .top-section {
                    width: 100%;
                    text-align: center;
                    padding-bottom: 0.8cm;
                    border-bottom: 3px solid ${accentColor.value};
                }
                .title-large {
                    font-size: ${isSquare ? '1.4em' : '1.7em'};
                    font-weight: bold;
                    color: ${accentColor.value};
                    line-height: 1.2;
                }
                .text-bottom {
                    text-align: center;
                    font-size: ${isSquare ? '0.9em' : '1em'};
                    line-height: 1.5;
                    white-space: pre-line;
                    color: ${textColor.value};
                    opacity: 0.85;
                }

                /* QR Code styles */
                .qr-container {
                    background: white;
                    padding: 0.5cm;
                    border-radius: 0.5cm;
                    box-shadow: 0 0.2cm 0.6cm rgba(0,0,0,0.1);
                }
                .qr-container-center {
                    background: white;
                    padding: 0.6cm;
                    border-radius: 0.5cm;
                    box-shadow: 0 0.2cm 0.8cm rgba(0,0,0,0.12);
                    flex: 1;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    margin: 0.8cm 0;
                }
                .qr-container-large {
                    background: white;
                    padding: 0.7cm;
                    border-radius: 0.6cm;
                    box-shadow: 0 0.3cm 1cm rgba(0,0,0,0.15);
                    flex: 1;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    margin: 1cm 0;
                }
                .qr-code {
                    width: ${isSquare ? '3.5cm' : '4cm'};
                    height: ${isSquare ? '3.5cm' : '4cm'};
                    display: block;
                }
                .qr-code-large {
                    width: ${isSquare ? '4.5cm' : '5cm'};
                    height: ${isSquare ? '4.5cm' : '5cm'};
                    display: block;
                }
                .qr-code-xlarge {
                    width: ${isSquare ? '5cm' : '5.5cm'};
                    height: ${isSquare ? '5cm' : '5.5cm'};
                    display: block;
                }

                @media print {
                    body {
                        print-color-adjust: exact;
                        -webkit-print-color-adjust: exact;
                    }
                }
            </style>
        </head>
        <body>
            ${cardHTML}
        </body>
        </html>
    `;

    printWindow.document.write(html);
    printWindow.document.close();

    printWindow.onload = () => {
        setTimeout(() => {
            printWindow.print();
        }, 250);
    };
};

// Initialize with Google template
selectType('google');
</script>

<template>
    <Dialog v-model:open="open">
        <DialogContent class="sm:max-w-4xl max-h-[90vh] overflow-y-auto">
            <DialogHeader>
                <DialogTitle>Générer une carte de visite</DialogTitle>
                <DialogDescription>
                    Personnalisez et téléchargez votre carte de visite
                </DialogDescription>
            </DialogHeader>

            <div class="grid grid-cols-2 gap-6">
                <!-- Left: Customization -->
                <div class="space-y-6">
                    <!-- Card Type Selection -->
                    <div class="space-y-3">
                        <Label>Type de carte</Label>
                        <div class="grid grid-cols-3 gap-2">
                            <Button
                                variant="outline"
                                :class="{ 'border-primary bg-primary/10': selectedType === 'instagram' }"
                                @click="selectType('instagram')"
                                class="flex flex-col items-center gap-2 h-auto py-4"
                            >
                                <Instagram class="h-5 w-5" />
                                <span class="text-xs">Instagram</span>
                            </Button>
                            <Button
                                variant="outline"
                                :class="{ 'border-primary bg-primary/10': selectedType === 'google' }"
                                @click="selectType('google')"
                                class="flex flex-col items-center gap-2 h-auto py-4"
                            >
                                <Star class="h-5 w-5" />
                                <span class="text-xs">Google</span>
                            </Button>
                            <Button
                                variant="outline"
                                :class="{ 'border-primary bg-primary/10': selectedType === 'general' }"
                                @click="selectType('general')"
                                class="flex flex-col items-center gap-2 h-auto py-4"
                            >
                                <FileText class="h-5 w-5" />
                                <span class="text-xs">Général</span>
                            </Button>
                        </div>
                    </div>

                    <!-- Format Selection -->
                    <div class="space-y-2">
                        <Label>Format</Label>
                        <div class="grid grid-cols-2 gap-2">
                            <Button
                                variant="outline"
                                :class="{ 'border-primary bg-primary/10': selectedFormat === '10x10' }"
                                @click="selectedFormat = '10x10'"
                            >
                                10cm × 10cm
                            </Button>
                            <Button
                                variant="outline"
                                :class="{ 'border-primary bg-primary/10': selectedFormat === '8x12' }"
                                @click="selectedFormat = '8x12'"
                            >
                                8cm × 12cm
                            </Button>
                        </div>
                    </div>

                    <!-- Customization -->
                    <div class="space-y-4">
                        <div class="space-y-2">
                            <Label for="customText">Texte personnalisé</Label>
                            <textarea
                                id="customText"
                                v-model="customText"
                                rows="3"
                                class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring resize-none"
                                placeholder="Entrez votre texte..."
                            />
                        </div>

                        <div class="grid grid-cols-3 gap-3">
                            <div class="space-y-2">
                                <Label for="bgColor">Fond</Label>
                                <input
                                    id="bgColor"
                                    v-model="bgColor"
                                    type="color"
                                    class="h-10 w-full cursor-pointer rounded border"
                                />
                            </div>

                            <div class="space-y-2">
                                <Label for="textColor">Texte</Label>
                                <input
                                    id="textColor"
                                    v-model="textColor"
                                    type="color"
                                    class="h-10 w-full cursor-pointer rounded border"
                                />
                            </div>

                            <div class="space-y-2">
                                <Label for="accentColor">Accent</Label>
                                <input
                                    id="accentColor"
                                    v-model="accentColor"
                                    type="color"
                                    class="h-10 w-full cursor-pointer rounded border"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Preview -->
                <div class="space-y-4">
                    <Label>Aperçu</Label>
                    <div class="rounded-lg border bg-muted/30 p-4 flex items-center justify-center">
                        <div
                            class="shadow-lg rounded-lg overflow-hidden"
                            :style="{
                                width: selectedFormat === '10x10' ? '280px' : '224px',
                                height: selectedFormat === '10x10' ? '280px' : '336px',
                                backgroundColor: bgColor,
                                color: textColor,
                            }"
                        >
                            <div class="w-full h-full flex flex-col items-center justify-center p-8 text-center">
                                <!-- Icon -->
                                <Instagram
                                    v-if="selectedType === 'instagram'"
                                    class="h-16 w-16 mb-4"
                                    :style="{ color: accentColor }"
                                />
                                <Star
                                    v-else-if="selectedType === 'google'"
                                    class="h-16 w-16 mb-4"
                                    :style="{ color: accentColor }"
                                />
                                <FileText
                                    v-else
                                    class="h-16 w-16 mb-4"
                                    :style="{ color: accentColor }"
                                />

                                <!-- Title -->
                                <div
                                    class="text-lg font-bold mb-3"
                                    :style="{ color: accentColor }"
                                >
                                    {{ businessName }}
                                </div>

                                <!-- Custom Text -->
                                <div class="text-sm mb-4 whitespace-pre-line">
                                    {{ customText }}
                                </div>

                                <!-- QR Code -->
                                <img
                                    :src="qrCodeUrl"
                                    alt="QR Code"
                                    class="w-20 h-20 rounded"
                                />
                            </div>
                        </div>
                    </div>

                    <Button @click="generatePDF" class="w-full" size="lg">
                        Générer le PDF
                    </Button>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>
