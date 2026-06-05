<script setup lang="ts">
import { Calendar, FileText, Pill } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import {
    medicationUrgencyToneFromDaysRemaining,
} from '@/lib/patient/medications/urgency/medicationUrgencyTone';
import { medicationUrgencyToneClasses } from '@/lib/patient/medications/urgency/medicationUrgencyToneClasses';
import { prescriptionExpiryStatusLine } from '@/lib/patient/prescriptions/prescriptionExpiryStatusLine';
import { cn } from '@/lib/utils';

const props = defineProps<{
    prescription: {
        medication_name: string;
        days_remaining: number;
        is_last_in_batch: boolean;
    };
}>();

const { t } = useI18n();

const urgencyTone = computed(() =>
    medicationUrgencyToneFromDaysRemaining(props.prescription.days_remaining),
);

const toneClasses = computed(() =>
    medicationUrgencyToneClasses(urgencyTone.value),
);

const expiryLabel = computed((): string =>
    prescriptionExpiryStatusLine(t, props.prescription.days_remaining),
);
</script>

<template>
    <div
        class="border-border bg-surface w-full min-w-0 rounded-2xl border shadow-sm"
    >
        <div class="flex items-start gap-3 px-4 py-4 md:px-5 md:py-3.5">
            <div
                :class="
                    cn(
                        'flex size-12 shrink-0 items-center justify-center rounded-xl sm:size-14',
                        toneClasses.pillWrap,
                    )
                "
            >
                <Pill
                    class="size-6 shrink-0 sm:size-7"
                    :class="toneClasses.pillIcon"
                    aria-hidden="true"
                />
            </div>
            <div class="min-w-0 flex-1">
                <div class="flex items-start justify-between gap-3">
                    <p
                        class="text-text-heading min-w-0 text-lg font-semibold md:text-base"
                    >
                        {{ prescription.medication_name }}
                    </p>
                    <span
                        class="inline-flex shrink-0 items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold"
                        :class="[toneClasses.pillWrap, toneClasses.pillIcon]"
                    >
                        <Calendar
                            class="size-3.5 shrink-0"
                            aria-hidden="true"
                        />
                        {{ expiryLabel }}
                    </span>
                </div>
                <p
                    v-if="prescription.is_last_in_batch"
                    class="text-danger mt-2 inline-flex items-center gap-1.5 text-sm font-semibold"
                >
                    <FileText
                        class="size-3.5 shrink-0"
                        aria-hidden="true"
                    />
                    {{ t('patient.prescriptions.lastPrescriptionAppointmentTag') }}
                </p>
            </div>
        </div>
    </div>
</template>
