<script setup lang="ts">
import { FileText } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
</script>

<template>
    <span
        class="inline-flex max-w-full min-w-0 items-center gap-2.5 rounded-2xl border border-primary/15 bg-primary/8 px-3 py-2 shadow-sm sm:gap-3 sm:px-3.5 sm:py-2.5"
    >
        <span
            class="flex size-8 shrink-0 items-center justify-center rounded-xl bg-primary/12 sm:size-9"
            aria-hidden="true"
        >
            <FileText class="size-4 text-primary sm:size-4.5" />
        </span>
        <span class="min-w-0 text-sm font-bold leading-snug text-text-heading sm:text-base">
            {{ t('patient.prescriptions.lastPrescriptionAppointmentTag') }}
        </span>
    </span>
</template>
