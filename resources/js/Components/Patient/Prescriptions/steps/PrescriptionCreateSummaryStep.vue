<script setup lang="ts">
import { Pencil } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { IconActionButton } from '@/Components/ui/icon-action-button';
import { patientFormLabelClass } from '@/lib/patient/patientFormFieldClasses';
import type { PatientPrescriptionForm } from '@/lib/patient/prescriptions/patientPrescriptionFormTypes';
import type { PatientPrescriptionMedicationChoice } from '@/lib/patient/prescriptions/patientPrescriptionsScreenProps';
import type { PrescriptionFormWizardStep } from '@/lib/patient/prescriptions/prescriptionFormWizardTypes';
import type { MedicationTypeValue } from '@/lib/types';
import { cn } from '@/lib/utils';

const props = defineProps<{
    form: PatientPrescriptionForm;
    idPrefix: string;
    selectedMedicationId: number | null;
    medicationChoices: PatientPrescriptionMedicationChoice[];
    goToWizardStep: (
        step: PrescriptionFormWizardStep,
        focusElementIdSuffix?: string,
    ) => void;
}>();

const { t } = useI18n();

const expiryDateDisplay = new Intl.DateTimeFormat('nl-NL', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
});

const summaryRowGroupClass =
    'flex flex-col gap-0.5 border-b border-border/60 py-3 last:border-b-0 sm:flex-row sm:items-baseline sm:justify-between sm:gap-4 sm:py-3.5';

const summaryDdClass =
    'flex min-w-0 flex-1 flex-row items-baseline justify-end gap-2';

const summaryLabelClass =
    'shrink-0 text-sm font-medium text-text-muted md:text-base';

const summaryValueClass =
    'min-w-0 flex-1 text-base font-semibold leading-snug text-text-heading sm:text-end md:text-lg';

const overviewSectionHeadingClass = cn(
    patientFormLabelClass,
    'mb-3 text-lg text-text-heading md:mb-4 md:text-xl',
);

const selectedMedication = computed(() =>
    props.medicationChoices.find(
        (choice) => choice.id === props.selectedMedicationId,
    ),
);

const medicationSummary = computed(() => {
    const medication = selectedMedication.value;

    if (medication === undefined) {
        return '—';
    }

    const typeLabel = t(
        `patient.medications.types.${medication.type_medication as MedicationTypeValue}`,
    );

    return `${medication.name} (${typeLabel})`;
});

const quantitySummary = computed(() => {
    const quantity = props.form.quantity;

    if (quantity === null || quantity < 1) {
        return '—';
    }

    if (quantity === 1) {
        return t('patient.prescriptions.quantity.one');
    }

    return t('patient.prescriptions.quantity.nPrescriptions', {
        n: String(quantity),
    });
});

function formatExpiryDate(raw: string): string {
    const trimmed = raw.trim();

    if (trimmed.length < 1) {
        return '—';
    }

    const parsed = new Date(`${trimmed}T12:00:00`);

    if (Number.isNaN(parsed.getTime())) {
        return trimmed;
    }

    return expiryDateDisplay.format(parsed);
}

function expiryDateFieldLabel(index: number): string {
    const quantity = props.form.quantity ?? 0;

    if (index === quantity && quantity > 1) {
        return t('patient.prescriptions.expiryDateLastPrescriptionLabel', {
            number: String(index),
        });
    }

    return t('patient.prescriptions.expiryDateLabel', {
        number: String(index),
    });
}

function summaryRowAria(fieldTranslationKey: string): string {
    return t('patient.prescriptions.overview.editRowAria', {
        field: t(`patient.prescriptions.${fieldTranslationKey}`),
    });
}

function activateSummaryRow(
    step: PrescriptionFormWizardStep,
    focusElementIdSuffix: string,
): void {
    if (props.form.processing) {
        return;
    }

    props.goToWizardStep(step, focusElementIdSuffix);
}
</script>

<template>
    <div class="space-y-6">
        <h2
            :id="`${idPrefix}-create-summary-title`"
            tabindex="-1"
            class="text-text-heading text-xl leading-tight font-bold md:text-2xl"
        >
            {{ t('patient.prescriptions.overview.title') }}
        </h2>

        <div>
            <p :class="overviewSectionHeadingClass">
                {{ t('patient.prescriptions.overview.sectionDetails') }}
            </p>
            <dl
                class="border-border/70 bg-surface rounded-2xl border px-4 py-1 md:px-5"
            >
                <div :class="summaryRowGroupClass">
                    <dt :class="summaryLabelClass">
                        {{ t('patient.prescriptions.medicationLabel') }}
                    </dt>
                    <dd :class="summaryDdClass">
                        <span :class="summaryValueClass">{{
                            medicationSummary
                        }}</span>
                        <IconActionButton
                            :ariaLabel="summaryRowAria('medicationLabel')"
                            :disabled="form.processing"
                            @click="activateSummaryRow(1, 'medication')"
                        >
                            <Pencil class="size-5" aria-hidden="true" />
                        </IconActionButton>
                    </dd>
                </div>
                <div :class="summaryRowGroupClass">
                    <dt :class="summaryLabelClass">
                        {{ t('patient.prescriptions.quantityLabel') }}
                    </dt>
                    <dd :class="summaryDdClass">
                        <span :class="summaryValueClass">{{
                            quantitySummary
                        }}</span>
                        <IconActionButton
                            :ariaLabel="summaryRowAria('quantityLabel')"
                            :disabled="form.processing"
                            @click="activateSummaryRow(1, 'quantity')"
                        >
                            <Pencil class="size-5" aria-hidden="true" />
                        </IconActionButton>
                    </dd>
                </div>
            </dl>
        </div>

        <div>
            <p :class="overviewSectionHeadingClass">
                {{ t('patient.prescriptions.overview.sectionExpiryDates') }}
            </p>
            <dl
                class="border-border/70 bg-surface rounded-2xl border px-4 py-1 md:px-5"
            >
                <div
                    v-for="index in form.quantity ?? 0"
                    :key="index"
                    :class="summaryRowGroupClass"
                >
                    <dt :class="summaryLabelClass">
                        {{ expiryDateFieldLabel(index) }}
                    </dt>
                    <dd :class="summaryDdClass">
                        <span :class="summaryValueClass">
                            {{
                                formatExpiryDate(
                                    form.prescription_expiry_dates[index - 1] ??
                                        '',
                                )
                            }}
                        </span>
                        <IconActionButton
                            :ariaLabel="expiryDateFieldLabel(index)"
                            :disabled="form.processing"
                            @click="
                                activateSummaryRow(
                                    2,
                                    `expiry-${index - 1}`,
                                )
                            "
                        >
                            <Pencil class="size-5" aria-hidden="true" />
                        </IconActionButton>
                    </dd>
                </div>
            </dl>
        </div>
    </div>
</template>
