<script setup lang="ts">
import { AlertTriangle, ChevronRight, Pill } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import { Card, CardContent } from '@/Components/ui/card';
import type { FamilyLowStockPatient } from '@/lib/family/overview/familyLowStockPatients';
import { cn } from '@/lib/utils';

const props = defineProps<{
    patient: FamilyLowStockPatient;
}>();

const emit = defineEmits<{
    click: [];
}>();

const { t } = useI18n();

function patientSummary(patient: FamilyLowStockPatient): string {
    const count = patient.medications.length;

    if (count === 1) {
        return t('family.overview.lowStockPatientSummaryOne');
    }

    return t('family.overview.lowStockPatientSummaryMany', {
        count: String(count),
    });
}

function supplyEstimateLine(days: number): string {
    if (days < 1) {
        return t('patient.inventory.supplyEstimateApproxLessThanDay');
    }

    if (days === 1) {
        return t('patient.inventory.supplyEstimateApproxOneDay');
    }

    return t('patient.inventory.supplyEstimateApproxDays', {
        days: String(days),
    });
}
</script>

<template>
    <button
        type="button"
        class="group block w-full text-left"
        @click="emit('click')"
    >
        <Card
            :class="
                cn(
                    'border-danger/70 bg-surface group-hover:bg-surface-2 dark:border-danger/80 rounded-2xl shadow-sm transition',
                )
            "
        >
            <CardContent class="p-4 md:p-4">
                <div class="flex items-start gap-3">
                    <div class="min-w-0 flex-1 space-y-2.5">
                        <div class="space-y-1">
                            <p
                                class="text-text-heading text-base leading-snug font-semibold md:text-[0.9375rem]"
                            >
                                {{ props.patient.patient_name }}
                            </p>
                            <p
                                class="text-danger inline-flex items-center gap-1.5 text-sm font-semibold"
                            >
                                <AlertTriangle
                                    :size="16"
                                    class="shrink-0"
                                    aria-hidden="true"
                                />
                                {{ t('patient.inventory.lowStockBadge') }}
                            </p>
                            <p class="text-text-muted text-sm">
                                {{ patientSummary(props.patient) }}
                            </p>
                        </div>

                        <ul class="border-border/70 space-y-2 border-t pt-2.5">
                            <li
                                v-for="medication in props.patient.medications"
                                :key="medication.id"
                                class="flex items-start gap-2"
                            >
                                <Pill
                                    :size="16"
                                    class="text-danger mt-0.5 shrink-0"
                                    aria-hidden="true"
                                />
                                <div class="min-w-0 space-y-0.5">
                                    <p
                                        class="text-text text-sm leading-snug font-medium"
                                    >
                                        {{ medication.name }}
                                    </p>
                                    <p
                                        class="text-text-muted text-sm leading-snug"
                                    >
                                        {{
                                            supplyEstimateLine(
                                                medication.supply_estimate_days,
                                            )
                                        }}
                                    </p>
                                </div>
                            </li>
                        </ul>
                    </div>

                    <ChevronRight
                        :size="18"
                        class="text-text-muted group-hover:text-text mt-0.5 shrink-0 transition"
                        aria-hidden="true"
                    />
                </div>
            </CardContent>
        </Card>
    </button>
</template>
