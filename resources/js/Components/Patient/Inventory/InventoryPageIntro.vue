<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Palmtree } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import { Button } from '@/Components/ui/button';
import {
    patientPageActionsBarClass,
    patientPageActionsGridClass,
    patientPageIntroButtonClass,
} from '@/lib/patient/patientPageTypography';

defineProps<{
    showVacationButton: boolean;
}>();

const { t } = useI18n();

</script>

<template>
    <div
        v-if="showVacationButton"
        :class="patientPageActionsBarClass"
    >
        <div :class="patientPageActionsGridClass">
            <Button
                as-child
                size="lg"
                :class="patientPageIntroButtonClass"
            >
                <Link :href="route('patient.inventory.vacation')">
                    <Palmtree
                        class="size-6 shrink-0"
                        aria-hidden="true"
                    />
                    {{ t('patient.inventory.vacationButton') }}
                </Link>
            </Button>
        </div>
    </div>
</template>
