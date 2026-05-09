<script setup lang="ts">
import { computed, nextTick, ref, watch } from 'vue';
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
const textareaRef = ref<HTMLTextAreaElement | null>(null);

const fieldClass =
    'box-border min-h-[8.5rem] w-full shrink-0 rounded-2xl border-2 border-border bg-surface px-4 py-3.5 text-lg leading-normal text-text placeholder:text-text-muted focus-visible:border-focus focus-visible:ring-2 focus-visible:ring-focus/25';

const confirmButtonClass = computed(() => {
    if (props.tone === 'success') {
        return 'min-h-14 w-full touch-manipulation border-2 border-success bg-success px-4 text-lg font-semibold text-white hover:bg-success hover:text-white sm:w-auto sm:min-w-40';
    }

    if (props.tone === 'danger') {
        return 'min-h-14 w-full touch-manipulation border-2 border-danger bg-danger px-4 text-lg font-semibold text-white hover:bg-danger hover:text-white sm:w-auto sm:min-w-40';
    }

    return 'min-h-14 w-full touch-manipulation px-4 text-lg font-semibold sm:w-auto sm:min-w-40';
});

watch(
    () => props.open,
    async (open) => {
        if (!open) {
            return;
        }

        note.value = '';

        await nextTick();
        textareaRef.value?.focus({ preventScroll: true });
    },
);

async function onOpenAutoFocus(event: Event): Promise<void> {
    event.preventDefault();

    await nextTick();

    const el = textareaRef.value;

    if (el === null) {
        return;
    }

    el.focus({ preventScroll: true });
    el.scrollIntoView({ block: 'nearest', behavior: 'auto' });
}

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
        <DialogContent
            :class="dialogContentClass"
            @open-auto-focus="onOpenAutoFocus"
        >
            <DialogHeader class="shrink-0 space-y-2 text-left">
                <DialogTitle class="pr-14 text-xl font-bold leading-tight text-text-heading">
                    {{ title }}
                </DialogTitle>
                <DialogDescription class="text-base leading-relaxed text-text-muted">
                    {{ description }}
                </DialogDescription>
            </DialogHeader>

            <div
                class="flex min-h-0 flex-1 basis-0 flex-col overflow-y-auto overscroll-y-contain [-webkit-overflow-scrolling:touch]"
            >
                <Label
                    :for="textareaId"
                    class="mb-2 block shrink-0 text-base font-semibold text-text-heading"
                >
                    {{ fieldLabel }}
                </Label>
                <textarea
                    :id="textareaId"
                    ref="textareaRef"
                    v-model="note"
                    :rows="rows"
                    :class="fieldClass"
                    :placeholder="placeholder"
                />
            </div>

            <DialogFooter
                class="mt-1 flex w-full shrink-0 flex-col-reverse gap-3 border-t border-border/60 pt-4 sm:flex-row sm:justify-between sm:border-t-0 sm:pt-0"
            >
                <Button
                    type="button"
                    variant="outline"
                    size="lg"
                    class="min-h-14 w-full touch-manipulation border-2 border-danger/50 px-4 text-lg font-semibold text-danger hover:border-danger hover:bg-danger/10 hover:text-danger sm:w-auto sm:min-w-40"
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
