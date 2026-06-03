<script setup lang="ts">
import { Button } from '@/Components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog';

defineProps<{
    open: boolean;
    title: string;
    description: string;
    confirmLabel: string;
    cancelLabel: string;
    processing?: boolean;
}>();

const emit = defineEmits<{
    'update:open': [open: boolean];
    confirm: [];
}>();

function close(): void {
    emit('update:open', false);
}

function confirm(): void {
    emit('confirm');
}
</script>

<template>
    <Dialog :open="open" @update:open="(value) => emit('update:open', value)">
        <DialogContent class="max-w-md gap-6 p-6 sm:p-8">
            <DialogHeader class="gap-3 text-left">
                <DialogTitle
                    class="text-text-heading text-xl leading-snug font-bold md:text-2xl"
                >
                    {{ title }}
                </DialogTitle>
                <DialogDescription
                    class="text-text-muted text-base leading-relaxed md:text-lg"
                >
                    {{ description }}
                </DialogDescription>
            </DialogHeader>

            <DialogFooter
                class="flex flex-col-reverse gap-3 sm:flex-col-reverse"
            >
                <Button
                    type="button"
                    variant="outline"
                    class="min-h-14 w-full touch-manipulation text-base font-semibold md:text-lg"
                    @click="close"
                >
                    {{ cancelLabel }}
                </Button>
                <Button
                    type="button"
                    variant="destructive"
                    class="min-h-14 w-full touch-manipulation text-base font-semibold md:text-lg"
                    :disabled="processing"
                    @click="confirm"
                >
                    {{ confirmLabel }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
