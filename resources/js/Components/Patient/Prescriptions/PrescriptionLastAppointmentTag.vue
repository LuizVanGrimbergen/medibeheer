<script setup lang="ts">
import { FileText } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
</script>

<template>
    <span
        class="border-primary/15 bg-primary/8 inline-flex max-w-full min-w-0 items-center gap-2.5 rounded-2xl border px-3 py-2 shadow-sm sm:gap-3 sm:px-3.5 sm:py-2.5"
    >
        <span
            class="bg-primary/12 flex size-8 shrink-0 items-center justify-center rounded-xl sm:size-9"
            aria-hidden="true"
        >
            <FileText class="text-primary size-4 sm:size-4.5" />
        </span>
        <span
            class="text-text-heading min-w-0 text-sm leading-snug font-bold sm:text-base"
        >
            {{ t('patient.prescriptions.lastPrescriptionAppointmentTag') }}
        </span>
    </span>
</template>
