export type DoctorPatientAdherenceTone =
    | 'success'
    | 'warning'
    | 'danger'
    | 'muted';

export function resolveDoctorPatientAdherenceTone(
    completeDays: number,
    scheduledDays: number,
): DoctorPatientAdherenceTone {
    if (scheduledDays === 0) {
        return 'muted';
    }

    const ratio = completeDays / scheduledDays;

    if (ratio >= 0.8) {
        return 'success';
    }

    if (ratio >= 0.5) {
        return 'warning';
    }

    return 'danger';
}

export function doctorPatientAdherenceProgressPercent(
    completeDays: number,
    scheduledDays: number,
): number {
    if (scheduledDays === 0) {
        return 0;
    }

    return Math.round((completeDays / scheduledDays) * 100);
}
