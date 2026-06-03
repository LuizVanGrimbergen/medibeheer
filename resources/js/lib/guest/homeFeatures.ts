import homeMessages from '@/translations/nl/home';

export const homeFeatureKeys = [
    'medication',
    'prescriptions',
    'intakes',
    'inventory',
    'appointments',
    'medicationPlans',
    'family',
    'checkins',
    'roles',
] as const;

export type HomeFeatureKey = (typeof homeFeatureKeys)[number];

export function homeFeaturePoints(key: HomeFeatureKey): string[] {
    return [...homeMessages.features[key].points];
}
