<script setup lang="ts">
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import MedicationCardDetailsPanel from '@/Components/Patient/Medications/MedicationCardDetailsPanel.vue';
import MedicationCardListHeader from '@/Components/Patient/Medications/MedicationCardListHeader.vue';
import PatientListCardActionsToolbar from '@/Components/Patient/PatientListCardActionsToolbar.vue';
import PatientListCardDetailsToggle from '@/Components/Patient/PatientListCardDetailsToggle.vue';
import { Card, CardContent } from '@/Components/ui/card';
import { Collapsible, CollapsibleContent } from '@/Components/ui/collapsible';
import { useMedicationCardDisplay } from '@/composables/patient/useMedicationCardDisplay';
import type { MedicationRegisterItem } from '@/lib/types';

const props = withDefaults(
    defineProps<{
        medication: MedicationRegisterItem;
        showActions?: boolean;
        showStock?: boolean;
        showStockSummary?: boolean;
        stockControlsFirst?: boolean;
        contentOnly?: boolean;
        stockUpdateRouteName?: string;
        listStatusEndedLabelKey?: string;
        listStatusRemovedLabelKey?: string;
        defaultOpen?: boolean;
    }>(),
    {
        showActions: true,
        showStock: false,
        showStockSummary: true,
        stockControlsFirst: false,
        contentOnly: false,
        stockUpdateRouteName: 'patient.medications.stocks.update',
        listStatusEndedLabelKey: 'patient.medications.listStatus.ended',
        listStatusRemovedLabelKey: 'patient.medications.listStatus.removed',
        defaultOpen: false,
    },
);

const isOpen = ref(props.defaultOpen);

const emit = defineEmits<{
    edit: [];
    delete: [];
}>();

const { t } = useI18n();

const canEdit = computed(
    () => props.showActions && props.medication.list_status !== 'removed',
);

const canDelete = computed(
    () => props.showActions && props.medication.list_status === 'active',
);

const hasEditOrDeleteActions = computed(() => canEdit.value || canDelete.value);

const {
    isInactiveListItem,
    listStatusLabel,
    sortedDoseTimes,
    doseTimesDisplay,
    scheduleDateRow,
    prescriptionExpiryDateRow,
    doseLine,
    strengthLine,
    hasMedicationDetailsGroup,
    typeLabel,
    stockProgressTone,
    medicationVisualToneClasses,
    medicationPillWrapToneClass,
    medicationPillIconClass,
    notePreview,
    supplyEstimateLine,
    stockProgressPercent,
    showCollapsedUrgencyAlert,
    showCollapsedSupplyDaysSummary,
    collapsedHeaderLine,
    stockProgressAriaLabel,
} = useMedicationCardDisplay({
    medication: () => props.medication,
    showStock: () => props.showStock,
    listStatusEndedLabelKey: props.listStatusEndedLabelKey,
    listStatusRemovedLabelKey: props.listStatusRemovedLabelKey,
});
</script>

<template>
    <div v-if="props.contentOnly" class="space-y-6">
        <MedicationCardDetailsPanel
            :medication="medication"
            :dose-line="doseLine"
            :strength-line="strengthLine"
            :sorted-dose-times="sortedDoseTimes"
            :dose-times-display="doseTimesDisplay"
            :schedule-date-row="scheduleDateRow"
            :prescription-expiry-date-row="prescriptionExpiryDateRow"
            :has-medication-details-group="hasMedicationDetailsGroup"
            :note-preview="notePreview"
            :show-stock="props.showStock"
            :show-stock-summary="props.showStockSummary"
            :stock-controls-first="props.stockControlsFirst"
            :stock-update-route-name="props.stockUpdateRouteName"
        />
    </div>

    <Card
        v-else
        class="bg-surface text-text w-full min-w-0 rounded-3xl border shadow-md shadow-black/[0.04]"
        :class="[
            medicationVisualToneClasses.border,
            isInactiveListItem ? 'opacity-90' : null,
        ]"
    >
        <CardContent class="relative p-6 sm:p-7">
            <Collapsible v-model:open="isOpen">
                <PatientListCardActionsToolbar
                    v-if="hasEditOrDeleteActions"
                    :ariaLabel="t('patient.medications.cardActionsAriaLabel')"
                    :showEdit="canEdit"
                    :showDelete="canDelete"
                    :editAriaLabel="t('patient.medications.actions.edit')"
                    :deleteAriaLabel="t('patient.medications.actions.delete')"
                    @edit="emit('edit')"
                    @delete="emit('delete')"
                />

                <MedicationCardListHeader
                    :medication="medication"
                    :is-open="isOpen"
                    :has-edit-or-delete-actions="hasEditOrDeleteActions"
                    :type-label="typeLabel"
                    :list-status-label="listStatusLabel"
                    :collapsed-header-line="collapsedHeaderLine"
                    :show-collapsed-urgency-alert="showCollapsedUrgencyAlert"
                    :show-collapsed-supply-days-summary="
                        showCollapsedSupplyDaysSummary
                    "
                    :stock-progress-tone="stockProgressTone"
                    :stock-progress-percent="stockProgressPercent"
                    :supply-estimate-line="supplyEstimateLine"
                    :stock-progress-aria-label="stockProgressAriaLabel"
                    :medication-pill-wrap-tone-class="medicationPillWrapToneClass"
                    :medication-pill-icon-class="medicationPillIconClass"
                />

                <CollapsibleContent>
                    <div class="space-y-6 pt-4">
                        <MedicationCardDetailsPanel
                            :medication="medication"
                            :dose-line="doseLine"
                            :strength-line="strengthLine"
                            :sorted-dose-times="sortedDoseTimes"
                            :dose-times-display="doseTimesDisplay"
                            :schedule-date-row="scheduleDateRow"
                            :prescription-expiry-date-row="
                                prescriptionExpiryDateRow
                            "
                            :has-medication-details-group="
                                hasMedicationDetailsGroup
                            "
                            :note-preview="notePreview"
                            :show-stock="props.showStock"
                            :show-stock-summary="props.showStockSummary"
                            :stock-controls-first="false"
                            :stock-update-route-name="props.stockUpdateRouteName"
                        />
                    </div>
                </CollapsibleContent>

                <PatientListCardDetailsToggle
                    :mode="isOpen ? 'collapse' : 'expand'"
                    :label="
                        t(
                            isOpen
                                ? 'patient.medications.cardCollapseHint'
                                : 'patient.medications.cardExpandHint',
                        )
                    "
                    :ariaLabel="
                        t(
                            isOpen
                                ? 'patient.medications.hideDetails'
                                : 'patient.medications.showDetails',
                        )
                    "
                />
            </Collapsible>
        </CardContent>
    </Card>
</template>
