<script setup lang="ts">
import type { PrimitiveProps } from 'reka-ui';
import { Primitive } from 'reka-ui';
import type { HTMLAttributes } from 'vue';
import type { ComponentPublicInstance } from 'vue';
import { computed, ref } from 'vue';
import { useGsapActionConfirm } from '@/composables/motion/useGsapActionConfirm';
import { useGsapButtonPress } from '@/composables/motion/useGsapButtonPress';
import { useGsapSuccessFlash } from '@/composables/motion/useGsapSuccessFlash';
import { cn } from '@/lib/utils';
import type { ButtonVariants } from '.';
import { buttonSuccessClass, buttonVariants } from '.';

interface Props extends PrimitiveProps {
    variant?: ButtonVariants['variant'];
    size?: ButtonVariants['size'];
    class?: HTMLAttributes['class'];
    motionPress?: boolean;
    success?: boolean;
    successFlash?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    as: 'button',
    motionPress: true,
    success: false,
    successFlash: false,
});

const rootRef = ref<HTMLElement | ComponentPublicInstance | null>(null);
const flashOverlayRef = ref<HTMLElement | null>(null);

const motionLayersEnabled = computed(() => !props.asChild);
const motionPressEnabled = computed(
    () =>
        props.motionPress &&
        motionLayersEnabled.value &&
        !props.success,
);
const successFlashActive = computed(
    () => props.successFlash && motionLayersEnabled.value,
);
const successConfirmActive = computed(
    () => props.success && motionLayersEnabled.value,
);

useGsapButtonPress(rootRef, motionPressEnabled);
useGsapSuccessFlash(flashOverlayRef, successFlashActive);
useGsapActionConfirm(rootRef, successConfirmActive);

const rootClass = computed(() =>
    cn(
        buttonVariants({ variant: props.variant, size: props.size }),
        motionLayersEnabled.value &&
            'relative overflow-hidden [&>:not([aria-hidden=true])]:relative [&>:not([aria-hidden=true])]:z-10',
        props.success && buttonSuccessClass,
        props.class,
    ),
);
</script>

<template>
    <Primitive
        ref="rootRef"
        :as="as"
        :as-child="asChild"
        :class="rootClass"
    >
        <span
            v-if="motionLayersEnabled"
            ref="flashOverlayRef"
            class="pointer-events-none absolute inset-0 z-0 rounded-[inherit] bg-success/15 opacity-0"
            aria-hidden="true"
        />
        <slot />
    </Primitive>
</template>
