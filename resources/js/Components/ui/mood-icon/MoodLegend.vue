<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import MoodIcon from '@/Components/ui/mood-icon/MoodIcon.vue';
import { DAILY_MOOD_OPTIONS } from '@/lib/mood/dailyMoodPresentation';

const { t } = useI18n();
</script>

<template>
    <span
        v-for="option in DAILY_MOOD_OPTIONS"
        :key="option.mood"
        class="text-text-heading inline-flex items-center gap-1.5 font-medium"
    >
        <MoodIcon :mood="option.mood" size="legend" />
        {{ t(option.labelKey) }}
    </span>
</template>
