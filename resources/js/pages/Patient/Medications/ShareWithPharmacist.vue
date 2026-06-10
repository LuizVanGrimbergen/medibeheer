<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import MobileShellPageWizard from '@/Components/shell/MobileShellPageWizard.vue';
import MobileShellWizardScrollBody from '@/Components/shell/MobileShellWizardScrollBody.vue';
import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import ListCardSkeleton from '@/Components/ui/skeleton/ListCardSkeleton.vue';
import PatientLayout from '@/Layouts/PatientLayout.vue';
import { isDeferredInertiaPropLoading } from '@/lib/inertia/isDeferredInertiaPropLoading';
import type { PatientMedicationsShareWithPharmacistScreenProps } from '@/lib/patient/medications/screen/patientMedicationsShareWithPharmacistScreenProps';
import {
    mobileShellFormWizardFooterPrimaryButtonClass,
    mobileShellFormWizardFooterRowClass,
    mobileShellPageFillClass,
    mobileShellWizardCardClass,
    mobileShellWizardCardInnerClass,
    mobileShellWizardFormClass,
    mobileShellWizardStepPanelClass,
} from '@/lib/shell/mobileShellDialogLayout';

const props = defineProps<PatientMedicationsShareWithPharmacistScreenProps>();

const { t } = useI18n();

const isMedicationNamesLoading = computed(() =>
    isDeferredInertiaPropLoading(props.medication_names),
);

const medicationNames = computed(() => props.medication_names ?? []);
</script>

<template>
    <Head>
        <title>{{ t('patient.medications.shareWithPharmacist.title') }}</title>
        <meta
            name="description"
            :content="t('patient.medications.shareWithPharmacist.metaDescription')"
        />
    </Head>

    <PatientLayout>
        <div :class="mobileShellPageFillClass">
            <MobileShellPageWizard
                :title="t('patient.medications.shareWithPharmacist.title')"
                :description="
                    t('patient.medications.shareWithPharmacist.description')
                "
            >
                <div :class="mobileShellWizardFormClass">
                    <MobileShellWizardScrollBody :active="true">
                        <div
                            :class="mobileShellWizardStepPanelClass"
                            :aria-busy="isMedicationNamesLoading"
                        >
                            <ListCardSkeleton v-if="isMedicationNamesLoading" />

                            <ul
                                v-else
                                class="flex w-full min-w-0 flex-col gap-5"
                                :aria-label="
                                    t(
                                        'patient.medications.shareWithPharmacist.title',
                                    )
                                "
                            >
                                <li
                                    v-for="(
                                        medicationName, index
                                    ) in medicationNames"
                                    :key="`${medicationName}-${index}`"
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
                                                    {{ medicationName }}
                                                </p>
                                            </div>
                                        </CardContent>
                                    </Card>
                                </li>
                            </ul>
                        </div>

                        <template #footer>
                            <div :class="mobileShellFormWizardFooterRowClass">
                                <Button
                                    as-child
                                    size="lg"
                                    :class="
                                        mobileShellFormWizardFooterPrimaryButtonClass
                                    "
                                >
                                    <Link
                                        :href="route('patient.medications')"
                                    >
                                        {{
                                            t(
                                                'patient.medications.shareWithPharmacist.done',
                                            )
                                        }}
                                    </Link>
                                </Button>
                            </div>
                        </template>
                    </MobileShellWizardScrollBody>
                </div>
            </MobileShellPageWizard>
        </div>
    </PatientLayout>
</template>
