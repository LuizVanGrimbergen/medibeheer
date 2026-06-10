<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import MobileShellWizardScrollBody from '@/Components/shell/MobileShellWizardScrollBody.vue';
import InventoryVacationDateField from '@/Components/Patient/Inventory/InventoryVacationDateField.vue';
import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import type { PatientInventoryVacationPageState } from '@/composables/patient/usePatientInventoryVacationPage';
import {
    mobileShellFormWizardFooterCancelButtonClass,
    mobileShellFormWizardFooterPrimaryButtonClass,
    mobileShellFormWizardFooterRowClass,
    mobileShellWizardCardClass,
    mobileShellWizardCardInnerClass,
    mobileShellWizardFormClass,
    mobileShellWizardStepPanelClass,
} from '@/lib/shell/mobileShellDialogLayout';

defineProps<{
    form: PatientInventoryVacationPageState['form'];
    minDateIso: string;
}>();

const emit = defineEmits<{
    submit: [];
}>();

const { t } = useI18n();
</script>

<template>
    <form
        :class="mobileShellWizardFormClass"
        novalidate
        @submit.prevent="emit('submit')"
    >
        <MobileShellWizardScrollBody :active="true">
            <div :class="mobileShellWizardStepPanelClass">
                <Card :class="mobileShellWizardCardClass">
                    <CardContent class="p-0">
                        <div
                            :class="[
                                mobileShellWizardCardInnerClass,
                                'space-y-6',
                            ]"
                        >
                            <InventoryVacationDateField
                                id="patient-inventory-vacation-starts-on"
                                v-model="form.starts_on"
                                :label="
                                    t('patient.inventory.vacationStartsOnLabel')
                                "
                                :min="minDateIso"
                                :error="form.errors.starts_on"
                            />
                            <InventoryVacationDateField
                                id="patient-inventory-vacation-ends-on"
                                v-model="form.ends_on"
                                :label="
                                    t('patient.inventory.vacationEndsOnLabel')
                                "
                                :min="form.starts_on || minDateIso"
                                :error="form.errors.ends_on"
                            />
                        </div>
                    </CardContent>
                </Card>
            </div>

            <template #footer>
                <div :class="mobileShellFormWizardFooterRowClass">
                    <Button
                        as-child
                        variant="secondary"
                        size="lg"
                        :class="mobileShellFormWizardFooterCancelButtonClass"
                        :disabled="form.processing"
                    >
                        <Link :href="route('patient.inventory')">
                            {{
                                t('patient.inventory.vacationBackToInventory')
                            }}
                        </Link>
                    </Button>
                    <Button
                        type="submit"
                        variant="default"
                        size="lg"
                        :class="mobileShellFormWizardFooterPrimaryButtonClass"
                        :disabled="form.processing"
                    >
                        {{
                            form.processing
                                ? t('patient.inventory.vacationLoading')
                                : t('patient.inventory.vacationCalculate')
                        }}
                    </Button>
                </div>
            </template>
        </MobileShellWizardScrollBody>
    </form>
</template>
