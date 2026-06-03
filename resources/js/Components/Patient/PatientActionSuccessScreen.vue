<script setup lang="ts">
import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import type { PatientActionSuccessDetail } from '@/composables/patient/usePatientActionSuccessScreen';
import {
    patientPageCardDetailLabelClass,
    patientPageCardDetailsGroupClass,
    patientPageCardDetailValueClass,
} from '@/lib/patient/patientPageTypography';
import { CircleCheck } from 'lucide-vue-next';
import { computed, ref, useId, type ComponentPublicInstance } from 'vue';
import { useGsapActionConfirm } from '@/composables/motion/useGsapActionConfirm';
import { useGsapCheckmarkDraw } from '@/composables/motion/useGsapCheckmarkDraw';

const open = defineModel<boolean>('open', { required: true });

const props = withDefaults(
    defineProps<{
        title: string;
        message?: string | null;
        eyebrow?: string | null;
        subtitle?: string | null;
        doneLabel: string;
        details?: PatientActionSuccessDetail[];
        teleport?: boolean;
    }>(),
    {
        message: null,
        eyebrow: null,
        subtitle: null,
        details: () => [],
        teleport: true,
    },
);

const emit = defineEmits<{
    done: [];
}>();

const titleId = useId();
const successIconRef = ref<HTMLElement | null>(null);
const checkmarkRef = ref<HTMLElement | ComponentPublicInstance | null>(null);

useGsapActionConfirm(successIconRef, open);
useGsapCheckmarkDraw(checkmarkRef, open);

const trimmedMessage = computed((): string | null => {
    if (typeof props.message !== 'string') {
        return null;
    }

    const trimmed = props.message.trim();

    return trimmed === '' ? null : trimmed;
});

const trimmedEyebrow = computed((): string | null => {
    if (typeof props.eyebrow !== 'string') {
        return null;
    }

    const trimmed = props.eyebrow.trim();

    return trimmed === '' ? null : trimmed;
});

const trimmedSubtitle = computed((): string | null => {
    if (typeof props.subtitle !== 'string') {
        return null;
    }

    const trimmed = props.subtitle.trim();

    return trimmed === '' ? null : trimmed;
});

const visibleDetails = computed((): PatientActionSuccessDetail[] =>
    props.details.filter(
        (detail) => detail.label.trim() !== '' && detail.value.trim() !== '',
    ),
);

const showSummaryCard = computed(
    () => trimmedMessage.value !== null || visibleDetails.value.length > 0,
);

function dismiss(): void {
    open.value = false;
    emit('done');
}
</script>

<template>
    <Teleport to="body" :disabled="!props.teleport">
        <div
            v-if="open"
            class="bg-surface fixed inset-0 z-[110] flex min-h-dvh min-h-svh flex-col"
            role="dialog"
            aria-modal="true"
            :aria-labelledby="titleId"
        >
            <div
                class="flex flex-1 flex-col items-center justify-center px-6 py-10 text-center sm:px-10"
            >
                <div
                    ref="successIconRef"
                    class="border-success/40 bg-success/10 mb-8 flex size-20 items-center justify-center rounded-2xl border-2 sm:size-24"
                >
                    <CircleCheck
                        ref="checkmarkRef"
                        class="text-success size-12 sm:size-14"
                        aria-hidden="true"
                        stroke-width="2"
                    />
                </div>

                <p
                    v-if="trimmedEyebrow !== null"
                    class="text-text-muted text-lg font-medium sm:text-xl"
                >
                    {{ trimmedEyebrow }}
                </p>

                <h1
                    :id="titleId"
                    :class="
                        trimmedEyebrow !== null
                            ? 'text-text-heading mt-4 max-w-full text-3xl leading-tight font-bold tracking-tight text-balance sm:text-4xl lg:text-5xl'
                            : 'text-text-heading max-w-full text-3xl leading-tight font-bold tracking-tight text-balance sm:text-4xl lg:text-5xl'
                    "
                >
                    {{ props.title }}
                </h1>

                <p
                    v-if="trimmedSubtitle !== null"
                    class="text-text-muted mt-4 max-w-sm text-base leading-relaxed sm:text-lg"
                >
                    {{ trimmedSubtitle }}
                </p>

                <Card
                    v-if="showSummaryCard"
                    class="border-border/80 bg-surface text-text mt-8 w-full max-w-lg rounded-2xl border shadow-md shadow-black/[0.04] sm:mt-10 sm:rounded-3xl"
                >
                    <CardContent class="p-0">
                        <div
                            class="bg-surface rounded-2xl px-4 py-4 text-left sm:rounded-3xl sm:px-5 sm:py-5 md:p-7 lg:p-8"
                        >
                            <p
                                v-if="trimmedMessage !== null"
                                :class="patientPageCardDetailValueClass"
                            >
                                {{ trimmedMessage }}
                            </p>

                            <div
                                v-if="visibleDetails.length > 0"
                                :class="[
                                    patientPageCardDetailsGroupClass,
                                    trimmedMessage !== null ? 'mt-5' : null,
                                ]"
                            >
                                <div
                                    v-for="(detail, index) in visibleDetails"
                                    :key="`${detail.label}-${index}`"
                                    class="space-y-1.5"
                                >
                                    <p :class="patientPageCardDetailLabelClass">
                                        {{ detail.label }}
                                    </p>
                                    <p :class="patientPageCardDetailValueClass">
                                        {{ detail.value }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div
                class="border-border bg-surface shrink-0 border-t px-4 pt-4 pb-[max(1.25rem,env(safe-area-inset-bottom,0px))] sm:px-6"
            >
                <Button
                    type="button"
                    variant="default"
                    size="lg"
                    class="min-h-14 w-full touch-manipulation text-lg font-semibold sm:min-h-16 sm:text-xl"
                    @click="dismiss"
                >
                    {{ props.doneLabel }}
                </Button>
            </div>
        </div>
    </Teleport>
</template>
