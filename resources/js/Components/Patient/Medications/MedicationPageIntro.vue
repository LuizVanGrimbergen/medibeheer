<script setup lang="ts">
import { Pill } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import { Button } from '@/Components/ui/button';

defineProps<{
    canCreateMedication: boolean;
}>();

const emit = defineEmits<{
    newMedicationClick: [];
}>();

const { t } = useI18n();
</script>

<template>
    <div
        class="flex min-w-0 w-full flex-col gap-5 sm:flex-row sm:items-start sm:justify-between sm:gap-6"
    >
        <div class="min-w-0 w-full">
            <h1 class="text-3xl font-bold leading-tight text-text-heading">
                {{ t('patient.medications.heading') }}
            </h1>
            <p class="mt-3 max-w-2xl text-base leading-relaxed text-text-muted">
                {{ t('patient.medications.description') }}
            </p>
        </div>

        <Button
            v-if="canCreateMedication"
            size="lg"
            class="min-h-14 w-full touch-manipulation gap-2.5 self-stretch px-6 font-body text-lg font-bold sm:w-auto sm:self-center sm:px-8"
            type="button"
            @click="emit('newMedicationClick')"
        >
            <Pill
                class="size-6 shrink-0"
                aria-hidden="true"
            />
            {{ t('patient.medications.newMedication') }}
        </Button>
    </div>
</template>
