<script setup lang="ts">
import { CheckCircle2, X } from 'lucide-vue-next';
import { computed, onUnmounted, ref, watch } from 'vue';
import { Button } from '@/Components/ui/button';

type TimeoutHandle = ReturnType<typeof globalThis.setTimeout>;

const props = withDefaults(
    defineProps<{
        message: string | null;
        autoHideMs?: number;
    }>(),
    {
        autoHideMs: 7000,
    },
);

const dismissed = ref(false);
const timeoutId = ref<TimeoutHandle | null>(null);

const open = computed(() => props.message !== null && !dismissed.value);

const stopTimer = () => {
    if (timeoutId.value === null) {
        return;
    }

    globalThis.clearTimeout(timeoutId.value);
    timeoutId.value = null;
};

const startTimer = () => {
    stopTimer();

    if (!open.value || props.autoHideMs <= 0) {
        return;
    }

    timeoutId.value = globalThis.setTimeout(() => {
        dismissed.value = true;
    }, props.autoHideMs);
};

watch(
    () => props.message,
    () => {
        dismissed.value = false;
        startTimer();
    },
    { immediate: true },
);

onUnmounted(() => {
    stopTimer();
});

function dismiss(): void {
    dismissed.value = true;
    stopTimer();
}
</script>

<template>
    <div
        v-if="open"
        class="relative rounded-2xl border-2 border-success/25 bg-success/5 px-4 py-4 pr-12 sm:pr-14"
    >
        <div class="flex items-start gap-2.5">
            <CheckCircle2
                class="mt-0.5 size-5 shrink-0 text-success"
                aria-hidden="true"
            />
            <p class="min-w-0 text-base font-semibold leading-relaxed text-text-heading sm:text-lg">
                {{ props.message }}
            </p>
        </div>
        <Button
            type="button"
            variant="ghost"
            size="icon"
            class="absolute right-3 top-3 h-9 w-9 text-text-muted hover:bg-surface-hover hover:text-text-heading sm:right-4 sm:top-4"
            aria-label="Melding sluiten"
            @click="dismiss"
        >
            <X class="size-4" />
        </Button>
    </div>
</template>

