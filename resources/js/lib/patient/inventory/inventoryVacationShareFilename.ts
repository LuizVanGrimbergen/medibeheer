export function inventoryVacationShareFilename(startsOn: string): string {
    const stem = startsOn.trim() !== '' ? startsOn : 'vakantie';

    return `medibeheer-vakantie-${stem}.png`;
}
