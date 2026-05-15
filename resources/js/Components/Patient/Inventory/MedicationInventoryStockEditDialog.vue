<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { formatMedicationStockDisplayAmount } from '@/lib/patient/medications/stock/formatMedicationStockDisplayAmount';
import { parseMedicationStockNumericValue } from '@/lib/patient/medications/stock/parseMedicationStockNumericValue';
import MedicationStockAmountField from '@/Components/Patient/Medications/form/MedicationStockAmountField.vue';
import { buttonVariants } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/Components/ui/dialog';
import { Input } from '@/Components/ui/input';
import { InputError } from '@/Components/ui/input-error';
import { Label } from '@/Components/ui/label';
import {
    patientFormFieldInputClass,
    patientFormFieldInvalidClass,
    patientFormLabelClass,
    patientFormLargeTouchFieldClass,
} from '@/lib/patient/patientFormFieldClasses';
import { patientShellDialogOverlayAboveAppChromeClass } from '@/lib/patient/patientShellDialogLayout';
import type { MedicationStockListItem } from '@/lib/types';
import { cn } from '@/lib/utils';

const props = defineProps<{
    open: boolean;
    medicationId: number;
    doseUnit: string | null;
    stock: MedicationStockListItem;
    formId: string;
    idPrefix: string;
    dialogContentClass: string;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

const { t } = useI18n();

const stockToAdd = ref('');
const addStockError = ref('');
const serverCurrentStockAtOpen = ref('');

const form = useForm({
    current_stock: '',
    low_stock: '',
});

watch(
    () => props.open,
    (open) => {
        if (!open) {
            return;
        }

        form.defaults({
            current_stock: props.stock.current_stock,
            low_stock: props.stock.low_stock,
        });
        form.reset();
        form.clearErrors();
        serverCurrentStockAtOpen.value = props.stock.current_stock;
        stockToAdd.value = '';
        addStockError.value = '';
    },
);

function prefersCommaDecimal(value: string): boolean {
    return value.trim().includes(',');
}

function formatStockTotal(total: number, useComma: boolean): string {
    if (!Number.isFinite(total)) {
        return '';
    }

    if (Number.isInteger(total)) {
        return String(total);
    }

    const raw = String(total);

    if (!useComma) {
        return raw;
    }

    return raw.replace('.', ',');
}

function mergeAddIntoCurrentStockForSubmit(): boolean {
    addStockError.value = '';

    if (stockToAdd.value.trim().length < 1) {
        form.current_stock = serverCurrentStockAtOpen.value;

        return true;
    }

    const snapshot = serverCurrentStockAtOpen.value.trim();

    const add = parseMedicationStockNumericValue(stockToAdd.value, props.doseUnit);

    if (add === null || add <= 0) {
        addStockError.value = t('patient.inventory.addStockInvalidAmount');

        return false;
    }

    const base = parseMedicationStockNumericValue(snapshot, props.doseUnit);

    if (base === null) {
        addStockError.value = t('patient.inventory.addStockInvalidBase');

        return false;
    }

    const useComma =
        prefersCommaDecimal(snapshot) || prefersCommaDecimal(stockToAdd.value);

    form.current_stock = formatMedicationStockDisplayAmount(
        t,
        formatStockTotal(base + add, useComma),
        props.doseUnit,
    );
    stockToAdd.value = '';

    return true;
}

const footerPrimaryButtonClass =
    'min-h-12 min-w-0 w-full touch-manipulation gap-2.5 rounded-2xl px-3 text-base font-semibold md:min-h-14 md:flex-1 md:px-4 lg:text-lg';

const footerCancelButtonClass =
    'min-h-12 min-w-0 w-full touch-manipulation rounded-2xl px-3 text-base font-semibold md:min-h-14 md:flex-1 md:px-4 lg:text-lg';

function closeDialog(): void {
    emit('update:open', false);
}

function submitStock(): void {
    if (!mergeAddIntoCurrentStockForSubmit()) {
        return;
    }

    form.put(
        route('patient.medications.stocks.update', {
            medication: props.medicationId,
            stock: props.stock.id,
        }),
        {
            preserveScroll: true,
            onSuccess: () => {
                router.flushAll();
                closeDialog();
            },
        },
    );
}
</script>

<template>
    <Dialog
        :open="props.open"
        @update:open="emit('update:open', $event)"
    >
        <DialogContent
            :class="props.dialogContentClass"
            :overlay-class="patientShellDialogOverlayAboveAppChromeClass('md')"
        >
            <DialogHeader class="shrink-0 space-y-1.5 pt-[env(safe-area-inset-top,0)] text-left sm:space-y-1 sm:pt-0 md:space-y-1">
                <DialogTitle class="text-xl font-bold leading-tight text-text-heading md:text-2xl">
                    {{ t('patient.inventory.editStockDialogTitle') }}
                </DialogTitle>
            </DialogHeader>

            <form
                :id="props.formId"
                class="flex min-h-0 flex-1 flex-col"
                novalidate
                @submit.prevent="submitStock"
            >
                <div
                    class="min-h-0 flex-1 overflow-y-auto overscroll-y-contain [-webkit-overflow-scrolling:touch] touch-pan-y"
                >
                    <div class="space-y-3 md:space-y-3">
                        <Card
                            class="rounded-2xl border border-border/80 bg-surface text-text shadow-md shadow-black/[0.04] md:rounded-3xl"
                        >
                            <CardContent class="p-0">
                                <div
                                    class="space-y-8 rounded-2xl bg-surface px-4 py-4 md:rounded-3xl md:px-5 md:py-5 lg:px-7 lg:py-7"
                                >
                                    <div>
                                        <Label
                                            :for="`${props.idPrefix}-current-stock`"
                                            :class="cn(patientFormLabelClass, 'text-xl')"
                                        >
                                            {{ t('patient.medications.fields.currentStock') }}
                                        </Label>
                                        <input
                                            :id="`${props.idPrefix}-current-stock`"
                                            type="text"
                                            readonly
                                            :value="serverCurrentStockAtOpen"
                                            :aria-readonly="true"
                                            :aria-invalid="Boolean(form.errors.current_stock)"
                                            :aria-describedby="
                                                form.errors.current_stock
                                                    ? `${props.idPrefix}-current-stock-error`
                                                    : undefined
                                            "
                                            autocomplete="off"
                                            :class="
                                                cn(
                                                    patientFormFieldInputClass,
                                                    patientFormLargeTouchFieldClass,
                                                    'mt-2 cursor-default bg-surface-2/80 text-text-heading',
                                                    form.errors.current_stock
                                                        ? patientFormFieldInvalidClass
                                                        : null,
                                                )
                                            "
                                        />
                                        <InputError
                                            :id="`${props.idPrefix}-current-stock-error`"
                                            :message="form.errors.current_stock"
                                        />
                                        <div class="mt-4 space-y-2">
                                            <Label
                                                :for="`${props.idPrefix}-stock-to-add`"
                                                :class="cn(patientFormLabelClass, 'text-base font-semibold')"
                                            >
                                                {{ t('patient.inventory.addStockAmountLabel') }}
                                            </Label>
                                            <Input
                                                :id="`${props.idPrefix}-stock-to-add`"
                                                v-model="stockToAdd"
                                                type="text"
                                                inputmode="decimal"
                                                autocomplete="off"
                                                maxlength="64"
                                                :placeholder="
                                                    t('patient.inventory.addStockAmountPlaceholder')
                                                "
                                                :class="
                                                    cn(
                                                        patientFormFieldInputClass,
                                                        patientFormLargeTouchFieldClass,
                                                        'min-h-12',
                                                        addStockError
                                                            ? patientFormFieldInvalidClass
                                                            : null,
                                                    )
                                                "
                                                :aria-invalid="Boolean(addStockError)"
                                                :aria-describedby="
                                                    addStockError
                                                        ? `${props.idPrefix}-stock-to-add-error`
                                                        : undefined
                                                "
                                            />
                                            <InputError
                                                v-if="addStockError.length > 0"
                                                :id="`${props.idPrefix}-stock-to-add-error`"
                                                :message="addStockError"
                                            />
                                        </div>
                                    </div>
                                    <MedicationStockAmountField
                                        v-model="form.low_stock"
                                        :id-prefix="props.idPrefix"
                                        field-id-suffix="low-stock"
                                        label-key="patient.medications.fields.lowStock"
                                        placeholder-example-amount="40"
                                        fallback-placeholder-key="patient.medications.fields.lowStockPlaceholder"
                                        :dose-unit="props.doseUnit ?? ''"
                                        :maxlength="64"
                                        :error-message="form.errors.low_stock"
                                    />
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>

                <div
                    class="pointer-events-auto relative z-10 shrink-0 pt-2 pb-[max(0.75rem,env(safe-area-inset-bottom,0px))]"
                >
                    <Card
                        class="rounded-2xl border border-border/80 bg-transparent text-text shadow-sm shadow-black/[0.03] md:rounded-3xl"
                    >
                        <CardContent class="px-4 py-3 md:px-5 md:py-3.5 lg:px-7 lg:py-4">
                            <div
                                class="flex min-w-0 w-full flex-col gap-2 md:flex-row-reverse md:gap-3"
                            >
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    :class="
                                        cn(
                                            buttonVariants({
                                                variant: 'default',
                                                size: 'lg',
                                            }),
                                            footerPrimaryButtonClass,
                                        )
                                    "
                                >
                                    {{ t('patient.medications.actions.save') }}
                                </button>

                                <button
                                    type="button"
                                    :disabled="form.processing"
                                    :class="
                                        cn(
                                            buttonVariants({
                                                variant: 'outline',
                                                size: 'lg',
                                            }),
                                            footerCancelButtonClass,
                                        )
                                    "
                                    @click.stop.prevent="closeDialog"
                                >
                                    {{ t('patient.medications.actions.cancel') }}
                                </button>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </form>
        </DialogContent>
    </Dialog>
</template>
