<script setup lang="ts">
import { ChevronDown } from 'lucide-vue-next';
import { Button } from '@/Components/ui/button';
import { CollapsibleTrigger } from '@/Components/ui/collapsible';
import {
    patientPageCardDetailsButtonClass,
    patientPageCardDetailsChevronClass,
    patientPageCardFooterSectionClass,
} from '@/lib/patient/patientPageTypography';
import { cn } from '@/lib/utils';

const props = defineProps<{
    mode: 'expand' | 'collapse';
    label: string;
    ariaLabel: string;
    wrapperClass?: string;
}>();
</script>

<template>
    <div
        :class="
            cn(
                props.mode === 'expand'
                    ? patientPageCardFooterSectionClass
                    : 'border-t border-border/50 pt-4',
                props.wrapperClass,
            )
        "
    >
        <CollapsibleTrigger as-child>
            <Button
                type="button"
                variant="ghost"
                :class="patientPageCardDetailsButtonClass"
                :aria-label="props.ariaLabel"
            >
                {{ props.label }}
                <span
                    :class="
                        cn(
                            patientPageCardDetailsChevronClass,
                            props.mode === 'collapse' && '[&_svg]:rotate-180',
                        )
                    "
                    aria-hidden="true"
                >
                    <ChevronDown />
                </span>
            </Button>
        </CollapsibleTrigger>
    </div>
</template>
