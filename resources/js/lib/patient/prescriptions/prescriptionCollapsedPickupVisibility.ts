/** Primary pickup CTA stays above the details toggle (collapsed and expanded). */
export function prescriptionShowsPrimaryPickupAction(): boolean {
    return true;
}

/** Pickup is not duplicated inside expanded details. */
export function prescriptionShowsExpandedPickupControl(
    _isDetailsOpen?: boolean,
): boolean {
    return false;
}

/** @deprecated Use {@link prescriptionShowsPrimaryPickupAction}. */
export function prescriptionShowsCollapsedPickupAction(
    _isDetailsOpen?: boolean,
): boolean {
    return prescriptionShowsPrimaryPickupAction();
}
