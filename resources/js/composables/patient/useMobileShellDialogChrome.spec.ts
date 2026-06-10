import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { effectScope, ref } from 'vue';

const finishHandlers: Array<() => void> = [];

vi.mock('@inertiajs/vue3', () => ({
    router: {
        on: vi.fn((event: string, handler: () => void) => {
            if (event === 'finish') {
                finishHandlers.push(handler);
            }

            return () => {
                const index = finishHandlers.indexOf(handler);

                if (index >= 0) {
                    finishHandlers.splice(index, 1);
                }
            };
        }),
    },
}));

describe('useMobileShellDialogChrome', () => {
    beforeEach(() => {
        finishHandlers.length = 0;
        vi.resetModules();
    });

    afterEach(() => {
        finishHandlers.length = 0;
    });

    async function loadModule() {
        return import('@/composables/patient/useMobileShellDialogChrome');
    }

    function runFinishHandlers(): void {
        for (const handler of [...finishHandlers]) {
            handler();
        }
    }

    it('keeps the footer hidden on full-page shell routes after Inertia finish', async () => {
        const {
            isMobileShellFooterHidden,
            useMobileShellPageChrome,
        } = await loadModule();

        const scope = effectScope();

        scope.run(() => {
            useMobileShellPageChrome();
        });

        expect(isMobileShellFooterHidden.value).toBe(true);

        runFinishHandlers();

        expect(isMobileShellFooterHidden.value).toBe(true);

        scope.stop();

        expect(isMobileShellFooterHidden.value).toBe(false);
    });

    it('clears dialog footer-hide state on Inertia finish without affecting page routes', async () => {
        const {
            isMobileShellFooterHidden,
            resetMobileShellDialogChromeOpenCount,
            useMobileShellDialogChromeSync,
            useMobileShellPageChrome,
        } = await loadModule();

        const pageScope = effectScope();
        pageScope.run(() => {
            useMobileShellPageChrome();
        });

        const dialogScope = effectScope();
        const open = ref(true);

        dialogScope.run(() => {
            useMobileShellDialogChromeSync(open);
        });

        expect(isMobileShellFooterHidden.value).toBe(true);

        resetMobileShellDialogChromeOpenCount();

        expect(isMobileShellFooterHidden.value).toBe(true);

        open.value = false;

        expect(isMobileShellFooterHidden.value).toBe(true);

        pageScope.stop();
        dialogScope.stop();
    });
});
