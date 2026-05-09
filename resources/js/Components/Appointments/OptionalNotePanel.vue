<script setup lang="ts">
import { computed, nextTick, ref, watch } from 'vue';
import { Button } from '@/Components/ui/button';

const props = withDefaults(
    defineProps<{
        open: boolean;
        title: string;
        description: string;
        fieldLabel: string;
        placeholder: string;
        textareaId: string;
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
    'box-border min-h-34 w-full shrink-0 rounded-2xl border-2 border-border bg-surface px-4 py-3.5 text-lg leading-normal text-text placeholder:text-text-muted focus-visible:border-focus focus-visible:ring-2 focus-visible:ring-focus/25';

const confirmVariantClass = computed(() => {
    if (props.tone === 'success') {
        return 'border-2 border-success bg-success text-white hover:bg-success hover:text-white';
    }

    if (props.tone === 'danger') {
        return 'border-2 border-danger bg-danger text-white hover:bg-danger hover:text-white';
    }

    return '';
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
    <div
        v-if="open"
        class="space-y-5 rounded-3xl border-2 border-border/80 bg-surface p-5 shadow-sm shadow-black/[0.04] sm:p-6"
    >
        <div class="space-y-2">
            <p class="text-lg font-bold leading-snug text-text-heading sm:text-xl">
                {{ title }}
            </p>
            <p class="text-base leading-relaxed text-text-muted">
                {{ description }}
            </p>
        </div>

        <div class="space-y-2">
            <p class="text-base font-semibold text-text-heading">
                {{ fieldLabel }}
            </p>
            <textarea
                :id="textareaId"
                ref="textareaRef"
                v-model="note"
                :rows="rows"
                :class="fieldClass"
                :placeholder="placeholder"
            />
        </div>

        <div class="flex flex-col gap-3 sm:flex-row">
            <Button
                type="button"
                variant="outline"
                size="lg"
                class="min-h-14 min-w-0 flex-1 touch-manipulation px-4 text-lg font-semibold"
                @click="close"
            >
                {{ cancelLabel }}
            </Button>
            <Button
                type="button"
                size="lg"
                class="min-h-14 min-w-0 flex-1 touch-manipulation px-4 text-lg font-semibold"
                :class="confirmVariantClass"
                :disabled="disabled"
                @click="submit"
            >
                {{ confirmLabel }}
            </Button>
        </div>
    </div>
</template>
