<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { InputError } from '@/Components/ui/input-error';
import { filterDecimalAmountInput } from '@/lib/patient/medications/validation/medicationFormValidationPrimitives';
import {
    mobileShellFormLabelClass,
    mobileShellFormSelectChevronStyle,
} from '@/lib/shell/mobileShellFormFieldClasses';
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
        amountInputMode: 'decimal',
    },
);

const { t } = useI18n();

const groupHasError = computed(
    (): boolean => Boolean(props.amountError) || Boolean(props.unitError),
);

const useTouchUnitButtons = computed(
    (): boolean =>
        props.unitOptions.length > 0 && props.unitOptions.length <= 4,
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

const unitButtonClass = (value: string): string =>
    cn(
        'focus-visible:border-focus focus-visible:ring-focus/30 min-h-14 min-w-14 shrink-0 touch-manipulation border-0 px-3 text-base leading-normal font-semibold transition-colors focus-visible:ring-2 focus-visible:outline-none md:min-w-16 md:px-4 md:text-lg',
        unit.value === value
            ? 'bg-primary/12 text-primary'
            : 'text-text-heading bg-transparent hover:bg-surface-hover',
    );

function selectUnit(value: string): void {
    unit.value = value;
}

function onAmountInput(event: Event): void {
    if (props.amountInputMode !== 'decimal') {
        return;
    }

    const target = event.target as HTMLInputElement;
    const filtered = filterDecimalAmountInput(target.value);

    if (filtered !== target.value) {
        amount.value = filtered;
    }
}
</script>

<template>
    <fieldset class="min-w-0 border-0 p-0">
        <legend :class="cn(mobileShellFormLabelClass, 'text-lg md:text-xl')">
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
                    'bg-surface flex min-h-14 w-full min-w-0 touch-manipulation rounded-2xl border-2 transition-[border-color,box-shadow]',
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
                :pattern="
                    amountInputMode === 'decimal'
                        ? '[0-9]+([.,][0-9]*)?'
                        : undefined
                "
                class="text-text placeholder:text-text-muted min-h-14 min-w-0 flex-1 border-0 bg-transparent px-4 py-3.5 text-lg leading-normal focus:outline-none focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                @input="onAmountInput"
                :aria-invalid="Boolean(amountError)"
                :aria-required="amountRequired"
                :aria-describedby="amountDescribedBy"
            />
            <div
                v-if="useTouchUnitButtons"
                class="border-border flex shrink-0 self-stretch border-l-2"
                role="radiogroup"
                :aria-label="unitAriaLabel"
                :aria-invalid="Boolean(unitError)"
                :aria-required="unitRequired"
                :aria-describedby="unitDescribedBy"
            >
                <button
                    v-for="opt in unitOptions"
                    :id="`${unitSelectId}-option-${opt.value}`"
                    :key="opt.value"
                    type="button"
                    :class="unitButtonClass(opt.value)"
                    :aria-pressed="unit === opt.value"
                    @click="selectUnit(opt.value)"
                >
                    {{ opt.label }}
                </button>
            </div>
            <div
                v-else
                class="border-border relative flex shrink-0 self-stretch border-l-2"
            >
                <select
                    :id="unitSelectId"
                    v-model="unit"
                    :name="unitName"
                    :aria-label="unitAriaLabel"
                    class="text-text-heading min-h-14 max-w-40 min-w-22 cursor-pointer touch-manipulation appearance-none border-0 bg-transparent bg-size-[1.25rem] bg-position-[right_0.75rem_center] bg-no-repeat py-3.5 pr-11 pl-3 text-base leading-normal font-semibold focus:outline-none focus-visible:outline-none"
                    :style="mobileShellFormSelectChevronStyle"
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
