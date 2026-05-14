<script setup lang="ts">
import { reactiveOmit } from '@vueuse/core';
import type { ProgressRootProps } from 'reka-ui';
import { ProgressIndicator, ProgressRoot } from 'reka-ui';
import type { HTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<
        ProgressRootProps & {
            class?: HTMLAttributes['class'];
            indicatorClass?: HTMLAttributes['class'];
        }
    >(),
    {
        modelValue: 0,
    },
);

const delegatedProps = reactiveOmit(props, 'class', 'indicatorClass');
</script>

<template>
    <ProgressRoot
        v-bind="delegatedProps"
        :class="
            cn(
                'relative h-4 w-full overflow-hidden rounded-full bg-surface-2',
                props.class,
            )
        "
    >
        <ProgressIndicator
            :class="
                cn(
                    'h-full min-w-0 rounded-full bg-primary transition-[width]',
                    props.indicatorClass,
                )
            "
            :style="{ width: `${props.modelValue ?? 0}%` }"
        />
    </ProgressRoot>
</template>
