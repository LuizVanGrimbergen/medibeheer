import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useInventoryVacationShareToPhotos } from '@/composables/inventory/useInventoryVacationShareToPhotos';
import { localCalendarDateIsoToday } from '@/lib/patient/appointments/validation/appointmentStartsAtLocalValidation';
import { buildInventoryVacationShareImagePayload } from '@/lib/patient/inventory/buildInventoryVacationShareImagePayload';
import {
    inventoryVacationDoseUnitChipForItem,
    inventoryVacationDoseUnitForItem,
} from '@/lib/patient/inventory/inventoryVacationDoseUnitDisplay';
import { formatInventoryVacationDateLabel } from '@/lib/patient/inventory/formatInventoryVacationDateLabel';
import type { PatientInventoryVacationPageProps } from '@/lib/patient/inventory/patientInventoryVacationPageProps';
import type { PatientInventoryVacationSupplyResult } from '@/lib/patient/inventory/patientInventoryVacationSupply';

export function usePatientInventoryVacationPage(
    props: PatientInventoryVacationPageProps,
) {
    const { t } = useI18n();

    const form = useForm({
        starts_on: props.starts_on,
        ends_on: props.ends_on,
    });

    const minDateIso = computed(() => localCalendarDateIsoToday());

    const showResults = computed(() => props.result !== null);

    const pageIntro = computed(() =>
        showResults.value ? '' : t('patient.inventory.vacationDialogDescription'),
    );

    const formattedStartsOn = computed(() =>
        formatInventoryVacationDateLabel(form.starts_on),
    );

    const formattedEndsOn = computed(() =>
        formatInventoryVacationDateLabel(form.ends_on),
    );

    const expiringPrescriptions = computed(
        () => props.expiring_prescriptions ?? [],
    );

    const pickupItemsWithSavedPackage = computed(() => {
        if (props.result === null) {
            return [];
        }

        return props.result.items.filter(
            (item) =>
                item.stock_pieces_per_package !== null &&
                item.stock_pieces_per_package > 0,
        );
    });

    const vacationSavedPackageHint = computed((): string | null => {
        const items = pickupItemsWithSavedPackage.value;

        if (items.length === 0) {
            return null;
        }

        const allLiquid = items.every(
            (item) => item.type_medication === 'liquid',
        );

        if (allLiquid) {
            return t(
                'patient.inventory.vacationPickupCalculator.liquid.savedPiecesHint',
            );
        }

        return t('patient.inventory.vacationPickupCalculator.savedPiecesHint');
    });

    const vacationSavedPackageHintUsesLiquidIcon = computed(
        (): boolean =>
            pickupItemsWithSavedPackage.value.length > 0 &&
            pickupItemsWithSavedPackage.value.every(
                (item) => item.type_medication === 'liquid',
            ),
    );

    const vacationDaysLabel = computed((): string => {
        const days = props.result?.vacation_days;

        if (days === undefined) {
            return '';
        }

        if (days === 1) {
            return t('patient.inventory.vacationResultsDaysValueOne');
        }

        return t('patient.inventory.vacationResultsDaysValue', {
            days: String(days),
        });
    });

    function submit(): void {
        form.post(route('patient.inventory.vacation.store'), {
            preserveScroll: true,
        });
    }

    const vacationShareImagePayload = computed(() => {
        if (props.result === null) {
            return null;
        }

        const appName = import.meta.env.VITE_APP_NAME ?? 'Medibeheer';

        return buildInventoryVacationShareImagePayload({
            result: props.result,
            startsOn: form.starts_on,
            endsOn: form.ends_on,
            vacationDaysLabel: vacationDaysLabel.value,
            title: t('patient.inventory.vacationDialogTitle'),
            periodHeading: t('patient.inventory.vacationResultsPeriodHeading'),
            departureLabel: t('patient.inventory.vacationResultsDepartureLabel'),
            returnLabel: t('patient.inventory.vacationResultsReturnLabel'),
            savedPackageHint: vacationSavedPackageHint.value,
            expiringPrescriptions: expiringPrescriptions.value,
            totalLabel: t('patient.inventory.vacationPickupCalculator.totalLabel'),
            minimumBoxesLabel: t(
                'patient.inventory.vacationPickupCalculator.minimumBoxesLabel',
            ),
            liquidMinimumBoxesLabel: t(
                'patient.medications.stockCalculator.liquid.numberOfBottles',
            ),
            piecesPerBoxLabel: t(
                'patient.medications.stockCalculator.piecesPerBox',
            ),
            liquidPiecesPerBoxLabel: t(
                'patient.medications.stockCalculator.liquid.mlPerBottle',
            ),
            listHeading: t('patient.inventory.vacationResultsTitle'),
            emptyMessage: t('patient.inventory.vacationEmptyResults'),
            skippedNote:
                props.result.skipped_medication_count > 0
                    ? t('patient.inventory.vacationSkippedNote', {
                          count: String(props.result.skipped_medication_count),
                      })
                    : null,
            footerLabel: appName,
            doseUnitForItem: inventoryVacationDoseUnitForItem,
            doseUnitChipForItem: (amount: string, doseUnit: string | null) =>
                inventoryVacationDoseUnitChipForItem(t, amount, doseUnit),
        });
    });

    const share = useInventoryVacationShareToPhotos({
        shareImagePayload: vacationShareImagePayload,
        startsOn: computed(() => form.starts_on),
        t,
    });

    return {
        form,
        minDateIso,
        showResults,
        pageIntro,
        formattedStartsOn,
        formattedEndsOn,
        expiringPrescriptions,
        vacationSavedPackageHint,
        vacationSavedPackageHintUsesLiquidIcon,
        vacationDaysLabel,
        submit,
        share,
        doseUnitForItem: inventoryVacationDoseUnitForItem,
        result: computed(
            (): PatientInventoryVacationSupplyResult | null => props.result,
        ),
    };
}

export type PatientInventoryVacationPageState = ReturnType<
    typeof usePatientInventoryVacationPage
>;
