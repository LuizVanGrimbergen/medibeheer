import type { MedicationIntakeCalendarDay } from '@/lib/patient/medications/history/medicationIntakeHistoryTypes';
import type { DailyCheckin } from '@/lib/types';

export type DoctorPatientMedicationAttentionItem = {
    kind: 'medication';
    date: string;
    status: 'partial' | 'none_taken';
    missedCount: number;
};

export type DoctorPatientWellbeingAttentionItem = {
    kind: 'wellbeing';
    date: string;
    mood: DailyCheckin['mood_score'];
    symptomCount: number;
};

export type DoctorPatientAttentionItem =
    | DoctorPatientMedicationAttentionItem
    | DoctorPatientWellbeingAttentionItem;

const ATTENTION_ITEM_LIMIT = 5;

function isWellbeingAttentionCheckin(checkin: DailyCheckin): boolean {
    if (checkin.mood_score === 'bad') {
        return true;
    }

    return (checkin.symptoms?.length ?? 0) > 0;
}

function buildMedicationAttentionItems(
    medicationDays: MedicationIntakeCalendarDay[],
): DoctorPatientMedicationAttentionItem[] {
    return medicationDays
        .filter(
            (day): day is MedicationIntakeCalendarDay & {
                status: 'partial' | 'none_taken';
            } =>
                day.status === 'partial' || day.status === 'none_taken',
        )
        .map((day) => ({
            kind: 'medication' as const,
            date: day.date,
            status: day.status,
            missedCount: Math.max(day.scheduled_count - day.taken_count, 0),
        }));
}

function buildWellbeingAttentionItems(
    wellbeingCheckins: DailyCheckin[],
): DoctorPatientWellbeingAttentionItem[] {
    return wellbeingCheckins
        .filter(isWellbeingAttentionCheckin)
        .map((checkin) => ({
            kind: 'wellbeing' as const,
            date: checkin.checkin_date,
            mood: checkin.mood_score,
            symptomCount: checkin.symptoms?.length ?? 0,
        }));
}

export function buildDoctorPatientAttentionItems(
    medicationDays: MedicationIntakeCalendarDay[],
    wellbeingCheckins: DailyCheckin[],
): DoctorPatientAttentionItem[] {
    const items: DoctorPatientAttentionItem[] = [
        ...buildMedicationAttentionItems(medicationDays),
        ...buildWellbeingAttentionItems(wellbeingCheckins),
    ];

    return items
        .sort((left, right) => right.date.localeCompare(left.date))
        .slice(0, ATTENTION_ITEM_LIMIT);
}
