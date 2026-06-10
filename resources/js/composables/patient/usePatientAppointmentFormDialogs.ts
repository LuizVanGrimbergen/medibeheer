import { useForm } from '@inertiajs/vue3';
import { computed, nextTick, onMounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { usePatientActionSuccessScreen } from '@/composables/patient/usePatientActionSuccessScreen';
import {
    PATIENT_APPOINTMENT_DOCTOR_TYPE_OPTIONS,
    patientAppointmentFormValuesToRequestPayload,
} from '@/lib/patient/appointments/form-wizard/patientAppointmentDialogFormSchema';
import { patientAppointmentToEditFormState } from '@/lib/patient/appointments/form-wizard/patientAppointmentToEditFormState';
import type { PatientAppointmentsScreenProps } from '@/lib/patient/appointments/screen/patientAppointmentsScreenProps';
import { localCalendarDateIsoToday } from '@/lib/patient/appointments/validation/appointmentStartsAtLocalValidation';
import { mobileShellDialogContentClass } from '@/lib/shell/mobileShellDialogLayout';
import type {
    AppointmentDoctorType,
    AppointmentStatusValue,
    Appointment as PatientAppointment,
} from '@/lib/types';

const appointmentFormDialogLayoutClass = mobileShellDialogContentClass('sm');

export function usePatientAppointmentFormDialogs(
    props: Pick<
        PatientAppointmentsScreenProps,
        'linked_families' | 'open_create_dialog'
    >,
) {
    const { t } = useI18n();
    const createSuccessScreen = usePatientActionSuccessScreen();
    const createDialogOpen = ref(false);

    onMounted(() => {
        if (!props.open_create_dialog) {
            return;
        }

        createDialogOpen.value = true;
    });

    function blankCreateForm(): {
        doctor_type: AppointmentDoctorType | '';
        provider_name: string;
        street: string;
        house_number: string;
        postal_code: string;
        city: string;
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
            street: '',
            house_number: '',
            postal_code: '',
            city: '',
            starts_at_date: '',
            starts_at_time: '',
            notes: '',
            needs_transport: false,
            transport_family_ids: (props.linked_families ?? []).map((f) => f.id),
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
        street: '',
        house_number: '',
        postal_code: '',
        city: '',
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

    const createStartsAtDateMinIso = computed(() =>
        localCalendarDateIsoToday(),
    );

    const editSchedulePermitPastStartsAtIfSameInstantMs = computed(() => {
        const a = appointmentBeingEdited.value;

        if (a === null) {
            return null;
        }

        const parsedMs = Date.parse(a.starts_at);

        if (Number.isNaN(parsedMs)) {
            return null;
        }

        return parsedMs;
    });

    watch(createDialogOpen, (open) => {
        if (!open) {
            return;
        }

        appointmentBeingEdited.value = null;
        editForm.reset();
        editForm.clearErrors();
        resetCreateDialogToFreshDefaults();
        void nextTick(() => {
            const el = document.getElementById(
                'appointment-create-doctor-type',
            );
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
            patientAppointmentToEditFormState(
                appointment,
                props.linked_families ?? [],
            ),
        );
        void nextTick(() => {
            const el = document.getElementById('appointment-edit-doctor-type');
            el?.focus();
        });
    }

    function submitNewAppointment(): void {
        createForm
            .transform((data) =>
                patientAppointmentFormValuesToRequestPayload({ ...data }),
            )
            .post(route('patient.appointments.store'), {
                preserveScroll: true,
                onSuccess: () => {
                    resetCreateDialogToFreshDefaults();
                    createDialogOpen.value = false;
                    createSuccessScreen.show({
                        title: t(
                            'patient.actionSuccess.appointments.created.title',
                        ),
                    });
                },
            });
    }

    function submitAppointmentRevision(): void {
        const appointment = appointmentBeingEdited.value;

        if (appointment === null) {
            return;
        }

        editForm
            .transform((data) =>
                patientAppointmentFormValuesToRequestPayload(
                    { ...data },
                    {
                        permitPastStartsAtIfSameInstantMs:
                            editSchedulePermitPastStartsAtIfSameInstantMs.value ??
                            undefined,
                    },
                ),
            )
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
        createSuccessOpen: createSuccessScreen.open,
        createSuccessTitle: createSuccessScreen.title,
        createStartsAtDateMinIso,
        editSchedulePermitPastStartsAtIfSameInstantMs,
        createDialogOpen,
        createForm,
        editForm,
        editDialogOpen,
        openAppointmentEditor,
        submitNewAppointment,
        submitAppointmentRevision,
    };
}
