<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { InputError } from '@/Components/ui/input-error';
import {
    patientFormLabelClass,
    patientFormSelectChevronStyle,
} from '@/lib/patient/patientFormFieldClasses';
import { cn } from '@/lib/utils';

const amount = defineModel<string>('amount', { required: true });
const unit = defineModel<string>('unit', { required: true });

const props = withDefaults(
    defineProps<{
        idPrefix: string;
        groupId: string;
        amountInputId: string;
        unitSelectId: string;
        legend: string;
        amountPlaceholder: string;
        unitAriaLabel: string;
        unitOptions: ReadonlyArray<{ value: string; label: string }>;
        amountName?: string;
        unitName?: string;
        amountError?: string;
        unitError?: string;
        amountRequired?: boolean;
        unitRequired?: boolean;
        amountInputMode?: 'text' | 'decimal';
    }>(),
    {
        amountName: undefined,
        unitName: undefined,
        amountError: undefined,
        unitError: undefined,
        amountRequired: false,
        unitRequired: false,
        amountInputMode: 'text',
    },
);

const { t } = useI18n();

const groupHasError = computed(
    (): boolean => Boolean(props.amountError) || Boolean(props.unitError),
);

const amountDescribedBy = computed((): string | undefined => {
    if (props.amountError === undefined || props.amountError === '') {
        return undefined;
    }

    return `${props.amountInputId}-error`;
});

const unitDescribedBy = computed((): string | undefined => {
    if (props.unitError === undefined || props.unitError === '') {
        return undefined;
    }

    return `${props.unitSelectId}-error`;
});
</script>

<template>
    <fieldset class="min-w-0 border-0 p-0">
        <legend :class="cn(patientFormLabelClass, 'text-lg md:text-xl')">
            {{ legend
            }}<span v-if="amountRequired || unitRequired" class="text-danger">
                *</span
            >
        </legend>
        <div
            :id="groupId"
            class="mt-2"
            :class="
                cn(
                    'bg-surface flex min-h-14 w-full min-w-0 touch-manipulation overflow-hidden rounded-2xl border-2 transition-[border-color,box-shadow]',
                    'focus-within:border-focus focus-within:ring-focus/25 focus-within:ring-2',
                    groupHasError
                        ? 'border-danger ring-danger/25 ring-2'
                        : 'border-border',
                )
            "
        >
            <input
                :id="amountInputId"
                v-model="amount"
                type="text"
                :name="amountName"
                :required="amountRequired"
                autocomplete="off"
                maxlength="500"
                :inputmode="amountInputMode"
                :placeholder="amountPlaceholder"
                class="text-text placeholder:text-text-muted min-h-14 min-w-0 flex-1 border-0 bg-transparent px-4 py-3.5 text-lg leading-normal focus:outline-none focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                :aria-invalid="Boolean(amountError)"
                :aria-required="amountRequired"
                :aria-describedby="amountDescribedBy"
            />
            <div
                class="border-border relative flex shrink-0 self-stretch border-l-2"
            >
                <select
                    :id="unitSelectId"
                    v-model="unit"
                    :name="unitName"
                    :aria-label="unitAriaLabel"
                    class="text-text-heading min-h-14 max-w-40 min-w-22 cursor-pointer touch-manipulation appearance-none border-0 bg-transparent bg-size-[1.25rem] bg-position-[right_0.75rem_center] bg-no-repeat py-3.5 pr-11 pl-3 text-base leading-normal font-semibold focus:outline-none focus-visible:outline-none"
                    :style="patientFormSelectChevronStyle"
                    :required="unitRequired"
                    :aria-invalid="Boolean(unitError)"
                    :aria-required="unitRequired"
                    :aria-describedby="unitDescribedBy"
                >
                    <option disabled value="">
                        {{ t('patient.medications.fields.selectPlaceholder') }}
                    </option>
                    <option
                        v-for="opt in unitOptions"
                        :key="opt.value"
                        :value="opt.value"
                    >
                        {{ opt.label }}
                    </option>
                </select>
            </div>
        </div>
        <InputError :id="`${amountInputId}-error`" :message="amountError" />
        <InputError :id="`${unitSelectId}-error`" :message="unitError" />
    </fieldset>
</template>
