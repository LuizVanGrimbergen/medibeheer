<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Trash2 } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import MedicationCard from '@/Components/Patient/Medications/MedicationCard.vue';
import MedicationDetailsEditDialog from '@/Components/Patient/Medications/form/MedicationDetailsEditDialog.vue';
import MedicationFormDialog from '@/Components/Patient/Medications/form/MedicationFormDialog.vue';
import MedicationPageIntro from '@/Components/Patient/Medications/MedicationPageIntro.vue';
import PatientActionSuccessScreen from '@/Components/Patient/PatientActionSuccessScreen.vue';
import PatientConfirmDialog from '@/Components/Patient/PatientConfirmDialog.vue';
import PatientDeferredListSection from '@/Components/Patient/PatientDeferredListSection.vue';
import PatientPageShell from '@/Components/Patient/PatientPageShell.vue';
import NumberedPagination from '@/Components/ui/pagination/NumberedPagination.vue';
import ListCardSkeleton from '@/Components/ui/skeleton/ListCardSkeleton.vue';
import { usePatientMedicationsPage } from '@/composables/patient/usePatientMedicationsPage';
import PatientLayout from '@/Layouts/PatientLayout.vue';
import { isDeferredInertiaPropLoading } from '@/lib/inertia/isDeferredInertiaPropLoading';
import type { PatientMedicationsScreenProps } from '@/lib/patient/medications/screen/patientMedicationsScreenProps';

const props = defineProps<PatientMedicationsScreenProps>();

const { t } = useI18n();

const isActiveMedicationsLoading = computed(() =>
    isDeferredInertiaPropLoading(props.active_medications),
);

const hasActiveMedications = computed(
    () => (props.active_medications?.meta.total ?? 0) > 0,
);

const activeMedicationItems = computed(
    () => props.active_medications?.data ?? [],
);

const showActiveMedicationsEmpty = computed(
    () =>
        !isActiveMedicationsLoading.value &&
        props.active_medications !== undefined &&
        props.active_medications.meta.total === 0,
);

const {
    medicationFormDialogLayoutClass,
    createSuccessOpen,
    createSuccessTitle,
    createDialogOpen,
    createForm,
    submitNewMedication,
    canCreateMedication,
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
} = usePatientMedicationsPage(props);
</script>

<template>
    <Head>
        <title>{{ t('patient.medications.title') }}</title>
        <meta
            name="description"
            :content="t('patient.medications.metaDescription')"
        />
    </Head>

    <PatientLayout>
        <PatientActionSuccessScreen
            v-model:open="createSuccessOpen"
            :title="createSuccessTitle"
            :done-label="t('patient.actionSuccess.done')"
        />

        <PatientPageShell :title="t('patient.medications.listHeading')">
            <MedicationPageIntro
                :can-create-medication="canCreateMedication"
                :has-active-medications="hasActiveMedications"
                @new-medication-click="createDialogOpen = true"
            />

            <PatientDeferredListSection
                :loading="isActiveMedicationsLoading"
                :show-empty="showActiveMedicationsEmpty"
                :empty-message="t('patient.medications.empty')"
            >
                <ul
                    v-if="activeMedicationItems.length > 0"
                    class="flex w-full min-w-0 flex-col gap-5"
                >
                    <li
                        v-for="medication in activeMedicationItems"
                        :key="medication.id"
                        class="min-w-0"
                    >
                        <MedicationCard
                            :medication="medication"
                            @edit="openEditMedication(medication)"
                            @delete="openDeleteMedicationDialog(medication)"
                        />
                    </li>
                </ul>

                <template #pagination>
                    <NumberedPagination
                        v-if="
                            props.active_medications !== undefined &&
                            props.active_medications.data.length > 0 &&
                            props.active_medications.meta.last_page > 1
                        "
                        route-name="patient.medications"
                        :meta="props.active_medications.meta"
                    />
                </template>
            </PatientDeferredListSection>
        </PatientPageShell>

        <MedicationFormDialog
            v-if="canCreateMedication"
            v-model:open="createDialogOpen"
            :title="t('patient.medications.dialogTitle')"
            form-id="patient-medication-create-form"
            id-prefix="patient-medication-create"
            :form="createForm"
            :dialog-content-class="medicationFormDialogLayoutClass"
            @cancel="createDialogOpen = false"
            @submit="submitNewMedication"
        />

        <MedicationDetailsEditDialog
            v-model:open="editDialogOpen"
            :title="t('patient.medications.dialogEditTitle')"
            form-id="patient-medication-edit-form"
            id-prefix="patient-medication-edit"
            :form="editForm"
            :dialog-content-class="medicationFormDialogLayoutClass"
            :processing="editForm.processing"
            @cancel="closeEditMedicationDialog"
            @submit="submitEditMedication"
        />

        <PatientConfirmDialog
            v-if="deleteMedication !== null"
            :open="deleteDialogOpen"
            :title="t('patient.medications.deleteConfirm.title')"
            :description="
                t('patient.medications.deleteConfirm.description', {
                    name: deleteMedication.name,
                })
            "
            :confirm-label="t('patient.medications.deleteConfirm.confirm')"
            :cancel-label="t('patient.medications.deleteConfirm.cancel')"
            :processing="deleteProcessing"
            :icon="Trash2"
            icon-tone="danger"
            cancel-first
            cancel-tone="primary"
            @update:open="
                (open) => {
                    if (!open) {
                        closeDeleteMedicationDialog();
                    }
                }
            "
            @confirm="confirmDeleteMedication"
        />
    </PatientLayout>
</template>
