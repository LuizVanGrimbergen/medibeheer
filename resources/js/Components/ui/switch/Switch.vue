<script setup lang="ts">
import { useVModel } from '@vueuse/core';
import type { HTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        modelValue: boolean;
        disabled?: boolean;
        id?: string;
        class?: HTMLAttributes['class'];
    }>(),
    {
        disabled: false,
    },
);

const emit = defineEmits<{
    'update:modelValue': [value: boolean];
}>();

const checked = useVModel(props, 'modelValue', emit);
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
                'inline-flex h-10 w-12.5 shrink-0 items-center rounded-full border-2 px-1 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus/30 focus-visible:ring-offset-2 focus-visible:ring-offset-surface disabled:cursor-not-allowed disabled:opacity-55 touch-manipulation',
                checked
                    ? 'justify-end border-primary bg-primary'
                    : 'justify-start border-border bg-surface-2',
                props.class,
            )
        "
        @click="checked = !checked"
    >
        <span
            class="pointer-events-none block size-7 shrink-0 rounded-full bg-white shadow-sm ring-1 ring-black/5"
            aria-hidden="true"
        />
    </button>
</template>
