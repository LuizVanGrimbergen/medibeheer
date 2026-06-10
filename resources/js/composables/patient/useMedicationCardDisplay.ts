import { computed, type ComputedRef } from 'vue';
import { useI18n } from 'vue-i18n';
import { medicationListVisualTone } from '@/lib/patient/inventory/medicationListVisualTone';
import { medicationUrgencyToneClasses } from '@/lib/patient/medications/urgency/medicationUrgencyToneClasses';
import { medicationStockProgressPercent } from '@/lib/patient/inventory/medicationStockProgressPercent';
import { medicationSupplyEstimateLine } from '@/lib/patient/inventory/medicationSupplyEstimateLine';
import { formatMedicationCardDateLabel } from '@/lib/patient/medications/display/formatMedicationCardDateLabel';
import { medicationCardSortedDoseTimes } from '@/lib/patient/medications/display/medicationCardSortedDoseTimes';
import {
    medicationCardHeaderSummary,
    medicationIntakeDoseLine,
    medicationIntakeNotePreview,
    medicationTypeLabel,
} from '@/lib/patient/medications/display/medicationIntakeSlotDisplay';
import { medicationUrgencyShowsAlertRow } from '@/lib/patient/medications/urgency/medicationUrgencyTone';
import type { MedicationRegisterItem } from '@/lib/types';

export type MedicationCardScheduleDateRow = {
    rangeDisplay: string;
    startDisplay: string;
    endDisplay: string;
    startIso: string | null;
    endIso: string | null;
    ariaLabel: string;
};

export type MedicationCardPrescriptionExpiryDateRow = {
    display: string;
    iso: string;
};

type UseMedicationCardDisplayOptions = {
    medication: ComputedRef<MedicationRegisterItem> | (() => MedicationRegisterItem);
    showStock: ComputedRef<boolean> | (() => boolean);
    listStatusEndedLabelKey?: string;
    listStatusRemovedLabelKey?: string;
};

export function useMedicationCardDisplay({
    medication,
    showStock,
    listStatusEndedLabelKey = 'patient.medications.listStatus.ended',
    listStatusRemovedLabelKey = 'patient.medications.listStatus.removed',
}: UseMedicationCardDisplayOptions) {
    const { t, locale } = useI18n();

    const medicationValue = computed(() =>
        typeof medication === 'function' ? medication() : medication.value,
    );

    const showStockValue = computed(() =>
        typeof showStock === 'function' ? showStock() : showStock.value,
    );

    const isInactiveListItem = computed(
        () =>
            medicationValue.value.list_status === 'ended' ||
            medicationValue.value.list_status === 'removed',
    );

    const listStatusLabel = computed((): string | null => {
        if (medicationValue.value.list_status === 'ended') {
            return t(listStatusEndedLabelKey);
        }

        if (medicationValue.value.list_status === 'removed') {
            return t(listStatusRemovedLabelKey);
        }

        return null;
    });

    const sortedDoseTimes = computed((): string[] =>
        medicationCardSortedDoseTimes(medicationValue.value),
    );

    const doseTimesDisplay = computed(() => sortedDoseTimes.value.join(', '));

    const scheduleDateRow = computed((): MedicationCardScheduleDateRow | null => {
        const first = medicationValue.value.schedules[0];

        if (first === undefined) {
            return null;
        }

        const startTrimmed = first.start_date?.trim() ?? '';
        const endTrimmed = first.end_date?.trim() ?? '';

        if (startTrimmed.length < 1 && endTrimmed.length < 1) {
            return null;
        }

        const startDisplay =
            startTrimmed.length < 1
                ? '—'
                : formatMedicationCardDateLabel(startTrimmed, locale.value);
        const endDisplay =
            endTrimmed.length < 1
                ? t('patient.medications.intakePeriodPresets.ongoing')
                : formatMedicationCardDateLabel(endTrimmed, locale.value);

        return {
            rangeDisplay: t('patient.medications.cardIntakePeriodRange', {
                start: startDisplay,
                end: endDisplay,
            }),
            startDisplay,
            endDisplay,
            startIso: startTrimmed.length < 1 ? null : startTrimmed,
            endIso: endTrimmed.length < 1 ? null : endTrimmed,
            ariaLabel: t('patient.medications.cardIntakePeriodAria', {
                start: startDisplay,
                end: endDisplay,
            }),
        };
    });

    const prescriptionExpiryDateRow = computed(
        (): MedicationCardPrescriptionExpiryDateRow | null => {
            const trimmed =
                medicationValue.value.prescription_expiry_date?.trim() ?? '';

            if (trimmed.length < 1) {
                return null;
            }

            return {
                display: formatMedicationCardDateLabel(trimmed, locale.value),
                iso: trimmed,
            };
        },
    );

    const doseLine = computed(() =>
        medicationIntakeDoseLine(t, {
            dose: medicationValue.value.dose,
            dose_unit: medicationValue.value.dose_unit,
            note: medicationValue.value.note,
            type_medication: medicationValue.value.type_medication,
        }),
    );

    const strengthLine = computed(
        () => medicationValue.value.strength?.trim() || null,
    );

    const hasMedicationDetailsGroup = computed(
        () =>
            doseLine.value !== null ||
            strengthLine.value !== null ||
            sortedDoseTimes.value.length > 0 ||
            scheduleDateRow.value !== null ||
            prescriptionExpiryDateRow.value !== null,
    );

    const typeLabel = computed(() =>
        medicationTypeLabel(t, medicationValue.value.type_medication),
    );

    const headerSummary = computed(() =>
        medicationCardHeaderSummary(t, {
            dose: medicationValue.value.dose,
            dose_unit: medicationValue.value.dose_unit,
            note: medicationValue.value.note,
            type_medication: medicationValue.value.type_medication,
            doseTimes: sortedDoseTimes.value,
        }),
    );

    const stockProgressTone = computed(() =>
        medicationListVisualTone(medicationValue.value),
    );

    const medicationVisualToneClasses = computed(() =>
        medicationUrgencyToneClasses(stockProgressTone.value),
    );

    const notePreview = computed(() =>
        medicationIntakeNotePreview(medicationValue.value),
    );

    const primaryStock = computed(() => medicationValue.value.stocks[0]);

    const supplyEstimateLine = computed((): string =>
        medicationSupplyEstimateLine(t, medicationValue.value),
    );

    const stockProgressPercent = computed((): number | null => {
        if (primaryStock.value === undefined) {
            return null;
        }

        return medicationStockProgressPercent(
            medicationValue.value.supply_estimate_days,
            medicationValue.value.supply_estimate_quality,
        );
    });

    const showCollapsedUrgencyAlert = computed(
        (): boolean =>
            showStockValue.value &&
            primaryStock.value !== undefined &&
            medicationUrgencyShowsAlertRow(stockProgressTone.value),
    );

    const showCollapsedSupplyDaysSummary = computed(
        (): boolean =>
            showStockValue.value &&
            primaryStock.value !== undefined &&
            !isInactiveListItem.value,
    );

    const collapsedHeaderLine = computed((): string =>
        showCollapsedSupplyDaysSummary.value
            ? supplyEstimateLine.value
            : headerSummary.value,
    );

    const stockProgressAriaLabel = computed((): string =>
        t('patient.inventory.stockProgressAria', {
            days: String(medicationValue.value.supply_estimate_days ?? 0),
        }),
    );

    return {
        isInactiveListItem,
        listStatusLabel,
        sortedDoseTimes,
        doseTimesDisplay,
        scheduleDateRow,
        prescriptionExpiryDateRow,
        doseLine,
        strengthLine,
        hasMedicationDetailsGroup,
        typeLabel,
        headerSummary,
        stockProgressTone,
        medicationVisualToneClasses,
        medicationPillWrapToneClass: computed(
            () => medicationVisualToneClasses.value.pillWrap,
        ),
        medicationPillIconClass: computed(
            () => medicationVisualToneClasses.value.pillIcon,
        ),
        notePreview,
        supplyEstimateLine,
        stockProgressPercent,
        showCollapsedUrgencyAlert,
        showCollapsedSupplyDaysSummary,
        collapsedHeaderLine,
        stockProgressAriaLabel,
    };
}
