import { router, useForm } from '@inertiajs/vue3';
import { nextTick, onMounted, ref, watch } from 'vue';
import type { MedicationCreateFormState } from '@/Components/Patient/Medications/form/MedicationFormTypes';
import { blankMedicationCreateForm } from '@/lib/patient/medications/create-form/medicationCreateFormDefaults';
import { medicationCreateFormStateToRequestPayload } from '@/lib/patient/medications/create-form/medicationCreateFormToRequestPayload';
import { medicationListItemToCreateFormState } from '@/lib/patient/medications/create-form/medicationListItemToCreateFormState';
import type { PatientMedicationsScreenProps } from '@/lib/patient/medications/screen/patientMedicationsScreenProps';
import { MEDICATION_SCHEDULE_DEFAULT_SNOOZE_MINUTES } from '@/lib/patient/medications/schedule/medicationScheduleDoseTimes';
import { parseMedicationTimesPerDayCount } from '@/lib/patient/medications/validation/medicationFormValidationPrimitives';
import {
    patientShellDialogContentClass,
} from '@/lib/patient/patientShellDialogLayout';
import type { MedicationListItem } from '@/lib/types';

const medicationFormDialogLayoutClass = patientShellDialogContentClass('md');

function attachMedicationScheduleTimeSlotWatcher(
    form: ReturnType<typeof useForm<MedicationCreateFormState>>,
): void {
    watch(
        () => form.schedule.times_per_day,
        () => {
            const count = parseMedicationTimesPerDayCount(form.schedule.times_per_day);

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
                    ...Array.from({ length: count - syncedSnoozeSlots.length }, () =>
                        String(MEDICATION_SCHEDULE_DEFAULT_SNOOZE_MINUTES),
                    ),
                ];

                return;
            }

            if (syncedSnoozeSlots.length > count) {
                form.schedule.snooze_time_slots = syncedSnoozeSlots.slice(0, count);
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

export function usePatientMedicationsPage(props: PatientMedicationsScreenProps) {
    const createDialogOpen = ref(false);
    const editDialogOpen = ref(false);
    const editMedicationId = ref<number | null>(null);
    const deleteDialogOpen = ref(false);
    const deleteMedication = ref<MedicationListItem | null>(null);
    const deleteProcessing = ref(false);
    function resetCreateDialogToFreshDefaults(): void {
        createForm.defaults(blankMedicationCreateForm());
        createForm.reset();
        createForm.clearErrors();
    }

    const createForm = useForm(blankMedicationCreateForm());
    attachMedicationScheduleTimeSlotWatcher(createForm);
    attachMedicationScheduleDateOrderWatcher(createForm);

    const editForm = useForm(blankMedicationCreateForm());
    attachMedicationScheduleTimeSlotWatcher(editForm);
    attachMedicationScheduleDateOrderWatcher(editForm);

    watch(createDialogOpen, (open) => {
        if (!open) {
            return;
        }

        resetCreateDialogToFreshDefaults();
        void nextTick(() => {
            document.getElementById('patient-medication-create-name')?.focus();
        });
    });

    watch(editDialogOpen, (open) => {
        if (!open) {
            editMedicationId.value = null;

            return;
        }

        void nextTick(() => {
            document.getElementById('patient-medication-edit-create-summary-title')?.focus();
        });
    });

    onMounted(() => {
        if (!props.can_create_medication) {
            return;
        }

        const params = new URLSearchParams(globalThis.location.search);

        if (params.get('create') !== '1') {
            return;
        }

        createDialogOpen.value = true;

        params.delete('create');
        const query = params.toString();
        const nextUrl = query.length > 0
            ? `${globalThis.location.pathname}?${query}`
            : globalThis.location.pathname;

        globalThis.history.replaceState({}, '', nextUrl);
    });

    function openEditMedication(medication: MedicationListItem): void {
        editMedicationId.value = medication.id;
        editForm.defaults(medicationListItemToCreateFormState(medication));
        editForm.reset();
        editForm.clearErrors();
        editDialogOpen.value = true;
    }

    function closeEditMedicationDialog(): void {
        editDialogOpen.value = false;
    }

    function submitNewMedication(): void {
        createForm
            .transform((data) => medicationCreateFormStateToRequestPayload(data))
            .post(route('patient.medications.store'), {
                preserveScroll: true,
                onSuccess: () => {
                    router.flushAll();
                    resetCreateDialogToFreshDefaults();
                    createDialogOpen.value = false;
                },
            });
    }

    function submitEditMedication(): void {
        if (editMedicationId.value === null) {
            return;
        }

        const id = editMedicationId.value;

        editForm
            .transform((data) => medicationCreateFormStateToRequestPayload(data))
            .put(route('patient.medications.update', id), {
                preserveScroll: true,
                onSuccess: () => {
                    router.flushAll();
                    closeEditMedicationDialog();
                },
            });
    }

    function openDeleteMedicationDialog(medication: MedicationListItem): void {
        deleteMedication.value = medication;
        deleteDialogOpen.value = true;
    }

    function closeDeleteMedicationDialog(): void {
        deleteDialogOpen.value = false;
        deleteMedication.value = null;
    }

    function confirmDeleteMedication(): void {
        if (deleteMedication.value === null) {
            return;
        }

        const medicationId = deleteMedication.value.id;

        deleteProcessing.value = true;

        router.delete(route('patient.medications.destroy', medicationId), {
            preserveScroll: true,
            onSuccess: () => {
                router.flushAll();
                closeDeleteMedicationDialog();
            },
            onFinish: () => {
                deleteProcessing.value = false;
            },
        });
    }

    return {
        medicationFormDialogLayoutClass,
        createDialogOpen,
        createForm,
        resetCreateDialogToFreshDefaults,
        submitNewMedication,
        canCreateMedication: props.can_create_medication,
        editDialogOpen,
        editForm,
        openEditMedication,
        closeEditMedicationDialog,
        submitEditMedication,
        openDeleteMedicationDialog,
        closeDeleteMedicationDialog,
        confirmDeleteMedication,
        deleteDialogOpen,
        deleteMedication,
        deleteProcessing,
    };
}
