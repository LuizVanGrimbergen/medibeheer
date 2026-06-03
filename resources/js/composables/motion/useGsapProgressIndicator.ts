import { animateProgressWidth } from '@/lib/motion/gsapMotion';
import { resolveGsapTargetElement } from '@/lib/motion/resolveGsapTargetElement';
import {
    nextTick,
    onMounted,
    onUnmounted,
    watch,
    type ComponentPublicInstance,
    type Ref,
} from 'vue';

export function useGsapProgressIndicator(
    indicatorRef: Ref<HTMLElement | ComponentPublicInstance | null>,
    modelValue: Ref<number | null | undefined>,
): void {
    let tween: ReturnType<typeof animateProgressWidth> | null = null;

    const applyWidth = (): void => {
        const element = resolveGsapTargetElement(indicatorRef.value);

        if (element === null) {
            return;
        }

        tween?.kill();
        tween = animateProgressWidth(element, modelValue.value ?? 0);
    };

    watch(modelValue, () => {
        void nextTick(applyWidth);
    });

    onMounted(() => {
        void nextTick(applyWidth);
    });

    onUnmounted(() => {
        tween?.kill();
        tween = null;
    });
}
