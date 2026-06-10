<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import PatientFormWizardFooter from '@/Components/Patient/form/PatientFormWizardFooter.vue';
import PatientFormWizardFooterButton from '@/Components/Patient/form/PatientFormWizardFooterButton.vue';
import MobileShellPageWizard from '@/Components/shell/MobileShellPageWizard.vue';
import MobileShellWizardScrollBody from '@/Components/shell/MobileShellWizardScrollBody.vue';
import { Card, CardContent } from '@/Components/ui/card';
import PatientLayout from '@/Layouts/PatientLayout.vue';
import type { PatientMedicationPlanReviewScreenProps } from '@/lib/patient/medicationPlans/patientMedicationPlanReviewScreenProps';
import {
    mobileShellPageFillClass,
    mobileShellWizardCardClass,
    mobileShellWizardCardInnerClass,
    mobileShellWizardFormClass,
    mobileShellWizardStepPanelClass,
} from '@/lib/shell/mobileShellDialogLayout';

const props = defineProps<PatientMedicationPlanReviewScreenProps>();

const { t } = useI18n();

const intro = computed((): string =>
    t('patient.medicationPlans.review.intro', {
        name:
            props.family_member_name ||
            t('patient.medicationPlans.review.unknownFamily'),
    }),
);

const displayMedicationNames = computed((): string[] =>
    props.medication_names.length > 0
        ? props.medication_names
        : [t('family.medicationPlans.unnamed')],
);

function accept(): void {
    router.post(route('patient.medication-plans.accept', props.proposal_id));
}

function decline(): void {
    router.post(route('patient.medication-plans.decline', props.proposal_id));
}
</script>

<template>
    <Head>
        <title>{{ t('patient.medicationPlans.review.title') }}</title>
    </Head>

    <PatientLayout>
        <div :class="mobileShellPageFillClass">
            <MobileShellPageWizard
                :title="t('patient.medicationPlans.review.title')"
                :description="intro"
            >
                <div :class="mobileShellWizardFormClass">
                    <MobileShellWizardScrollBody :active="true">
                        <div :class="mobileShellWizardStepPanelClass">
                            <ul
                                class="flex w-full min-w-0 flex-col gap-5"
                                :aria-label="
                                    t('patient.medicationPlans.review.title')
                                "
                            >
                                <li
                                    v-for="(name, index) in displayMedicationNames"
                                    :key="index"
                                    class="min-w-0"
                                >
                                    <Card :class="mobileShellWizardCardClass">
                                        <CardContent class="p-0">
                                            <div
                                                :class="
                                                    mobileShellWizardCardInnerClass
                                                "
                                            >
                                                <p
                                                    class="text-text-heading text-lg leading-snug font-bold sm:text-xl"
                                                >
                                                    {{ name }}
                                                </p>
                                            </div>
                                        </CardContent>
                                    </Card>
                                </li>
                            </ul>
                        </div>

                        <template #footer>
                            <PatientFormWizardFooter>
                                <PatientFormWizardFooterButton
                                    variant="primary"
                                    @click="accept"
                                >
                                    {{
                                        t('patient.medicationPlans.review.accept')
                                    }}
                                </PatientFormWizardFooterButton>
                                <PatientFormWizardFooterButton
                                    variant="danger"
                                    @click="decline"
                                >
                                    {{
                                        t(
                                            'patient.medicationPlans.review.decline',
                                        )
                                    }}
                                </PatientFormWizardFooterButton>
                            </PatientFormWizardFooter>
                        </template>
                    </MobileShellWizardScrollBody>
                </div>
            </MobileShellPageWizard>
        </div>
    </PatientLayout>
</template>
