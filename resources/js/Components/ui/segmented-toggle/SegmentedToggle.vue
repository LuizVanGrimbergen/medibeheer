<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { Button } from '@/Components/ui/button';
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
</script>

<template>
    <div
        :class="
            cn(
                'flex w-full overflow-hidden rounded-2xl border border-border bg-surface',
                props.class,
            )
        "
        role="tablist"
    >
        <Button
            v-for="opt in options"
            :key="opt.value"
            type="button"
            :variant="modelValue === opt.value ? 'default' : 'ghost'"
            class="h-12 flex-1 rounded-none text-base"
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

