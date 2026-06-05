<script setup lang="ts">
import { ActionSuccessScreen } from '@/Components/ui/action-success-screen';
import type { PatientActionSuccessDetail } from '@/composables/patient/usePatientActionSuccessScreen';

const open = defineModel<boolean>('open', { required: true });

const props = withDefaults(
    defineProps<{
        title: string;
        message?: string | null;
        eyebrow?: string | null;
        subtitle?: string | null;
        doneLabel?: string;
        details?: PatientActionSuccessDetail[];
        teleport?: boolean;
    }>(),
    {
        message: null,
        eyebrow: null,
        subtitle: null,
        doneLabel: undefined,
        details: () => [],
        teleport: true,
    },
);

defineEmits<{
    done: [];
}>();
</script>

<template>
    <ActionSuccessScreen
        v-model:open="open"
        v-bind="props"
        @done="$emit('done')"
    >
        <template v-if="$slots.footer" #footer>
            <slot name="footer" />
        </template>
    </ActionSuccessScreen>
</template>
