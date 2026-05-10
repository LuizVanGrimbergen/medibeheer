<script setup lang="ts">
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

defineProps<{
    textareaId: string;
}>();

const note = defineModel<string>({ required: true });
</script>

<template>
    <div class="space-y-4 sm:space-y-6">
        <p class="daily-checkin-step-title">
            {{ t('patient.dashboard.dailyCheckins.noteDialog.title') }}
        </p>
        <p class="daily-checkin-step-description">
            {{ t('patient.dashboard.dailyCheckins.noteDialog.description') }}
        </p>

        <div class="space-y-2 sm:space-y-3">
            <p class="daily-checkin-step-label">
                {{ t('patient.dashboard.dailyCheckins.noteDialog.label') }}
            </p>
            <textarea
                :id="textareaId"
                v-model="note"
                rows="4"
                class="box-border min-h-32 w-full shrink-0 rounded-2xl border-2 border-border bg-surface px-4 py-3.5 text-lg leading-relaxed text-text placeholder:text-text-muted focus-visible:border-focus focus-visible:ring-2 focus-visible:ring-focus/25 sm:min-h-40 sm:px-5 sm:py-4 sm:text-xl"
                :placeholder="t('patient.dashboard.dailyCheckins.noteDialog.placeholder')"
            />
        </div>
    </div>
</template>
