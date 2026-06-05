<script setup lang="ts">
import type { ComponentPublicInstance, HTMLAttributes } from 'vue';
import { ref, toRef } from 'vue';
import { Button } from '@/Components/ui/button';
import { useGsapSegmentedToggleIndicator } from '@/composables/motion/useGsapSegmentedToggleIndicator';
import type { SegmentedToggleTabRefs } from '@/composables/motion/useGsapSegmentedToggleIndicator';
import { resolveGsapTargetElement } from '@/lib/motion/resolveGsapTargetElement';
import { cn } from '@/lib/utils';

export type SegmentedToggleOption<T extends string> = {
    value: T;
    label: string;
    count?: number;
};

const props = defineProps<{
    modelValue: string;
    options: SegmentedToggleOption<string>[];
    class?: HTMLAttributes['class'];
}>();

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const containerRef = ref<HTMLElement | null>(null);
const indicatorRef = ref<HTMLElement | null>(null);
const tabRefs: SegmentedToggleTabRefs = {};

const { syncIndicator } = useGsapSegmentedToggleIndicator(
    containerRef,
    indicatorRef,
    toRef(props, 'modelValue'),
    tabRefs,
);

function registerTabRef(
    value: string,
    target: Element | ComponentPublicInstance | null,
): void {
    const element = resolveGsapTargetElement(
        target as HTMLElement | ComponentPublicInstance | null,
    );

    if (element === null) {
        delete tabRefs[value];

        return;
    }

    if (tabRefs[value] === element) {
        return;
    }

    tabRefs[value] = element;

    if (value === props.modelValue) {
        void syncIndicator();
    }
}

function tabButtonClass(value: string): string {
    if (props.modelValue === value) {
        return 'text-white hover:bg-transparent hover:opacity-100';
    }

    return 'text-text hover:bg-surface-hover';
}
</script>

<template>
    <div
        ref="containerRef"
        :class="
            cn(
                'relative flex w-full overflow-hidden rounded-2xl border border-border bg-surface',
                props.class,
            )
        "
        role="tablist"
    >
        <span
            ref="indicatorRef"
            class="bg-primary pointer-events-none absolute z-0 opacity-0"
            aria-hidden="true"
        />

        <Button
            v-for="opt in options"
            :key="opt.value"
            :ref="(target) => registerTabRef(opt.value, target)"
            type="button"
            variant="ghost"
            :motion-press="false"
            class="relative z-10 h-12 flex-1 rounded-none text-base"
            :class="tabButtonClass(opt.value)"
            role="tab"
            :aria-selected="modelValue === opt.value"
            @click="emit('update:modelValue', opt.value)"
        >
            {{ opt.label }}
            <span
                v-if="typeof opt.count === 'number'"
                :class="modelValue === opt.value ? 'text-white/80' : 'text-text-muted'"
            >
                ({{ opt.count }})
            </span>
        </Button>
    </div>
</template>
