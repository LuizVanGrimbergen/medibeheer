<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import MedicationCard from '@/Components/Medications/MedicationCard.vue';
import MedicationDetailsEditDialog from '@/Components/Patient/Medications/form/MedicationDetailsEditDialog.vue';
import MedicationFormDialog from '@/Components/Patient/Medications/form/MedicationFormDialog.vue';
import MedicationPageIntro from '@/Components/Patient/Medications/form/MedicationPageIntro.vue';
import MedicationRemoveFromListDialog from '@/Components/Patient/Medications/MedicationRemoveFromListDialog.vue';
import { Card, CardContent } from '@/Components/ui/card';
import NumberedPagination from '@/Components/ui/pagination/NumberedPagination.vue';
import { usePatientMedicationsPage } from '@/composables/usePatientMedicationsPage';
import PatientPageShell from '@/Components/Patient/PatientPageShell.vue';
import PatientLayout from '@/Layouts/PatientLayout.vue';
import type { PatientMedicationsScreenProps } from '@/lib/patient/medications/screen/patientMedicationsScreenProps';

const props = defineProps<PatientMedicationsScreenProps>();

const { t } = useI18n();

const {
    medicationFormDialogLayoutClass,
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
        <PatientPageShell :title="t('patient.medications.listHeading')">
            <MedicationPageIntro
                :can-create-medication="canCreateMedication"
                :has-active-medications="props.active_medications.meta.total > 0"
                @new-medication-click="createDialogOpen = true"
            />

            <section class="space-y-5">
                <ul
                    v-if="props.active_medications.data.length > 0"
                    class="flex min-w-0 w-full flex-col gap-5"
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
                        props.active_medications.data.length > 0 &&
                            props.active_medications.meta.last_page > 1
                    "
                    route-name="patient.medications"
                    :meta="props.active_medications.meta"
                />

                <Card
                    v-if="props.active_medications.meta.total === 0"
                    class="rounded-2xl border-2 border-dashed border-border bg-surface-2/70 text-text shadow-none"
                >
                    <CardContent
                        class="px-5 py-14 text-center text-lg leading-relaxed text-text-muted sm:px-8"
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
