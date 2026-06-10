<script setup lang="ts">
import { computed, ref } from 'vue';
import PatientConfirmDialog from '@/Components/Patient/PatientConfirmDialog.vue';
import { Button } from '@/Components/ui/button';
import { mobileShellPageSectionInnerRowClass } from '@/lib/shell/mobileShellLayout';
import { mobileShellSoftDangerActionButtonClass } from '@/lib/shell/mobileShellActionButtonClasses';
import {
    mobileShellSectionBodyTextClass,
} from '@/lib/shell/mobileShellTypography';
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
        /** Flat list row inside {@link CollapsibleSectionCard} (no nested card chrome). */
        layout?: 'card' | 'collapsible';
    }>(),
    {
        density: 'default',
        layout: 'card',
    },
);

const emit = defineEmits<{
    action: [];
}>();

const confirmOpen = ref(false);

const isCompact = computed(() => props.density === 'compact');
const isCollapsibleLayout = computed(() => props.layout === 'collapsible');

const rowClass = computed(() => {
    if (isCollapsibleLayout.value) {
        return cn(
            'flex min-w-0 flex-col gap-3 sm:flex-row sm:items-center sm:justify-between',
            'border-border border-t pt-4 first:border-t-0 first:pt-0',
        );
    }

    return cn(
        'flex flex-col bg-surface sm:flex-row sm:items-center sm:justify-between',
        isCompact.value
            ? 'gap-3 rounded-xl border border-border px-4 py-3 md:gap-4 md:px-4 md:py-3'
            : cn('gap-3', mobileShellPageSectionInnerRowClass),
    );
});

const titleClass = computed(() => {
    if (isCollapsibleLayout.value) {
        return 'text-text-heading truncate text-base leading-snug font-semibold sm:text-lg';
    }

    return cn(
        'leading-snug text-text-heading',
        isCompact.value
            ? 'text-base font-semibold'
            : 'text-lg font-bold md:text-xl',
    );
});

const subtitleClass = computed(() =>
    cn(
        'mt-1',
        mobileShellSectionBodyTextClass,
        (isCompact.value || isCollapsibleLayout.value) && 'text-sm',
    ),
);

const buttonClass = computed(() =>
    cn(
        mobileShellSoftDangerActionButtonClass,
        'shrink-0 sm:w-auto sm:flex-none',
        (isCompact.value || isCollapsibleLayout.value) &&
            'md:min-h-10 md:px-4 md:text-sm',
        !isCompact.value &&
            !isCollapsibleLayout.value &&
            'md:px-6',
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
            <p :class="[titleClass, !isCollapsibleLayout && 'break-all']">
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

        <PatientConfirmDialog
            :open="confirmOpen"
            :title="props.confirmTitle"
            :description="props.confirmDescription"
            :confirm-label="props.confirmLabel"
            :cancel-label="props.cancelLabel"
            @update:open="
                (open) => {
                    confirmOpen = open;
                }
            "
            @confirm="handleConfirm"
        />
    </div>
</template>
