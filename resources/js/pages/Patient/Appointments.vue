<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { CalendarPlus } from 'lucide-vue-next';
import { computed, nextTick, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import AppointmentCard from '@/Components/Patient/Appointments/AppointmentCard.vue';
import AppointmentFormDialog from '@/Components/Patient/Appointments/AppointmentFormDialog.vue';
import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import PatientLayout from '@/Layouts/PatientLayout.vue';
import type {
    Appointment,
    AppointmentCancelledCommitPayload,
    AppointmentDoctorType,
    AppointmentDoneCommitPayload,
    AppointmentStatusValue,
} from '@/lib/types';

const props = defineProps<{
    appointments: Appointment[];
}>();

const { t } = useI18n();

const createDialogOpen = ref(false);
const patchingAppointmentIds = ref<number[]>([]);
const pendingDoneById = ref<Partial<Record<number, boolean>>>({});

const doctorTypeValues: AppointmentDoctorType[] = [
    'dentist',
    'hospital',
    'general_practitioner',
    'specialist',
];

function createAppointmentFormDefaults(): {
    doctor_type: AppointmentDoctorType | '';
    provider_name: string;
    address: string;
    starts_at_date: string;
    starts_at_time: string;
    notes: string;
    status: AppointmentStatusValue;
} {
    return {
        doctor_type: '',
        provider_name: '',
        address: '',
        starts_at_date: '',
        starts_at_time: '',
        notes: '',
        status: 'scheduled',
    };
}

function resetCreateFormToDefaults(): void {
    form.defaults(createAppointmentFormDefaults());
    form.reset();
    form.clearErrors();
}

const form = useForm(createAppointmentFormDefaults());

const editForm = useForm({
    doctor_type: '' as AppointmentDoctorType | '',
    provider_name: '',
    address: '',
    starts_at_date: '',
    starts_at_time: '',
    notes: '',
    status: 'scheduled' as AppointmentStatusValue,
});

const editingAppointment = ref<Appointment | null>(null);

const editDialogOpen = computed({
    get: () => editingAppointment.value !== null,
    set: (open: boolean) => {
        if (open) {
            return;
        }

        editingAppointment.value = null;
        editForm.reset();
        editForm.clearErrors();
    },
});

const dialogContentClass =
    'flex min-h-0 w-[min(36rem,calc(100vw-2rem))] max-w-none flex-col gap-5 overflow-hidden overscroll-contain rounded-2xl border-2 border-border bg-surface p-5 text-text shadow-lg touch-manipulation max-h-[min(92dvh,calc(100dvh-env(safe-area-inset-bottom)-env(safe-area-inset-top)))] sm:gap-6 sm:p-6 sm:max-h-[min(90vh,44rem)]';

watch(createDialogOpen, (open) => {
    if (!open) {
        return;
    }

    editingAppointment.value = null;
    editForm.reset();
    editForm.clearErrors();
    resetCreateFormToDefaults();
    void nextTick(() => {
        const el = document.getElementById('appointment-create-provider-name');
        el?.focus();
    });
});

function toDatetimeLocalValue(iso: string): string {
    const d = new Date(iso);

    if (Number.isNaN(d.getTime())) {
        return '';
    }

    const pad = (n: number) => String(n).padStart(2, '0');

    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
}

function openEditDialog(appointment: Appointment): void {
    if (appointment.status === 'cancelled') {
        return;
    }

    createDialogOpen.value = false;
    editingAppointment.value = appointment;
    editForm.clearErrors();
    editForm.doctor_type = appointment.doctor_type;
    editForm.provider_name = appointment.provider_name;
    editForm.address = appointment.address;
    const startsLocal = toDatetimeLocalValue(appointment.starts_at);
    const [startsDate, startsTimeRaw] = startsLocal.split('T');
    editForm.starts_at_date = startsDate ?? '';
    editForm.starts_at_time = startsTimeRaw
        ? startsTimeRaw.slice(0, 5)
        : '';
    editForm.notes = appointment.notes ?? '';
    editForm.status = appointment.status;
    void nextTick(() => {
        const el = document.getElementById('appointment-edit-provider-name');
        el?.focus();
    });
}

const plannedAppointments = computed(() =>
    [...props.appointments]
        .filter((a) => a.status === 'scheduled')
        .sort(
            (a, b) =>
                new Date(a.starts_at).getTime() - new Date(b.starts_at).getTime(),
        ),
);

const hasNoAppointmentsAtAll = computed(
    () => props.appointments.length === 0,
);

function submitCreate(): void {
    form.transform((data) => {
        const {
            starts_at_date: startsAtDate,
            starts_at_time: startsAtTime,
            notes,
            ...rest
        } = data;

        return {
            ...rest,
            starts_at:
                startsAtDate && startsAtTime
                    ? `${startsAtDate}T${startsAtTime}`
                    : '',
            notes: notes === '' ? null : notes,
        };
    }).post(route('patient.appointments.store'), {
        preserveScroll: true,
        onSuccess: () => {
            resetCreateFormToDefaults();
            createDialogOpen.value = false;
        },
    });
}

function submitEdit(): void {
    const appointment = editingAppointment.value;

    if (appointment === null) {
        return;
    }

    editForm
        .transform((data) => {
            const {
                starts_at_date: startsAtDate,
                starts_at_time: startsAtTime,
                notes,
                ...rest
            } = data;

            return {
                ...rest,
                starts_at:
                    startsAtDate && startsAtTime
                        ? `${startsAtDate}T${startsAtTime}`
                        : '',
                notes: notes === '' ? null : notes,
            };
        })
        .patch(route('patient.appointments.update', appointment.id), {
            preserveScroll: true,
            onSuccess: () => {
                editingAppointment.value = null;
                editForm.reset();
            },
        });
}

function confirmDelete(appointment: Appointment): void {
    if (!confirm(t('patient.appointments.deleteConfirm'))) {
        return;
    }

    router.delete(route('patient.appointments.destroy', appointment.id), {
        preserveScroll: true,
    });
}

function isAppointmentPatching(appointmentId: number): boolean {
    return patchingAppointmentIds.value.includes(appointmentId);
}

function doneDisplayed(appointment: Appointment): boolean {
    const pending = pendingDoneById.value[appointment.id];

    if (pending !== undefined) {
        return pending;
    }

    return appointment.status === 'done';
}

function commitAppointmentCancelled(
    appointment: Appointment,
    payload: AppointmentCancelledCommitPayload,
): void {
    if (isAppointmentPatching(appointment.id)) {
        return;
    }

    router.patch(
        route('patient.appointments.update', appointment.id),
        {
            status: 'cancelled' as AppointmentStatusValue,
            cancellation_reason: payload.cancellation_reason,
        },
        {
            preserveScroll: true,
            onStart: () => {
                patchingAppointmentIds.value = [
                    ...patchingAppointmentIds.value,
                    appointment.id,
                ];
            },
            onFinish: () => {
                patchingAppointmentIds.value =
                    patchingAppointmentIds.value.filter(
                        (id) => id !== appointment.id,
                    );
            },
        },
    );
}

function commitAppointmentDone(
    appointment: Appointment,
    payload: AppointmentDoneCommitPayload,
): void {
    if (isAppointmentPatching(appointment.id)) {
        return;
    }

    if (doneDisplayed(appointment)) {
        return;
    }

    pendingDoneById.value = {
        ...pendingDoneById.value,
        [appointment.id]: true,
    };

    router.patch(
        route('patient.appointments.update', appointment.id),
        {
            status: 'done' as AppointmentStatusValue,
            doctor_visit_summary: payload.doctor_visit_summary,
        },
        {
            preserveScroll: true,
            onStart: () => {
                patchingAppointmentIds.value = [
                    ...patchingAppointmentIds.value,
                    appointment.id,
                ];
            },
            onFinish: () => {
                patchingAppointmentIds.value =
                    patchingAppointmentIds.value.filter(
                        (id) => id !== appointment.id,
                    );
                const nextPending = { ...pendingDoneById.value };
                delete nextPending[appointment.id];
                pendingDoneById.value = nextPending;
            },
        },
    );
}

function revertAppointmentDone(appointment: Appointment): void {
    if (isAppointmentPatching(appointment.id)) {
        return;
    }

    if (!doneDisplayed(appointment)) {
        return;
    }

    pendingDoneById.value = {
        ...pendingDoneById.value,
        [appointment.id]: false,
    };

    router.patch(
        route('patient.appointments.update', appointment.id),
        {
            status: 'scheduled' as AppointmentStatusValue,
            doctor_visit_summary: null,
        },
        {
            preserveScroll: true,
            onStart: () => {
                patchingAppointmentIds.value = [
                    ...patchingAppointmentIds.value,
                    appointment.id,
                ];
            },
            onFinish: () => {
                patchingAppointmentIds.value =
                    patchingAppointmentIds.value.filter(
                        (id) => id !== appointment.id,
                    );
                const nextPending = { ...pendingDoneById.value };
                delete nextPending[appointment.id];
                pendingDoneById.value = nextPending;
            },
        },
    );
}
</script>

<template>
    <Head>
        <title>{{ t('patient.appointments.title') }}</title>
    </Head>

    <PatientLayout>
        <div class="flex flex-col gap-10">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between sm:gap-6">
                <div class="min-w-0">
                    <h1 class="text-3xl font-bold leading-tight text-text-heading">
                        {{ t('patient.appointments.heading') }}
                    </h1>
                    <p class="mt-3 max-w-2xl text-base leading-relaxed text-text-muted">
                        {{ t('patient.appointments.plannedDescription') }}
                    </p>
                </div>

                <Button
                    size="lg"
                    class="min-h-14 w-full touch-manipulation gap-2.5 self-stretch px-6 text-lg sm:w-auto sm:self-center sm:px-8"
                    @click="createDialogOpen = true"
                >
                    <CalendarPlus
                        class="size-6 shrink-0"
                        aria-hidden="true"
                    />
                    {{ t('patient.appointments.newAppointment') }}
                </Button>
            </div>

            <section class="space-y-5">
                <h2 class="text-2xl font-bold leading-tight text-text-heading">
                    {{ t('patient.appointments.plannedHeading') }}
                </h2>

                <ul
                    v-if="plannedAppointments.length > 0"
                    class="flex flex-col gap-5"
                >
                    <li
                        v-for="appointment in plannedAppointments"
                        :key="appointment.id"
                    >
                        <AppointmentCard
                            :appointment="appointment"
                            :done-displayed="doneDisplayed(appointment)"
                            :is-patching="isAppointmentPatching(appointment.id)"
                            @edit="openEditDialog(appointment)"
                            @delete="confirmDelete(appointment)"
                            @update:done="
                                (on) => {
                                    if (!on) {
                                        revertAppointmentDone(appointment);
                                    }
                                }
                            "
                            @commit-done="
                                (payload) =>
                                    commitAppointmentDone(appointment, payload)
                            "
                            @commit-cancelled="
                                (payload) =>
                                    commitAppointmentCancelled(
                                        appointment,
                                        payload,
                                    )
                            "
                        />
                    </li>
                </ul>

                <Card
                    v-else
                    class="rounded-2xl border-2 border-dashed border-border bg-surface-2/70 text-text shadow-none"
                >
                    <CardContent class="px-5 py-14 text-center text-lg leading-relaxed text-text-muted sm:px-8">
                        {{
                            hasNoAppointmentsAtAll
                                ? t('patient.appointments.empty')
                                : t('patient.appointments.emptyPlanned')
                        }}
                    </CardContent>
                </Card>
            </section>
        </div>

        <AppointmentFormDialog
            :open="createDialogOpen"
            :title="t('patient.appointments.dialogTitle')"
            :description="t('patient.appointments.dialogDescription')"
            form-id="appointment-create-form"
            id-prefix="appointment-create"
            :doctor-type-values="doctorTypeValues"
            :show-doctor-type-placeholder="true"
            :form="form"
            :dialog-content-class="dialogContentClass"
            @update:open="(open) => (createDialogOpen = open)"
            @submit="submitCreate"
            @cancel="createDialogOpen = false"
        />

        <AppointmentFormDialog
            :open="editDialogOpen"
            :title="t('patient.appointments.dialogEditTitle')"
            :description="t('patient.appointments.dialogEditDescription')"
            form-id="appointment-edit-form"
            id-prefix="appointment-edit"
            :doctor-type-values="doctorTypeValues"
            :show-doctor-type-placeholder="false"
            :form="editForm"
            :dialog-content-class="dialogContentClass"
            @update:open="(open) => (editDialogOpen = open)"
            @submit="submitEdit"
            @cancel="editDialogOpen = false"
        />
    </PatientLayout>
</template>
