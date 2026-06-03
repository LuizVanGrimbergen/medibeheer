import { router, useForm } from '@inertiajs/vue3';
import { watch } from 'vue';
import type { MedicationCreateFormState } from '@/Components/Patient/Medications/form/MedicationFormTypes';
import type { MedicationPlanProposalFormInitial } from '@/lib/family/medicationPlans/medicationPlanProposalInitialToFormState';
import { medicationPlanProposalInitialToFormState } from '@/lib/family/medicationPlans/medicationPlanProposalInitialToFormState';
import { blankMedicationCreateForm } from '@/lib/patient/medications/create-form/medicationCreateFormDefaults';
import { medicationCreateFormStateToRequestPayload } from '@/lib/patient/medications/create-form/medicationCreateFormToRequestPayload';
import { MEDICATION_SCHEDULE_DEFAULT_SNOOZE_MINUTES } from '@/lib/patient/medications/schedule/medicationScheduleDoseTimes';
import { parseMedicationTimesPerDayCount } from '@/lib/patient/medications/validation/medicationFormValidationPrimitives';

function attachMedicationScheduleTimeSlotWatcher(
    form: ReturnType<typeof useForm<MedicationCreateFormState>>,
): void {
    watch(
        () => form.schedule.times_per_day,
        () => {
            const count = parseMedicationTimesPerDayCount(
                form.schedule.times_per_day,
            );

            if (count === null) {
                return;
            }

            const slots = form.schedule.dose_time_slots;
            const snoozeSlots = form.schedule.snooze_time_slots;

            if (slots.length === count && snoozeSlots.length === count) {
                return;
            }

            if (slots.length < count) {
                form.schedule.dose_time_slots = [
                    ...slots,
                    ...Array.from({ length: count - slots.length }, () => ''),
                ];
            }

            if (slots.length > count) {
                form.schedule.dose_time_slots = slots.slice(0, count);
            }

            const syncedSnoozeSlots = form.schedule.snooze_time_slots;

            if (syncedSnoozeSlots.length < count) {
                form.schedule.snooze_time_slots = [
                    ...syncedSnoozeSlots,
                    ...Array.from(
                        { length: count - syncedSnoozeSlots.length },
                        () =>
                            String(MEDICATION_SCHEDULE_DEFAULT_SNOOZE_MINUTES),
                    ),
                ];

                return;
            }

            if (syncedSnoozeSlots.length > count) {
                form.schedule.snooze_time_slots = syncedSnoozeSlots.slice(
                    0,
                    count,
                );
            }
        },
    );
}

function attachMedicationScheduleDateOrderWatcher(
    form: ReturnType<typeof useForm<MedicationCreateFormState>>,
): void {
    watch(
        () => form.schedule.start_date,
        () => {
            const start = form.schedule.start_date.trim();
            const end = form.schedule.end_date.trim();

            if (start.length < 1 || end.length < 1) {
                return;
            }

            if (end < start) {
                form.schedule.end_date = start;
            }
        },
    );
}

type UseFamilyMedicationPlanProposalFormPageOptions = {
    mode: 'create' | 'edit' | 'addItem';
    proposalId?: number;
    itemId?: number | null;
    initial?: MedicationPlanProposalFormInitial | null;
};

type SubmitOptions = {
    thenAddAnother?: boolean;
};

export function useFamilyMedicationPlanProposalFormPage(
    options: UseFamilyMedicationPlanProposalFormPageOptions,
) {
    const defaults =
        options.mode !== 'create' &&
        options.initial !== null &&
        options.initial !== undefined
            ? medicationPlanProposalInitialToFormState(options.initial)
            : blankMedicationCreateForm();

    const form = useForm(defaults);
    attachMedicationScheduleTimeSlotWatcher(form);
    attachMedicationScheduleDateOrderWatcher(form);

    function submit(submitOptions?: SubmitOptions): void {
        const payload = medicationCreateFormStateToRequestPayload(form.data());

        if (options.mode === 'create') {
            form.transform(() => payload).post(
                route('family.medication-plans.store'),
            );

            return;
        }

        if (options.proposalId === undefined) {
            return;
        }

        if (options.mode === 'addItem') {
            form.transform(() => payload).post(
                route(
                    'family.medication-plans.items.store',
                    options.proposalId,
                ),
                {
                    onSuccess: () => {
                        if (submitOptions?.thenAddAnother) {
                            router.visit(
                                route(
                                    'family.medication-plans.items.create',
                                    options.proposalId,
                                ),
                            );
                        }
                    },
                },
            );

            return;
        }

        const updateUrl = route(
            'family.medication-plans.update',
            options.proposalId,
        );

        form.transform(() => ({
            ...payload,
            item_id: options.itemId ?? null,
        })).put(updateUrl, {
            onSuccess: () => {
                if (!submitOptions?.thenAddAnother) {
                    return;
                }

                router.visit(
                    route(
                        'family.medication-plans.items.create',
                        options.proposalId,
                    ),
                );
            },
        });
    }

    return {
        form,
        submit,
    };
}
