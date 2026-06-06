<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import PatientShellPageWizard from '@/Components/Patient/form/PatientShellPageWizard.vue';
import PatientShellWizardScrollBody from '@/Components/Patient/form/PatientShellWizardScrollBody.vue';
import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import PatientLayout from '@/Layouts/PatientLayout.vue';
import {
    patientFormWizardFooterPrimaryButtonClass,
    patientFormWizardFooterRowClass,
    patientShellPageFillClass,
    patientShellWizardCardClass,
    patientShellWizardCardInnerClass,
    patientShellWizardFormClass,
    patientShellWizardStepPanelClass,
} from '@/lib/patient/patientShellDialogLayout';

const props = withDefaults(
    defineProps<{
        medication_names?: string[];
    }>(),
    {
        medication_names: () => [],
    },
);

const { t } = useI18n();
</script>

<template>
    <Head>
        <title>{{ t('patient.medications.pharmacistOverview.title') }}</title>
        <meta
            name="description"
            :content="
                t('patient.medications.pharmacistOverview.metaDescription')
            "
        />
    </Head>

    <PatientLayout>
        <div :class="patientShellPageFillClass">
            <PatientShellPageWizard
                :title="t('patient.medications.pharmacistOverview.title')"
                :description="
                    t('patient.medications.pharmacistOverview.description')
                "
            >
                <div :class="patientShellWizardFormClass">
                    <PatientShellWizardScrollBody :active="true">
                        <div :class="patientShellWizardStepPanelClass">
                            <ul
                                class="flex w-full min-w-0 flex-col gap-5"
                                :aria-label="
                                    t(
                                        'patient.medications.pharmacistOverview.title',
                                    )
                                "
                            >
                                <li
                                    v-for="(
                                        medicationName, index
                                    ) in props.medication_names"
                                    :key="`${medicationName}-${index}`"
                                    class="min-w-0"
                                >
                                    <Card :class="patientShellWizardCardClass">
                                        <CardContent class="p-0">
                                            <div
                                                :class="
                                                    patientShellWizardCardInnerClass
                                                "
                                            >
                                                <p
                                                    class="text-text-heading text-lg leading-snug font-bold sm:text-xl"
                                                >
                                                    {{ medicationName }}
                                                </p>
                                            </div>
                                        </CardContent>
                                    </Card>
                                </li>
                            </ul>
                        </div>

                        <template #footer>
                            <div :class="patientFormWizardFooterRowClass">
                                <Button
                                    as-child
                                    size="lg"
                                    :class="
                                        patientFormWizardFooterPrimaryButtonClass
                                    "
                                >
                                    <Link
                                        :href="route('patient.medications')"
                                    >
                                        {{
                                            t(
                                                'patient.medications.pharmacistOverview.done',
                                            )
                                        }}
                                    </Link>
                                </Button>
                            </div>
                        </template>
                    </PatientShellWizardScrollBody>
                </div>
            </PatientShellPageWizard>
        </div>
    </PatientLayout>
</template>
