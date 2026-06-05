<script setup lang="ts">
import { Clock, Pill } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import { Card, CardContent } from '@/Components/ui/card';
import type { FamilyLowStockPatient } from '@/lib/family/overview/familyLowStockPatients';
import { medicationSupplyEstimateLine } from '@/lib/patient/inventory/medicationSupplyEstimateLine';
import { medicationUrgencyToneClasses } from '@/lib/patient/medications/urgency/medicationUrgencyToneClasses';
import { cn } from '@/lib/utils';

const toneClasses = medicationUrgencyToneClasses('critical');

const props = defineProps<{
    patient: FamilyLowStockPatient;
}>();

const emit = defineEmits<{
    click: [];
}>();

const { t } = useI18n();
</script>

<template>
    <button type="button" class="block w-full text-left" @click="emit('click')">
        <Card
            :class="
                cn(
                    'bg-surface text-text w-full min-w-0 rounded-3xl border shadow-md shadow-black/[0.04]',
                    toneClasses.border,
                )
            "
        >
            <CardContent class="p-4 md:p-4">
                <div class="flex items-center gap-3">
                    <div class="min-w-0 flex-1 space-y-2.5">
                        <p
                            class="text-text-heading text-base leading-snug font-semibold md:text-[0.9375rem]"
                        >
                            {{ props.patient.patient_name }}
                        </p>

                        <div
                            v-for="medication in props.patient.medications"
                            :key="medication.id"
                            class="text-text flex flex-wrap items-center gap-x-5 gap-y-2 text-base"
                        >
                            <span
                                class="inline-flex min-w-0 items-center gap-2"
                            >
                                <Pill
                                    :size="18"
                                    :class="
                                        cn('shrink-0', toneClasses.pillIcon)
                                    "
                                    aria-hidden="true"
                                />
                                <span class="font-semibold">
                                    {{ medication.name }}
                                </span>
                            </span>
                            <span
                                class="inline-flex min-w-0 items-center gap-2"
                            >
                                <Clock
                                    :size="18"
                                    :class="
                                        cn('shrink-0', toneClasses.pillIcon)
                                    "
                                    aria-hidden="true"
                                />
                                <span class="font-semibold">
                                    {{
                                        medicationSupplyEstimateLine(t, {
                                            supply_estimate_days:
                                                medication.supply_estimate_days,
                                            supply_estimate_quality: 'approx',
                                        })
                                    }}
                                </span>
                            </span>
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>
    </button>
</template>
