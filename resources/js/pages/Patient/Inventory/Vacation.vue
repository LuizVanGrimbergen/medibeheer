<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import MobileShellPageWizard from '@/Components/shell/MobileShellPageWizard.vue';
import InventoryVacationDateFormStep from '@/Components/Patient/Inventory/InventoryVacationDateFormStep.vue';
import InventoryVacationResultsStep from '@/Components/Patient/Inventory/InventoryVacationResultsStep.vue';
import PatientLayout from '@/Layouts/PatientLayout.vue';
import { usePatientInventoryVacationPage } from '@/composables/patient/usePatientInventoryVacationPage';
import { isDeferredInertiaPropLoading } from '@/lib/inertia/isDeferredInertiaPropLoading';
import type { PatientInventoryVacationPageProps } from '@/lib/patient/inventory/patientInventoryVacationPageProps';
import { mobileShellPageFillClass } from '@/lib/shell/mobileShellDialogLayout';

const props = defineProps<PatientInventoryVacationPageProps>();

const { t } = useI18n();

const {
    form,
    minDateIso,
    showResults,
    pageIntro,
    result,
    vacationDaysLabel,
    formattedStartsOn,
    formattedEndsOn,
    expiringPrescriptions,
    vacationSavedPackageHint,
    vacationSavedPackageHintUsesLiquidIcon,
    doseUnitForItem,
    share,
    submit,
} = usePatientInventoryVacationPage(props);

const isExpiringPrescriptionsLoading = computed(
    () =>
        showResults.value &&
        isDeferredInertiaPropLoading(props.expiring_prescriptions),
);
</script>

<template>
    <Head>
        <title>{{ t('patient.inventory.vacationDialogTitle') }}</title>
        <meta
            name="description"
            :content="t('patient.inventory.vacationMetaDescription')"
        />
    </Head>

    <PatientLayout>
        <div :class="mobileShellPageFillClass">
            <MobileShellPageWizard
                :title="t('patient.inventory.vacationDialogTitle')"
                :description="pageIntro"
            >
                <InventoryVacationDateFormStep
                    v-if="!showResults"
                    :form="form"
                    :min-date-iso="minDateIso"
                    @submit="submit"
                />

                <InventoryVacationResultsStep
                    v-else-if="result !== null"
                    :result="result"
                    :vacation-days-label="vacationDaysLabel"
                    :formatted-starts-on="formattedStartsOn"
                    :formatted-ends-on="formattedEndsOn"
                    :expiring-prescriptions="expiringPrescriptions"
                    :is-expiring-prescriptions-loading="
                        isExpiringPrescriptionsLoading
                    "
                    :vacation-saved-package-hint="vacationSavedPackageHint"
                    :vacation-saved-package-hint-uses-liquid-icon="
                        vacationSavedPackageHintUsesLiquidIcon
                    "
                    :dose-unit-for-item="doseUnitForItem"
                    :share="share"
                />
            </MobileShellPageWizard>
        </div>
    </PatientLayout>
</template>
