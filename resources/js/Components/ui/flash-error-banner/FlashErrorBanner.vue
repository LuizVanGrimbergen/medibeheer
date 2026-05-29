<script setup lang="ts">
import { computed, onUnmounted, ref, watch } from 'vue';
import { Alert, AlertDescription, AlertTitle } from '@/Components/ui/alert';

const props = defineProps<{
    message: string | null;
    rateLimitSeconds: number | null;
}>();

type IntervalHandle = ReturnType<typeof globalThis.setInterval>;

const remainingSeconds = ref<number | null>(null);
const intervalId = ref<IntervalHandle | null>(null);

const stopCountdown = () => {
    if (intervalId.value === null) {
        return;
    }

    globalThis.clearInterval(intervalId.value);
    intervalId.value = null;
};

const startCountdown = (seconds: number | null) => {
    stopCountdown();
    remainingSeconds.value = seconds;

    if (seconds === null || seconds <= 0) {
        return;
    }

    intervalId.value = globalThis.setInterval(() => {
        if (remainingSeconds.value === null) {
            stopCountdown();

            return;
        }

        if (remainingSeconds.value <= 1) {
            remainingSeconds.value = 0;
            stopCountdown();

            return;
        }

        remainingSeconds.value -= 1;
    }, 1000);
};

watch(
    () => props.rateLimitSeconds,
    (seconds) => {
        startCountdown(seconds);
    },
    { immediate: true },
);

onUnmounted(() => {
    stopCountdown();
});

const displayMessage = computed(() => {
    if (props.message === null) {
        return null;
    }

    if (remainingSeconds.value === null) {
        return props.message;
    }

    return props.message.replace(/\d+/, String(remainingSeconds.value));
});
</script>

<template>
    <Alert
        v-if="displayMessage"
        class="border-primary/40 bg-primary/10 text-primary"
    >
        <AlertTitle>{{ displayMessage }}</AlertTitle>
        <AlertDescription class="sr-only">Flash error message.</AlertDescription>
    </Alert>
</template>
