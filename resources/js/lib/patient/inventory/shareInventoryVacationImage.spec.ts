import { describe, expect, it } from 'vitest';
import { buildInventoryVacationShareFiles } from '@/lib/patient/inventory/shareInventoryVacationImage';

describe('buildInventoryVacationShareFiles', () => {
    it('creates JPEG files with a .jpg extension for iOS Photos sharing', () => {
        const files = buildInventoryVacationShareFiles(
            [new Blob(['a'], { type: 'image/jpeg' })],
            'medibeheer-vakantie-2026-07-01',
        );

        expect(files).toHaveLength(1);
        expect(files[0]?.name).toBe('medibeheer-vakantie-2026-07-01.jpg');
        expect(files[0]?.type).toBe('image/jpeg');
    });

    it('numbers multi-page vacation exports as JPEG files', () => {
        const files = buildInventoryVacationShareFiles(
            [
                new Blob(['a'], { type: 'image/jpeg' }),
                new Blob(['b'], { type: 'image/jpeg' }),
            ],
            'medibeheer-vakantie-2026-07-01.jpg',
        );

        expect(files.map((file) => file.name)).toEqual([
            'medibeheer-vakantie-2026-07-01-1.jpg',
            'medibeheer-vakantie-2026-07-01-2.jpg',
        ]);
    });
});
