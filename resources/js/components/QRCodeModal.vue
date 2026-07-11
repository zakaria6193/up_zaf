<script setup lang="ts">
import { ref } from 'vue';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Download, Copy, Check, ExternalLink } from 'lucide-vue-next';

const props = defineProps<{
    businessName: string;
    qrCodeUrl: string;
    publicUrl: string;
    nanoid: string;
}>();

const open = defineModel<boolean>('open', { default: false });
const copied = ref(false);

const copyUrl = async () => {
    await navigator.clipboard.writeText(props.publicUrl);
    copied.value = true;
    setTimeout(() => {
        copied.value = false;
    }, 2000);
};

const downloadQR = () => {
    const link = document.createElement('a');
    link.href = props.qrCodeUrl;
    link.download = `${props.nanoid}-qr-code.png`;
    link.click();
};
</script>

<template>
    <Dialog v-model:open="open">
        <DialogContent class="sm:max-w-lg">
            <DialogHeader>
                <DialogTitle>QR Code - {{ businessName }}</DialogTitle>
                <DialogDescription>
                    Scannez ce code ou copiez le lien pour partager
                </DialogDescription>
            </DialogHeader>

            <div class="space-y-4">
                <!-- QR Code -->
                <div class="flex justify-center rounded-lg border bg-muted/30 p-6">
                    <img
                        :src="qrCodeUrl"
                        :alt="`QR Code pour ${businessName}`"
                        class="h-64 w-64 rounded-lg"
                    />
                </div>

                <!-- Public URL -->
                <div class="space-y-2">
                    <label class="text-sm font-medium">Lien public</label>
                    <div class="flex gap-2">
                        <code class="flex-1 rounded border bg-muted px-3 py-2 text-sm">
                            {{ publicUrl }}
                        </code>
                        <Button
                            size="icon"
                            variant="outline"
                            @click="copyUrl"
                            :title="copied ? 'Copié!' : 'Copier le lien'"
                        >
                            <Check v-if="copied" class="h-4 w-4 text-green-600" />
                            <Copy v-else class="h-4 w-4" />
                        </Button>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex gap-2">
                    <Button @click="downloadQR" class="flex-1" variant="outline">
                        <Download class="mr-2 h-4 w-4" />
                        Télécharger QR
                    </Button>
                    <Button as="a" :href="publicUrl" target="_blank" class="flex-1" variant="outline">
                        <ExternalLink class="mr-2 h-4 w-4" />
                        Voir la page
                    </Button>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>
