<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import PatientListCardDetailsToggle from '@/Components/Patient/PatientListCardDetailsToggle.vue';
import { Collapsible, CollapsibleContent } from '@/Components/ui/collapsible';
import { patientPageCardHeaderSummaryClass } from '@/lib/patient/patientPageTypography';
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
</script>

<template>
    <div
        :class="
            cn(
                'border-border bg-surface mt-8 rounded-2xl border-2 p-5 shadow-sm sm:p-6',
                props.class,
            )
        "
    >
        <Collapsible v-model:open="open" :unmount-on-hide="false">
            <h3
                class="text-text-heading text-lg leading-snug font-bold md:text-xl"
            >
                {{ props.heading }}
            </h3>
            <p
                v-if="!open"
                :class="cn('mt-1', patientPageCardHeaderSummaryClass)"
            >
                {{ collapsedSummary }}
            </p>

            <CollapsibleContent>
                <div
                    class="border-border/70 mt-5 flex flex-col gap-4 border-t pt-5"
                >
                    <p
                        v-if="props.intro !== undefined && props.intro !== ''"
                        class="text-text-muted text-base leading-relaxed"
                    >
                        {{ props.intro }}
                    </p>

                    <slot />
                </div>
            </CollapsibleContent>

            <PatientListCardDetailsToggle
                :scroll-on-expand="false"
                :mode="open ? 'collapse' : 'expand'"
                :label="
                    t(
                        open
                            ? `${props.labelsNamespace}.listCollapseHint`
                            : `${props.labelsNamespace}.listExpandHint`,
                    )
                "
                :ariaLabel="
                    t(
                        open
                            ? `${props.labelsNamespace}.hideDetails`
                            : `${props.labelsNamespace}.showDetails`,
                    )
                "
            />
        </Collapsible>
    </div>
</template>
