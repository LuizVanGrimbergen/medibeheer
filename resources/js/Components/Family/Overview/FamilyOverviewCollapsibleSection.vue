<script setup lang="ts">
import { ChevronDown } from 'lucide-vue-next';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/Components/ui/collapsible';
import { cn } from '@/lib/utils';

const open = defineModel<boolean>('open', { required: true });

const props = withDefaults(
    defineProps<{
        heading: string;
        toggleLabel: string;
        iconWrapperClass: string;
        collapsedSummary?: string;
        collapsedSummaryClass?: string;
        contentClass?: string;
    }>(),
    {
        contentClass:
            'border-t border-border px-4 pb-4 pt-4 md:px-5 md:pb-5 md:pt-4',
    },
);
</script>

<template>
    <Collapsible
        v-model:open="open"
        class="rounded-2xl border border-border bg-surface shadow-sm"
    >
        <CollapsibleTrigger as-child>
            <button
                type="button"
                class="flex w-full items-center gap-3 rounded-2xl px-4 py-4 text-left transition hover:bg-surface-2 md:px-5 md:py-3.5"
            >
                <div
                    :class="
                        cn(
                            'flex size-10 shrink-0 items-center justify-center rounded-full',
                            props.iconWrapperClass,
                        )
                    "
                    aria-hidden="true"
                >
                    <slot name="icon" />
                </div>

                <div class="min-w-0 flex-1">
                    <h2 class="text-lg font-semibold text-text-heading md:text-base">
                        {{ props.heading }}
                    </h2>
                    <p
                        v-if="
                            !open &&
                                props.collapsedSummary !== undefined &&
                                props.collapsedSummary !== ''
                        "
                        :class="
                            cn(
                                'mt-0.5 text-sm text-text-muted',
                                props.collapsedSummaryClass,
                            )
                        "
                    >
                        {{ props.collapsedSummary }}
                    </p>
                </div>

                <ChevronDown
                    :size="20"
                    :class="
                        cn(
                            'shrink-0 text-text-muted transition-transform duration-200',
                            open && 'rotate-180',
                        )
                    "
                    aria-hidden="true"
                />
                <span class="sr-only">{{ props.toggleLabel }}</span>
            </button>
        </CollapsibleTrigger>

        <CollapsibleContent>
            <div :class="props.contentClass">
                <slot />
            </div>
        </CollapsibleContent>
    </Collapsible>
</template>
