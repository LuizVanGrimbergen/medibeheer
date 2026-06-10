<script setup lang="ts">
import { Card, CardContent } from '@/Components/ui/card';
import { InputError } from '@/Components/ui/input-error';
import { Label } from '@/Components/ui/label';
import {
    mobileShellFormFieldInputClass,
    mobileShellFormFieldInvalidClass,
    mobileShellFormLabelClass,
} from '@/lib/shell/mobileShellFormFieldClasses';
import {
    mobileShellWizardCardClass,
    mobileShellWizardCardInnerClass,
} from '@/lib/shell/mobileShellDialogLayout';
import { cn } from '@/lib/utils';

const props = defineProps<{
    id: string;
    label: string;
    placeholder: string;
    error?: string;
}>();

const model = defineModel<string>({ required: true });

const errorId = `${props.id}-error`;
</script>

<template>
    <Card :class="mobileShellWizardCardClass">
        <CardContent class="p-0">
            <div :class="mobileShellWizardCardInnerClass">
                <div class="space-y-2">
                    <Label :for="id" :class="mobileShellFormLabelClass">
                        {{ label }}
                    </Label>
                    <textarea
                        :id="id"
                        v-model="model"
                        rows="6"
                        :class="
                            cn(
                                mobileShellFormFieldInputClass,
                                'min-h-42 w-full resize-y py-4 text-lg leading-relaxed md:min-h-48 md:text-xl',
                                error ? mobileShellFormFieldInvalidClass : null,
                            )
                        "
                        :placeholder="placeholder"
                        :aria-invalid="Boolean(error)"
                        :aria-describedby="error ? errorId : undefined"
                    />
                    <InputError :id="errorId" :message="error" />
                </div>
            </div>
        </CardContent>
    </Card>
</template>
