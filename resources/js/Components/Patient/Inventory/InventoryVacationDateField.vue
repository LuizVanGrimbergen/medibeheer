<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { InputError } from '@/Components/ui/input-error';
import { Label } from '@/Components/ui/label';
import {
    patientFormFieldInvalidClass,
    patientFormLabelClass,
    patientFormNativeDateTimeInputClass,
} from '@/lib/patient/patientFormFieldClasses';
import { cn } from '@/lib/utils';

const model = defineModel<string>({ required: true });

defineProps<{
    id: string;
    label: string;
    min: string;
    error: string | undefined;
}>();

const { t } = useI18n();

const isEmpty = computed(() => model.value.trim() === '');
</script>

<template>
    <div>
        <Label
            :for="id"
            :class="cn(patientFormLabelClass, 'text-xl')"
        >
            {{ label }} <span class="text-danger">*</span>
        </Label>

        <div class="relative">
            <span
                v-if="isEmpty"
                class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-xl leading-normal text-text-placeholder"
                aria-hidden="true"
            >
                {{ t('patient.inventory.vacationDateFormatHint') }}
            </span>

            <input
                :id="id"
                v-model="model"
                type="date"
                :min="min"
                lang="nl-NL"
                aria-required="true"
                autocomplete="off"
                :placeholder="t('patient.inventory.vacationDateFormatHint')"
                :class="
                    cn(
                        patientFormNativeDateTimeInputClass,
                        isEmpty ? 'text-transparent' : null,
                        error ? patientFormFieldInvalidClass : null,
                    )
                "
                :aria-invalid="Boolean(error)"
            />
        </div>

        <InputError :message="error" />
    </div>
</template>
