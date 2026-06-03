<script setup lang="ts">
import { reactiveOmit } from '@vueuse/core';
import type { ProgressRootProps } from 'reka-ui';
import { ProgressIndicator, ProgressRoot } from 'reka-ui';
import type { HTMLAttributes } from 'vue';
import type { ComponentPublicInstance } from 'vue';
import { ref, toRef } from 'vue';
import { useGsapProgressIndicator } from '@/composables/motion/useGsapProgressIndicator';
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

const indicatorRef = ref<HTMLElement | ComponentPublicInstance | null>(null);

useGsapProgressIndicator(indicatorRef, toRef(() => props.modelValue));
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
            ref="indicatorRef"
            :class="
                cn(
                    'h-full min-w-0 rounded-full bg-primary',
                    props.indicatorClass,
                )
            "
        />
    </ProgressRoot>
</template>
