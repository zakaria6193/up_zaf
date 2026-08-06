<script setup lang="ts">
import { ref } from 'vue';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { AlertTriangle } from 'lucide-vue-next';

const props = defineProps<{
    title: string;
    description?: string;
    itemName?: string;
    loading?: boolean;
}>();

const emit = defineEmits<{
    confirm: [];
}>();

const open = defineModel<boolean>('open', { default: false });
const processing = ref(false);

const handleConfirm = async () => {
    processing.value = true;
    emit('confirm');
};
</script>

<template>
    <Dialog v-model:open="open">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-destructive/10">
                        <AlertTriangle class="h-5 w-5 text-destructive" />
                    </div>
                    <div class="flex-1">
                        <DialogTitle>{{ title }}</DialogTitle>
                        <DialogDescription v-if="description || itemName" class="mt-1">
                            {{ description }}
                            <span v-if="itemName" class="font-semibold text-foreground">{{ itemName }}</span>
                            ?
                        </DialogDescription>
                    </div>
                </div>
            </DialogHeader>

            <div class="rounded-lg border border-destructive/20 bg-destructive/5 p-4">
                <p class="text-sm text-muted-foreground">
                    This action cannot be undone. All associated data (links, menu, etc.) will also be deleted.
                </p>
            </div>

            <DialogFooter class="gap-2 sm:gap-0">
                <Button
                    variant="outline"
                    @click="open = false"
                    :disabled="processing || loading"
                >
                    Cancel
                </Button>
                <Button
                    variant="destructive"
                    @click="handleConfirm"
                    :disabled="processing || loading"
                >
                    {{ processing || loading ? 'Deleting...' : 'Delete' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
