export function medicationStockPackageCountForQuantity(
    quantity: number,
    piecesPerPackage: number,
): number {
    if (quantity <= 0 || piecesPerPackage <= 0) {
        return 0;
    }

    return Math.ceil(quantity / piecesPerPackage);
}
