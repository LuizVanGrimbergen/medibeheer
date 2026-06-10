<script setup lang="ts">
import CollapsibleSectionCard from '@/Components/ui/collapsible-section/CollapsibleSectionCard.vue';

const open = defineModel<boolean>('open', { required: true });

const props = withDefaults(
    defineProps<{
        heading: string;
        toggleLabel: string;
        collapsedSummary?: string;
        iconWrapperClass?: string;
    }>(),
    {
        collapsedSummary: undefined,
        iconWrapperClass: 'bg-role-doctor/12 text-role-doctor',
    },
);
</script>

<template>
    <CollapsibleSectionCard
        class="scroll-mt-20"
        v-model:open="open"
        :heading="props.heading"
        :toggle-label="props.toggleLabel"
        :collapsed-summary="props.collapsedSummary"
        collapsed-summary-class="line-clamp-2"
        :icon-wrapper-class="props.iconWrapperClass"
        content-class="flex flex-col gap-4 border-t border-border px-4 pb-4 pt-4 md:gap-3 md:px-5 md:pb-5 md:pt-4"
    >
        <template #icon>
            <slot name="icon" />
        </template>

        <slot />
    </CollapsibleSectionCard>
</template>
