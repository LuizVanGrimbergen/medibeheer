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
        class="rounded-2xl border-2 border-border bg-surface shadow-sm"
    >
        <CollapsibleTrigger as-child>
            <button
                type="button"
                class="flex w-full items-center gap-4 rounded-2xl px-5 py-5 text-left transition hover:bg-surface-2/80 sm:px-6"
                :aria-label="props.toggleLabel"
            >
                <div class="min-w-0 flex-1">
                    <h3 class="text-lg font-bold leading-snug text-text-heading md:text-xl">
                        {{ props.heading }}
                    </h3>
                    <p
                        v-if="!open"
                        class="mt-1 text-base leading-relaxed text-text-muted"
                    >
                        {{ collapsedSummary }}
                    </p>
                </div>

                <ChevronDown
                    :size="24"
                    :class="
                        cn(
                            'shrink-0 text-text-muted transition-transform duration-200',
                            open && 'rotate-180',
                        )
                    "
                    aria-hidden="true"
                />
            </button>
        </CollapsibleTrigger>

        <CollapsibleContent class="border-t-2 border-border px-5 pb-5 pt-5 sm:px-6 sm:pb-6 sm:pt-6">
            <p
                v-if="props.intro !== undefined && props.intro !== ''"
                class="mb-5 text-base leading-relaxed text-text-muted"
            >
                {{ props.intro }}
            </p>

            <div class="flex flex-col gap-4">
                <slot />
            </div>
        </CollapsibleContent>
    </Collapsible>
</template>
