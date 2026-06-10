<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import PatientPageShell from '@/Components/Patient/PatientPageShell.vue';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import PatientLayout from '@/Layouts/PatientLayout.vue';
import { medicationDoseUnitChipForAmount } from '@/lib/patient/medications/options/medicationDoseUnitChipForAmount';
import {
    mobileShellActionDangerOutlineButtonClass,
    mobileShellActionPrimaryButtonClass,
} from '@/lib/shell/mobileShellDialogLayout';
import type { PatientMedicationPlanReviewScreenProps } from '@/lib/patient/medicationPlans/patientMedicationPlanReviewScreenProps';
import type { MedicationDoseUnitValue } from '@/lib/types';
import { cn } from '@/lib/utils';

const props = defineProps<PatientMedicationPlanReviewScreenProps>();

const { t } = useI18n();

const doseUnitLabel = computed((): string | null => {
    if (props.dose_unit === null || props.dose_unit.trim().length < 1) {
        return null;
    }

    if (props.dose === null || props.dose.trim().length < 1) {
        return props.dose_unit;
    }

    return medicationDoseUnitChipForAmount(
        t,
        props.dose,
        props.dose_unit as MedicationDoseUnitValue,
    );
});

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
        <PatientPageShell :title="t('patient.medicationPlans.review.title')">
            <div>
                <p class="text-text-muted text-sm leading-relaxed">
                    {{
                        t('patient.medicationPlans.review.intro', {
                            name:
                                props.family_member_name ||
                                t(
                                    'patient.medicationPlans.review.unknownFamily',
                                ),
                        })
                    }}
                </p>
            </div>

            <Card class="border-border rounded-2xl">
                <CardHeader class="border-border border-b px-5 py-4">
                    <CardTitle class="text-text-heading text-lg font-semibold">
                        {{
                            props.medication_name ??
                            t('family.medicationPlans.unnamed')
                        }}
                    </CardTitle>
                </CardHeader>
                <CardContent
                    class="text-text flex flex-col gap-3 px-5 py-5 text-sm"
                >
                    <p v-if="props.dose">
                        <span class="text-text-heading font-semibold"
                            >{{
                                t('patient.medicationPlans.review.dose')
                            }}:</span
                        >
                        {{ props.dose }}
                        <span v-if="doseUnitLabel"> ({{ doseUnitLabel }})</span>
                    </p>
                    <p v-if="props.strength">
                        <span class="text-text-heading font-semibold"
                            >{{
                                t('patient.medicationPlans.review.strength')
                            }}:</span
                        >
                        {{ props.strength }}
                    </p>
                    <p v-if="props.current_stock">
                        <span class="text-text-heading font-semibold"
                            >{{
                                t('patient.medicationPlans.review.stock')
                            }}:</span
                        >
                        {{ props.current_stock }}
                    </p>
                    <p v-if="props.note">
                        <span class="text-text-heading font-semibold"
                            >{{
                                t('patient.medicationPlans.review.note')
                            }}:</span
                        >
                        {{ props.note }}
                    </p>
                </CardContent>
            </Card>

            <div class="flex flex-col gap-3 sm:flex-row-reverse sm:gap-3">
                <Button
                    type="button"
                    :class="cn(mobileShellActionPrimaryButtonClass)"
                    @click="accept"
                >
                    {{ t('patient.medicationPlans.review.accept') }}
                </Button>
                <Button
                    type="button"
                    variant="outline"
                    :class="cn(mobileShellActionDangerOutlineButtonClass)"
                    @click="decline"
                >
                    {{ t('patient.medicationPlans.review.decline') }}
                </Button>
            </div>
        </PatientPageShell>
    </PatientLayout>
</template>
