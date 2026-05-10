<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import { MoodPicker } from '@/Components/ui/mood-picker';
import type { DailyMoodScoreValue } from '@/lib/types';

const { t } = useI18n();

defineProps<{
    modelValue: DailyMoodScoreValue | null;
    disabled: boolean;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: DailyMoodScoreValue | null];
}>();
</script>

<template>
    <div class="space-y-5 sm:space-y-7">
        <div class="space-y-1 sm:space-y-1.5">
            <p class="daily-checkin-mood-step-title">
                {{ t('patient.dashboard.dailyCheckins.title') }}
            </p>
            <p class="daily-checkin-mood-step-description">
                {{ t('patient.dashboard.dailyCheckins.description') }}
            </p>
        </div>

        <MoodPicker
            :model-value="modelValue"
            :disabled="disabled"
            @update:model-value="emit('update:modelValue', $event)"
        />
    </div>
</template>
