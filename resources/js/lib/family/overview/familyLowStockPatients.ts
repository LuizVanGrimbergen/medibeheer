export type FamilyLowStockMedication = {
    id: number;
    name: string;
    supply_estimate_days: number;
};

export type FamilyLowStockPatient = {
    patient_id: number;
    patient_name: string;
    switch_url: string;
    medications_url: string;
    medications: FamilyLowStockMedication[];
};

