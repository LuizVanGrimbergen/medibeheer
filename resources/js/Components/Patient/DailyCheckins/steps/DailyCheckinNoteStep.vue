<script setup lang="ts">
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

defineProps<{
    textareaId: string;
}>();

const note = defineModel<string>({ required: true });
</script>

<template>
    <div class="space-y-3 sm:space-y-5">
        <p class="text-base font-bold leading-snug text-text-heading sm:text-lg lg:text-xl">
            {{ t('patient.dashboard.dailyCheckins.noteDialog.title') }}
        </p>
        <p class="text-sm leading-snug text-text-muted sm:text-base sm:leading-relaxed">
            {{ t('patient.dashboard.dailyCheckins.noteDialog.description') }}
        </p>

        <div class="space-y-1.5 sm:space-y-2">
            <p class="text-sm font-semibold text-text-heading sm:text-base">
                {{ t('patient.dashboard.dailyCheckins.noteDialog.label') }}
            </p>
            <textarea
                :id="textareaId"
                v-model="note"
                rows="4"
                class="box-border min-h-28 w-full shrink-0 rounded-xl border-2 border-border bg-surface px-3 py-3 text-base leading-normal text-text placeholder:text-text-muted focus-visible:border-focus focus-visible:ring-2 focus-visible:ring-focus/25 sm:min-h-34 sm:rounded-2xl sm:px-4 sm:py-3.5 sm:text-lg"
                :placeholder="t('patient.dashboard.dailyCheckins.noteDialog.placeholder')"
            />
        </div>
    </div>
</template>
