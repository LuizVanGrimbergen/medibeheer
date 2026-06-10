<script setup lang="ts">
import { Users } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import CollapsibleSectionCard from '@/Components/ui/collapsible-section/CollapsibleSectionCard.vue';
import { mobileShellSectionBodyTextClass } from '@/lib/shell/mobileShellTypography';
import { cn } from '@/lib/utils';

const open = defineModel<boolean>('open', { required: true });

const props = withDefaults(
    defineProps<{
        heading: string;
        count: number;
        collapsedOne: string;
        collapsedMany: string;
        labelsNamespace?: 'patient.family' | 'patient.doctors';
        intro?: string;
        class?: string;
    }>(),
    {
        labelsNamespace: 'patient.family',
    },
);

const { t } = useI18n();

const collapsedSummary = computed((): string => {
    if (props.count === 1) {
        return props.collapsedOne;
    }

    return props.collapsedMany.replace('{count}', String(props.count));
});

const toggleLabel = computed((): string =>
    t(
        open.value
            ? `${props.labelsNamespace}.hideDetails`
            : `${props.labelsNamespace}.showDetails`,
    ),
);
</script>

<template>
    <div :class="cn('mt-8', props.class)">
        <CollapsibleSectionCard
            v-model:open="open"
            :heading="props.heading"
            :toggle-label="toggleLabel"
            :collapsed-summary="collapsedSummary"
            icon-wrapper-class="bg-primary/10 text-primary"
            content-class="flex flex-col gap-4"
        >
            <template #icon>
                <Users aria-hidden="true" />
            </template>

            <p
                v-if="props.intro !== undefined && props.intro !== ''"
                :class="mobileShellSectionBodyTextClass"
            >
                {{ props.intro }}
            </p>

            <slot />
        </CollapsibleSectionCard>
    </div>
</template>
