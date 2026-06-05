import { describe, expect, it } from 'vitest';
import { formatRemainingDaysStatusLine } from '@/lib/patient/medications/urgency/formatRemainingDaysStatusLine';
import {
    buildRemainingDaysDurationSegments,
    decomposeRemainingDaysDuration,
    formatDecomposedRemainingDaysDurationLine,
    joinRemainingDaysDurationSegments,
    REMAINING_DAYS_PER_YEAR,
} from '@/lib/patient/medications/urgency/remainingDaysDuration';

const durationUnits = {
    year: 'year',
    monthOne: 'monthOne',
    months: 'months',
    dayOne: 'dayOne',
    days: 'days',
} as const;

const supplyKeys = {
    lessThanDay: 'lessThanDay',
    oneDay: 'oneDay',
    days: 'days',
    durationUnits,
    durationStatusLine: 'supplyStatusLine',
} as const;

const expiryKeys = {
    expired: 'expired',
    expiresToday: 'expiresToday',
    oneDay: 'oneDay',
    days: 'days',
    durationUnits,
    durationStatusLine: 'expiryStatusLine',
} as const;

function translate(key: string, values?: Record<string, string>): string {
    if (values === undefined) {
        return key;
    }

    return `${key}:${JSON.stringify(values)}`;
}

describe('decomposeRemainingDaysDuration', () => {
    it('returns null below 31 days', () => {
        expect(decomposeRemainingDaysDuration(30)).toBeNull();
    });

    it('splits months below one year', () => {
        expect(decomposeRemainingDaysDuration(31)).toEqual({
            years: 0,
            months: 1,
            days: 0,
        });
        expect(decomposeRemainingDaysDuration(95)).toEqual({
            years: 0,
            months: 3,
            days: 2,
        });
    });

    it('splits years from twelve months onward', () => {
        expect(REMAINING_DAYS_PER_YEAR).toBe(372);

        expect(decomposeRemainingDaysDuration(372)).toEqual({
            years: 1,
            months: 0,
            days: 0,
        });
        expect(decomposeRemainingDaysDuration(744)).toEqual({
            years: 2,
            months: 0,
            days: 0,
        });
        expect(decomposeRemainingDaysDuration(403)).toEqual({
            years: 1,
            months: 1,
            days: 0,
        });
        expect(decomposeRemainingDaysDuration(405)).toEqual({
            years: 1,
            months: 1,
            days: 2,
        });
        expect(decomposeRemainingDaysDuration(383)).toEqual({
            years: 1,
            months: 0,
            days: 11,
        });
    });
});

describe('joinRemainingDaysDurationSegments', () => {
    it('joins one, two, and three segments naturally in Dutch', () => {
        expect(joinRemainingDaysDurationSegments(['1 jaar'])).toBe('1 jaar');
        expect(joinRemainingDaysDurationSegments(['1 jaar', '2 maanden'])).toBe(
            '1 jaar en 2 maanden',
        );
        expect(
            joinRemainingDaysDurationSegments([
                '1 jaar',
                '2 maanden',
                '5 dagen',
            ]),
        ).toBe('1 jaar, 2 maanden en 5 dagen');
    });
});

describe('formatDecomposedRemainingDaysDurationLine', () => {
    it('composes year-based status lines from parts', () => {
        expect(
            formatDecomposedRemainingDaysDurationLine(
                translate,
                { years: 1, months: 0, days: 0 },
                durationUnits,
                'supplyStatusLine',
            ),
        ).toBe('supplyStatusLine:{"duration":"year:{\\"count\\":\\"1\\"}"}');

        expect(
            buildRemainingDaysDurationSegments(
                translate,
                { years: 2, months: 1, days: 3 },
                durationUnits,
            ),
        ).toEqual(['year:{"count":"2"}', 'monthOne', 'days:{"count":"3"}']);
    });
});

describe('formatRemainingDaysStatusLine', () => {
    it('formats supply lines below 31 days in days', () => {
        expect(
            formatRemainingDaysStatusLine(translate, 30, supplyKeys, 'supply'),
        ).toBe('days:{"days":"30"}');
    });

    it('formats supply lines from 31 days with composed duration', () => {
        expect(
            formatRemainingDaysStatusLine(translate, 31, supplyKeys, 'supply'),
        ).toBe('supplyStatusLine:{"duration":"monthOne"}');
        expect(
            formatRemainingDaysStatusLine(translate, 372, supplyKeys, 'supply'),
        ).toBe('supplyStatusLine:{"duration":"year:{\\"count\\":\\"1\\"}"}');
        expect(
            formatRemainingDaysStatusLine(translate, 405, supplyKeys, 'supply'),
        ).toBe(
            'supplyStatusLine:{"duration":"year:{\\"count\\":\\"1\\"}, monthOne en days:{\\"count\\":\\"2\\"}"}',
        );
    });

    it('formats expiry edge cases before duration conversion', () => {
        expect(
            formatRemainingDaysStatusLine(translate, -1, expiryKeys, 'expiry'),
        ).toBe('expired');
        expect(
            formatRemainingDaysStatusLine(translate, 0, expiryKeys, 'expiry'),
        ).toBe('expiresToday');
        expect(
            formatRemainingDaysStatusLine(translate, 1, expiryKeys, 'expiry'),
        ).toBe('oneDay');
    });
});
