<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { InputError } from '@/Components/ui/input-error';
import {
    isPatientFormCountCustomValue,
    isPatientFormCountPresetValue,
    PATIENT_FORM_COUNT_PRESET_VALUES,
    patientFormCountCustomOptions,
} from '@/lib/patient/form/patientFormCountPresetConstants';
import {
    mobileShellFormFieldInvalidClass,
    mobileShellFormLabelClass,
    mobileShellFormSelectBaseClass,
    mobileShellFormSelectChevronStyle,
} from '@/lib/shell/mobileShellFormFieldClasses';
import { cn } from '@/lib/utils';

const count = defineModel<number | null>({ required: true });

const props = withDefaults(
    defineProps<{
        idPrefix: string;
        label: string;
        required?: boolean;
        errorMessage?: string;
        optionLabel: (value: number) => string;
        customTriggerLabel: string;
        customPlaceholder: string;
        customSelectAriaLabel: string;
        customMin?: number;
        customMax?: number;
    }>(),
    {
        required: false,
        errorMessage: undefined,
        customMin: 5,
        customMax: 24,
    },
);

const prefersCustomCount = ref(
    count.value !== null &&
        isPatientFormCountCustomValue(
            count.value,
            props.customMin,
            props.customMax,
        ),
);

const showCustomCountSelect = computed(
    () =>
        prefersCustomCount.value ||
        (count.value !== null &&
            isPatientFormCountCustomValue(
                count.value,
                props.customMin,
                props.customMax,
            )),
);

const customCountOptions = computed(() =>
    patientFormCountCustomOptions(props.customMin, props.customMax),
);

const presetButtonClass = (preset: number): string => {
    const selected =
        count.value !== null &&
        !showCustomCountSelect.value &&
        count.value === preset;

    return selected
        ? 'border-primary bg-primary/10 text-text-heading'
        : 'border-border bg-surface text-text hover:bg-surface-hover';
};

const customTriggerButtonClass = computed((): string =>
    showCustomCountSelect.value
        ? 'border-primary bg-primary/10 text-text-heading'
        : 'border-border bg-surface text-text hover:bg-surface-hover',
);

const fieldsetInvalidClass = computed((): string | null =>
    props.errorMessage ? 'rounded-2xl p-0.5 ring-2 ring-danger/25' : null,
);

const customCountSelect = computed({
    get(): string {
        if (
            count.value === null ||
            !isPatientFormCountCustomValue(
                count.value,
                props.customMin,
                props.customMax,
            )
        ) {
            return '';
        }

        return String(count.value);
    },
    set(value: string) {
        if (value === '') {
            return;
        }

        const parsed = Number(value);

        if (
            !Number.isInteger(parsed) ||
            parsed < props.customMin ||
            parsed > props.customMax
        ) {
            return;
        }

        count.value = parsed;
    },
});

const selectPreset = (preset: number): void => {
    prefersCustomCount.value = false;
    count.value = preset;
};

const selectCustom = (): void => {
    prefersCustomCount.value = true;

    if (
        count.value === null ||
        !isPatientFormCountCustomValue(
            count.value,
            props.customMin,
            props.customMax,
        )
    ) {
        count.value = props.customMin;
    }
};

watch(count, (value) => {
    if (value === null) {
        prefersCustomCount.value = false;

        return;
    }

    if (prefersCustomCount.value) {
        return;
    }

    if (!isPatientFormCountPresetValue(value)) {
        prefersCustomCount.value = true;
    }
});
</script>

<template>
    <div>
        <span
            :id="`${idPrefix}-count-label`"
            :class="cn(mobileShellFormLabelClass, 'text-xl')"
        >
            {{ label }}
            <span v-if="required" class="text-danger"> *</span>
        </span>
        <div
            :id="`${idPrefix}-count`"
            :class="
                cn('mt-2 touch-manipulation space-y-4', fieldsetInvalidClass)
            "
        >
            <div
                class="grid grid-cols-1 gap-2 sm:grid-cols-2 sm:gap-3"
                role="radiogroup"
                :aria-labelledby="`${idPrefix}-count-label`"
                :aria-invalid="Boolean(errorMessage)"
                :aria-describedby="
                    errorMessage ? `${idPrefix}-count-error` : undefined
                "
            >
                <button
                    v-for="preset in PATIENT_FORM_COUNT_PRESET_VALUES"
                    :id="`${idPrefix}-count-option-${preset}`"
                    :key="preset"
                    type="button"
                    class="focus-visible:border-focus focus-visible:ring-focus/30 min-h-14 rounded-2xl border-2 px-4 py-3.5 text-left text-base leading-snug font-semibold transition-colors focus-visible:ring-2 focus-visible:outline-none md:min-h-[3.75rem] md:px-5 md:text-lg"
                    :class="presetButtonClass(preset)"
                    :aria-pressed="!showCustomCountSelect && count === preset"
                    @click="selectPreset(preset)"
                >
                    {{ optionLabel(preset) }}
                </button>
                <button
                    :id="`${idPrefix}-count-custom-trigger`"
                    type="button"
                    class="focus-visible:border-focus focus-visible:ring-focus/30 min-h-14 rounded-2xl border-2 px-4 py-3.5 text-left text-base leading-snug font-semibold transition-colors focus-visible:ring-2 focus-visible:outline-none md:min-h-[3.75rem] md:px-5 md:text-lg"
                    :class="customTriggerButtonClass"
                    :aria-pressed="showCustomCountSelect"
                    @click="selectCustom"
                >
                    {{ customTriggerLabel }}
                </button>
            </div>
            <select
                v-if="showCustomCountSelect"
                :id="`${idPrefix}-count-custom`"
                v-model="customCountSelect"
                class="w-full text-base md:text-lg"
                :aria-label="customSelectAriaLabel"
                :class="
                    cn(
                        mobileShellFormSelectBaseClass,
                        errorMessage ? mobileShellFormFieldInvalidClass : null,
                    )
                "
                :style="mobileShellFormSelectChevronStyle"
                :aria-invalid="Boolean(errorMessage)"
                :aria-describedby="
                    errorMessage ? `${idPrefix}-count-error` : undefined
                "
            >
                <option disabled value="">
                    {{ customPlaceholder }}
                </option>
                <option
                    v-for="option in customCountOptions"
                    :key="option"
                    :value="String(option)"
                >
                    {{ optionLabel(option) }}
                </option>
            </select>
        </div>
        <InputError :id="`${idPrefix}-count-error`" :message="errorMessage" />
    </div>
</template>
