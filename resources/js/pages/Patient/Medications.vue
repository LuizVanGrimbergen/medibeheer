<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import MedicationCard from '@/Components/Medications/MedicationCard.vue';
import MedicationDetailsEditDialog from '@/Components/Patient/Medications/form/MedicationDetailsEditDialog.vue';
import MedicationFormDialog from '@/Components/Patient/Medications/form/MedicationFormDialog.vue';
import MedicationPageIntro from '@/Components/Patient/Medications/MedicationPageIntro.vue';
import { Card, CardContent } from '@/Components/ui/card';
import NumberedPagination from '@/Components/ui/pagination/NumberedPagination.vue';
import { usePatientMedicationsPage } from '@/composables/usePatientMedicationsPage';
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
    confirmAndDeleteMedication,
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
        <div class="flex min-w-0 w-full flex-col gap-8">
            <MedicationPageIntro
                :can-create-medication="canCreateMedication"
                @new-medication-click="createDialogOpen = true"
            />

            <section class="space-y-5">
                <h1 class="text-2xl font-bold leading-tight text-text-heading sm:text-3xl sm:leading-tight">
                    {{ t('patient.medications.listHeading') }}
                </h1>

                <ul
                    v-if="props.medications.data.length > 0"
                    class="flex min-w-0 w-full flex-col gap-6 sm:gap-7"
                >
                    <li
                        v-for="medication in props.medications.data"
                        :key="medication.id"
                        class="min-w-0"
                    >
                        <MedicationCard
                            :medication="medication"
                            @edit="openEditMedication(medication)"
                            @delete="confirmAndDeleteMedication(medication)"
                        />
                    </li>
                </ul>

                <NumberedPagination
                    v-if="
                        props.medications.data.length > 0 &&
                            props.medications.meta.last_page > 1
                    "
                    route-name="patient.medications"
                    :meta="props.medications.meta"
                />

                <Card
                    v-if="props.medications.meta.total === 0"
                    class="rounded-2xl border-2 border-dashed border-border bg-surface-2/70 text-text shadow-none"
                >
                    <CardContent
                        class="px-5 py-14 text-center text-lg leading-relaxed text-text-muted sm:px-8"
                    >
                        {{ t('patient.medications.empty') }}
                    </CardContent>
                </Card>
            </section>
        </div>

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
    </PatientLayout>
</template>
