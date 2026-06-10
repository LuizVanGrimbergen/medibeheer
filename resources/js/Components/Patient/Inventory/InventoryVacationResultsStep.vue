<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import MobileShellWizardScrollBody from '@/Components/shell/MobileShellWizardScrollBody.vue';
import InventoryVacationExpiringPrescriptionsSection from '@/Components/Patient/Inventory/InventoryVacationExpiringPrescriptionsSection.vue';
import InventoryVacationPickupItemsList from '@/Components/Patient/Inventory/InventoryVacationPickupItemsList.vue';
import InventoryVacationResultsPeriodSummary from '@/Components/Patient/Inventory/InventoryVacationResultsPeriodSummary.vue';
import InventoryVacationSavedPackageHintBanner from '@/Components/Patient/Inventory/InventoryVacationSavedPackageHintBanner.vue';
import InventoryVacationShareActions from '@/Components/Patient/Inventory/InventoryVacationShareActions.vue';
import { Button } from '@/Components/ui/button';
import ListCardSkeleton from '@/Components/ui/skeleton/ListCardSkeleton.vue';
import type { PatientInventoryVacationPageState } from '@/composables/patient/usePatientInventoryVacationPage';
import type { PatientInventoryVacationExpiringPrescription } from '@/lib/patient/inventory/patientInventoryVacationExpiringPrescription';
import type { PatientInventoryVacationSupplyResult } from '@/lib/patient/inventory/patientInventoryVacationSupply';
import {
    mobileShellFormWizardFooterPrimaryButtonClass,
    mobileShellFormWizardFooterRowClass,
    mobileShellWizardFormClass,
    mobileShellWizardStepPanelClass,
} from '@/lib/shell/mobileShellDialogLayout';

defineProps<{
    result: PatientInventoryVacationSupplyResult;
    vacationDaysLabel: string;
    formattedStartsOn: string;
    formattedEndsOn: string;
    expiringPrescriptions: PatientInventoryVacationExpiringPrescription[];
    isExpiringPrescriptionsLoading: boolean;
    vacationSavedPackageHint: string | null;
    vacationSavedPackageHintUsesLiquidIcon: boolean;
    doseUnitForItem: (doseUnit: string | null) => string | null;
    share: PatientInventoryVacationPageState['share'];
}>();

const { t } = useI18n();
</script>

<template>
    <div :class="mobileShellWizardFormClass">
        <MobileShellWizardScrollBody :active="true">
            <div :class="mobileShellWizardStepPanelClass">
                <InventoryVacationResultsPeriodSummary
                    :vacation-days-label="vacationDaysLabel"
                    :formatted-starts-on="formattedStartsOn"
                    :formatted-ends-on="formattedEndsOn"
                />

                <p
                    v-if="result.items.length === 0"
                    class="border-border/80 bg-surface-2/80 text-text rounded-2xl border px-4 py-5 text-base leading-relaxed md:text-lg"
                >
                    {{ t('patient.inventory.vacationEmptyResults') }}
                </p>

                <InventoryVacationShareActions
                    :is-saving="share.isSaving.value"
                    :save-error="share.saveError.value"
                    :save-share-hint-visible="share.saveShareHintVisible.value"
                    :saved-share-image-count="share.savedShareImageCount.value"
                    :share-flow-open="share.shareFlowOpen.value"
                    :share-step-current="share.shareStepCurrent.value"
                    :share-step-total="share.shareStepTotal.value"
                    :planned-share-image-count="share.plannedShareImageCount.value"
                    :prepare-share-files="share.prepareShareFiles"
                    :share-current-image="share.shareCurrentImage"
                />

                <InventoryVacationSavedPackageHintBanner
                    v-if="vacationSavedPackageHint !== null"
                    :hint="vacationSavedPackageHint"
                    :uses-liquid-icon="vacationSavedPackageHintUsesLiquidIcon"
                />

                <ListCardSkeleton v-if="isExpiringPrescriptionsLoading" />

                <InventoryVacationExpiringPrescriptionsSection
                    v-else-if="expiringPrescriptions.length > 0"
                    :heading="
                        t('patient.inventory.vacationExpiringPrescriptionsHeading')
                    "
                    :intro="
                        t('patient.inventory.vacationExpiringPrescriptionsIntro')
                    "
                    :prescriptions="expiringPrescriptions"
                />

                <InventoryVacationPickupItemsList
                    v-if="result.items.length > 0"
                    :items="result.items"
                    :dose-unit-for-item="doseUnitForItem"
                />

                <p
                    v-if="result.skipped_medication_count > 0"
                    class="text-text-muted text-sm leading-relaxed sm:text-base"
                >
                    {{
                        t('patient.inventory.vacationSkippedNote', {
                            count: String(result.skipped_medication_count),
                        })
                    }}
                </p>
            </div>

            <template #footer>
                <div :class="mobileShellFormWizardFooterRowClass">
                    <Button
                        as-child
                        variant="default"
                        size="lg"
                        :class="mobileShellFormWizardFooterPrimaryButtonClass"
                    >
                        <Link :href="route('patient.inventory')">
                            {{ t('patient.inventory.vacationDone') }}
                        </Link>
                    </Button>
                </div>
            </template>
        </MobileShellWizardScrollBody>
    </div>
</template>
