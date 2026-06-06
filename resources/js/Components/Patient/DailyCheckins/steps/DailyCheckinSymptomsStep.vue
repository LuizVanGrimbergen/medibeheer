<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import { Button } from '@/Components/ui/button';
import { DAILY_CHECKIN_SYMPTOM_VALUES } from '@/lib/patient/dailyCheckinSymptoms';
import type { DailyCheckinSymptomValue } from '@/lib/types';

const { t } = useI18n();

defineProps<{
    processing: boolean;
    chipClass: (symptom: DailyCheckinSymptomValue) => string;
}>();

const emit = defineEmits<{
    toggle: [symptom: DailyCheckinSymptomValue];
}>();
</script>

<template>
    <div class="space-y-4 sm:space-y-6">
        <p class="daily-checkin-step-title">
            {{ t('patient.dashboard.dailyCheckins.symptoms.title') }}
        </p>

        <div class="flex flex-wrap gap-2 sm:gap-3">
            <Button
                v-for="symptom in DAILY_CHECKIN_SYMPTOM_VALUES"
                :key="symptom"
                type="button"
                variant="ghost"
                :class="chipClass(symptom)"
                :disabled="processing"
                @click="emit('toggle', symptom)"
            >
                {{
                    t(
                        `patient.dashboard.dailyCheckins.symptoms.options.${symptom}`,
                    )
                }}
            </Button>
        </div>
    </div>
</template>
