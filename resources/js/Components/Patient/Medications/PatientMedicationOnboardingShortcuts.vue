<script setup lang="ts">
import { Pill, Users } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import PatientMedicationOnboardingOption from '@/Components/Patient/Medications/PatientMedicationOnboardingOption.vue';
import { Card, CardContent } from '@/Components/ui/card';

defineProps<{
    canCreateMedication: boolean;
}>();

const { t } = useI18n();

const addMedicationUrl = computed(
    () => `${route('patient.medications')}?create=1`,
);
const importPlanUrl = computed(
    () => `${route('patient.family')}#family-pending-plans`,
);
</script>

<template>
    <div class="space-y-3 sm:space-y-4">
        <Card
            class="border-border/80 bg-surface text-text rounded-2xl border shadow-md shadow-black/[0.04] sm:rounded-3xl"
        >
            <CardContent class="p-0">
                <section
                    class="bg-surface space-y-5 rounded-2xl px-4 py-4 sm:space-y-6 sm:rounded-3xl sm:px-5 sm:py-5 md:p-7 lg:p-8"
                    aria-labelledby="patient-medication-setup-heading"
                >
                    <div class="flex items-start gap-2.5 sm:gap-3">
                        <Pill
                            class="text-primary mt-0.5 size-6 shrink-0 sm:mt-0 sm:size-7"
                            aria-hidden="true"
                        />
                        <div class="min-w-0 space-y-1 sm:space-y-1.5">
                            <p
                                id="patient-medication-setup-heading"
                                class="daily-checkin-mood-step-title"
                            >
                                {{
                                    t(
                                        'patient.dashboard.medicationSetup.heading',
                                    )
                                }}
                            </p>

                            <p
                                v-if="canCreateMedication"
                                class="daily-checkin-mood-step-description"
                            >
                                {{
                                    t(
                                        'patient.dashboard.medicationSetup.pickOne',
                                    )
                                }}
                            </p>
                        </div>
                    </div>

                    <div
                        v-if="canCreateMedication"
                        class="flex flex-col gap-4"
                        :aria-label="
                            t(
                                'patient.dashboard.medicationSetup.optionsAriaLabel',
                            )
                        "
                    >
                        <PatientMedicationOnboardingOption
                            :title="
                                t(
                                    'patient.dashboard.medicationSetup.optionOne.title',
                                )
                            "
                            :href="addMedicationUrl"
                        />

                        <div class="relative py-2">
                            <hr
                                class="border-border border-0 border-t"
                                :aria-label="
                                    t(
                                        'patient.dashboard.medicationSetup.orDivider',
                                    )
                                "
                            />
                            <span
                                class="bg-surface text-text-muted absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 px-4 text-base font-semibold sm:text-lg"
                                aria-hidden="true"
                            >
                                {{
                                    t(
                                        'patient.dashboard.medicationSetup.orDivider',
                                    )
                                }}
                            </span>
                        </div>

                        <PatientMedicationOnboardingOption
                            :icon="Users"
                            :title="
                                t(
                                    'patient.dashboard.medicationSetup.optionTwo.title',
                                )
                            "
                            :description="
                                t(
                                    'patient.dashboard.medicationSetup.optionTwo.description',
                                )
                            "
                            :cta="
                                t(
                                    'patient.dashboard.medicationSetup.optionTwo.cta',
                                )
                            "
                            :href="importPlanUrl"
                        />
                    </div>

                    <PatientMedicationOnboardingOption
                        v-else
                        :icon="Users"
                        :title="
                            t(
                                'patient.dashboard.medicationSetup.optionTwo.title',
                            )
                        "
                        :description="
                            t(
                                'patient.dashboard.medicationSetup.optionTwo.description',
                            )
                        "
                        :cta="
                            t('patient.dashboard.medicationSetup.optionTwo.cta')
                        "
                        :href="importPlanUrl"
                    />
                </section>
            </CardContent>
        </Card>
    </div>
</template>
