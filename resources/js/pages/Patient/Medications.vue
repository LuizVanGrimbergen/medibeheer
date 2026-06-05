<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import MedicationCard from '@/Components/Medications/MedicationCard.vue';
import MedicationDetailsEditDialog from '@/Components/Patient/Medications/form/MedicationDetailsEditDialog.vue';
import MedicationFormDialog from '@/Components/Patient/Medications/form/MedicationFormDialog.vue';
import MedicationPageIntro from '@/Components/Patient/Medications/form/MedicationPageIntro.vue';
import MedicationRemoveFromListDialog from '@/Components/Patient/Medications/MedicationRemoveFromListDialog.vue';
import PatientActionSuccessScreen from '@/Components/Patient/PatientActionSuccessScreen.vue';
import PatientPageShell from '@/Components/Patient/PatientPageShell.vue';
import { Card, CardContent } from '@/Components/ui/card';
import NumberedPagination from '@/Components/ui/pagination/NumberedPagination.vue';
import { usePatientMedicationsPage } from '@/composables/patient/usePatientMedicationsPage';
import PatientLayout from '@/Layouts/PatientLayout.vue';
import type { PatientMedicationsScreenProps } from '@/lib/patient/medications/screen/patientMedicationsScreenProps';

const props = defineProps<PatientMedicationsScreenProps>();

const { t } = useI18n();

const isActiveMedicationsLoading = computed(
    () => props.active_medications === undefined,
);

const hasActiveMedications = computed(
    () => (props.active_medications?.meta.total ?? 0) > 0,
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

            <section class="space-y-5" :aria-busy="isActiveMedicationsLoading">
                <ul
                    v-if="isActiveMedicationsLoading"
                    class="flex w-full min-w-0 flex-col gap-5"
                    aria-hidden="true"
                >
                    <li v-for="index in 3" :key="index" class="min-w-0">
                        <Card
                            class="border-border/80 bg-surface text-text rounded-2xl border shadow-md shadow-black/[0.04] sm:rounded-3xl"
                        >
                            <CardContent class="space-y-4 p-5 sm:p-6">
                                <div
                                    class="bg-surface-2 h-7 w-2/3 max-w-xs animate-pulse rounded-lg"
                                />
                                <div
                                    class="bg-surface-2 h-5 w-1/2 max-w-48 animate-pulse rounded-lg"
                                />
                                <div
                                    class="bg-surface-2 h-16 w-full animate-pulse rounded-xl"
                                />
                            </CardContent>
                        </Card>
                    </li>
                </ul>

                <ul
                    v-else-if="
                        props.active_medications !== undefined &&
                        props.active_medications.data.length > 0
                    "
                    class="flex w-full min-w-0 flex-col gap-5"
                >
                    <li
                        v-for="medication in props.active_medications.data"
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

                <NumberedPagination
                    v-if="
                        props.active_medications !== undefined &&
                        props.active_medications.data.length > 0 &&
                        props.active_medications.meta.last_page > 1
                    "
                    route-name="patient.medications"
                    :meta="props.active_medications.meta"
                />

                <Card
                    v-else-if="
                        props.active_medications !== undefined &&
                        props.active_medications.meta.total === 0
                    "
                    class="border-border bg-surface-2/70 text-text rounded-2xl border-2 border-dashed shadow-none"
                >
                    <CardContent
                        class="text-text-muted px-5 py-14 text-center text-lg leading-relaxed sm:px-8"
                    >
                        {{ t('patient.medications.empty') }}
                    </CardContent>
                </Card>
            </section>
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

        <MedicationRemoveFromListDialog
            v-if="deleteMedication !== null"
            v-model:open="deleteDialogOpen"
            :medication-name="deleteMedication.name"
            :processing="deleteProcessing"
            @cancel="closeDeleteMedicationDialog"
            @confirm="confirmDeleteMedication"
        />
    </PatientLayout>
</template>
