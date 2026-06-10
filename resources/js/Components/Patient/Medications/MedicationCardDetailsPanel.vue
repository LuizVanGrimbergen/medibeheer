<script setup lang="ts">
import {
    Calendar,
    CalendarClock,
    Clock,
    Package,
    Scale,
} from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import MedicationStockControls from '@/Components/shared/medications/MedicationStockControls.vue';
import PatientListCardDetailsGroup from '@/Components/Patient/PatientListCardDetailsGroup.vue';
import PatientListCardDetailsGroupItem from '@/Components/Patient/PatientListCardDetailsGroupItem.vue';
import type {
    MedicationCardPrescriptionExpiryDateRow,
    MedicationCardScheduleDateRow,
} from '@/composables/patient/useMedicationCardDisplay';
import { mobileShellPageCardDetailValueClass } from '@/lib/shell/mobileShellTypography';
import type { MedicationRegisterItem } from '@/lib/types';

const { t } = useI18n();

defineProps<{
    medication: MedicationRegisterItem;
    doseLine: string | null;
    strengthLine: string | null;
    sortedDoseTimes: string[];
    doseTimesDisplay: string;
    scheduleDateRow: MedicationCardScheduleDateRow | null;
    prescriptionExpiryDateRow: MedicationCardPrescriptionExpiryDateRow | null;
    hasMedicationDetailsGroup: boolean;
    notePreview: string | null;
    showStock: boolean;
    showStockSummary: boolean;
    stockControlsFirst: boolean;
    stockUpdateRouteName: string;
}>();
</script>

<template>
    <MedicationStockControls
        v-if="showStock && stockControlsFirst"
        :medication="medication"
        :update-route-name="stockUpdateRouteName"
        :id-prefix="`medication-card-stock-${medication.id}`"
        :can-adjust-stock="medication.list_status === 'active'"
        :show-summary="showStockSummary"
    />

    <PatientListCardDetailsGroup v-if="hasMedicationDetailsGroup">
        <PatientListCardDetailsGroupItem
            v-if="doseLine !== null"
            :label="t('patient.medications.overview.amountPerIntake')"
        >
            <template #icon>
                <Package :stroke-width="2" aria-hidden="true" />
            </template>
            {{ doseLine }}
        </PatientListCardDetailsGroupItem>

        <PatientListCardDetailsGroupItem
            v-if="strengthLine !== null"
            :label="t('patient.medications.fields.strength')"
        >
            <template #icon>
                <Scale :stroke-width="2" aria-hidden="true" />
            </template>
            {{ strengthLine }}
        </PatientListCardDetailsGroupItem>

        <PatientListCardDetailsGroupItem
            v-if="sortedDoseTimes.length > 0"
            :label="t('patient.medications.fields.doseTime')"
        >
            <template #icon>
                <Clock :stroke-width="2" aria-hidden="true" />
            </template>
            {{ doseTimesDisplay }}
        </PatientListCardDetailsGroupItem>

        <PatientListCardDetailsGroupItem
            v-if="scheduleDateRow !== null"
            :label="t('patient.medications.fields.intakePeriod')"
            raw-value
        >
            <template #icon>
                <Calendar :stroke-width="2" aria-hidden="true" />
            </template>
            <p
                :class="mobileShellPageCardDetailValueClass"
                :aria-label="scheduleDateRow.ariaLabel"
            >
                {{ scheduleDateRow.rangeDisplay }}
            </p>
        </PatientListCardDetailsGroupItem>

        <PatientListCardDetailsGroupItem
            v-if="prescriptionExpiryDateRow !== null"
            :label="t('patient.medications.fields.prescriptionExpiryDateShort')"
            raw-value
        >
            <template #icon>
                <CalendarClock :stroke-width="2" aria-hidden="true" />
            </template>
            <p :class="mobileShellPageCardDetailValueClass">
                <time :datetime="prescriptionExpiryDateRow.iso">
                    {{ prescriptionExpiryDateRow.display }}
                </time>
            </p>
        </PatientListCardDetailsGroupItem>
    </PatientListCardDetailsGroup>

    <p
        v-if="notePreview !== null"
        class="border-primary text-text border-l-[3px] py-0.5 pl-3.5 text-base leading-relaxed italic sm:text-lg"
    >
        {{ notePreview }}
    </p>

    <MedicationStockControls
        v-if="showStock && !stockControlsFirst"
        :medication="medication"
        :update-route-name="stockUpdateRouteName"
        :id-prefix="`medication-card-stock-${medication.id}`"
        :can-adjust-stock="medication.list_status === 'active'"
        :show-summary="showStockSummary"
        :class="
            showStockSummary ? 'border-border/70 border-t pt-5' : undefined
        "
    />
</template>
