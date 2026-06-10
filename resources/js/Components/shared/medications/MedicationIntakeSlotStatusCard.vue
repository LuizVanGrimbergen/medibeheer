<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import MedicationTypeLeadIcon from '@/Components/shared/medications/MedicationTypeLeadIcon.vue';
import type { MedicationIntakeHistorySlot } from '@/lib/patient/medications/history/medicationIntakeHistoryTypes';
import { medicationIntakeSlotPresentationForSlot } from '@/lib/patient/medications/history/medicationIntakeSlotPresentation';
import { cn } from '@/lib/utils';

const props = defineProps<{
    intakeSlot: Pick<
        MedicationIntakeHistorySlot,
        'name' | 'type_medication' | 'dose_time' | 'snooze_minutes' | 'taken_at'
    >;
}>();

const { t } = useI18n();

const presentation = computed(() =>
    medicationIntakeSlotPresentationForSlot(props.intakeSlot),
);
</script>

<template>
    <div
        class="border-border bg-surface w-full min-w-0 rounded-2xl border shadow-sm"
    >
        <div class="flex items-start gap-3 px-4 py-4 md:px-5 md:py-3.5">
            <div
                :class="
                    cn(
                        'flex size-12 shrink-0 items-center justify-center rounded-xl sm:size-14',
                        presentation.iconWrapperClass,
                    )
                "
            >
                <MedicationTypeLeadIcon
                    :medication-type="intakeSlot.type_medication"
                    :icon-tone-class="presentation.iconToneClass"
                />
            </div>
            <div class="min-w-0 flex-1">
                <div class="flex items-start justify-between gap-3">
                    <p
                        class="text-text-heading min-w-0 text-lg font-semibold md:text-base"
                    >
                        {{ intakeSlot.name }}
                    </p>
                    <span
                        class="inline-flex shrink-0 items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold"
                        :class="presentation.tagClass"
                    >
                        <component
                            :is="presentation.icon"
                            class="size-3.5 shrink-0"
                            aria-hidden="true"
                        />
                        {{ t(presentation.labelKey) }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>
