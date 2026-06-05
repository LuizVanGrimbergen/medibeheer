<script setup lang="ts">
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { ref } from 'vue';
import { Button } from '@/Components/ui/button';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        selectedDate: string | null;
        heading: string;
        density?: 'default' | 'compact';
        showHeading?: boolean;
        showDayNavigation?: boolean;
        selectedDateLabel?: string;
        prevDayAriaLabel?: string;
        nextDayAriaLabel?: string;
    }>(),
    {
        density: 'default',
        showHeading: true,
        showDayNavigation: false,
        selectedDateLabel: '',
        prevDayAriaLabel: '',
        nextDayAriaLabel: '',
    },
);

const emit = defineEmits<{
    'previous-day': [];
    'next-day': [];
}>();

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
        :class="
            props.density === 'compact'
                ? 'scroll-mt-20 space-y-2'
                : 'scroll-mt-24 space-y-3'
        "
        :aria-label="props.showHeading ? undefined : props.heading"
        tabindex="-1"
    >
        <div
            v-if="props.showDayNavigation"
            class="grid w-full grid-cols-[auto_1fr_auto] items-center gap-2"
        >
            <Button
                type="button"
                variant="ghost"
                size="icon-sm"
                class="text-primary justify-self-start hover:bg-surface-hover"
                :aria-label="props.prevDayAriaLabel"
                @click="emit('previous-day')"
            >
                <ChevronLeft
                    class="size-5 shrink-0 stroke-[2.25]"
                    aria-hidden="true"
                />
            </Button>
            <div class="min-w-0 text-center">
                <h2
                    :class="
                        cn(
                            'text-text-heading truncate font-semibold leading-snug',
                            props.density === 'compact'
                                ? 'text-sm'
                                : 'text-base md:text-lg',
                        )
                    "
                >
                    {{ props.selectedDateLabel }}
                </h2>
                <p
                    v-if="props.showHeading"
                    class="text-text-muted mt-0.5 truncate text-xs md:text-sm"
                >
                    {{ props.heading }}
                </p>
            </div>
            <Button
                type="button"
                variant="ghost"
                size="icon-sm"
                class="text-primary justify-self-end hover:bg-surface-hover"
                :aria-label="props.nextDayAriaLabel"
                @click="emit('next-day')"
            >
                <ChevronRight
                    class="size-5 shrink-0 stroke-[2.25]"
                    aria-hidden="true"
                />
            </Button>
        </div>
        <h2
            v-else-if="props.showHeading"
            :class="
                cn(
                    'text-text-heading font-semibold',
                    props.density === 'compact' ? 'text-base' : 'text-lg',
                )
            "
        >
            {{ props.heading }}
        </h2>
        <slot />
    </section>
</template>
