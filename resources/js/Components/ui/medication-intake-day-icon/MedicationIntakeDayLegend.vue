<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import MedicationIntakeDayIcon from '@/Components/ui/medication-intake-day-icon/MedicationIntakeDayIcon.vue';
import {
    MEDICATION_INTAKE_DAY_LEGEND_OPTIONS,
    type MedicationIntakeDayIconStatusValue,
} from '@/lib/patient/medications/history/medicationIntakeDayPresentation';
import { cn } from '@/lib/utils';

export type MedicationIntakeDayLegendPresentation = 'legend' | 'filter';

const props = withDefaults(
    defineProps<{
        statuses?: readonly MedicationIntakeDayIconStatusValue[];
        selectable?: boolean;
        selectedStatus?: MedicationIntakeDayIconStatusValue | null;
        statusCounts?: Record<MedicationIntakeDayIconStatusValue, number>;
        selectLabelKey?: string;
        presentation?: MedicationIntakeDayLegendPresentation;
    }>(),
    {
        statuses: undefined,
        selectable: false,
        selectedStatus: null,
        statusCounts: undefined,
        selectLabelKey: 'doctor.patients.snapshotMedicationStatusSelect',
        presentation: 'legend',
    },
);

const emit = defineEmits<{
    selectStatus: [status: MedicationIntakeDayIconStatusValue];
}>();

const { t } = useI18n();

const legendOptions = computed(() => {
    if (props.statuses === undefined) {
        return MEDICATION_INTAKE_DAY_LEGEND_OPTIONS;
    }

    return MEDICATION_INTAKE_DAY_LEGEND_OPTIONS.filter((option) =>
        props.statuses!.includes(option.status),
    );
});

function isStatusDisabled(
    status: MedicationIntakeDayIconStatusValue,
): boolean {
    if (props.statusCounts === undefined) {
        return false;
    }

    return props.statusCounts[status] === 0;
}

function selectedRingClass(
    status: MedicationIntakeDayIconStatusValue,
): string {
    if (props.selectedStatus !== status) {
        return '';
    }

    if (status === 'complete') {
        return 'ring-success';
    }

    if (status === 'partial') {
        return 'ring-warning';
    }

    return 'ring-danger';
}
</script>

<template>
    <div
        v-if="props.selectable && props.presentation === 'filter'"
        class="bg-surface-2 border-border flex w-full gap-0.5 rounded-lg border p-0.5"
    >
        <button
            v-for="option in legendOptions"
            :key="option.status"
            type="button"
            :disabled="isStatusDisabled(option.status)"
            :class="
                cn(
                    'flex min-w-0 flex-1 items-center justify-center gap-1 rounded-md px-1.5 py-1 text-center transition-all',
                    option.iconBackgroundClass,
                    !isStatusDisabled(option.status) &&
                        'hover:ring-border cursor-pointer hover:ring-1',
                    props.selectedStatus === option.status
                        ? 'ring-1 shadow-sm'
                        : 'opacity-75',
                    selectedRingClass(option.status),
                    isStatusDisabled(option.status) &&
                        'cursor-default opacity-40',
                )
            "
            :aria-label="
                t(props.selectLabelKey, {
                    status: t(option.labelKey),
                })
            "
            :aria-pressed="props.selectedStatus === option.status"
            @click="emit('selectStatus', option.status)"
        >
            <MedicationIntakeDayIcon :status="option.status" size="calendar-day" />
            <span
                v-if="props.statusCounts !== undefined"
                :class="
                    cn(
                        'text-sm font-semibold tabular-nums leading-none',
                        option.faceClass,
                    )
                "
            >
                {{ props.statusCounts[option.status] }}
            </span>
        </button>
    </div>

    <template v-else-if="props.selectable">
        <button
            v-for="option in legendOptions"
            :key="option.status"
            type="button"
            :disabled="isStatusDisabled(option.status)"
            :class="
                cn(
                    'inline-flex items-center gap-1.5 rounded-lg px-2 py-1 font-medium transition-shadow',
                    option.faceClass,
                    !isStatusDisabled(option.status) &&
                        'hover:ring-border cursor-pointer hover:ring-2',
                    props.selectedStatus === option.status &&
                        'ring-2 ring-offset-1',
                    selectedRingClass(option.status),
                    isStatusDisabled(option.status) && 'cursor-default opacity-50',
                )
            "
            :aria-label="
                t(props.selectLabelKey, {
                    status: t(option.labelKey),
                })
            "
            :aria-pressed="props.selectedStatus === option.status"
            @click="emit('selectStatus', option.status)"
        >
            <MedicationIntakeDayIcon :status="option.status" size="legend" />
            {{ t(option.labelKey) }}
        </button>
    </template>

    <template v-else>
        <span
            v-for="option in legendOptions"
            :key="option.status"
            class="text-text-heading inline-flex items-center gap-1.5 font-medium"
        >
            <MedicationIntakeDayIcon :status="option.status" size="legend" />
            {{ t(option.labelKey) }}
        </span>
    </template>
</template>
