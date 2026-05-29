<script setup lang="ts">
import { ref } from 'vue';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        selectedDate: string | null;
        heading: string;
        density?: 'default' | 'compact';
        showHeading?: boolean;
    }>(),
    {
        density: 'default',
        showHeading: true,
    },
);

const sectionRef = ref<HTMLElement | null>(null);

function scrollIntoView(options?: ScrollIntoViewOptions): void {
    sectionRef.value?.scrollIntoView(options);
}

defineExpose({
    scrollIntoView,
});
</script>

<template>
    <section
        v-if="selectedDate !== null"
        ref="sectionRef"
        :class="props.density === 'compact' ? 'scroll-mt-20 space-y-2' : 'scroll-mt-24 space-y-3'"
        :aria-label="props.showHeading ? undefined : props.heading"
        tabindex="-1"
    >
        <h2
            v-if="props.showHeading"
            :class="
                cn(
                    'font-semibold text-text-heading',
                    props.density === 'compact' ? 'text-base' : 'text-lg',
                )
            "
        >
            {{ props.heading }}
        </h2>
        <slot />
    </section>
</template>
