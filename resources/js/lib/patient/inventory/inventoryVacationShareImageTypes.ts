export type InventoryVacationShareImageItem = {
    name: string;
    primaryLabel: string;
    primaryValue: string | null;
    secondaryLabel: string;
    secondaryValue: string | null;
    totalLabel: string;
    totalNumeric: string | null;
    totalUnitChip: string | null;
};

export type InventoryVacationShareImagePayload = {
    title: string;
    periodHeading: string;
    daysLabel: string;
    departureLabel: string;
    departureDate: string;
    returnLabel: string;
    returnDate: string;
    savedPackageHint: string | null;
    totalLabel: string;
    listHeading: string;
    items: InventoryVacationShareImageItem[];
    emptyMessage: string | null;
    skippedNote: string | null;
    footerLabel: string;
    pageLabel: string | null;
};
