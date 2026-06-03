import type { GsapTween } from '@/lib/motion/gsapMotion';
import {
    animateButtonPress,
    resetButtonPressScale,
} from '@/lib/motion/gsapMotion';
import { resolveGsapTargetElement } from '@/lib/motion/resolveGsapTargetElement';
import type { ComponentPublicInstance, Ref } from 'vue';
import { onMounted, onUnmounted, watch } from 'vue';

export function useGsapButtonPress(
    targetRef: Ref<HTMLElement | ComponentPublicInstance | null>,
    enabled: Ref<boolean>,
): void {
    let tween: GsapTween | null = null;
    let element: HTMLElement | null = null;

    const releasePress = (): void => {
        if (element === null || !enabled.value) {
            return;
        }

        tween?.kill();
        void animateButtonPress(element, false).then((nextTween) => {
            tween = nextTween;
        });
    };

    const onPointerDown = (event: PointerEvent): void => {
        if (!enabled.value || element === null) {
            return;
        }

        if (event.button !== 0) {
            return;
        }

        if (
            element.hasAttribute('disabled') ||
            element.getAttribute('aria-disabled') === 'true'
        ) {
            return;
        }

        tween?.kill();
        void animateButtonPress(element, true).then((nextTween) => {
            tween = nextTween;
        });
    };

    const attachListeners = (): void => {
        detachListeners();

        element = resolveGsapTargetElement(targetRef.value);

        if (element === null) {
            return;
        }

        element.addEventListener('pointerdown', onPointerDown);
        element.addEventListener('pointerup', releasePress);
        element.addEventListener('pointerleave', releasePress);
        element.addEventListener('pointercancel', releasePress);
    };

    const detachListeners = (): void => {
        if (element === null) {
            return;
        }

        element.removeEventListener('pointerdown', onPointerDown);
        element.removeEventListener('pointerup', releasePress);
        element.removeEventListener('pointerleave', releasePress);
        element.removeEventListener('pointercancel', releasePress);

        resetButtonPressScale(element);
        element = null;
    };

    onMounted(() => {
        watch(
            [enabled, targetRef],
            () => {
                if (!enabled.value) {
                    detachListeners();

                    return;
                }

                attachListeners();
            },
            { immediate: true, flush: 'post' },
        );
    });

    onUnmounted(() => {
        tween?.kill();
        tween = null;
        detachListeners();
    });
}
