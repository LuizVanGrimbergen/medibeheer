<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { CalendarPlus, Trash2 } from 'lucide-vue-next';
import { computed, nextTick, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import AppointmentCard from '@/Components/shared/appointments/AppointmentCard.vue';
import AppointmentFormDialog from '@/Components/Patient/Appointments/form/AppointmentFormDialog.vue';
import PatientActionSuccessScreen from '@/Components/Patient/PatientActionSuccessScreen.vue';
import PatientConfirmDialog from '@/Components/Patient/PatientConfirmDialog.vue';
import PatientDeferredListSection from '@/Components/Patient/PatientDeferredListSection.vue';
import PatientPageIntroActionBar from '@/Components/Patient/PatientPageIntroActionBar.vue';
import PatientPageShell from '@/Components/Patient/PatientPageShell.vue';
import { Button } from '@/Components/ui/button';
import NumberedPagination from '@/Components/ui/pagination/NumberedPagination.vue';
import { usePatientAppointmentsPage } from '@/composables/patient/usePatientAppointmentsPage';
import PatientLayout from '@/Layouts/PatientLayout.vue';
import { readNumericScreenQueryParam } from '@/lib/inertia/readNumericScreenQueryParam';
import { areAnyDeferredInertiaPropsLoading } from '@/lib/inertia/isDeferredInertiaPropLoading';
import type { PatientAppointmentsScreenProps } from '@/lib/patient/appointments/screen/patientAppointmentsScreenProps';
import {
    mobileShellPageIntroButtonClass,
    mobileShellPageSectionTitleClass,
} from '@/lib/shell/mobileShellTypography';

const props = defineProps<PatientAppointmentsScreenProps>();

const { t } = useI18n();
const page = usePage();

const isAppointmentsLoading = computed(() =>
    areAnyDeferredInertiaPropsLoading(
        props.appointments,
        props.linked_families,
    ),
);

const paginationQuery = computed((): Record<string, string | number> => {
    const query: Record<string, string | number> = {};
    const appointment = readNumericScreenQueryParam('appointment', page.url);

    if (appointment !== null) {
        query.appointment = appointment;
    }

    return query;
});

function scrollToDeepLinkedAppointment(): void {
    const appointmentId = readNumericScreenQueryParam('appointment', page.url);

    if (appointmentId === null) {
        return;
    }

    const id = Number(appointmentId);

    if (
        !props.appointments?.data.some(
            (appointment) => appointment.id === id,
        )
    ) {
        return;
    }

    nextTick(() => {
        document
            .getElementById(`patient-appointment-${id}`)
            ?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });
}

watch(
    () => [page.url, props.appointments?.data] as const,
    () => {
        scrollToDeepLinkedAppointment();
    },
    { immediate: true, deep: true },
);

const {
    doctorTypeOptions,
    createSuccessOpen,
    createSuccessTitle,
    createDialogOpen,
    form,
    editForm,
    editDialogOpen,
    dialogContentClass,
    createStartsAtDateMinIso,
    editSchedulePermitPastStartsAtIfSameInstantMs,
    openAppointmentEditor,
    plannedAppointments,
    hasNoAppointmentsAtAll,
    submitNewAppointment,
    submitAppointmentRevision,
    openDeleteAppointmentDialog,
    closeDeleteAppointmentDialog,
    confirmDeleteAppointment,
    deleteDialogOpen,
    appointmentPendingDelete,
    deleteProcessing,
    isAppointmentUpdateInFlight,
    isAppointmentMarkedDoneInUi,
    reopenScheduledAppointmentAfterCompletion,
} = usePatientAppointmentsPage(props);

const showPlannedAppointmentsEmpty = computed(
    () =>
        !isAppointmentsLoading.value && plannedAppointments.value.length === 0,
);

const plannedAppointmentsEmptyMessage = computed((): string =>
    hasNoAppointmentsAtAll.value
        ? t('patient.appointments.empty')
        : t('patient.appointments.emptyPlanned'),
);
</script>

<template>
    <Head>
        <title>{{ t('patient.appointments.title') }}</title>
        <meta
            name="description"
            :content="t('patient.appointments.metaDescription')"
        />
    </Head>

    <PatientLayout>
        <PatientActionSuccessScreen
            v-model:open="createSuccessOpen"
            :title="createSuccessTitle"
            :done-label="t('patient.actionSuccess.done')"
        />

        <PatientPageShell :title="t('patient.appointments.heading')">
            <PatientPageIntroActionBar>
                <Button
                    size="lg"
                    :class="mobileShellPageIntroButtonClass"
                    type="button"
                    @click="createDialogOpen = true"
                >
                    <CalendarPlus class="size-6 shrink-0" aria-hidden="true" />
                    {{ t('patient.appointments.newAppointment') }}
                </Button>
            </PatientPageIntroActionBar>

            <PatientDeferredListSection
                :loading="isAppointmentsLoading"
                :show-empty="showPlannedAppointmentsEmpty"
                :empty-message="plannedAppointmentsEmptyMessage"
            >
                <template #heading>
                    <h2 :class="mobileShellPageSectionTitleClass">
                        {{ t('patient.appointments.plannedHeading') }}
                    </h2>
                </template>

                <ul
                    v-if="plannedAppointments.length > 0"
                    class="flex w-full min-w-0 flex-col gap-5"
                >
                    <li
                        v-for="appointment in plannedAppointments"
                        :key="appointment.id"
                        class="min-w-0"
                    >
                        <AppointmentCard
                            :anchor-id="`patient-appointment-${appointment.id}`"
                            :appointment="appointment"
                            :done-displayed="
                                isAppointmentMarkedDoneInUi(appointment)
                            "
                            :is-patching="
                                isAppointmentUpdateInFlight(appointment.id)
                            "
                            :show-actions="true"
                            :show-provider-subtitle="false"
                            :show-transport-section="true"
                            :show-done-toggle="true"
                            :complete-form-href="
                                route(
                                    'patient.appointments.complete',
                                    appointment.id,
                                )
                            "
                            :cancel-form-href="
                                route(
                                    'patient.appointments.cancel',
                                    appointment.id,
                                )
                            "
                            @edit="openAppointmentEditor(appointment)"
                            @delete="openDeleteAppointmentDialog(appointment)"
                            @update:done="
                                (on) => {
                                    if (!on) {
                                        reopenScheduledAppointmentAfterCompletion(
                                            appointment,
                                        );
                                    }
                                }
                            "
                        />
                    </li>
                </ul>

                <template #pagination>
                    <NumberedPagination
                        v-if="
                            props.appointments !== undefined &&
                            plannedAppointments.length > 0 &&
                            props.appointments.meta.last_page > 1
                        "
                        route-name="patient.appointments"
                        :meta="props.appointments.meta"
                        :query="paginationQuery"
                    />
                </template>
            </PatientDeferredListSection>
        </PatientPageShell>

        <AppointmentFormDialog
            :open="createDialogOpen"
            :title="t('patient.appointments.dialogTitle')"
            form-id="appointment-create-form"
            id-prefix="appointment-create"
            :doctor-type-values="doctorTypeOptions"
            :show-doctor-type-placeholder="true"
            :form="form"
            :transport-families="props.linked_families ?? []"
            :dialog-content-class="dialogContentClass"
            :starts-at-date-input-min-iso="createStartsAtDateMinIso"
            @update:open="(open) => (createDialogOpen = open)"
            @submit="submitNewAppointment"
            @cancel="createDialogOpen = false"
        />

        <PatientConfirmDialog
            v-if="appointmentPendingDelete !== null"
            :open="deleteDialogOpen"
            :title="t('patient.appointments.deleteConfirm.title')"
            :description="t('patient.appointments.deleteConfirm.message')"
            :confirm-label="t('patient.appointments.deleteConfirm.confirm')"
            :cancel-label="t('patient.appointments.deleteConfirm.cancel')"
            :processing="deleteProcessing"
            :icon="Trash2"
            icon-tone="danger"
            cancel-first
            cancel-tone="primary"
            @update:open="
                (open) => {
                    if (!open) {
                        closeDeleteAppointmentDialog();
                    }
                }
            "
            @confirm="confirmDeleteAppointment"
        />

        <AppointmentFormDialog
            :open="editDialogOpen"
            :title="t('patient.appointments.dialogEditTitle')"
            form-id="appointment-edit-form"
            id-prefix="appointment-edit"
            :doctor-type-values="doctorTypeOptions"
            :show-doctor-type-placeholder="false"
            :form="editForm"
            :transport-families="props.linked_families ?? []"
            :dialog-content-class="dialogContentClass"
            :schedule-permit-past-starts-at-if-same-instant-ms="
                editSchedulePermitPastStartsAtIfSameInstantMs
            "
            @update:open="(open) => (editDialogOpen = open)"
            @submit="submitAppointmentRevision"
            @cancel="editDialogOpen = false"
        />
    </PatientLayout>
</template>
