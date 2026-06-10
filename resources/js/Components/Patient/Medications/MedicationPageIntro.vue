<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ClipboardList, Pill } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import PatientPageIntroActionBar from '@/Components/Patient/PatientPageIntroActionBar.vue';
import { Button } from '@/Components/ui/button';
import { mobileShellPageIntroButtonClass } from '@/lib/shell/mobileShellTypography';

const props = defineProps<{
    canCreateMedication: boolean;
    hasActiveMedications: boolean;
}>();

const emit = defineEmits<{
    newMedicationClick: [];
}>();

const { t } = useI18n();

const show = computed(
    () => props.canCreateMedication || props.hasActiveMedications,
);
</script>

<template>
    <PatientPageIntroActionBar :show="show">
        <Button
            v-if="canCreateMedication"
            size="lg"
            :class="mobileShellPageIntroButtonClass"
            type="button"
            @click="emit('newMedicationClick')"
        >
            <Pill class="size-6 shrink-0" aria-hidden="true" />
            {{ t('patient.medications.newMedication') }}
        </Button>

        <Button
            v-if="hasActiveMedications"
            as-child
            variant="outline"
            size="lg"
            :class="mobileShellPageIntroButtonClass"
        >
            <Link :href="route('patient.medications.share-with-pharmacist')">
                <ClipboardList class="size-6 shrink-0" aria-hidden="true" />
                {{ t('patient.medications.shareWithPharmacist.button') }}
            </Link>
        </Button>
    </PatientPageIntroActionBar>
</template>
