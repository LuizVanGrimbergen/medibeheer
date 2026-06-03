<script setup lang="ts">
import { CheckCircle2, CircleX } from 'lucide-vue-next';
import type { HTMLAttributes } from 'vue';
import { useI18n } from 'vue-i18n';
import AppointmentPairActionButtons from '@/Components/Appointments/AppointmentPairActionButtons.vue';
import { Button } from '@/Components/ui/button';
import { cn } from '@/lib/utils';

const props = defineProps<{
    modelValue: boolean;
    disabled: boolean;
    completeFormHref: string;
    cancelFormHref: string;
    class?: HTMLAttributes['class'];
}>();

const emit = defineEmits<{
    'update:modelValue': [done: boolean];
}>();

const { t } = useI18n();

function revert(): void {
    if (props.disabled) {
        return;
    }

    emit('update:modelValue', false);
}
</script>

<template>
    <div :class="cn('w-full min-w-0', props.class)">
        <div v-if="!modelValue">
            <AppointmentPairActionButtons
                :disabled="disabled"
                :primary-href="completeFormHref"
                :secondary-href="cancelFormHref"
            >
                <template #primary>
                    <CheckCircle2 class="size-6 shrink-0" aria-hidden="true" />
                    {{ t('patient.appointments.doneToggle.markDone') }}
                </template>
                <template #secondary>
                    <CircleX class="size-6 shrink-0" aria-hidden="true" />
                    {{ t('patient.appointments.doneToggle.markCancelled') }}
                </template>
            </AppointmentPairActionButtons>
        </div>

        <div
            v-else
            class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between sm:gap-4"
        >
            <p
                class="text-success flex items-center gap-2 text-lg leading-snug font-semibold"
            >
                <CheckCircle2 class="size-6 shrink-0" aria-hidden="true" />
                {{ t('patient.appointments.doneToggle.markDone') }}
            </p>
            <Button
                type="button"
                variant="outline"
                size="lg"
                class="min-h-12 w-full shrink-0 touch-manipulation px-4 text-base font-semibold sm:w-auto"
                :disabled="disabled"
                @click="revert"
            >
                {{ t('patient.appointments.doneToggle.markUndone') }}
            </Button>
        </div>
    </div>
</template>
