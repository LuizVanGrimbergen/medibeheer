<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Button, buttonVariants } from '@/Components/ui/button';
import {
    patientFormWizardFooterCancelButtonClass,
    patientFormWizardFooterOutlineButtonClass,
    patientFormWizardFooterPrimaryButtonClass,
} from '@/lib/patient/patientShellDialogLayout';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        variant: 'primary' | 'outline' | 'danger';
        type?: 'submit' | 'button';
        href?: string;
        disabled?: boolean;
    }>(),
    {
        type: 'button',
        disabled: false,
    },
);

const emit = defineEmits<{
    click: [];
}>();

const buttonClass = computed((): string => {
    if (props.variant === 'primary') {
        return patientFormWizardFooterPrimaryButtonClass;
    }

    if (props.variant === 'danger') {
        return patientFormWizardFooterCancelButtonClass;
    }

    return patientFormWizardFooterOutlineButtonClass;
});

const buttonVariant = computed(() => {
    if (props.variant === 'danger') {
        return 'secondary' as const;
    }

    if (props.variant === 'outline') {
        return 'outline' as const;
    }

    return 'default' as const;
});
</script>

<template>
    <Button
        v-if="props.href !== undefined"
        as-child
        :variant="buttonVariant"
        size="lg"
        :disabled="props.disabled"
        :class="buttonClass"
    >
        <Link :href="props.href">
            <slot />
        </Link>
    </Button>

    <button
        v-else-if="props.type === 'submit'"
        type="submit"
        :disabled="props.disabled"
        :class="
            cn(
                buttonVariants({ variant: 'default', size: 'lg' }),
                buttonClass,
            )
        "
    >
        <slot />
    </button>

    <Button
        v-else
        type="button"
        :variant="buttonVariant"
        size="lg"
        :disabled="props.disabled"
        :class="buttonClass"
        @click="emit('click')"
    >
        <slot />
    </Button>
</template>
