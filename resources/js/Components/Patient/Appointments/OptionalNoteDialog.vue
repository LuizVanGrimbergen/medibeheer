<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Button } from '@/Components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog';
import { Label } from '@/Components/ui/label';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        open: boolean;
        title: string;
        description: string;
        fieldLabel: string;
        placeholder: string;
        textareaId: string;
        dialogContentClass: string;
        cancelLabel: string;
        confirmLabel: string;
        rows?: number;
        disabled?: boolean;
        tone?: 'success' | 'danger' | 'default';
    }>(),
    {
        rows: 5,
        disabled: false,
        tone: 'default',
    },
);

const emit = defineEmits<{
    'update:open': [value: boolean];
    submit: [payload: { text: string | null }];
}>();

const note = ref('');

const fieldClass =
    'h-auto min-h-14 w-full rounded-2xl border-2 border-border bg-surface px-4 py-3.5 text-lg leading-normal text-text placeholder:text-text-muted focus-visible:border-focus focus-visible:ring-2 focus-visible:ring-focus/25';

const confirmButtonClass = computed(() => {
    if (props.tone === 'success') {
        return 'min-h-12 w-full touch-manipulation border-2 border-success bg-success text-base font-semibold text-white hover:bg-success hover:text-white sm:w-auto';
    }

    if (props.tone === 'danger') {
        return 'min-h-12 w-full touch-manipulation border-2 border-danger bg-danger text-base font-semibold text-white hover:bg-danger hover:text-white sm:w-auto';
    }

    return 'min-h-12 w-full touch-manipulation text-base sm:w-auto';
});

watch(
    () => props.open,
    (open) => {
        if (!open) {
            return;
        }

        note.value = '';
    },
);

function normalizeField(value: string): string | null {
    const trimmed = value.trim();

    return trimmed === '' ? null : trimmed;
}

function close(): void {
    emit('update:open', false);
}

function submit(): void {
    emit('submit', { text: normalizeField(note.value) });
    close();
}
</script>

<template>
    <Dialog
        :open="open"
        @update:open="emit('update:open', $event)"
    >
        <DialogContent :class="dialogContentClass">
            <DialogHeader class="shrink-0 space-y-2 text-left">
                <DialogTitle class="pr-14 text-xl font-bold leading-tight text-text-heading">
                    {{ title }}
                </DialogTitle>
                <DialogDescription class="text-base leading-relaxed text-text-muted">
                    {{ description }}
                </DialogDescription>
            </DialogHeader>

            <div
                class="min-h-0 flex-1 overflow-y-auto overscroll-y-contain [-webkit-overflow-scrolling:touch]"
            >
                <Label
                    :for="textareaId"
                    class="mb-2 block text-base font-semibold text-text-heading"
                >
                    {{ fieldLabel }}
                </Label>
                <textarea
                    :id="textareaId"
                    v-model="note"
                    :rows="rows"
                    :class="fieldClass"
                    :placeholder="placeholder"
                />
            </div>

            <DialogFooter
                class="mt-1 flex w-full shrink-0 flex-col-reverse gap-3 border-t border-border/60 pt-4 sm:flex-row sm:justify-end sm:border-t-0 sm:pt-0"
            >
                <Button
                    type="button"
                    variant="outline"
                    size="lg"
                    class="min-h-12 w-full touch-manipulation text-base sm:w-auto"
                    @click="close"
                >
                    {{ cancelLabel }}
                </Button>
                <Button
                    type="button"
                    size="lg"
                    :disabled="disabled"
                    :class="cn(confirmButtonClass)"
                    @click="submit"
                >
                    {{ confirmLabel }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
