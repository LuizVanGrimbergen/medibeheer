<script setup lang="ts">
import { ChevronDown } from 'lucide-vue-next';
import PatientListCardDetailsToggle from '@/Components/Patient/PatientListCardDetailsToggle.vue';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/Components/ui/collapsible';
import { mobileShellSectionSubHeadingClass } from '@/lib/shell/mobileShellTypography';
import { cn } from '@/lib/utils';

const open = defineModel<boolean>('open', { required: true });

const props = withDefaults(
    defineProps<{
        heading: string;
        toggleLabel: string;
        iconWrapperClass: string;
        subheading?: string;
        collapsedSummary?: string;
        collapsedSummaryClass?: string;
        contentClass?: string;
        toggleVariant?: 'header' | 'footer-button';
        expandLabel?: string;
        collapseLabel?: string;
    }>(),
    {
        contentClass:
            'border-t border-border px-4 pb-4 pt-4 md:px-5 md:pb-5 md:pt-4',
        toggleVariant: 'header',
    },
);

const usesFooterButtonToggle = props.toggleVariant === 'footer-button';
</script>

<template>
    <Collapsible
        v-model:open="open"
        class="border-border bg-surface rounded-2xl border-2 shadow-sm"
    >
        <CollapsibleTrigger v-if="!usesFooterButtonToggle" as-child>
            <button
                type="button"
                :class="
                    cn(
                        'hover:bg-surface-2 flex w-full items-center gap-3 px-4 py-4 text-left transition md:px-5 md:py-3.5',
                        open ? 'rounded-t-2xl' : 'rounded-2xl',
                    )
                "
            >
                <div
                    :class="
                        cn(
                            'flex size-10 shrink-0 items-center justify-center rounded-full [&_svg]:size-5 [&_svg]:shrink-0 [&_svg]:stroke-[1.75]',
                            props.iconWrapperClass,
                        )
                    "
                    aria-hidden="true"
                >
                    <slot name="icon" />
                </div>

                <div class="min-w-0 flex-1">
                    <h2 :class="mobileShellSectionSubHeadingClass">
                        {{ props.heading }}
                    </h2>
                    <p
                        v-if="
                            props.subheading !== undefined &&
                            props.subheading !== ''
                        "
                        class="text-text-muted mt-0.5 text-sm"
                    >
                        {{ props.subheading }}
                    </p>
                    <p
                        v-if="
                            !open &&
                            props.collapsedSummary !== undefined &&
                            props.collapsedSummary !== ''
                        "
                        :class="
                            cn(
                                'text-text-muted mt-0.5 text-sm',
                                props.collapsedSummaryClass,
                            )
                        "
                    >
                        {{ props.collapsedSummary }}
                    </p>
                </div>

                <ChevronDown
                    :size="20"
                    :stroke-width="1.75"
                    :class="
                        cn(
                            'text-text-muted shrink-0 transition-transform duration-200',
                            open && 'rotate-180',
                        )
                    "
                    aria-hidden="true"
                />
                <span class="sr-only">{{ props.toggleLabel }}</span>
            </button>
        </CollapsibleTrigger>

        <div
            v-else
            class="flex w-full items-center gap-3 px-4 py-4 md:px-5 md:py-3.5"
            :class="open ? 'rounded-t-2xl' : 'rounded-2xl'"
        >
            <div
                :class="
                    cn(
                        'flex size-10 shrink-0 items-center justify-center rounded-full [&_svg]:size-5 [&_svg]:shrink-0 [&_svg]:stroke-[1.75]',
                        props.iconWrapperClass,
                    )
                "
                aria-hidden="true"
            >
                <slot name="icon" />
            </div>

            <div class="min-w-0 flex-1">
                <h2 :class="mobileShellSectionSubHeadingClass">
                    {{ props.heading }}
                </h2>
                <p
                    v-if="
                        props.subheading !== undefined && props.subheading !== ''
                    "
                    class="text-text-muted mt-0.5 text-sm"
                >
                    {{ props.subheading }}
                </p>
                <p
                    v-if="
                        !open &&
                        props.collapsedSummary !== undefined &&
                        props.collapsedSummary !== ''
                    "
                    :class="
                        cn(
                            'text-text-muted mt-0.5 text-sm',
                            props.collapsedSummaryClass,
                        )
                    "
                >
                    {{ props.collapsedSummary }}
                </p>
            </div>
        </div>

        <CollapsibleContent>
            <div :class="props.contentClass">
                <slot />
            </div>
        </CollapsibleContent>

        <PatientListCardDetailsToggle
            v-if="usesFooterButtonToggle"
            :mode="open ? 'collapse' : 'expand'"
            :label="open ? (props.collapseLabel ?? '') : (props.expandLabel ?? '')"
            :ariaLabel="props.toggleLabel"
            wrapper-class="px-4 pb-4 md:px-5 md:pb-5"
        />
    </Collapsible>
</template>
