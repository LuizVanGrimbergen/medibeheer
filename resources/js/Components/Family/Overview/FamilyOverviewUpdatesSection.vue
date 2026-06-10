<script setup lang="ts">
import { Bell } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import CollapsibleSectionCard from '@/Components/ui/collapsible-section/CollapsibleSectionCard.vue';
import FamilyUpdatesDayContent from '@/Components/Family/Updates/FamilyUpdatesDayContent.vue';
import type { MedicationIntakeHistorySlot } from '@/lib/patient/medications/history/medicationIntakeHistoryTypes';
import type { DailyCheckin } from '@/lib/types';

const props = withDefaults(
    defineProps<{
        checkins: DailyCheckin[];
        medicationIntakes: MedicationIntakeHistorySlot[];
        defaultOpen?: boolean;
    }>(),
    {
        defaultOpen: false,
    },
);

const { t } = useI18n();

const isOpen = ref(props.defaultOpen);

const updateCount = computed(
    (): number => props.checkins.length + props.medicationIntakes.length,
);

const collapsedSummary = computed((): string => {
    const count = updateCount.value;

    if (count === 0) {
        return t('family.updates.emptyToday');
    }

    if (count === 1) {
        return t('family.updates.collapsedSummaryOne');
    }

    return t('family.updates.collapsedSummaryMany', {
        count: String(count),
    });
});
</script>

<template>
    <div id="family-overview-updates">
        <CollapsibleSectionCard
            v-model:open="isOpen"
            :heading="t('family.updates.sectionHeading')"
            :toggle-label="t('family.overview.updatesToggle')"
            :collapsed-summary="collapsedSummary"
            icon-wrapper-class="bg-primary/12 text-primary"
        >
            <template #icon>
                <Bell :size="20" :stroke-width="1.75" />
            </template>

            <FamilyUpdatesDayContent
                :checkins="props.checkins"
                :medication-intakes="props.medicationIntakes"
            />
        </CollapsibleSectionCard>
    </div>
</template>
