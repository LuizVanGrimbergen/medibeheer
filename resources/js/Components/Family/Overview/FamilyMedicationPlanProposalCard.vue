<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { Copy, Pencil } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import PatientFormWizardFooter from '@/Components/Patient/form/PatientFormWizardFooter.vue';
import PatientFormWizardFooterButton from '@/Components/Patient/form/PatientFormWizardFooterButton.vue';
import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import type { FamilyMedicationPlanProposalSummary } from '@/lib/family/medicationPlans/familyMedicationPlanProposalSummary';
const props = defineProps<{
    proposal: FamilyMedicationPlanProposalSummary;
}>();

const { t } = useI18n();

const cardEmail = computed((): string | null => {
    const email = props.proposal.patient_email?.trim();

    if (email === undefined || email.length < 1) {
        return null;
    }

    return email;
});

const showRevokeAction = computed((): boolean => props.proposal.can_revoke);

function revokeProposal(): void {
    router.post(props.proposal.revoke_url, {}, { preserveScroll: true });
}

function duplicateProposal(): void {
    router.post(props.proposal.duplicate_url, {}, { preserveScroll: true });
}
</script>

<template>
    <Card
        class="border-border/80 bg-surface text-text w-full min-w-0 rounded-3xl border shadow-md shadow-black/[0.04]"
    >
        <CardContent class="space-y-3 p-4 md:p-4">
            <div class="flex items-start gap-3">
                <div class="min-w-0 flex-1 space-y-2.5">
                    <p
                        v-if="cardEmail !== null"
                        class="text-text-heading text-base leading-snug font-semibold md:text-[0.9375rem]"
                    >
                        {{ cardEmail }}
                    </p>

                    <p
                        v-if="proposal.patient_name !== null"
                        class="text-text-muted text-sm leading-snug"
                    >
                        {{
                            t('family.overview.medicationPlansAcceptedBy', {
                                name: proposal.patient_name,
                            })
                        }}
                    </p>
                </div>

                <div
                    v-if="proposal.can_edit || proposal.can_duplicate"
                    class="flex shrink-0 gap-1"
                >
                    <Button
                        v-if="proposal.can_edit"
                        as-child
                        variant="ghost"
                        size="icon"
                        class="size-10 shrink-0 rounded-xl"
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
                        class="size-10 shrink-0 rounded-xl"
                        :aria-label="t('family.medicationPlans.edit')"
                        @click="duplicateProposal"
                    >
                        <Copy :size="18" />
                    </Button>
                </div>
            </div>

            <PatientFormWizardFooter v-if="showRevokeAction" class="pt-1">
                <PatientFormWizardFooterButton
                    variant="danger"
                    @click="revokeProposal"
                >
                    {{ t('family.medicationPlans.revoke') }}
                </PatientFormWizardFooterButton>
            </PatientFormWizardFooter>
        </CardContent>
    </Card>
</template>
