import { useForm } from '@inertiajs/vue3';
import { computed, nextTick, ref, watch } from 'vue';
import {
    PATIENT_APPOINTMENT_DOCTOR_TYPE_OPTIONS,
    patientAppointmentDialogFormIsSubmittable,
    patientAppointmentFormValuesToRequestPayload,
} from '@/lib/patient/appointments/patientAppointmentDialogFormSchema';
import type { PatientAppointmentsScreenProps } from '@/lib/patient/appointments/patientAppointmentsScreenProps';
import { patientAppointmentToEditFormState } from '@/lib/patient/appointments/patientAppointmentToEditFormState';
import type {
    Appointment as PatientAppointment,
    AppointmentDoctorType,
    AppointmentStatusValue,
} from '@/lib/types';

const appointmentFormDialogLayoutClass =
    'flex min-h-0 w-[min(36rem,calc(100vw-2rem))] max-w-none flex-col gap-5 overflow-hidden overscroll-contain rounded-2xl border-2 border-border bg-surface p-5 text-text shadow-lg touch-manipulation max-h-[min(92dvh,calc(100dvh-env(safe-area-inset-bottom)-env(safe-area-inset-top)))] sm:gap-6 sm:p-6 sm:max-h-[min(90vh,44rem)]';

export function usePatientAppointmentFormDialogs(
    props: Pick<PatientAppointmentsScreenProps, 'linked_families'>,
) {
    const createDialogOpen = ref(false);

    function blankCreateForm(): {
        doctor_type: AppointmentDoctorType | '';
        provider_name: string;
        address: string;
        starts_at_date: string;
        starts_at_time: string;
        notes: string;
        needs_transport: boolean;
        transport_family_ids: number[];
        status: AppointmentStatusValue;
    } {
        return {
            doctor_type: '',
            provider_name: '',
            address: '',
            starts_at_date: '',
            starts_at_time: '',
            notes: '',
            needs_transport: false,
            transport_family_ids: props.linked_families.map((f) => f.id),
            status: 'scheduled',
        };
    }

    function resetCreateDialogToFreshDefaults(): void {
        createForm.defaults(blankCreateForm());
        createForm.reset();
        createForm.clearErrors();
    }

    const createForm = useForm(blankCreateForm());

    const editForm = useForm({
        doctor_type: '' as AppointmentDoctorType | '',
        provider_name: '',
        address: '',
        starts_at_date: '',
        starts_at_time: '',
        notes: '',
        needs_transport: false,
        transport_family_ids: [] as number[],
        status: 'scheduled' as AppointmentStatusValue,
    });

    const appointmentBeingEdited = ref<PatientAppointment | null>(null);

    const editDialogOpen = computed({
        get: () => appointmentBeingEdited.value !== null,
        set: (open: boolean) => {
            if (open) {
                return;
            }

            appointmentBeingEdited.value = null;
            editForm.reset();
            editForm.clearErrors();
        },
    });

    const createSubmitDisabled = computed(
        () =>
            !patientAppointmentDialogFormIsSubmittable({
                doctor_type: createForm.doctor_type,
                provider_name: createForm.provider_name,
                address: createForm.address,
                starts_at_date: createForm.starts_at_date,
                starts_at_time: createForm.starts_at_time,
                notes: createForm.notes,
                needs_transport: createForm.needs_transport,
                transport_family_ids: createForm.transport_family_ids,
                status: createForm.status,
            }),
    );

    const editSubmitDisabled = computed(
        () =>
            !patientAppointmentDialogFormIsSubmittable({
                doctor_type: editForm.doctor_type,
                provider_name: editForm.provider_name,
                address: editForm.address,
                starts_at_date: editForm.starts_at_date,
                starts_at_time: editForm.starts_at_time,
                notes: editForm.notes,
                needs_transport: editForm.needs_transport,
                transport_family_ids: editForm.transport_family_ids,
                status: editForm.status,
            }),
    );

    watch(createDialogOpen, (open) => {
        if (!open) {
            return;
        }

        appointmentBeingEdited.value = null;
        editForm.reset();
        editForm.clearErrors();
        resetCreateDialogToFreshDefaults();
        void nextTick(() => {
            const el = document.getElementById('appointment-create-provider-name');
            el?.focus();
        });
    });

    function openAppointmentEditor(appointment: PatientAppointment): void {
        if (appointment.status === 'cancelled') {
            return;
        }

        createDialogOpen.value = false;
        appointmentBeingEdited.value = appointment;
        editForm.clearErrors();
        Object.assign(
            editForm,
            patientAppointmentToEditFormState(appointment, props.linked_families),
        );
        void nextTick(() => {
            const el = document.getElementById('appointment-edit-provider-name');
            el?.focus();
        });
    }

    function submitNewAppointment(): void {
        createForm
            .transform((data) => patientAppointmentFormValuesToRequestPayload({ ...data }))
            .post(route('patient.appointments.store'), {
                preserveScroll: true,
                onSuccess: () => {
                    resetCreateDialogToFreshDefaults();
                    createDialogOpen.value = false;
                },
            });
    }

    function submitAppointmentRevision(): void {
        const appointment = appointmentBeingEdited.value;

        if (appointment === null) {
            return;
        }

        editForm
            .transform((data) => patientAppointmentFormValuesToRequestPayload({ ...data }))
            .patch(route('patient.appointments.update', appointment.id), {
                preserveScroll: true,
                onSuccess: () => {
                    appointmentBeingEdited.value = null;
                    editForm.reset();
                },
            });
    }

    return {
        doctorTypeOptions: PATIENT_APPOINTMENT_DOCTOR_TYPE_OPTIONS,
        appointmentFormDialogLayoutClass,
        createDialogOpen,
        createForm,
        editForm,
        editDialogOpen,
        createSubmitDisabled,
        editSubmitDisabled,
        openAppointmentEditor,
        submitNewAppointment,
        submitAppointmentRevision,
    };
}
