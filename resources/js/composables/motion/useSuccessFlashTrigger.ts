import type { Ref } from 'vue';
import { nextTick, ref, watch } from 'vue';

export function useSuccessFlashTrigger(isSuccess: Ref<boolean>): Ref<boolean> {
    const shouldFlash = ref(false);

    watch(isSuccess, (success, previousSuccess) => {
        if (!success || previousSuccess === true) {
            return;
        }

        shouldFlash.value = true;
    });

    watch(shouldFlash, (flash) => {
        if (!flash) {
            return;
        }

        void nextTick(() => {
            shouldFlash.value = false;
        });
    });

    return shouldFlash;
}
