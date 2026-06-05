<script setup lang="ts">
import { Calendar, FileText, Pill } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import { Card, CardContent } from '@/Components/ui/card';
import type { FamilyExpiringPrescriptionPatient } from '@/lib/family/overview/familyExpiringPrescriptionPatients';
import { medicationUrgencyToneClasses } from '@/lib/patient/medications/urgency/medicationUrgencyToneClasses';
import { prescriptionExpiryStatusLine } from '@/lib/patient/prescriptions/prescriptionExpiryStatusLine';
import { cn } from '@/lib/utils';

const toneClasses = medicationUrgencyToneClasses('critical');

const props = defineProps<{
    patient: FamilyExpiringPrescriptionPatient;
}>();

const emit = defineEmits<{
    click: [];
}>();

const { t } = useI18n();
</script>

<template>
    <button
        type="button"
        class="block w-full text-left"
        @click="emit('click')"
    >
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
                            v-for="prescription in props.patient.prescriptions"
                            :key="prescription.id"
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
                                    {{ prescription.medication_name }}
                                </span>
                            </span>
                            <span
                                class="inline-flex min-w-0 items-center gap-2"
                            >
                                <Calendar
                                    :size="18"
                                    :class="
                                        cn('shrink-0', toneClasses.pillIcon)
                                    "
                                    aria-hidden="true"
                                />
                                <span class="font-semibold">
                                    {{
                                        prescriptionExpiryStatusLine(
                                            t,
                                            prescription.days_remaining,
                                        )
                                    }}
                                </span>
                            </span>
                            <span
                                class="inline-flex min-w-0 items-center gap-2"
                            >
                                <FileText
                                    :size="18"
                                    :class="
                                        cn('shrink-0', toneClasses.pillIcon)
                                    "
                                    aria-hidden="true"
                                />
                                <span class="font-semibold">
                                    {{
                                        prescription.is_last_in_batch
                                            ? t(
                                                  'family.overview.prescriptionLastInBatch',
                                              )
                                            : t(
                                                  'family.overview.prescriptionNotLastInBatch',
                                              )
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
