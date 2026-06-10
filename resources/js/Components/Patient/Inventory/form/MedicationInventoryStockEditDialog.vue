<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import MobileShellFormDialog from '@/Components/shell/MobileShellFormDialog.vue';
import MobileShellWizardCard from '@/Components/shell/MobileShellWizardCard.vue';
import MedicationStockBoxRefillCalculator from '@/Components/Patient/Inventory/form/MedicationStockBoxRefillCalculator.vue';
import PatientActionSuccessScreen from '@/Components/Patient/PatientActionSuccessScreen.vue';
import { Button } from '@/Components/ui/button';
import type { PatientActionSuccessDetail } from '@/composables/patient/usePatientActionSuccessScreen';
import { usePatientActionSuccessScreen } from '@/composables/patient/usePatientActionSuccessScreen';
import type { MedicationStockProgressTone } from '@/lib/patient/inventory/medicationListVisualTone';
import { formatMedicationStockDisplayAmount } from '@/lib/patient/medications/stock/formatMedicationStockDisplayAmount';
import { parseMedicationStockNumericValue } from '@/lib/patient/medications/stock/parseMedicationStockNumericValue';
import {
    mobileShellFormWizardFooterCancelButtonClass,
    mobileShellFormWizardFooterPrimaryButtonClass,
    mobileShellFormWizardFooterRowClass,
} from '@/lib/shell/mobileShellDialogLayout';
import type { MedicationStockItem } from '@/lib/types';

const props = withDefaults(
    defineProps<{
        open: boolean;
        medicationId: number;
        medicationName: string;
        doseUnit: string | null;
        stock: MedicationStockItem;
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
    <MobileShellFormDialog
        :open="props.open"
        :title="t('patient.inventory.editStockDialogTitle')"
        :form-id="props.formId"
        :dialog-content-class="props.dialogContentClass"
        @update:open="emit('update:open', $event)"
        @submit="submitStock"
        @cancel="closeDialog"
    >
        <div class="space-y-3 md:space-y-3">
            <MobileShellWizardCard>
                <MedicationStockBoxRefillCalculator
                    v-model:amount-to-add="stockToAdd"
                    :id-prefix="props.idPrefix"
                    :dose-unit="props.doseUnit"
                    :current-stock="serverCurrentStockAtOpen"
                    :stock-progress-tone="props.stockProgressTone"
                    :stock-pieces-per-package="props.stockPiecesPerPackage"
                    :error-message="
                        addStockError || form.errors.current_stock
                    "
                />
            </MobileShellWizardCard>
        </div>

        <template #footer>
            <div :class="mobileShellFormWizardFooterRowClass">
                <Button
                    type="button"
                    variant="secondary"
                    size="lg"
                    :disabled="form.processing"
                    :class="mobileShellFormWizardFooterCancelButtonClass"
                    @click="closeDialog"
                >
                    {{ t('patient.medications.actions.cancel') }}
                </Button>
                <Button
                    type="submit"
                    variant="default"
                    size="lg"
                    :disabled="form.processing"
                    :class="mobileShellFormWizardFooterPrimaryButtonClass"
                >
                    {{ t('patient.medications.actions.save') }}
                </Button>
            </div>
        </template>
    </MobileShellFormDialog>

    <PatientActionSuccessScreen
        v-model:open="stockUpdateSuccessOpen"
        :title="stockUpdateSuccessTitle"
        :message="stockUpdateSuccessMessage"
        :details="stockUpdateSuccessDetails"
        :done-label="t('patient.actionSuccess.done')"
    />
</template>
