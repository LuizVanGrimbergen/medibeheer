<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { HTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        href: string;
        selected?: boolean;
        prefetch?: boolean | 'hover' | 'click' | 'mount';
        class?: HTMLAttributes['class'];
    }>(),
    {
        selected: false,
        prefetch: 'hover',
    },
);
</script>

<template>
    <Link
        :href="props.href"
        :prefetch="props.prefetch"
        :class="
            cn(
                'block rounded-xl border border-border bg-surface px-4 py-3 text-base font-semibold leading-snug transition-colors',
                props.selected
                    ? 'border-primary/50 bg-primary/10 text-primary'
                    : 'text-text-heading hover:bg-surface-hover',
                props.class,
            )
        "
    >
        <slot />
    </Link>
</template>
