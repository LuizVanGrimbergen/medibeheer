<script setup lang="ts">
import { ChevronDown } from 'lucide-vue-next';
import { nextTick, useTemplateRef } from 'vue';
import { Button } from '@/Components/ui/button';
import { CollapsibleTrigger } from '@/Components/ui/collapsible';
import { scrollPatientListCardDetailsIntoView } from '@/lib/patient/patientListCardDetailsExpandScroll';
import {
    patientPageCardDetailsButtonClass,
    patientPageCardDetailsChevronClass,
    patientPageCardDetailsCollapseWrapperClass,
    patientPageCardDetailsExpandWrapperClass,
} from '@/lib/patient/patientPageTypography';
import { cn } from '@/lib/utils';

const props = defineProps<{
    mode: 'expand' | 'collapse';
    label: string;
    ariaLabel: string;
    wrapperClass?: string;
}>();

const wrapperRef = useTemplateRef<HTMLElement>('wrapper');

async function onExpandClick(): Promise<void> {
    if (props.mode !== 'expand') {
        return;
    }

    await nextTick();
    scrollPatientListCardDetailsIntoView(wrapperRef.value);
}
</script>

<template>
    <div
        ref="wrapper"
        :class="
            cn(
                props.mode === 'expand'
                    ? patientPageCardDetailsExpandWrapperClass
                    : patientPageCardDetailsCollapseWrapperClass,
                props.wrapperClass,
            )
        "
    >
        <CollapsibleTrigger as-child>
            <Button
                type="button"
                variant="outline"
                :class="patientPageCardDetailsButtonClass"
                :aria-label="props.ariaLabel"
                @click="onExpandClick"
            >
                <span class="min-w-0">{{ props.label }}</span>
                <ChevronDown
                    :class="
                        cn(
                            patientPageCardDetailsChevronClass,
                            props.mode === 'collapse' && 'rotate-180',
                        )
                    "
                    aria-hidden="true"
                />
            </Button>
        </CollapsibleTrigger>
    </div>
</template>
