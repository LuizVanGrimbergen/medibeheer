<script setup lang="ts">
import { AlertCircle } from 'lucide-vue-next';
import type { HTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        id?: string;
        message?: string;
        variant?: 'alert' | 'inline';
        class?: HTMLAttributes['class'];
    }>(),
    {
        variant: 'alert',
    },
);
</script>

<template>
    <div
        v-show="message"
        :id="id"
        :role="props.variant === 'alert' ? 'alert' : undefined"
        :aria-atomic="props.variant === 'alert' ? 'true' : undefined"
        :class="
            cn(
                props.variant === 'inline'
                    ? 'mt-2 text-sm font-medium leading-snug text-danger'
                    : 'mt-2 flex gap-2.5 rounded-xl border border-danger/40 bg-danger/10 px-3 py-2.5 text-left text-base leading-snug text-danger',
                props.class,
            )
        "
    >
        <template v-if="props.variant !== 'inline'">
            <AlertCircle
                class="mt-0.5 size-5 shrink-0 opacity-90"
                aria-hidden="true"
            />
            <p class="min-w-0 flex-1 font-medium">
                {{ message }}
            </p>
        </template>
        <p v-else>
            {{ message }}
        </p>
    </div>
</template>
