<script setup lang="ts">
import { CalendarDays } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import {
    medicationUrgencyPanelClass,
    medicationUrgencyPanelIconWrapClass,
} from '@/lib/patient/medications/urgency/medicationUrgencyPanelClasses';
import { formatPrescriptionExpiryDateLabel } from '@/lib/patient/prescriptions/formatPrescriptionExpiryDateLabel';
import { prescriptionExpiryUrgencyContext } from '@/lib/patient/prescriptions/prescriptionExpiryUrgency';

const props = defineProps<{
    prescriptionExpiryDate: string | null;
    prescriptionNumber?: number;
}>();

const { locale, t } = useI18n();

const urgencyContext = computed(() =>
    prescriptionExpiryUrgencyContext(props.prescriptionExpiryDate),
);

const expiryDateIso = computed((): string | null => {
    const trimmed = props.prescriptionExpiryDate?.trim() ?? '';

    return trimmed.length > 0 ? trimmed : null;
});

const formattedExpiryDate = computed((): string | null => {
    if (expiryDateIso.value === null) {
        return null;
    }

    return formatPrescriptionExpiryDateLabel(expiryDateIso.value, locale.value);
});

const expiryPanelClass = computed(() =>
    medicationUrgencyPanelClass(urgencyContext.value?.tone ?? null),
);

const expiryPanelIconWrapClass = computed(() =>
    medicationUrgencyPanelIconWrapClass(urgencyContext.value?.tone ?? null),
);

const expiryDateHeading = computed((): string => {
    if (props.prescriptionNumber !== undefined) {
        return t('patient.prescriptions.expiryDateLabel', {
            number: String(props.prescriptionNumber),
        });
    }

    return t('patient.medications.fields.prescriptionExpiryDateShort');
});
</script>

<template>
    <div
        v-if="formattedExpiryDate !== null && expiryDateIso !== null"
        class="flex w-full min-w-0 justify-start"
    >
        <div
            class="min-w-0 flex-1"
            :class="expiryPanelClass"
        >
            <div :class="expiryPanelIconWrapClass">
                <CalendarDays
                    class="size-5 sm:size-6"
                    aria-hidden="true"
                />
            </div>
            <div class="flex min-w-0 flex-1 flex-col gap-0.5">
                <span
                    class="text-sm font-semibold leading-snug text-text-heading sm:text-base"
                >
                    {{ expiryDateHeading }}
                </span>
                <time
                    :datetime="expiryDateIso"
                    class="whitespace-pre-wrap wrap-break-word text-2xl font-bold leading-none tracking-tight text-text-heading sm:text-3xl"
                >
                    {{ formattedExpiryDate }}
                </time>
            </div>
        </div>
    </div>
</template>
