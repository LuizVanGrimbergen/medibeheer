<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import PatientActionSuccessScreen from '@/Components/Patient/PatientActionSuccessScreen.vue';
import PatientPageShell from '@/Components/Patient/PatientPageShell.vue';
import AddPrescriptionDialog from '@/Components/Patient/Prescriptions/AddPrescriptionDialog.vue';
import MedicationPrescriptionCard from '@/Components/Patient/Prescriptions/MedicationPrescriptionCard.vue';
import PrescriptionsPageIntro from '@/Components/Patient/Prescriptions/PrescriptionsPageIntro.vue';
import { Card, CardContent } from '@/Components/ui/card';
import NumberedPagination from '@/Components/ui/pagination/NumberedPagination.vue';
import { usePatientPrescriptionsPage } from '@/composables/usePatientPrescriptionsPage';
import PatientLayout from '@/Layouts/PatientLayout.vue';
import { compareMedicationPrescriptionListItems } from '@/lib/patient/prescriptions/compareMedicationPrescriptionListItems';
import type { PatientPrescriptionsScreenProps } from '@/lib/patient/prescriptions/patientPrescriptionsScreenProps';
import type { MedicationPrescriptionListItem } from '@/lib/types';

const props = defineProps<PatientPrescriptionsScreenProps>();

const { t } = useI18n();

const {
    prescriptionFormDialogLayoutClass,
    addSuccessOpen,
    addSuccessTitle,
    addSuccessMessage,
    addSuccessDetails,
    addDialogOpen,
    selectedMedicationId,
    quantityClientError,
    medicationClientError,
    expiryDatesClientError,
    canAddPrescription,
    form,
    currentStep,
    progressLabel,
    openAddPrescriptionDialog,
    closeAddPrescriptionDialog,
    handlePrescriptionDialogSubmit,
    handlePrescriptionDialogBackOrCancel,
} = usePatientPrescriptionsPage(() => props.medication_choices);

const sortedPrescriptions = computed((): MedicationPrescriptionListItem[] => {
    const items = [...props.prescriptions.data];

    items.sort(compareMedicationPrescriptionListItems);

    return items;
});

const emptyStateMessage = computed((): string => {
    if (props.medication_choices.length < 1) {
        return t('patient.prescriptions.emptyMedications');
    }

    return t('patient.prescriptions.empty');
});
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
            :message="addSuccessMessage"
            :details="addSuccessDetails"
            :done-label="t('patient.actionSuccess.done')"
        />

        <PatientPageShell :title="t('patient.prescriptions.listHeading')">
            <PrescriptionsPageIntro
                :can-add-prescription="canAddPrescription"
                @add-prescription-click="openAddPrescriptionDialog"
            />

            <section class="space-y-5">
                <ul
                    v-if="props.prescriptions.data.length > 0"
                    class="flex w-full min-w-0 flex-col gap-6 sm:gap-7"
                >
                    <li
                        v-for="prescription in sortedPrescriptions"
                        :key="prescription.id"
                        class="min-w-0"
                    >
                        <MedicationPrescriptionCard
                            :prescription="prescription"
                        />
                    </li>
                </ul>

                <NumberedPagination
                    v-if="
                        props.prescriptions.data.length > 0 &&
                        props.prescriptions.meta.last_page > 1
                    "
                    route-name="patient.prescriptions"
                    :meta="props.prescriptions.meta"
                />

                <Card
                    v-if="props.prescriptions.meta.total === 0"
                    class="border-border bg-surface-2/70 text-text rounded-2xl border-2 border-dashed shadow-none"
                >
                    <CardContent
                        class="text-text-muted px-5 py-14 text-center text-lg leading-relaxed sm:px-8"
                    >
                        {{ emptyStateMessage }}
                    </CardContent>
                </Card>
            </section>
        </PatientPageShell>

        <AddPrescriptionDialog
            v-model:open="addDialogOpen"
            v-model:selected-medication-id="selectedMedicationId"
            form-id="patient-add-prescription-form"
            id-prefix="patient-add-prescription"
            :dialog-content-class="prescriptionFormDialogLayoutClass"
            :form="form"
            :medication-choices="props.medication_choices"
            :current-step="currentStep"
            :progress-label="progressLabel"
            :quantity-client-error="quantityClientError"
            :medication-client-error="medicationClientError"
            :expiry-dates-client-error="expiryDatesClientError"
            @submit="handlePrescriptionDialogSubmit"
            @cancel="closeAddPrescriptionDialog"
            @back="handlePrescriptionDialogBackOrCancel"
        />
    </PatientLayout>
</template>
