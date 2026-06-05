import type { ComponentPublicInstance, Ref } from 'vue';
import { nextTick, onMounted, onUnmounted, watch } from 'vue';
import type {
    FooterNavIndicatorMetrics,
    GsapTween,
} from '@/lib/motion/gsapMotion';
import { animateFooterNavIndicator } from '@/lib/motion/gsapMotion';
import { loadGsap } from '@/lib/motion/loadGsap';
import { resolveGsapTargetElement } from '@/lib/motion/resolveGsapTargetElement';

export type SegmentedToggleTabRefs = Record<string, HTMLElement>;

export function useGsapSegmentedToggleIndicator(
    containerRef: Ref<HTMLElement | ComponentPublicInstance | null>,
    indicatorRef: Ref<HTMLElement | ComponentPublicInstance | null>,
    activeValue: Ref<string>,
    tabRefs: SegmentedToggleTabRefs,
): { syncIndicator: () => Promise<void> } {
    let tween: GsapTween | null = null;
    let resizeObserver: ResizeObserver | null = null;
    let resizeFrameId: number | null = null;
    let allowAnimation = false;

    const cancelResizeFrame = (): void => {
        if (resizeFrameId === null) {
            return;
        }

        globalThis.cancelAnimationFrame(resizeFrameId);
        resizeFrameId = null;
    };

    const scheduleSyncFromResize = (): void => {
        cancelResizeFrame();

        resizeFrameId = globalThis.requestAnimationFrame(() => {
            resizeFrameId = null;
            void syncIndicator();
        });
    };

    const measureActiveTab = (): FooterNavIndicatorMetrics | null => {
        const container = resolveGsapTargetElement(containerRef.value);
        const value = activeValue.value;
        const tab = tabRefs[value];

        if (container === null || tab === undefined) {
            return null;
        }

        const containerRect = container.getBoundingClientRect();
        const tabRect = tab.getBoundingClientRect();

        return {
            left: tabRect.left - containerRect.left,
            top: tabRect.top - containerRect.top,
            width: tabRect.width,
            height: tabRect.height,
        };
    };

    const syncIndicator = async (): Promise<void> => {
        await nextTick();

        const indicator = resolveGsapTargetElement(indicatorRef.value);
        const metrics = measureActiveTab();

        if (indicator === null) {
            return;
        }

        tween?.kill();

        if (metrics === null) {
            const gsap = await loadGsap();
            gsap.set(indicator, { opacity: 0 });

            return;
        }

        tween = await animateFooterNavIndicator(
            indicator,
            metrics,
            allowAnimation,
        );

        if (!allowAnimation) {
            allowAnimation = true;
        }
    };

    watch(
        activeValue,
        () => {
            void syncIndicator();
        },
        { flush: 'post' },
    );

    onMounted(() => {
        void syncIndicator();

        const container = resolveGsapTargetElement(containerRef.value);

        if (container === null) {
            return;
        }

        resizeObserver = new ResizeObserver(() => {
            scheduleSyncFromResize();
        });
        resizeObserver.observe(container);
    });

    onUnmounted(() => {
        cancelResizeFrame();
        resizeObserver?.disconnect();
        resizeObserver = null;
        tween?.kill();
        tween = null;
    });

    return { syncIndicator };
}
