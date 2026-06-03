<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import MedicationStockBoxRefillCalculator from '@/Components/Patient/Inventory/form/MedicationStockBoxRefillCalculator.vue';
import PatientActionSuccessScreen from '@/Components/Patient/PatientActionSuccessScreen.vue';
import { buttonVariants } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog';
import type { PatientActionSuccessDetail } from '@/composables/usePatientActionSuccessScreen';
import { usePatientActionSuccessScreen } from '@/composables/usePatientActionSuccessScreen';
import type { MedicationStockProgressTone } from '@/lib/patient/inventory/medicationListVisualTone';
import { formatMedicationStockDisplayAmount } from '@/lib/patient/medications/stock/formatMedicationStockDisplayAmount';
import { parseMedicationStockNumericValue } from '@/lib/patient/medications/stock/parseMedicationStockNumericValue';
import { patientShellDialogOverlayAboveAppChromeClass } from '@/lib/patient/patientShellDialogLayout';
import type { MedicationStockListItem } from '@/lib/types';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        open: boolean;
        medicationId: number;
        medicationName: string;
        doseUnit: string | null;
        stock: MedicationStockListItem;
        stockProgressTone?: MedicationStockProgressTone | null;
        stockPiecesPerPackage?: number | null;
        formId: string;
        idPrefix: string;
        dialogContentClass: string;
        updateRouteName?: string;
    }>(),
    {
        stockProgressTone: null,
        stockPiecesPerPackage: null,
        updateRouteName: 'patient.medications.stocks.update',
    },
);

const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

const { t } = useI18n();
const {
    open: stockUpdateSuccessOpen,
    title: stockUpdateSuccessTitle,
    message: stockUpdateSuccessMessage,
    details: stockUpdateSuccessDetails,
    show: showStockUpdateSuccess,
} = usePatientActionSuccessScreen();

const stockToAdd = ref('');
const addStockError = ref('');
const serverCurrentStockAtOpen = ref('');

const form = useForm({
    current_stock: '',
});

watch(
    () => props.open,
    (open) => {
        if (!open) {
            return;
        }

        form.defaults({
            current_stock: props.stock.current_stock,
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

    const add = parseMedicationStockNumericValue(
        stockToAdd.value,
        props.doseUnit,
    );

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

function buildStockUpdateSuccessDetails(
    amountAddedInput: string,
    savedCurrentStock: string,
): PatientActionSuccessDetail[] {
    const summary: PatientActionSuccessDetail[] = [];
    const medicationName = props.medicationName.trim();

    if (medicationName !== '') {
        summary.push({
            label: t('patient.actionSuccess.summary.medication'),
            value: medicationName,
        });
    }

    if (amountAddedInput !== '') {
        summary.push({
            label: t('patient.inventory.addStockCalculatedTotal'),
            value: formatMedicationStockDisplayAmount(
                t,
                amountAddedInput,
                props.doseUnit,
            ),
        });
    }

    if (savedCurrentStock.trim() !== '') {
        summary.push({
            label: t('patient.inventory.addStockNewTotal'),
            value: savedCurrentStock,
        });
    }

    return summary;
}

function submitStock(): void {
    const amountAddedInput = stockToAdd.value.trim();

    if (!mergeAddIntoCurrentStockForSubmit()) {
        return;
    }

    const savedCurrentStock = form.current_stock;
    const successDetails = buildStockUpdateSuccessDetails(
        amountAddedInput,
        savedCurrentStock,
    );

    form.put(
        route(props.updateRouteName, {
            medication: props.medicationId,
            stock: props.stock.id,
        }),
        {
            preserveScroll: true,
            onSuccess: () => {
                router.flushAll();
                closeDialog();
                showStockUpdateSuccess({
                    title: t(
                        'patient.actionSuccess.inventory.stockUpdated.title',
                    ),
                    message: t(
                        'patient.actionSuccess.inventory.stockUpdated.message',
                    ),
                    details: successDetails,
                });
            },
        },
    );
}
</script>

<template>
    <Dialog :open="props.open" @update:open="emit('update:open', $event)">
        <DialogContent
            :class="props.dialogContentClass"
            :overlay-class="patientShellDialogOverlayAboveAppChromeClass('md')"
        >
            <DialogHeader
                class="shrink-0 space-y-1.5 pt-[env(safe-area-inset-top,0)] text-left sm:space-y-1 sm:pt-0 md:space-y-1"
            >
                <DialogTitle
                    class="text-text-heading text-xl leading-tight font-bold md:text-2xl"
                >
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
                    class="min-h-0 flex-1 touch-pan-y overflow-y-auto overscroll-y-contain [-webkit-overflow-scrolling:touch]"
                >
                    <div class="space-y-3 md:space-y-3">
                        <Card
                            class="border-border/80 bg-surface text-text rounded-2xl border shadow-md shadow-black/[0.04] md:rounded-3xl"
                        >
                            <CardContent class="p-0">
                                <div
                                    class="bg-surface space-y-5 rounded-2xl px-4 py-4 md:space-y-6 md:rounded-3xl md:px-5 md:py-5 lg:px-7 lg:py-7"
                                >
                                    <MedicationStockBoxRefillCalculator
                                        v-model:amount-to-add="stockToAdd"
                                        :id-prefix="props.idPrefix"
                                        :dose-unit="props.doseUnit"
                                        :current-stock="
                                            serverCurrentStockAtOpen
                                        "
                                        :stock-progress-tone="
                                            props.stockProgressTone
                                        "
                                        :stock-pieces-per-package="
                                            props.stockPiecesPerPackage
                                        "
                                        :error-message="
                                            addStockError ||
                                            form.errors.current_stock
                                        "
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
                        class="border-border/80 text-text rounded-2xl border bg-transparent shadow-sm shadow-black/[0.03] md:rounded-3xl"
                    >
                        <CardContent
                            class="px-4 py-3 md:px-5 md:py-3.5 lg:px-7 lg:py-4"
                        >
                            <div
                                class="flex w-full min-w-0 flex-col gap-2 md:flex-row-reverse md:gap-3"
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
                                    {{
                                        t('patient.medications.actions.cancel')
                                    }}
                                </button>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </form>
        </DialogContent>
    </Dialog>

    <PatientActionSuccessScreen
        v-model:open="stockUpdateSuccessOpen"
        :title="stockUpdateSuccessTitle"
        :message="stockUpdateSuccessMessage"
        :details="stockUpdateSuccessDetails"
        :done-label="t('patient.actionSuccess.done')"
    />
</template>
