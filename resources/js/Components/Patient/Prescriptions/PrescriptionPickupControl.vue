<script setup lang="ts">
import { PackageCheck } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { Button } from '@/Components/ui/button';
import { patientPageIntroButtonClass } from '@/lib/patient/patientPageTypography';
import type { MedicationPrescriptionPickupStatusValue } from '@/lib/types';

const props = defineProps<{
    pickupStatus: MedicationPrescriptionPickupStatusValue;
    disabled?: boolean;
}>();

const emit = defineEmits<{
    'update:pickupStatus': [value: MedicationPrescriptionPickupStatusValue];
}>();

const { t } = useI18n();

const isPickedUp = computed((): boolean => props.pickupStatus === 'picked_up');

const markButtonClass = patientPageIntroButtonClass;

function markPickedUp(): void {
    if (props.disabled || isPickedUp.value) {
        return;
    }

    emit('update:pickupStatus', 'picked_up');
}
</script>

<template>
    <Button
        v-if="!isPickedUp"
        type="button"
        size="lg"
        :disabled="disabled"
        :class="markButtonClass"
        :aria-label="
            t('patient.prescriptions.pickupStatus.markButtonAriaLabel')
        "
        @click="markPickedUp"
    >
        <PackageCheck class="size-6 shrink-0" aria-hidden="true" />
        {{ t('patient.prescriptions.pickupStatus.markButton') }}
    </Button>
</template>
