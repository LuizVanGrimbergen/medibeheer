<script setup lang="ts">
import { ChevronDown } from 'lucide-vue-next';
import { computed } from 'vue';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/Components/ui/collapsible';
import { cn } from '@/lib/utils';

const open = defineModel<boolean>('open', { required: true });

const props = defineProps<{
    heading: string;
    toggleLabel: string;
    count: number;
    collapsedOne: string;
    collapsedMany: string;
    intro?: string;
}>();

const collapsedSummary = computed((): string => {
    if (props.count === 1) {
        return props.collapsedOne;
    }

    return props.collapsedMany.replace('{count}', String(props.count));
});
</script>

<template>
    <Collapsible
        v-model:open="open"
        class="border-border bg-surface rounded-2xl border-2 shadow-sm"
    >
        <CollapsibleTrigger as-child>
            <button
                type="button"
                class="hover:bg-surface-2/80 flex w-full items-center gap-4 rounded-2xl px-5 py-5 text-left transition sm:px-6"
                :aria-label="props.toggleLabel"
            >
                <div class="min-w-0 flex-1">
                    <h3
                        class="text-text-heading text-lg leading-snug font-bold md:text-xl"
                    >
                        {{ props.heading }}
                    </h3>
                    <p
                        v-if="!open"
                        class="text-text-muted mt-1 text-base leading-relaxed"
                    >
                        {{ collapsedSummary }}
                    </p>
                </div>

                <ChevronDown
                    :size="24"
                    :class="
                        cn(
                            'text-text-muted shrink-0 transition-transform duration-200',
                            open && 'rotate-180',
                        )
                    "
                    aria-hidden="true"
                />
            </button>
        </CollapsibleTrigger>

        <CollapsibleContent
            class="border-border border-t-2 px-5 pt-5 pb-5 sm:px-6 sm:pt-6 sm:pb-6"
        >
            <p
                v-if="props.intro !== undefined && props.intro !== ''"
                class="text-text-muted mb-5 text-base leading-relaxed"
            >
                {{ props.intro }}
            </p>

            <div class="flex flex-col gap-4">
                <slot />
            </div>
        </CollapsibleContent>
    </Collapsible>
</template>
