<script setup lang="ts">
import { computed } from 'vue';
import { Button, buttonVariants } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { InputError } from '@/Components/ui/input-error';
import { Label } from '@/Components/ui/label';
import {
    mobileShellFormFieldInputClass,
    mobileShellFormFieldInvalidClass,
    mobileShellFormLabelClass,
} from '@/lib/shell/mobileShellFormFieldClasses';
import { mobileShellPageIntroButtonClass } from '@/lib/shell/mobileShellTypography';
import { cn } from '@/lib/utils';

const props = defineProps<{
    inputId: string;
    email: string;
    emailError?: string;
    processing: boolean;
    disabled: boolean;
    emailLabel: string;
    emailPlaceholder: string;
    submitLabel: string;
}>();

const emit = defineEmits<{
    'update:email': [value: string];
    submit: [];
}>();

const emailErrorId = computed(() => `${props.inputId}-error`);

const hasEmailError = computed(
    () => props.emailError !== undefined && props.emailError !== '',
);
</script>

<template>
    <form class="mt-6 flex flex-col gap-5" @submit.prevent="emit('submit')">
        <div>
            <Label :for="props.inputId" :class="mobileShellFormLabelClass">
                {{ props.emailLabel }}
            </Label>
            <Input
                :id="props.inputId"
                :model-value="props.email"
                type="email"
                autocomplete="email"
                :class="
                    cn(
                        mobileShellFormFieldInputClass,
                        hasEmailError ? mobileShellFormFieldInvalidClass : null,
                    )
                "
                :placeholder="props.emailPlaceholder"
                :aria-invalid="hasEmailError"
                :aria-describedby="hasEmailError ? emailErrorId : undefined"
                @update:model-value="emit('update:email', String($event))"
            />
            <InputError :id="emailErrorId" :message="props.emailError" />
        </div>

        <Button
            type="submit"
            :class="
                cn(
                    buttonVariants({ variant: 'default', size: 'lg' }),
                    mobileShellPageIntroButtonClass,
                    'sm:w-auto',
                )
            "
            :disabled="props.processing || props.disabled"
        >
            {{ props.submitLabel }}
        </Button>
    </form>
</template>
