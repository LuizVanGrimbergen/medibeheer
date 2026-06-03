import type {
    FooterNavIndicatorMetrics,
    GsapTween,
} from '@/lib/motion/gsapMotion';
import { animateFooterNavIndicator } from '@/lib/motion/gsapMotion';
import { loadGsap } from '@/lib/motion/loadGsap';
import { resolveGsapTargetElement } from '@/lib/motion/resolveGsapTargetElement';
import type { PatientFooterNavRouteName } from '@/lib/patient/navigation/patientFooterNavClasses';
import type { ComponentPublicInstance, Ref } from 'vue';
import { nextTick, onMounted, onUnmounted, watch } from 'vue';

export type FooterNavLinkRefs = Partial<
    Record<PatientFooterNavRouteName, HTMLElement>
>;

export function useGsapFooterNavIndicator(
    containerRef: Ref<HTMLElement | ComponentPublicInstance | null>,
    indicatorRef: Ref<HTMLElement | ComponentPublicInstance | null>,
    activeRoute: Ref<PatientFooterNavRouteName | undefined>,
    linkRefs: FooterNavLinkRefs,
): void {
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

    const syncIndicator = async (): Promise<void> => {
        await nextTick();

        const indicator = resolveGsapTargetElement(indicatorRef.value);
        const metrics = measureActiveLink();

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
        activeRoute,
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
}
