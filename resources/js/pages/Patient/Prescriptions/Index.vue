<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { FileText, Trash2 } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import PatientActionSuccessScreen from '@/Components/Patient/PatientActionSuccessScreen.vue';
import PatientConfirmDialog from '@/Components/Patient/PatientConfirmDialog.vue';
import PatientDeferredListSection from '@/Components/Patient/PatientDeferredListSection.vue';
import PatientPageShell from '@/Components/Patient/PatientPageShell.vue';
import PrescriptionFormDialog from '@/Components/Patient/Prescriptions/PrescriptionFormDialog.vue';
import PrescriptionExpiryEditDialog from '@/Components/Patient/Prescriptions/form/PrescriptionExpiryEditDialog.vue';
import MedicationPrescriptionCard from '@/Components/Patient/Prescriptions/MedicationPrescriptionCard.vue';
import PrescriptionPickedUpSuccessScreen from '@/Components/Patient/Prescriptions/PrescriptionPickedUpSuccessScreen.vue';
import PatientPageIntroActionBar from '@/Components/Patient/PatientPageIntroActionBar.vue';
import { Button } from '@/Components/ui/button';
import NumberedPagination from '@/Components/ui/pagination/NumberedPagination.vue';
import { usePatientPrescriptionCardActions } from '@/composables/patient/usePatientPrescriptionCardActions';
import { usePatientPrescriptionsPage } from '@/composables/patient/usePatientPrescriptionsPage';
import PatientLayout from '@/Layouts/PatientLayout.vue';
import { areAnyDeferredInertiaPropsLoading } from '@/lib/inertia/isDeferredInertiaPropLoading';
import { compareMedicationPrescriptionItems } from '@/lib/patient/prescriptions/compareMedicationPrescriptionItems';
import type { PatientPrescriptionsScreenProps } from '@/lib/patient/prescriptions/patientPrescriptionsScreenProps';
import { mobileShellPageIntroButtonClass } from '@/lib/shell/mobileShellTypography';
import type { MedicationPrescriptionItem } from '@/lib/types';

const props = defineProps<PatientPrescriptionsScreenProps>();

const { t } = useI18n();

const isPrescriptionsLoading = computed(() =>
    areAnyDeferredInertiaPropsLoading(
        props.prescriptions,
        props.medication_choices,
    ),
);

const medicationChoices = computed(
    () => props.medication_choices ?? [],
);

const {
    prescriptionFormDialogLayoutClass,
    addSuccessOpen,
    addSuccessTitle,
    pickupSuccessOpen,
    pickupSuccessIsLastPrescription,
    showPrescriptionPickedUpSuccess,
    prescriptionFormDialogOpen,
    selectedMedicationId,
    quantityClientError,
    medicationClientError,
    expiryDatesClientError,
    canOpenPrescriptionForm,
    form,
    currentStep,
    progressLabel,
    openPrescriptionFormDialog,
    closePrescriptionFormDialog,
    handlePrescriptionDialogSubmit,
    handlePrescriptionDialogBackOrCancel,
    goToPrescriptionWizardStepFromSummary,
} = usePatientPrescriptionsPage(() => medicationChoices.value);

const {
    editDialogOpen,
    prescriptionPendingEdit,
    openEditPrescriptionDialog,
    closeEditPrescriptionDialog,
    deleteDialogOpen,
    prescriptionPendingDelete,
    deleteProcessing,
    openDeletePrescriptionDialog,
    closeDeletePrescriptionDialog,
    confirmDeletePrescription,
} = usePatientPrescriptionCardActions();

const sortedPrescriptions = computed((): MedicationPrescriptionItem[] => {
    if (props.prescriptions === undefined) {
        return [];
    }

    const items = [...props.prescriptions.data];

    items.sort(compareMedicationPrescriptionItems);

    return items;
});

const emptyStateMessage = computed((): string => {
    if (medicationChoices.value.length < 1) {
        return t('patient.prescriptions.emptyMedications');
    }

    return t('patient.prescriptions.empty');
});

const showPrescriptionsEmpty = computed(
    () =>
        !isPrescriptionsLoading.value &&
        props.prescriptions !== undefined &&
        props.prescriptions.meta.total === 0,
);
</script>

<template>
    <Head>
        <title>{{ t('patient.prescriptions.title') }}</title>
        <meta
            name="description"
            :content="t('patient.prescriptions.metaDescription')"
        />
    </Head>

    <PatientLayout>
        <PatientActionSuccessScreen
            v-model:open="addSuccessOpen"
            :title="addSuccessTitle"
            :done-label="t('patient.actionSuccess.done')"
        />

        <PrescriptionPickedUpSuccessScreen
            v-model:open="pickupSuccessOpen"
            :is-last-prescription="pickupSuccessIsLastPrescription"
        />

        <PatientPageShell :title="t('patient.prescriptions.listHeading')">
            <PatientPageIntroActionBar :show="canOpenPrescriptionForm">
                <Button
                    size="lg"
                    :class="mobileShellPageIntroButtonClass"
                    type="button"
                    @click="openPrescriptionFormDialog"
                >
                    <FileText class="size-6 shrink-0" aria-hidden="true" />
                    {{ t('patient.prescriptions.addPrescription') }}
                </Button>
            </PatientPageIntroActionBar>

            <PatientDeferredListSection
                :loading="isPrescriptionsLoading"
                :show-empty="showPrescriptionsEmpty"
                :empty-message="emptyStateMessage"
            >
                <ul
                    v-if="sortedPrescriptions.length > 0"
                    class="flex w-full min-w-0 flex-col gap-5"
                >
                    <li
                        v-for="prescription in sortedPrescriptions"
                        :key="prescription.id"
                        class="min-w-0"
                    >
                        <MedicationPrescriptionCard
                            :prescription="prescription"
                            :on-picked-up="showPrescriptionPickedUpSuccess"
                            @edit="openEditPrescriptionDialog(prescription)"
                            @delete="openDeletePrescriptionDialog(prescription)"
                        />
                    </li>
                </ul>

                <template #pagination>
                    <NumberedPagination
                        v-if="
                            props.prescriptions !== undefined &&
                            props.prescriptions.data.length > 0 &&
                            props.prescriptions.meta.last_page > 1
                        "
                        route-name="patient.prescriptions"
                        :meta="props.prescriptions.meta"
                    />
                </template>
            </PatientDeferredListSection>
        </PatientPageShell>

        <PrescriptionExpiryEditDialog
            v-if="prescriptionPendingEdit !== null"
            :open="editDialogOpen"
            :prescription="prescriptionPendingEdit"
            form-id="patient-prescription-edit-form"
            id-prefix="patient-prescription-edit"
            :dialog-content-class="prescriptionFormDialogLayoutClass"
            @update:open="
                (open) => {
                    if (!open) {
                        closeEditPrescriptionDialog();
                    }
                }
            "
            @saved="closeEditPrescriptionDialog"
        />

        <PatientConfirmDialog
            v-if="prescriptionPendingDelete !== null"
            :open="deleteDialogOpen"
            :title="t('patient.prescriptions.deleteConfirm.title')"
            :description="
                t('patient.prescriptions.deleteConfirm.message', {
                    name: prescriptionPendingDelete.medication.name,
                })
            "
            :confirm-label="t('patient.prescriptions.deleteConfirm.confirm')"
            :cancel-label="t('patient.prescriptions.deleteConfirm.cancel')"
            :processing="deleteProcessing"
            :icon="Trash2"
            icon-tone="danger"
            cancel-first
            cancel-tone="primary"
            @update:open="
                (open) => {
                    if (!open) {
                        closeDeletePrescriptionDialog();
                    }
                }
            "
            @confirm="confirmDeletePrescription"
        />

        <PrescriptionFormDialog
            v-model:open="prescriptionFormDialogOpen"
            v-model:selected-medication-id="selectedMedicationId"
            form-id="patient-prescription-form"
            id-prefix="patient-prescription"
            :dialog-content-class="prescriptionFormDialogLayoutClass"
            :form="form"
            :medication-choices="medicationChoices"
            :current-step="currentStep"
            :progress-label="progressLabel"
            :quantity-client-error="quantityClientError"
            :medication-client-error="medicationClientError"
            :expiry-dates-client-error="expiryDatesClientError"
            :go-to-wizard-step-from-summary="
                goToPrescriptionWizardStepFromSummary
            "
            @submit="handlePrescriptionDialogSubmit"
            @cancel="closePrescriptionFormDialog"
            @back="handlePrescriptionDialogBackOrCancel"
        />
    </PatientLayout>
</template>
