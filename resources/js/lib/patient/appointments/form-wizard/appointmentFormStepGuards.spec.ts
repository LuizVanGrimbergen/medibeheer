import { describe, expect, it } from 'vitest';
import { collectAppointmentFormStepValidationFieldErrors } from '@/lib/patient/appointments/form-wizard/appointmentFormStepGuards';

const emptySnapshot = {
    doctor_type: '',
    provider_name: '',
    street: '',
    postal_code: '',
    city: '',
    starts_at_date: '',
    starts_at_time: '',
    needs_transport: false,
    transport_family_ids: [] as number[],
};

describe('collectAppointmentFormStepValidationFieldErrors schedule step', () => {
    it('requires date and time before advancing', () => {
        expect(
            collectAppointmentFormStepValidationFieldErrors(
                'schedule',
                emptySnapshot,
            ),
        ).toEqual({
            starts_at_date: 'Kies een dag voor de afspraak.',
            starts_at_time: 'Kies een uur voor de afspraak.',
        });
    });

    it('requires only the missing schedule field', () => {
        expect(
            collectAppointmentFormStepValidationFieldErrors('schedule', {
                ...emptySnapshot,
                starts_at_date: '2026-12-01',
            }),
        ).toEqual({
            starts_at_time: 'Kies een uur voor de afspraak.',
        });
    });
});
