import type { PatientInventoryVacationSupplyResult } from '@/lib/patient/inventory/patientInventoryVacationSupply';

export type PatientInventoryVacationPageProps = {
    starts_on: string;
    ends_on: string;
    result: PatientInventoryVacationSupplyResult | null;
};
