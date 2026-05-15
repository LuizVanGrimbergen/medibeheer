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
        v-if="canCreateMedication"
        class="flex min-w-0 w-full flex-col gap-5 sm:flex-row sm:items-center sm:justify-end sm:gap-6"
    >
        <Button
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
