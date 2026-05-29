<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { ClipboardList, Pencil, Plus } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import FamilyOverviewCollapsibleSection from '@/Components/Family/Overview/FamilyOverviewCollapsibleSection.vue';
import { Button } from '@/Components/ui/button';
import type { FamilyMedicationPlanProposalSummary } from '@/lib/family/medicationPlans/familyMedicationPlanProposalSummary';

const props = defineProps<{
    proposals: FamilyMedicationPlanProposalSummary[];
    hideViewAll?: boolean;
}>();

const { t } = useI18n();
const isOpen = ref(true);

const dangerOutlineButtonClass =
    'min-h-12 touch-manipulation gap-2.5 rounded-2xl border-2 border-danger/40 bg-danger/10 px-3 text-base font-semibold text-danger hover:border-danger hover:bg-danger/20 hover:text-danger md:min-h-14 md:px-4 md:text-base lg:text-lg';

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

function statusLabel(status: string): string {
    return t(`family.medicationPlans.status.${status}`);
}

function revokeProposal(proposal: FamilyMedicationPlanProposalSummary): void {
    router.post(proposal.revoke_url, {}, { preserveScroll: true });
}

function duplicateProposal(proposal: FamilyMedicationPlanProposalSummary): void {
    router.post(proposal.duplicate_url, {}, { preserveScroll: true });
}

function proposalItemClass(proposal: FamilyMedicationPlanProposalSummary): string {
    if (proposal.status === 'accepted') {
        return 'min-w-0 rounded-xl border bg-surface text-text shadow-sm border-success/55 dark:border-success/65';
    }

    return 'min-w-0 rounded-xl border border-border bg-surface-2/50';
}
</script>

<template>
    <FamilyOverviewCollapsibleSection
        v-model:open="isOpen"
        :heading="t('family.overview.medicationPlansHeading')"
        :toggle-label="t('family.overview.medicationPlansToggle')"
        :collapsed-summary="collapsedSummary"
        collapsed-summary-class="line-clamp-2"
        icon-wrapper-class="bg-role-family/12 text-role-family"
        content-class="flex flex-col gap-4 border-t border-border px-4 pb-4 pt-4 md:gap-3 md:px-5 md:pb-5 md:pt-4"
    >
        <template #icon>
            <ClipboardList class="size-5" />
        </template>

        <p class="text-sm leading-relaxed text-text-muted">
            {{ t('family.overview.medicationPlansIntro') }}
        </p>

        <ul
            v-if="props.proposals.length > 0"
            class="flex flex-col gap-3"
        >
            <li
                v-for="proposal in props.proposals"
                :key="proposal.id"
                :class="proposalItemClass(proposal)"
            >
                <div class="relative flex flex-col gap-2 px-4 py-3 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <p class="font-semibold text-text-heading">
                            {{ proposal.medication_name ?? t('family.medicationPlans.unnamed') }}
                        </p>
                        <p
                            v-if="proposal.patient_name !== null"
                            class="text-sm text-text-muted"
                        >
                            {{ t('family.overview.medicationPlansAcceptedBy', { name: proposal.patient_name }) }}
                        </p>
                        <p class="text-sm text-text-muted">
                            {{ statusLabel(proposal.status) }}
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <Button
                            v-if="proposal.can_edit"
                            as-child
                            variant="ghost"
                            size="icon"
                            :aria-label="t('family.medicationPlans.edit')"
                        >
                            <Link :href="proposal.edit_url">
                                <Pencil :size="18" />
                            </Link>
                        </Button>
                        <Button
                            v-if="proposal.can_duplicate"
                            type="button"
                            variant="ghost"
                            size="icon"
                            :aria-label="t('family.medicationPlans.edit')"
                            @click="duplicateProposal(proposal)"
                        >
                            <Pencil :size="18" />
                        </Button>
                        <Button
                            v-if="proposal.can_publish"
                            as-child
                            size="sm"
                        >
                            <Link :href="proposal.publish_url">
                                {{ t('family.medicationPlans.publish') }}
                            </Link>
                        </Button>
                        <Button
                            v-if="proposal.can_revoke"
                            type="button"
                            variant="outline"
                            size="lg"
                            :class="dangerOutlineButtonClass"
                            @click="revokeProposal(proposal)"
                        >
                            {{ t('family.medicationPlans.revoke') }}
                        </Button>
                    </div>
                </div>
            </li>
        </ul>

        <p
            v-else
            class="text-sm text-text-muted"
        >
            {{ t('family.overview.medicationPlansEmpty') }}
        </p>

        <div class="flex flex-col gap-2 sm:flex-row">
            <Button
                as-child
                class="w-full sm:w-auto"
            >
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

    </FamilyOverviewCollapsibleSection>
</template>
