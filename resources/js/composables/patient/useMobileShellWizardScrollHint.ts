import type { Ref } from 'vue';
import { nextTick, onUnmounted, ref, watch } from 'vue';

const FOOTER_VISIBILITY_INSET_PX = 8;

export function useMobileShellWizardScrollHint(
    scrollRef: Ref<HTMLElement | null>,
    footerRef: Ref<HTMLElement | null>,
    active: Ref<boolean>,
): { showScrollHint: Ref<boolean> } {
    const showScrollHint = ref(false);

    let intersectionObserver: IntersectionObserver | null = null;
    let resizeObserver: ResizeObserver | null = null;
    let scrollElementWithListener: HTMLElement | null = null;
    let remeasureFrameId: number | null = null;
    let openDelayTimeoutId: ReturnType<typeof globalThis.setTimeout> | null =
        null;

    function cancelRemeasure(): void {
        if (remeasureFrameId !== null) {
            globalThis.cancelAnimationFrame(remeasureFrameId);
            remeasureFrameId = null;
        }
    }

    function cancelOpenDelay(): void {
        if (openDelayTimeoutId !== null) {
            globalThis.clearTimeout(openDelayTimeoutId);
            openDelayTimeoutId = null;
        }
    }

    function updateScrollHint(): void {
        const scrollElement = scrollRef.value;
        const footerElement = footerRef.value;

        if (!active.value || scrollElement === null || footerElement === null) {
            showScrollHint.value = false;

            return;
        }

        const hasOverflow =
            scrollElement.scrollHeight >
            scrollElement.clientHeight + FOOTER_VISIBILITY_INSET_PX;

        if (!hasOverflow) {
            showScrollHint.value = false;

            return;
        }

        const scrollRect = scrollElement.getBoundingClientRect();
        const footerRect = footerElement.getBoundingClientRect();
        const footerIsVisible =
            footerRect.top < scrollRect.bottom - FOOTER_VISIBILITY_INSET_PX &&
            footerRect.bottom > scrollRect.top + FOOTER_VISIBILITY_INSET_PX;

        showScrollHint.value = !footerIsVisible;
    }

    function scheduleRemeasure(): void {
        cancelRemeasure();

        remeasureFrameId = globalThis.requestAnimationFrame(() => {
            remeasureFrameId = null;
            updateScrollHint();
        });
    }

    function disconnectObservers(): void {
        intersectionObserver?.disconnect();
        intersectionObserver = null;
        resizeObserver?.disconnect();
        resizeObserver = null;

        if (scrollElementWithListener !== null) {
            scrollElementWithListener.removeEventListener(
                'scroll',
                updateScrollHint,
            );
            scrollElementWithListener = null;
        }
    }

    function connectObservers(): void {
        disconnectObservers();

        const scrollElement = scrollRef.value;
        const footerElement = footerRef.value;

        if (
            !active.value ||
            scrollElement === null ||
            footerElement === null ||
            !scrollElement.contains(footerElement)
        ) {
            showScrollHint.value = false;

            return;
        }

        scrollElement.addEventListener('scroll', updateScrollHint, {
            passive: true,
        });
        scrollElementWithListener = scrollElement;

        intersectionObserver = new IntersectionObserver(
            () => {
                updateScrollHint();
            },
            {
                root: scrollElement,
                threshold: 0,
                rootMargin: `0px 0px -${FOOTER_VISIBILITY_INSET_PX}px 0px`,
            },
        );
        intersectionObserver.observe(footerElement);

        resizeObserver = new ResizeObserver(() => {
            scheduleRemeasure();
        });
        resizeObserver.observe(scrollElement);
        resizeObserver.observe(footerElement);

        scheduleRemeasure();
    }

    async function syncObservers(): Promise<void> {
        if (!active.value) {
            showScrollHint.value = false;
            disconnectObservers();

            return;
        }

        await nextTick();
        connectObservers();

        cancelOpenDelay();
        openDelayTimeoutId = globalThis.setTimeout(() => {
            openDelayTimeoutId = null;
            updateScrollHint();
        }, 400);
    }

    watch(
        () => [scrollRef.value, footerRef.value, active.value] as const,
        () => {
            void syncObservers();
        },
        { immediate: true, flush: 'post' },
    );

    onUnmounted(() => {
        cancelRemeasure();
        cancelOpenDelay();
        disconnectObservers();
    });

    return { showScrollHint };
}
