<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ClipboardList, Plus } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import FamilyMedicationPlanProposalCard from '@/Components/Family/Overview/FamilyMedicationPlanProposalCard.vue';
import CollapsibleSectionCard from '@/Components/ui/collapsible-section/CollapsibleSectionCard.vue';
import { Button } from '@/Components/ui/button';
import type { FamilyMedicationPlanProposalSummary } from '@/lib/family/medicationPlans/familyMedicationPlanProposalSummary';

const props = defineProps<{
    proposals: FamilyMedicationPlanProposalSummary[];
    hideViewAll?: boolean;
}>();

const { t } = useI18n();
const isOpen = ref(false);

const collapsedSummary = computed((): string => {
    if (props.proposals.length === 0) {
        return t('family.overview.medicationPlansCollapsedEmpty');
    }

    if (props.proposals.length === 1) {
        return t('family.overview.medicationPlansCollapsedOne');
    }

    return t('family.overview.medicationPlansCollapsedMany', {
        count: String(props.proposals.length),
    });
});
</script>

<template>
    <CollapsibleSectionCard
        v-model:open="isOpen"
        :heading="t('family.overview.medicationPlansHeading')"
        :toggle-label="t('family.overview.medicationPlansToggle')"
        :collapsed-summary="collapsedSummary"
        collapsed-summary-class="line-clamp-2"
        icon-wrapper-class="bg-role-family/12 text-role-family"
        content-class="flex flex-col gap-4 border-t border-border px-4 pb-4 pt-4 md:gap-3 md:px-5 md:pb-5 md:pt-4"
    >
        <template #icon>
            <ClipboardList :size="20" :stroke-width="1.75" />
        </template>

        <p class="text-text-muted text-sm leading-relaxed">
            {{ t('family.overview.medicationPlansIntro') }}
        </p>

        <ul v-if="props.proposals.length > 0" class="flex flex-col gap-3">
            <li v-for="proposal in props.proposals" :key="proposal.id">
                <FamilyMedicationPlanProposalCard :proposal="proposal" />
            </li>
        </ul>

        <div class="flex flex-col gap-2 sm:flex-row">
            <Button as-child class="w-full sm:w-auto">
                <Link :href="route('family.medication-plans.create')">
                    <Plus class="size-4" />
                    {{ t('family.medicationPlans.create') }}
                </Link>
            </Button>
            <Button
                v-if="!props.hideViewAll"
                as-child
                variant="outline"
                class="w-full sm:w-auto"
            >
                <Link :href="route('family.link')">
                    {{ t('family.overview.medicationPlansViewAll') }}
                </Link>
            </Button>
        </div>
    </CollapsibleSectionCard>
</template>
