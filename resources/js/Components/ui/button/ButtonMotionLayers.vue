<script setup lang="ts">
import type { ComponentPublicInstance, ComputedRef, Ref } from 'vue';
import { useGsapActionConfirm } from '@/composables/motion/useGsapActionConfirm';
import { useGsapButtonPress } from '@/composables/motion/useGsapButtonPress';
import { useGsapSuccessFlash } from '@/composables/motion/useGsapSuccessFlash';

export type ButtonMotionBindings = {
    rootRef: Ref<HTMLElement | ComponentPublicInstance | null>;
    flashOverlayRef: Ref<HTMLElement | null>;
    motionPressEnabled: Ref<boolean> | ComputedRef<boolean>;
    successFlashActive: Ref<boolean> | ComputedRef<boolean>;
    successConfirmActive: Ref<boolean> | ComputedRef<boolean>;
};

const props = defineProps<{
    bindings: ButtonMotionBindings;
}>();

useGsapButtonPress(props.bindings.rootRef, props.bindings.motionPressEnabled);
useGsapSuccessFlash(
    props.bindings.flashOverlayRef,
    props.bindings.successFlashActive,
);
useGsapActionConfirm(
    props.bindings.rootRef,
    props.bindings.successConfirmActive,
);
</script>

<template>
    <span aria-hidden="true" class="hidden" />
</template>
