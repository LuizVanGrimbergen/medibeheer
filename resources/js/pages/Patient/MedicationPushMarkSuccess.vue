<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import PatientActionSuccessScreen from '@/Components/Patient/PatientActionSuccessScreen.vue';

const props = defineProps<{
    medication_name: string;
}>();

const { t } = useI18n();
const open = ref(true);

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
        <title>
            {{ t('patient.dashboard.medicationIntakePushSuccess.eyebrow') }}
        </title>
    </Head>

    <div class="bg-surface min-h-dvh min-h-svh">
        <PatientActionSuccessScreen
            v-model:open="open"
            :teleport="false"
            :eyebrow="
                t('patient.dashboard.medicationIntakePushSuccess.eyebrow')
            "
            :title="props.medication_name"
            :subtitle="
                t('patient.dashboard.medicationIntakePushSuccess.subtitle')
            "
            :done-label="
                t('patient.dashboard.medicationIntakePushSuccess.done')
            "
            @done="onDone"
        />
    </div>
</template>
