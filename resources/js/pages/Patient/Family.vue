<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import PatientFamilyDoctorsSection from '@/Components/Patient/Family/PatientFamilyDoctorsSection.vue';
import PatientFamilyMembersSection from '@/Components/Patient/Family/PatientFamilyMembersSection.vue';
import PatientPageShell from '@/Components/Patient/PatientPageShell.vue';
import { buttonVariants } from '@/Components/ui/button';
import PatientLayout from '@/Layouts/PatientLayout.vue';
import { formatCareTeamExpiry } from '@/lib/patient/careTeam/formatCareTeamExpiry';
import type {
    AcceptedMedicationPlanProposal,
    LinkedCareTeamMember,
    PendingCareTeamInvitation,
    PendingMedicationPlanProposal,
} from '@/lib/types';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        family_invitations?: PendingCareTeamInvitation[];
        pending_medication_plans?: PendingMedicationPlanProposal[];
        accepted_medication_plans?: AcceptedMedicationPlanProposal[];
        family_invitation_store_url?: string;
        doctor_invitations?: PendingCareTeamInvitation[];
        linked_doctors?: LinkedCareTeamMember[];
        linked_family_members?: LinkedCareTeamMember[];
        doctor_invitation_store_url?: string;
    }>(),
    {
        family_invitations: () => [],
        pending_medication_plans: () => [],
        accepted_medication_plans: () => [],
        family_invitation_store_url: '',
        doctor_invitations: () => [],
        linked_doctors: () => [],
        linked_family_members: () => [],
        doctor_invitation_store_url: '',
    },
);

const { t } = useI18n();

const sectionHeadingClass =
    'text-xl font-bold leading-snug text-text-heading md:text-2xl';

const subHeadingClass = 'text-lg font-semibold leading-snug text-text-heading';

const bodyTextClass = 'text-base leading-relaxed text-text-muted';

const actionPrimaryButtonClass =
    'min-h-12 min-w-0 w-full touch-manipulation gap-2.5 rounded-2xl px-3 text-base font-semibold sm:w-auto sm:flex-1 md:min-h-14 md:px-4 md:text-lg';

const actionOutlineButtonClass =
    'min-h-12 min-w-0 w-full touch-manipulation rounded-2xl px-3 text-base font-semibold sm:w-auto sm:flex-1 md:min-h-14 md:px-4 md:text-lg';

const actionSecondaryOutlineButtonClass =
    'min-h-12 min-w-0 w-full touch-manipulation rounded-2xl border-2 border-border px-3 text-base font-semibold sm:w-auto sm:flex-1 md:min-h-14 md:px-4 md:text-lg';

function acceptMedicationPlan(proposal: PendingMedicationPlanProposal): void {
    router.post(proposal.accept_url, {}, { preserveScroll: true });
}

function declineMedicationPlan(proposal: PendingMedicationPlanProposal): void {
    router.post(proposal.decline_url, {}, { preserveScroll: true });
}

function reviewMedicationPlan(proposal: PendingMedicationPlanProposal): void {
    router.visit(proposal.review_url);
}
</script>

<template>
    <Head>
        <title>{{ t('patient.family.title') }}</title>
        <meta
            name="description"
            :content="t('patient.family.metaDescription')"
        />
    </Head>

    <PatientLayout>
        <PatientPageShell :title="t('patient.family.heading')">
            <section
                v-if="props.pending_medication_plans.length > 0"
                id="family-pending-plans"
                class="border-primary/40 bg-primary/5 scroll-mt-24 rounded-2xl border-2 p-6 shadow-sm sm:p-8"
                aria-labelledby="family-action-required-heading"
            >
                <h2
                    id="family-action-required-heading"
                    :class="sectionHeadingClass"
                >
                    {{ t('patient.family.actionRequiredHeading') }}
                </h2>
                <p class="mt-3" :class="bodyTextClass">
                    {{ t('patient.family.actionRequiredIntro') }}
                </p>

                <ul class="mt-6 flex flex-col gap-4">
                    <li
                        v-for="plan in props.pending_medication_plans"
                        :key="plan.id"
                        class="border-border bg-surface rounded-2xl border-2 px-4 py-4 sm:px-5 sm:py-5"
                    >
                        <div
                            class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
                        >
                            <div class="min-w-0 space-y-1">
                                <p
                                    class="text-text-heading text-lg leading-snug font-bold md:text-xl"
                                >
                                    {{
                                        plan.medication_name ??
                                        t('family.medicationPlans.unnamed')
                                    }}
                                </p>
                                <p
                                    v-if="plan.family_member_name !== ''"
                                    :class="bodyTextClass"
                                >
                                    {{
                                        t(
                                            'patient.family.pendingMedicationPlansFrom',
                                            {
                                                name: plan.family_member_name,
                                            },
                                        )
                                    }}
                                </p>
                                <p
                                    v-if="plan.expires_at !== null"
                                    class="text-text-muted text-sm md:text-base"
                                >
                                    {{
                                        t('patient.family.expiresAt', {
                                            date: formatCareTeamExpiry(
                                                plan.expires_at,
                                            ),
                                        })
                                    }}
                                </p>
                            </div>

                            <div
                                class="flex w-full min-w-0 flex-col gap-2 sm:flex-row-reverse sm:gap-3"
                            >
                                <button
                                    type="button"
                                    :class="
                                        cn(
                                            buttonVariants({
                                                variant: 'default',
                                                size: 'lg',
                                            }),
                                            actionPrimaryButtonClass,
                                        )
                                    "
                                    @click="acceptMedicationPlan(plan)"
                                >
                                    {{
                                        t(
                                            'patient.family.pendingMedicationPlansAccept',
                                        )
                                    }}
                                </button>
                                <button
                                    type="button"
                                    :class="
                                        cn(
                                            buttonVariants({
                                                variant: 'outline',
                                                size: 'lg',
                                            }),
                                            actionOutlineButtonClass,
                                        )
                                    "
                                    @click="reviewMedicationPlan(plan)"
                                >
                                    {{
                                        t(
                                            'patient.family.pendingMedicationPlansReview',
                                        )
                                    }}
                                </button>
                                <button
                                    type="button"
                                    :class="
                                        cn(
                                            buttonVariants({
                                                variant: 'outline',
                                                size: 'lg',
                                            }),
                                            actionSecondaryOutlineButtonClass,
                                        )
                                    "
                                    @click="declineMedicationPlan(plan)"
                                >
                                    {{
                                        t(
                                            'patient.family.pendingMedicationPlansDecline',
                                        )
                                    }}
                                </button>
                            </div>
                        </div>
                    </li>
                </ul>
            </section>

            <PatientFamilyMembersSection
                :linked-family-members="props.linked_family_members"
                :family-invitations="props.family_invitations"
                :family-invitation-store-url="props.family_invitation_store_url"
            />

            <PatientFamilyDoctorsSection
                :linked-doctors="props.linked_doctors"
                :doctor-invitations="props.doctor_invitations"
                :doctor-invitation-store-url="props.doctor_invitation_store_url"
            />

            <section
                v-if="
                    props.accepted_medication_plans.length > 0 ||
                    props.pending_medication_plans.length === 0
                "
                class="border-border bg-surface rounded-2xl border-2 p-6 shadow-sm sm:p-8"
                aria-labelledby="family-medication-plans-heading"
            >
                <h2
                    id="family-medication-plans-heading"
                    :class="sectionHeadingClass"
                >
                    {{ t('patient.family.pendingMedicationPlansHeading') }}
                </h2>

                <p
                    v-if="
                        props.pending_medication_plans.length === 0 &&
                        props.accepted_medication_plans.length === 0
                    "
                    class="mt-4"
                    :class="bodyTextClass"
                >
                    {{ t('patient.family.pendingMedicationPlansEmpty') }}
                </p>

                <div
                    v-if="props.accepted_medication_plans.length > 0"
                    class="mt-6"
                >
                    <h3 :class="subHeadingClass">
                        {{ t('patient.family.acceptedMedicationPlansHeading') }}
                    </h3>
                    <p class="mt-2" :class="bodyTextClass">
                        {{ t('patient.family.acceptedMedicationPlansIntro') }}
                    </p>

                    <ul class="mt-4 flex flex-col gap-3">
                        <li
                            v-for="plan in props.accepted_medication_plans"
                            :key="plan.id"
                            class="bg-surface border-success/55 dark:border-success/65 rounded-2xl border-2 px-4 py-4 shadow-sm sm:px-5 sm:py-5"
                        >
                            <div class="flex flex-col gap-1">
                                <p
                                    class="text-text-heading text-base font-semibold md:text-lg"
                                >
                                    {{
                                        plan.medication_name ??
                                        t('family.medicationPlans.unnamed')
                                    }}
                                </p>
                                <p
                                    v-if="plan.family_member_name !== ''"
                                    :class="bodyTextClass"
                                >
                                    {{
                                        t(
                                            'patient.family.acceptedMedicationPlansBy',
                                            {
                                                name: plan.family_member_name,
                                            },
                                        )
                                    }}
                                </p>
                                <p
                                    v-if="plan.accepted_at !== null"
                                    class="text-text-muted text-sm md:text-base"
                                >
                                    {{
                                        t('patient.family.acceptedAt', {
                                            date: formatCareTeamExpiry(
                                                plan.accepted_at,
                                            ),
                                        })
                                    }}
                                </p>
                            </div>
                        </li>
                    </ul>
                </div>
            </section>
        </PatientPageShell>
    </PatientLayout>
</template>
