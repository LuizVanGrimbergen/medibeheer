<script setup lang="ts">
import { AlertTriangle } from 'lucide-vue-next';
import { computed } from 'vue';
import { Progress } from '@/Components/ui/progress';
import type { MedicationUrgencyTone } from '@/lib/patient/medications/urgency/medicationUrgencyTone';
import {
    medicationUrgencyProgressIndicatorClass,
    medicationUrgencyShowsAlertRow,
    medicationUrgencyStatusTextClass,
} from '@/lib/patient/medications/urgency/medicationUrgencyTone';

const props = withDefaults(
    defineProps<{
        tone: MedicationUrgencyTone | null;
        progressPercent: number | null;
        statusLine: string;
        progressAriaLabel: string;
        criticalAlertLabel: string;
        warningAlertLabel: string;
        showProgressBar?: boolean;
        showInlineAlertIcon?: boolean;
    }>(),
    {
        showProgressBar: true,
        showInlineAlertIcon: true,
    },
);

const showAlertRow = computed(() => medicationUrgencyShowsAlertRow(props.tone));

const progressIndicatorClass = computed(() =>
    medicationUrgencyProgressIndicatorClass(props.tone),
);

const statusLineClass = computed(() =>
    medicationUrgencyStatusTextClass(props.tone),
);

const alertIconClass = computed((): string => {
    if (props.tone === 'critical') {
        return 'text-danger';
    }

    return 'text-stock-near dark:text-stock-near-dark';
});
</script>

<template>
    <div :class="showProgressBar ? 'space-y-3.5' : null">
        <Progress
            v-if="showProgressBar && progressPercent !== null"
            :model-value="progressPercent"
            :indicator-class="progressIndicatorClass"
            :aria-label="progressAriaLabel"
            class="h-4 w-full sm:h-5"
        />

        <div
            class="flex min-w-0 items-start gap-3"
            :role="showAlertRow ? 'alert' : undefined"
        >
            <template v-if="showAlertRow && showInlineAlertIcon">
                <span class="sr-only">{{
                    tone === 'critical' ? criticalAlertLabel : warningAlertLabel
                }}</span>
                <AlertTriangle
                    class="mt-0.5 size-6 shrink-0 sm:mt-0 sm:size-7"
                    :class="alertIconClass"
                    aria-hidden="true"
                />
            </template>
            <p
                class="min-w-0 text-base leading-relaxed font-semibold sm:text-lg"
                :class="statusLineClass"
            >
                {{ statusLine }}
            </p>
        </div>
    </div>
</template>
