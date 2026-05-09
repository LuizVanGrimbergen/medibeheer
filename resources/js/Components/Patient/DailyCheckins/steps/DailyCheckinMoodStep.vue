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
    <div class="space-y-4 sm:space-y-6">
        <div class="space-y-1 sm:space-y-1.5">
            <p class="text-base font-bold leading-snug text-text-heading sm:text-lg lg:text-xl">
                {{ t('patient.dashboard.dailyCheckins.title') }}
            </p>
            <p class="text-sm leading-snug text-text-muted sm:text-base sm:leading-relaxed">
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
