import { effectScope, ref } from 'vue';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';

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

describe('usePatientShellDialogChrome', () => {
    beforeEach(() => {
        finishHandlers.length = 0;
        vi.resetModules();
    });

    afterEach(() => {
        finishHandlers.length = 0;
    });

    async function loadModule() {
        return import('@/composables/patient/usePatientShellDialogChrome');
    }

    function runFinishHandlers(): void {
        for (const handler of [...finishHandlers]) {
            handler();
        }
    }

    it('keeps the footer hidden on full-page shell routes after Inertia finish', async () => {
        const {
            isPatientShellFooterHidden,
            resetPatientShellDialogChromeOpenCount,
            usePatientShellPageChrome,
        } = await loadModule();

        const scope = effectScope();

        scope.run(() => {
            usePatientShellPageChrome();
        });

        expect(isPatientShellFooterHidden.value).toBe(true);

        runFinishHandlers();

        expect(isPatientShellFooterHidden.value).toBe(true);

        scope.stop();

        expect(isPatientShellFooterHidden.value).toBe(false);
    });

    it('clears dialog footer-hide state on Inertia finish without affecting page routes', async () => {
        const {
            isPatientShellFooterHidden,
            resetPatientShellDialogChromeOpenCount,
            usePatientShellDialogChromeSync,
            usePatientShellPageChrome,
        } = await loadModule();

        const pageScope = effectScope();
        pageScope.run(() => {
            usePatientShellPageChrome();
        });

        const dialogScope = effectScope();
        const open = ref(true);

        dialogScope.run(() => {
            usePatientShellDialogChromeSync(open);
        });

        expect(isPatientShellFooterHidden.value).toBe(true);

        resetPatientShellDialogChromeOpenCount();

        expect(isPatientShellFooterHidden.value).toBe(true);

        open.value = false;

        expect(isPatientShellFooterHidden.value).toBe(true);

        pageScope.stop();
        dialogScope.stop();
    });
});
