<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { CircleCheck } from 'lucide-vue-next';
import { useId } from 'vue';
import { useI18n } from 'vue-i18n';
import { Button } from '@/Components/ui/button';

const props = defineProps<{
    medication_name: string;
}>();

const { t } = useI18n();
const titleId = useId();

function readCsrfToken(): string | null {
    const match = /XSRF-TOKEN=([^;]+)/.exec(document.cookie);

    if (match?.[1] === undefined) {
        return null;
    }

    return decodeURIComponent(match[1]);
}

async function onDone(): Promise<void> {
    const csrfToken = readCsrfToken();

    await fetch(route('patient.medication-push-mark.ack'), {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            ...(csrfToken !== null ? { 'X-XSRF-TOKEN': csrfToken } : {}),
        },
        credentials: 'same-origin',
    });

    router.visit(route('patient.dashboard'), { replace: true });
}
</script>

<template>
    <Head>
        <title>{{ t('patient.dashboard.medicationIntakePushSuccess.eyebrow') }}</title>
    </Head>

    <div
        class="flex min-h-svh min-h-dvh flex-col bg-surface"
        role="dialog"
        aria-modal="true"
        :aria-labelledby="titleId"
    >
        <div
            class="flex flex-1 flex-col items-center justify-center px-6 py-10 text-center sm:px-10"
        >
            <div
                class="mb-8 flex size-20 items-center justify-center rounded-2xl border-2 border-success/40 bg-success/10 sm:size-24"
            >
                <CircleCheck
                    class="size-12 text-success sm:size-14"
                    aria-hidden="true"
                    stroke-width="2"
                />
            </div>

            <p class="text-lg font-medium text-text-muted sm:text-xl">
                {{ t('patient.dashboard.medicationIntakePushSuccess.eyebrow') }}
            </p>

            <h1
                :id="titleId"
                class="mt-4 max-w-full text-balance text-3xl font-bold leading-tight tracking-tight text-text-heading sm:text-4xl lg:text-5xl"
            >
                {{ props.medication_name }}
            </h1>

            <p class="mt-4 max-w-sm text-base leading-relaxed text-text-muted sm:text-lg">
                {{ t('patient.dashboard.medicationIntakePushSuccess.subtitle') }}
            </p>
        </div>

        <div
            class="shrink-0 border-t border-border bg-surface px-4 pt-4 pb-[max(1.25rem,env(safe-area-inset-bottom,0px))] sm:px-6"
        >
            <Button
                type="button"
                variant="default"
                size="lg"
                class="min-h-14 w-full touch-manipulation text-lg font-semibold sm:min-h-16 sm:text-xl"
                @click="onDone"
            >
                {{ t('patient.dashboard.medicationIntakePushSuccess.done') }}
            </Button>
        </div>
    </div>
</template>
