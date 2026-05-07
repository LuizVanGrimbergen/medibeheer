<script setup lang="ts">
import { useVModel } from '@vueuse/core';
import { computed, type HTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        modelValue: boolean;
        disabled?: boolean;
        id?: string;
        size?: 'md' | 'lg';
        class?: HTMLAttributes['class'];
    }>(),
    {
        disabled: false,
        size: 'md',
    },
);

const emit = defineEmits<{
    'update:modelValue': [value: boolean];
}>();

const checked = useVModel(props, 'modelValue', emit);

const baseClass = computed(() =>
    props.size === 'lg'
        ? 'inline-flex cursor-pointer h-12 w-16 shrink-0 items-center rounded-full border-2 px-1.5 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus/30 focus-visible:ring-offset-2 focus-visible:ring-offset-surface disabled:cursor-not-allowed disabled:opacity-55 touch-manipulation'
        : 'inline-flex cursor-pointer h-10 w-12.5 shrink-0 items-center rounded-full border-2 px-1 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus/30 focus-visible:ring-offset-2 focus-visible:ring-offset-surface disabled:cursor-not-allowed disabled:opacity-55 touch-manipulation',
);

const thumbClass = computed(() =>
    props.size === 'lg'
        ? 'pointer-events-none block size-8 shrink-0 rounded-full bg-white shadow-sm ring-1 ring-black/5'
        : 'pointer-events-none block size-7 shrink-0 rounded-full bg-white shadow-sm ring-1 ring-black/5',
);
</script>

<template>
    <button
        :id="id"
        type="button"
        role="switch"
        :aria-checked="checked"
        :disabled="disabled"
        :class="
            cn(
                baseClass,
                checked
                    ? 'justify-end border-primary bg-primary'
                    : 'justify-start border-border bg-surface-2',
                props.class,
            )
        "
        @click="checked = !checked"
    >
        <span
            :class="thumbClass"
            aria-hidden="true"
        />
    </button>
</template>
