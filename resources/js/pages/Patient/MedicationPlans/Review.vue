<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import PatientPageShell from '@/Components/Patient/PatientPageShell.vue';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import PatientLayout from '@/Layouts/PatientLayout.vue';
import { medicationDoseUnitChipForAmount } from '@/lib/patient/medications/options/medicationDoseUnitChipForAmount';
import type { MedicationDoseUnitValue } from '@/lib/types';
import { cn } from '@/lib/utils';

const props = defineProps<{
    proposal_id: number;
    family_member_name: string;
    medication_name: string | null;
    dose: string | null;
    dose_unit: string | null;
    strength: string | null;
    note: string | null;
    current_stock: string | null;
}>();

const { t } = useI18n();

const actionPrimaryButtonClass =
    'min-h-12 min-w-0 w-full touch-manipulation gap-2.5 rounded-2xl px-3 text-base font-semibold sm:w-auto sm:flex-1 md:min-h-14 md:px-4 lg:text-lg';

const actionDangerOutlineButtonClass =
    'min-h-12 min-w-0 w-full touch-manipulation rounded-2xl border-2 border-danger/40 bg-danger/10 px-3 text-base font-semibold text-danger hover:border-danger hover:bg-danger/20 hover:text-danger sm:w-auto sm:flex-1 md:min-h-14 md:px-4 lg:text-lg';

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
                <p class="text-sm leading-relaxed text-text-muted">
                    {{
                        t('patient.medicationPlans.review.intro', {
                            name: props.family_member_name || t('patient.medicationPlans.review.unknownFamily'),
                        })
                    }}
                </p>
            </div>

            <Card class="rounded-2xl border-border">
                <CardHeader class="border-b border-border px-5 py-4">
                    <CardTitle class="text-lg font-semibold text-text-heading">
                        {{ props.medication_name ?? t('family.medicationPlans.unnamed') }}
                    </CardTitle>
                </CardHeader>
                <CardContent class="flex flex-col gap-3 px-5 py-5 text-sm text-text">
                    <p v-if="props.dose">
                        <span class="font-semibold text-text-heading">{{ t('patient.medicationPlans.review.dose') }}:</span>
                        {{ props.dose }}
                        <span v-if="doseUnitLabel"> ({{ doseUnitLabel }})</span>
                    </p>
                    <p v-if="props.strength">
                        <span class="font-semibold text-text-heading">{{ t('patient.medicationPlans.review.strength') }}:</span>
                        {{ props.strength }}
                    </p>
                    <p v-if="props.current_stock">
                        <span class="font-semibold text-text-heading">{{ t('patient.medicationPlans.review.stock') }}:</span>
                        {{ props.current_stock }}
                    </p>
                    <p v-if="props.note">
                        <span class="font-semibold text-text-heading">{{ t('patient.medicationPlans.review.note') }}:</span>
                        {{ props.note }}
                    </p>
                </CardContent>
            </Card>

            <div class="flex flex-col gap-3 sm:flex-row-reverse sm:gap-3">
                <Button
                    type="button"
                    :class="cn(actionPrimaryButtonClass)"
                    @click="accept"
                >
                    {{ t('patient.medicationPlans.review.accept') }}
                </Button>
                <Button
                    type="button"
                    variant="outline"
                    :class="cn(actionDangerOutlineButtonClass)"
                    @click="decline"
                >
                    {{ t('patient.medicationPlans.review.decline') }}
                </Button>
            </div>
        </PatientPageShell>
    </PatientLayout>
</template>
