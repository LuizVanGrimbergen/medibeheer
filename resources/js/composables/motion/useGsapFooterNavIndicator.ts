import type { ComponentPublicInstance, Ref } from 'vue';
import { nextTick, onMounted, onUnmounted, watch } from 'vue';
import type {
    FooterNavIndicatorMetrics,
    GsapTween,
} from '@/lib/motion/gsapMotion';
import { animateFooterNavIndicator } from '@/lib/motion/gsapMotion';
import { loadGsap } from '@/lib/motion/loadGsap';
import { resolveGsapTargetElement } from '@/lib/motion/resolveGsapTargetElement';
import type { PatientFooterNavRouteName } from '@/lib/patient/navigation/patientFooterNavClasses';

export type FooterNavLinkRefs = Partial<
    Record<PatientFooterNavRouteName, HTMLElement>
>;

export function useGsapFooterNavIndicator(
    containerRef: Ref<HTMLElement | ComponentPublicInstance | null>,
    indicatorRef: Ref<HTMLElement | ComponentPublicInstance | null>,
    activeRoute: Ref<PatientFooterNavRouteName | undefined>,
    linkRefs: FooterNavLinkRefs,
    enabled: Ref<boolean>,
): void {
    let tween: GsapTween | null = null;
    let resizeObserver: ResizeObserver | null = null;
    let resizeFrameId: number | null = null;
    let syncFrameId: number | null = null;
    let allowAnimation = false;

    const cancelResizeFrame = (): void => {
        if (resizeFrameId === null) {
            return;
        }

        globalThis.cancelAnimationFrame(resizeFrameId);
        resizeFrameId = null;
    };

    const cancelSyncFrame = (): void => {
        if (syncFrameId === null) {
            return;
        }

        globalThis.cancelAnimationFrame(syncFrameId);
        syncFrameId = null;
    };

    const scheduleSyncFromResize = (): void => {
        cancelResizeFrame();

        resizeFrameId = globalThis.requestAnimationFrame(() => {
            resizeFrameId = null;
            scheduleSyncIndicator(false);
        });
    };

    const measureActiveLink = (): FooterNavIndicatorMetrics | null => {
        const container = resolveGsapTargetElement(containerRef.value);
        const route = activeRoute.value;

        if (container === null || route === undefined) {
            return null;
        }

        const link = linkRefs[route];

        if (link === undefined) {
            return null;
        }

        const containerRect = container.getBoundingClientRect();
        const linkRect = link.getBoundingClientRect();

        return {
            left: linkRect.left - containerRect.left,
            top: linkRect.top - containerRect.top,
            width: linkRect.width,
            height: linkRect.height,
        };
    };

    const hideIndicator = async (): Promise<void> => {
        const indicator = resolveGsapTargetElement(indicatorRef.value);

        if (indicator === null) {
            return;
        }

        tween?.kill();
        allowAnimation = false;

        const gsap = await loadGsap();
        gsap.set(indicator, { opacity: 0, x: 0, width: 0 });
    };

    const syncIndicator = async (animated: boolean): Promise<void> => {
        await nextTick();

        if (!enabled.value) {
            await hideIndicator();

            return;
        }

        const indicator = resolveGsapTargetElement(indicatorRef.value);
        const metrics = measureActiveLink();

        if (indicator === null) {
            return;
        }

        tween?.kill();

        if (metrics === null) {
            await hideIndicator();

            return;
        }

        tween = await animateFooterNavIndicator(
            indicator,
            metrics,
            animated && allowAnimation,
        );

        if (!allowAnimation) {
            allowAnimation = true;
        }
    };

    const scheduleSyncIndicator = (animated: boolean): void => {
        cancelSyncFrame();

        syncFrameId = globalThis.requestAnimationFrame(() => {
            syncFrameId = null;
            void syncIndicator(animated);
        });
    };

    watch(
        activeRoute,
        () => {
            scheduleSyncIndicator(true);
        },
        { flush: 'post' },
    );

    watch(enabled, (isEnabled) => {
        if (isEnabled) {
            void loadGsap();
        }

        scheduleSyncIndicator(false);
    });

    onMounted(() => {
        if (enabled.value) {
            void loadGsap();
        }

        scheduleSyncIndicator(false);

        const container = resolveGsapTargetElement(containerRef.value);

        if (container === null) {
            return;
        }

        resizeObserver = new ResizeObserver(() => {
            if (!enabled.value) {
                return;
            }

            scheduleSyncFromResize();
        });
        resizeObserver.observe(container);
    });

    onUnmounted(() => {
        cancelSyncFrame();
        cancelResizeFrame();
        resizeObserver?.disconnect();
        resizeObserver = null;
        tween?.kill();
        tween = null;
    });
}
