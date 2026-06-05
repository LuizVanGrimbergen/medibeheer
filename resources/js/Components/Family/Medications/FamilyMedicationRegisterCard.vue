<script setup lang="ts">
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import FamilyOverviewCollapsibleSection from '@/Components/Family/Overview/FamilyOverviewCollapsibleSection.vue';
import MedicationCard from '@/Components/Medications/MedicationCard.vue';
import MedicationTypeLeadIcon from '@/Components/Medications/MedicationTypeLeadIcon.vue';
import { medicationListVisualTone } from '@/lib/patient/inventory/medicationListVisualTone';
import { medicationListVisualToneClasses } from '@/lib/patient/inventory/medicationListVisualToneClasses';
import { medicationSupplyEstimateLine } from '@/lib/patient/inventory/medicationSupplyEstimateLine';
import { medicationCardHeaderSummary } from '@/lib/patient/medications/display/medicationIntakeSlotDisplay';
import { medicationUrgencyShowsAlertRow } from '@/lib/patient/medications/urgency/medicationUrgencyTone';
import type { MedicationListItem } from '@/lib/types';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        medication: MedicationListItem;
        defaultOpen?: boolean;
    }>(),
    {
        defaultOpen: false,
    },
);

const { t } = useI18n();

const isOpen = ref(props.defaultOpen);

const stockProgressTone = computed(() =>
    medicationListVisualTone(props.medication),
);

const medicationVisualToneClasses = computed(() =>
    medicationListVisualToneClasses(stockProgressTone.value),
);

const iconWrapperClass = computed((): string =>
    cn(
        '!size-12 !rounded-xl sm:!size-14',
        medicationVisualToneClasses.value.pillWrap,
    ),
);

const sortedDoseTimes = computed((): string[] => {
    const seen = new Set<string>();

    for (const schedule of props.medication.schedules) {
        const raw = schedule.dose_time?.trim();

        if (raw === undefined || raw.length < 1) {
            continue;
        }

        for (const segment of raw.split(',')) {
            const part = segment.trim();

            if (part.length > 0) {
                seen.add(part);
            }
        }
    }

    return Array.from(seen);
});

const listStatusLabel = computed((): string | null => {
    if (props.medication.list_status === 'ended') {
        return t('family.medications.listStatus.ended');
    }

    if (props.medication.list_status === 'removed') {
        return t('family.medications.listStatus.removed');
    }

    return null;
});

const headerSummary = computed((): string =>
    medicationCardHeaderSummary(t, {
        dose: props.medication.dose,
        dose_unit: props.medication.dose_unit,
        note: props.medication.note,
        type_medication: props.medication.type_medication,
        doseTimes: sortedDoseTimes.value,
    }),
);

const collapsedSummary = computed((): string => {
    const isActive = props.medication.list_status === 'active';
    const hasStock = props.medication.stocks.length > 0;

    if (isActive && hasStock) {
        const estimateLine = medicationSupplyEstimateLine(t, props.medication);

        if (medicationUrgencyShowsAlertRow(stockProgressTone.value)) {
            return `${t('family.medications.collapsedSummaryLowStock')} · ${estimateLine}`;
        }

        return estimateLine;
    }

    if (listStatusLabel.value !== null) {
        return listStatusLabel.value;
    }

    return headerSummary.value;
});
</script>

<template>
    <FamilyOverviewCollapsibleSection
        v-model:open="isOpen"
        :heading="medication.name"
        :toggle-label="t('family.medications.registerToggle')"
        :collapsed-summary="collapsedSummary"
        :icon-wrapper-class="iconWrapperClass"
    >
        <template #icon>
            <MedicationTypeLeadIcon
                :medication-type="medication.type_medication"
                :icon-tone-class="medicationVisualToneClasses.pillIcon"
            />
        </template>

        <MedicationCard
            :medication="medication"
            content-only
            show-stock
            stock-controls-first
            stock-update-route-name="family.medications.stocks.update"
            list-status-ended-label-key="family.medications.listStatus.ended"
            list-status-removed-label-key="family.medications.listStatus.removed"
        />
    </FamilyOverviewCollapsibleSection>
</template>
