<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { buttonVariants } from '@/Components/ui/button';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        ariaLabel: string;
        tone?: 'neutral' | 'danger';
        disabled?: boolean;
        class?: HTMLAttributes['class'];
    }>(),
    {
        tone: 'neutral',
    },
);

const emit = defineEmits<{
    click: [];
}>();
</script>

<template>
    <button
        type="button"
        :disabled="disabled"
        :aria-label="ariaLabel"
        :class="
            cn(
                buttonVariants({ variant: 'ghost', size: 'icon' }),
                props.tone === 'danger'
                    ? 'text-text-muted hover:bg-danger/10 hover:text-danger'
                    : 'text-text-muted hover:bg-surface-hover hover:text-text-heading',
                props.class,
            )
        "
        @click="emit('click')"
    >
        <slot />
    </button>
</template>
