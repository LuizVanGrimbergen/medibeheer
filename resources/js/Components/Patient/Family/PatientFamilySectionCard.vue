<script setup lang="ts">
import { computed } from 'vue';
import {
    mobileShellPageSectionCardAccentClass,
    mobileShellPageSectionCardClass,
} from '@/lib/shell/mobileShellLayout';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        id?: string;
        ariaLabelledby?: string;
        variant?: 'default' | 'accent';
        scrollMargin?: boolean;
    }>(),
    {
        variant: 'default',
        scrollMargin: false,
    },
);

const sectionClass = computed(() =>
    cn(
        props.variant === 'accent'
            ? mobileShellPageSectionCardAccentClass
            : mobileShellPageSectionCardClass,
        props.scrollMargin && 'scroll-mt-24',
    ),
);
</script>

<template>
    <section
        :id="props.id"
        :class="sectionClass"
        :aria-labelledby="props.ariaLabelledby"
    >
        <slot />
    </section>
</template>
