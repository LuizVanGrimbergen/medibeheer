import { formatInventoryVacationDateLabel } from '@/lib/patient/inventory/formatInventoryVacationDateLabel';
import type { InventoryVacationShareImagePayload } from '@/lib/patient/inventory/inventoryVacationShareImageTypes';
import type { PatientInventoryVacationExpiringPrescription } from '@/lib/patient/inventory/patientInventoryVacationExpiringPrescription';
import type { PatientInventoryVacationSupplyResult } from '@/lib/patient/inventory/patientInventoryVacationSupply';
import { medicationStockPackageCountForQuantity } from '@/lib/patient/medications/stock/medicationStockPackageCountForQuantity';
import { parseMedicationStockNumericValue } from '@/lib/patient/medications/stock/parseMedicationStockNumericValue';

type BuildInventoryVacationShareImagePayloadInput = {
    result: PatientInventoryVacationSupplyResult;
    startsOn: string;
    endsOn: string;
    vacationDaysLabel: string;
    title: string;
    periodHeading: string;
    departureLabel: string;
    returnLabel: string;
    savedPackageHint: string | null;
    expiringPrescriptions: PatientInventoryVacationExpiringPrescription[];
    totalLabel: string;
    minimumBoxesLabel: string;
    liquidMinimumBoxesLabel: string;
    piecesPerBoxLabel: string;
    liquidPiecesPerBoxLabel: string;
    listHeading: string;
    emptyMessage: string;
    skippedNote: string | null;
    footerLabel: string;
    doseUnitForItem: (doseUnit: string | null) => string | null;
    doseUnitChipForItem: (
        amount: string,
        doseUnit: string | null,
    ) => string | null;
};

export function buildInventoryVacationShareImagePayload(
    input: BuildInventoryVacationShareImagePayloadInput,
): InventoryVacationShareImagePayload {
    return {
        title: input.title,
        periodHeading: input.periodHeading,
        daysLabel: input.vacationDaysLabel,
        departureLabel: input.departureLabel,
        departureDate: formatInventoryVacationDateLabel(input.startsOn),
        returnLabel: input.returnLabel,
        returnDate: formatInventoryVacationDateLabel(input.endsOn),
        savedPackageHint: input.savedPackageHint,
        expiringPrescriptions: input.expiringPrescriptions,
        totalLabel: input.totalLabel,
        listHeading: input.listHeading,
        items: input.result.items.map((item) => {
            const doseUnit = input.doseUnitForItem(item.dose_unit);
            const pickupNumeric = parseMedicationStockNumericValue(
                item.pickup_quantity,
                doseUnit,
            );
            const isLiquid = item.type_medication === 'liquid';
            const hasSavedPackage =
                item.stock_pieces_per_package !== null &&
                item.stock_pieces_per_package > 0;

            let primaryValue: string | null = null;
            let secondaryValue: string | null = null;

            if (
                hasSavedPackage &&
                pickupNumeric !== null &&
                pickupNumeric > 0 &&
                item.stock_pieces_per_package !== null
            ) {
                primaryValue = String(
                    medicationStockPackageCountForQuantity(
                        pickupNumeric,
                        item.stock_pieces_per_package,
                    ),
                );
                secondaryValue = String(item.stock_pieces_per_package);
            }

            const totalNumeric =
                pickupNumeric !== null && pickupNumeric > 0
                    ? String(pickupNumeric)
                    : null;

            let totalUnitChip: string | null = null;

            if (totalNumeric !== null) {
                totalUnitChip = input.doseUnitChipForItem(
                    totalNumeric,
                    item.dose_unit,
                );
            }

            return {
                medicationId: item.medication_id,
                name: item.name,
                primaryLabel: isLiquid
                    ? input.liquidMinimumBoxesLabel
                    : input.minimumBoxesLabel,
                primaryValue,
                secondaryLabel: isLiquid
                    ? input.liquidPiecesPerBoxLabel
                    : input.piecesPerBoxLabel,
                secondaryValue,
                totalLabel: input.totalLabel,
                totalNumeric,
                totalUnitChip,
            };
        }),
        emptyMessage:
            input.result.items.length === 0 ? input.emptyMessage : null,
        skippedNote: input.skippedNote,
        footerLabel: input.footerLabel,
        pageLabel: null,
    };
}
