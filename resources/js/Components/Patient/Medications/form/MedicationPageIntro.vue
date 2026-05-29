<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ClipboardList, Pill } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { Button } from '@/Components/ui/button';

const props = defineProps<{
    canCreateMedication: boolean;
    hasActiveMedications: boolean;
}>();

const emit = defineEmits<{
    newMedicationClick: [];
}>();

const { t } = useI18n();

const hasActions = computed(
    () => props.canCreateMedication || props.hasActiveMedications,
);

const actionButtonClass =
    'min-h-14 w-full touch-manipulation gap-2.5 px-6 font-body text-lg font-bold sm:px-8';
</script>

<template>
    <div
        class="flex min-w-0 w-full flex-col gap-5 sm:flex-row sm:items-start sm:justify-between sm:gap-6"
    >
        <h1 class="min-w-0 text-3xl font-bold leading-tight text-text-heading sm:text-4xl sm:leading-tight">
            {{ t('patient.medications.listHeading') }}
        </h1>

        <div
            v-if="hasActions"
            class="grid w-full shrink-0 grid-cols-1 gap-3 sm:ml-auto sm:w-max"
        >
            <Button
                v-if="canCreateMedication"
                size="lg"
                :class="actionButtonClass"
                type="button"
                @click="emit('newMedicationClick')"
            >
                <Pill
                    class="size-6 shrink-0"
                    aria-hidden="true"
                />
                {{ t('patient.medications.newMedication') }}
            </Button>

            <Button
                v-if="hasActiveMedications"
                as-child
                variant="outline"
                size="lg"
                :class="actionButtonClass"
            >
                <Link :href="route('patient.medications.pharmacist-overview')">
                    <ClipboardList
                        class="size-6 shrink-0"
                        aria-hidden="true"
                    />
                    {{ t('patient.medications.pharmacistOverview.button') }}
                </Link>
            </Button>
        </div>
    </div>
</template>
