<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import MobileShellNativeDateTimeInput from '@/Components/shell/MobileShellNativeDateTimeInput.vue';
import { InputError } from '@/Components/ui/input-error';
import { Label } from '@/Components/ui/label';
import {
    mobileShellFormLabelClass,
} from '@/lib/shell/mobileShellFormFieldClasses';
import { cn } from '@/lib/utils';

const model = defineModel<string>({ required: true });

defineProps<{
    id: string;
    label: string;
    min: string;
    error: string | undefined;
}>();

const { t } = useI18n();
</script>

<template>
    <div>
        <Label :for="id" :class="cn(mobileShellFormLabelClass, 'text-xl')">
            {{ label }} <span class="text-danger">*</span>
        </Label>

        <MobileShellNativeDateTimeInput
            :id="id"
            v-model="model"
            type="date"
            :min="min"
            lang="nl-NL"
            :placeholder="t('patient.inventory.vacationDateFormatHint')"
            :invalid="Boolean(error)"
        />

        <InputError :message="error" />
    </div>
</template>
