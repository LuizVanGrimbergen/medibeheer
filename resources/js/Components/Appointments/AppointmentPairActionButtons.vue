<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Button, buttonVariants } from '@/Components/ui/button';
import {
    patientAppointmentFormPrimaryPairButtonClass,
    patientSoftDangerActionButtonClass,
} from '@/lib/patient/appointments/ui/patientSoftDangerActionButtonClass';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        disabled?: boolean;
        showSecondary?: boolean;
        centered?: boolean;
        primaryClass?: string;
        secondaryClass?: string;
        primaryHref?: string;
        secondaryHref?: string;
    }>(),
    {
        disabled: false,
        showSecondary: true,
        centered: false,
        primaryClass: '',
        secondaryClass: '',
        primaryHref: undefined,
        secondaryHref: undefined,
    },
);

defineEmits<{
    'primary-click': [];
    'secondary-click': [];
}>();

const pairContainerClass = computed(() =>
    cn(
        'flex min-w-0 flex-col gap-3 sm:gap-3',
        props.centered
            ? 'w-full items-center justify-center sm:w-auto sm:flex-row-reverse sm:justify-center'
            : 'w-full sm:flex-row-reverse',
    ),
);

const centeredPairButtonClass =
    'w-full max-w-xs sm:max-w-none sm:min-w-[10.5rem]';

const primaryLayoutClass = computed(() => {
    const base = props.showSecondary
        ? `${patientAppointmentFormPrimaryPairButtonClass} [&_svg]:size-6`
        : 'min-h-14 w-full touch-manipulation gap-2.5 px-4 text-lg font-semibold sm:w-auto [&_svg]:size-6';

    if (props.centered && props.showSecondary) {
        return cn(base, centeredPairButtonClass);
    }

    return base;
});

const secondaryLayoutClass = computed(() =>
    cn(
        patientSoftDangerActionButtonClass,
        'gap-2.5 [&_svg]:size-6',
        props.centered && props.showSecondary && centeredPairButtonClass,
    ),
);
</script>

<template>
    <div :class="pairContainerClass">
        <template v-if="primaryHref">
            <Button
                v-if="disabled"
                type="button"
                variant="default"
                size="lg"
                disabled
                :class="cn(primaryLayoutClass, primaryClass)"
            >
                <slot name="primary" />
            </Button>
            <Link
                v-else
                :href="primaryHref"
                prefetch
                :class="
                    cn(
                        buttonVariants({ variant: 'default', size: 'lg' }),
                        primaryLayoutClass,
                        primaryClass,
                    )
                "
            >
                <slot name="primary" />
            </Link>
        </template>
        <Button
            v-else
            type="button"
            variant="default"
            size="lg"
            :disabled="disabled"
            :class="cn(primaryLayoutClass, primaryClass)"
            @click="$emit('primary-click')"
        >
            <slot name="primary" />
        </Button>

        <template v-if="showSecondary && secondaryHref">
            <Button
                v-if="disabled"
                type="button"
                variant="secondary"
                size="lg"
                disabled
                :class="cn(secondaryLayoutClass, secondaryClass)"
            >
                <slot name="secondary" />
            </Button>
            <Link
                v-else
                :href="secondaryHref"
                prefetch
                :class="
                    cn(
                        buttonVariants({ variant: 'secondary', size: 'lg' }),
                        secondaryLayoutClass,
                        secondaryClass,
                    )
                "
            >
                <slot name="secondary" />
            </Link>
        </template>
        <Button
            v-else-if="showSecondary"
            type="button"
            variant="secondary"
            size="lg"
            :disabled="disabled"
            :class="cn(secondaryLayoutClass, secondaryClass)"
            @click="$emit('secondary-click')"
        >
            <slot name="secondary" />
        </Button>
    </div>
</template>
