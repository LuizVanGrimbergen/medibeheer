<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import FamilyMedicationPlanPublishDialog from '@/Components/Family/MedicationPlans/FamilyMedicationPlanPublishDialog.vue';
import FamilyLayout from '@/Layouts/FamilyLayout.vue';
import { validatePatientEmailField } from '@/lib/validation/validatePatientEmailField';

const props = defineProps<{
    proposal_id: number;
    cancel_url: string;
}>();

const { t } = useI18n();

const form = useForm({
    patient_email: '',
});

function submit(): void {
    form.clearErrors('patient_email');

    const clientError = validatePatientEmailField(form.patient_email, {
        required: t('family.medicationPlans.publishPage.emailRequired'),
        invalid: t('family.medicationPlans.publishPage.emailInvalid'),
    });

    if (clientError !== null) {
        form.setError('patient_email', clientError);

        return;
    }

    form.transform((data) => ({
        ...data,
        patient_email: data.patient_email.trim().toLowerCase(),
    }));

    form.post(
        route('family.medication-plans.publish.store', props.proposal_id),
        {
            preserveScroll: true,
        },
    );
}
</script>

<template>
    <Head>
        <title>{{ t('family.medicationPlans.publishPage.title') }}</title>
    </Head>

    <FamilyLayout>
        <FamilyMedicationPlanPublishDialog
            form-id="family-medication-plan-publish"
            :cancel-url="props.cancel_url"
            :patient-email="form.patient_email"
            :email-error="form.errors.patient_email"
            :processing="form.processing"
            @update:patient-email="form.patient_email = $event"
            @submit="submit"
        />
    </FamilyLayout>
</template>
