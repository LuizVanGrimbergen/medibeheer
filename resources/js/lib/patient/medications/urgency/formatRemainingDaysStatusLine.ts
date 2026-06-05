import {
    decomposeRemainingDaysDuration,
    formatDecomposedRemainingDaysDurationLine
    
} from '@/lib/patient/medications/urgency/remainingDaysDuration';
import type {RemainingDaysDurationUnitKeys} from '@/lib/patient/medications/urgency/remainingDaysDuration';

type RemainingDaysStatusTranslate = (
    key: string,
    values?: Record<string, string>,
) => string;

export type RemainingDaysStatusLineKeys = {
    lessThanDay?: string;
    expired?: string;
    expiresToday?: string;
    oneDay: string;
    days: string;
    durationUnits: RemainingDaysDurationUnitKeys;
    durationStatusLine: string;
};

export type ExpiryRemainingDaysStatusLineKeys = RemainingDaysStatusLineKeys & {
    expired: string;
    expiresToday: string;
};

export type RemainingDaysStatusLineMode = 'supply' | 'expiry';

export function formatRemainingDaysStatusLine(
    t: RemainingDaysStatusTranslate,
    daysRemaining: number,
    keys: RemainingDaysStatusLineKeys,
    mode: 'supply',
): string;
export function formatRemainingDaysStatusLine(
    t: RemainingDaysStatusTranslate,
    daysRemaining: number,
    keys: ExpiryRemainingDaysStatusLineKeys,
    mode: 'expiry',
): string;
export function formatRemainingDaysStatusLine(
    t: RemainingDaysStatusTranslate,
    daysRemaining: number,
    keys: RemainingDaysStatusLineKeys,
    mode: RemainingDaysStatusLineMode,
): string {
    if (mode === 'expiry') {
        const expiryKeys = keys as ExpiryRemainingDaysStatusLineKeys;

        if (daysRemaining < 0) {
            return t(expiryKeys.expired);
        }

        if (daysRemaining < 1) {
            return t(expiryKeys.expiresToday);
        }
    } else if (daysRemaining < 1) {
        if (keys.lessThanDay === undefined) {
            return t(keys.days, { days: String(daysRemaining) });
        }

        return t(keys.lessThanDay);
    }

    if (daysRemaining === 1) {
        return t(keys.oneDay);
    }

    const durationParts = decomposeRemainingDaysDuration(daysRemaining);

    if (durationParts === null) {
        return t(keys.days, { days: String(daysRemaining) });
    }

    return formatDecomposedRemainingDaysDurationLine(
        t,
        durationParts,
        keys.durationUnits,
        keys.durationStatusLine,
    );
}
