export type PatientInventoryVacationSupplyItem = {
    medication_id: number;
    name: string;
    type_medication: string;
    dose_unit: string | null;
    pickup_quantity: string;
    needed_for_period: string;
    current_stock: string;
    stock_pieces_per_package: number | null;
};

export type PatientInventoryVacationSupplyResult = {
    vacation_days: number;
    items: PatientInventoryVacationSupplyItem[];
    skipped_medication_count: number;
};

export type PatientInventoryVacationSupplyForm = {
    starts_on: string;
    ends_on: string;
};
