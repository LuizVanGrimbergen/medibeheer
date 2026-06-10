<script setup lang="ts">
import { ChevronDown } from 'lucide-vue-next';
import { toRef, useTemplateRef } from 'vue';
import { useI18n } from 'vue-i18n';
import MobileShellWizardFooterCard from '@/Components/shell/MobileShellWizardFooterCard.vue';
import { useMobileShellWizardScrollHint } from '@/composables/patient/useMobileShellWizardScrollHint';
import { useMobileShellWizardScrollReset } from '@/composables/patient/useMobileShellWizardScrollReset';
import {
    mobileShellWizardScrollBodyClass,
    mobileShellWizardShellClass,
} from '@/lib/shell/mobileShellDialogLayout';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        active?: boolean;
        /** When set, scroll position resets to top whenever this value changes (wizard step). */
        stepKey?: number | string;
    }>(),
    {
        active: true,
    },
);

const scrollRef = useTemplateRef<HTMLElement>('scrollRef');
const footerRef = useTemplateRef<HTMLElement>('footerRef');
const activeRef = toRef(() => props.active);

const { showScrollHint } = useMobileShellWizardScrollHint(
    scrollRef,
    footerRef,
    activeRef,
);

const stepKeyRef = toRef(() => props.stepKey);

useMobileShellWizardScrollReset(scrollRef, stepKeyRef, activeRef);

const { t } = useI18n();
</script>

<template>
    <div :class="mobileShellWizardShellClass">
        <div ref="scrollRef" :class="mobileShellWizardScrollBodyClass">
            <div
                class="flex min-h-full min-w-0 flex-col space-y-3 pb-[max(0.75rem,env(safe-area-inset-bottom,0px))] sm:space-y-4"
            >
                <slot />

                <MobileShellWizardFooterCard
                    v-if="$slots.footer"
                    class="mt-auto shrink-0"
                >
                    <div ref="footerRef">
                        <slot name="footer" />
                    </div>
                </MobileShellWizardFooterCard>
            </div>
        </div>

        <Transition
            enter-active-class="transition-opacity duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="showScrollHint"
                class="pointer-events-none absolute inset-x-0 bottom-0 z-20 flex flex-col items-center"
                role="status"
                aria-live="polite"
            >
                <div
                    class="from-surface via-surface/95 flex w-full flex-col items-center bg-linear-to-t from-40% to-transparent px-4 pt-6 pb-2"
                >
                    <p
                        class="text-primary flex items-center gap-2 text-center text-base leading-snug font-bold md:text-lg"
                    >
                        <ChevronDown
                            :class="
                                cn(
                                    'size-6 shrink-0 motion-safe:animate-bounce md:size-7',
                                )
                            "
                            aria-hidden="true"
                        />
                        {{ t('patient.formWizard.scrollDownForActions') }}
                        <ChevronDown
                            :class="
                                cn(
                                    'size-6 shrink-0 motion-safe:animate-bounce md:size-7',
                                )
                            "
                            aria-hidden="true"
                        />
                    </p>
                </div>
            </div>
        </Transition>
    </div>
</template>
