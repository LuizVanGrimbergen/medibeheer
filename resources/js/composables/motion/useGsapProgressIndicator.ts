import type { ComponentPublicInstance, Ref } from 'vue';
import { nextTick, onMounted, onUnmounted, watch } from 'vue';
import type { GsapTween } from '@/lib/motion/gsapMotion';
import { animateProgressWidth } from '@/lib/motion/gsapMotion';
import { resolveGsapTargetElement } from '@/lib/motion/resolveGsapTargetElement';

export function useGsapProgressIndicator(
    indicatorRef: Ref<HTMLElement | ComponentPublicInstance | null>,
    modelValue: Ref<number | null | undefined>,
): void {
    let tween: GsapTween | null = null;

    const applyWidth = async (): Promise<void> => {
        const element = resolveGsapTargetElement(indicatorRef.value);

        if (element === null) {
            return;
        }

        tween?.kill();
        tween = await animateProgressWidth(element, modelValue.value ?? 0);
    };

    watch(modelValue, () => {
        void nextTick(() => {
            void applyWidth();
        });
    });

    onMounted(() => {
        void nextTick(() => {
            void applyWidth();
        });
    });

    onUnmounted(() => {
        tween?.kill();
        tween = null;
    });
}
