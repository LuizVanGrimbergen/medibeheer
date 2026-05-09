<script setup lang="ts">
import { CircleCheck } from 'lucide-vue-next';
import { computed, onUnmounted, ref, useId, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import type { DailyMoodScoreValue } from '@/lib/types';

const props = defineProps<{
    mood: DailyMoodScoreValue | null;
}>();

const { t } = useI18n();
const titleId = useId();
const dismissed = ref(false);

watch(
    () => props.mood,
    () => {
        dismissed.value = false;
    },
);

let removeEscapeListener: (() => void) | null = null;

const open = computed(() => props.mood !== null && !dismissed.value);

watch(open, (isOpen) => {
    removeEscapeListener?.();
    removeEscapeListener = null;

    if (!isOpen) {
        return;
    }

    const onEscape = (event: KeyboardEvent): void => {
        if (event.key === 'Escape') {
            dismissed.value = true;
        }
    };

    globalThis.addEventListener('keydown', onEscape);
    removeEscapeListener = () => globalThis.removeEventListener('keydown', onEscape);
});

onUnmounted(() => {
    removeEscapeListener?.();
});

const bodyMessageKey = computed(() =>
    props.mood === 'good'
        ? 'patient.dashboard.dailyCheckins.success.messageGood'
        : 'patient.dashboard.dailyCheckins.success.messageComfort',
);

function dismiss(): void {
    dismissed.value = true;
}
</script>

<template>
    <Teleport to="body">
        <div
            v-if="open"
            class="fixed inset-0 z-50 flex flex-col items-center justify-center bg-surface/85 px-4 py-10 backdrop-blur-sm animate-in fade-in duration-200 sm:px-6"
            role="dialog"
            aria-modal="true"
            :aria-labelledby="titleId"
        >
            <Card
                class="w-full max-w-md rounded-2xl border border-border/80 bg-surface text-text shadow-md shadow-black/[0.04] animate-in fade-in zoom-in-95 duration-200 sm:rounded-3xl"
            >
                <CardContent
                    class="flex flex-col items-center space-y-4 p-6 text-center sm:space-y-6 sm:p-6 lg:p-7"
                >
                    <div
                        class="flex size-14 shrink-0 items-center justify-center rounded-xl border border-primary/35 bg-primary/10 sm:size-16"
                    >
                        <CircleCheck
                            class="size-8 text-primary sm:size-9"
                            aria-hidden="true"
                            stroke-width="2"
                        />
                    </div>

                    <div class="space-y-1 sm:space-y-1.5">
                        <h2
                            :id="titleId"
                            class="text-base font-bold leading-snug text-text-heading sm:text-lg lg:text-xl"
                        >
                            {{ t('patient.dashboard.dailyCheckins.success.title') }}
                        </h2>

                        <p
                            class="text-sm leading-snug text-text-muted sm:text-base sm:leading-relaxed"
                        >
                            {{ t(bodyMessageKey) }}
                        </p>
                    </div>

                    <Button
                        type="button"
                        variant="default"
                        size="lg"
                        class="min-h-11 w-full touch-manipulation px-4 text-sm font-semibold sm:min-h-14 sm:px-4 sm:text-lg"
                        @click="dismiss"
                    >
                        {{ t('patient.dashboard.dailyCheckins.success.done') }}
                    </Button>
                </CardContent>
            </Card>
        </div>
    </Teleport>
</template>
