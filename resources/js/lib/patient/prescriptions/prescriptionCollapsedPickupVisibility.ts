export function prescriptionShowsCollapsedPickupAction(
    isDetailsOpen: boolean,
): boolean {
    return !isDetailsOpen;
}

export function prescriptionShowsExpandedPickupControl(
    isDetailsOpen: boolean,
): boolean {
    return isDetailsOpen;
}
