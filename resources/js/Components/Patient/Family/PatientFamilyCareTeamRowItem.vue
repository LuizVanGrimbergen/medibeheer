<script setup lang="ts">
import { computed, ref } from 'vue';
import PatientFamilyActionConfirmDialog from '@/Components/Patient/Family/PatientFamilyActionConfirmDialog.vue';
import { Button } from '@/Components/ui/button';
import { patientSoftDangerActionButtonClass } from '@/lib/patient/appointments/ui/patientSoftDangerActionButtonClass';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        title: string;
        subtitle?: string;
        actionLabel: string;
        confirmTitle: string;
        confirmDescription: string;
        confirmLabel: string;
        cancelLabel: string;
        density?: 'default' | 'compact';
    }>(),
    {
        density: 'default',
    },
);

const emit = defineEmits<{
    action: [];
}>();

const confirmOpen = ref(false);

const isCompact = computed(() => props.density === 'compact');

const rowClass = computed(() =>
    cn(
        'flex flex-col bg-surface sm:flex-row sm:items-center sm:justify-between',
        isCompact.value
            ? 'gap-3 rounded-xl border border-border px-4 py-3 md:gap-4 md:px-4 md:py-3'
            : 'gap-3 rounded-2xl border-2 border-border px-4 py-4 sm:px-5 sm:py-5',
    ),
);

const titleClass = computed(() =>
    cn(
        'leading-snug text-text-heading',
        isCompact.value
            ? 'text-base font-semibold'
            : 'text-lg font-bold md:text-xl',
    ),
);

const subtitleClass = computed(() =>
    cn(
        'mt-1 leading-relaxed text-text-muted',
        isCompact.value ? 'text-sm' : 'text-base',
    ),
);

const buttonClass = computed(() =>
    cn(
        patientSoftDangerActionButtonClass,
        'shrink-0 sm:w-auto sm:flex-none',
        isCompact.value ? 'md:min-h-10 md:px-4 md:text-sm' : 'md:px-6',
    ),
);

function openConfirm(): void {
    confirmOpen.value = true;
}

function handleConfirm(): void {
    emit('action');
    confirmOpen.value = false;
}
</script>

<template>
    <div :class="rowClass">
        <div class="min-w-0">
            <p :class="titleClass">
                {{ props.title }}
            </p>
            <p
                v-if="props.subtitle !== undefined && props.subtitle !== ''"
                :class="subtitleClass"
            >
                {{ props.subtitle }}
            </p>
        </div>

        <Button
            type="button"
            variant="outline"
            :class="buttonClass"
            @click="openConfirm"
        >
            {{ props.actionLabel }}
        </Button>

        <PatientFamilyActionConfirmDialog
            :open="confirmOpen"
            :title="props.confirmTitle"
            :description="props.confirmDescription"
            :confirm-label="props.confirmLabel"
            :cancel-label="props.cancelLabel"
            @update:open="(open) => { confirmOpen = open; }"
            @confirm="handleConfirm"
        />
    </div>
</template>
