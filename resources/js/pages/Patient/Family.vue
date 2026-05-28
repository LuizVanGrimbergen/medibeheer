<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { Button, buttonVariants } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { InputError } from '@/Components/ui/input-error';
import { Label } from '@/Components/ui/label';
import PatientPageShell from '@/Components/Patient/PatientPageShell.vue';
import PatientLayout from '@/Layouts/PatientLayout.vue';
import type { AcceptedMedicationPlanProposal, PendingFamilyInvitation, PendingMedicationPlanProposal } from '@/lib/types';
import { cn } from '@/lib/utils';
import { validatePatientEmailField } from '@/lib/validation/validatePatientEmailField';

const props = withDefaults(
    defineProps<{
        invitations?: PendingFamilyInvitation[];
        pending_medication_plans?: PendingMedicationPlanProposal[];
        accepted_medication_plans?: AcceptedMedicationPlanProposal[];
        familyInvitationStoreUrl?: string;
    }>(),
    {
        invitations: () => [],
        pending_medication_plans: () => [],
        accepted_medication_plans: () => [],
        familyInvitationStoreUrl: '',
    },
);

const { t } = useI18n();

const labelClass =
    'mb-2 block text-base font-semibold leading-snug text-text-heading';

const fieldInputClass =
    'h-12 w-full rounded-2xl border-2 border-border bg-surface px-4 text-base leading-normal text-text placeholder:text-text-muted focus-visible:border-focus focus-visible:ring-2 focus-visible:ring-focus/25';

const actionPrimaryButtonClass =
    'min-h-12 min-w-0 w-full touch-manipulation gap-2.5 rounded-2xl px-3 text-base font-semibold sm:w-auto sm:flex-1 md:min-h-14 md:px-4 lg:text-lg';

const actionOutlineButtonClass =
    'min-h-12 min-w-0 w-full touch-manipulation rounded-2xl px-3 text-base font-semibold sm:w-auto sm:flex-1 md:min-h-14 md:px-4 lg:text-lg';

const actionDangerOutlineButtonClass =
    'min-h-12 min-w-0 w-full touch-manipulation rounded-2xl border-2 border-danger/40 bg-danger/10 px-3 text-base font-semibold text-danger hover:border-danger hover:bg-danger/20 hover:text-danger sm:w-auto sm:flex-1 md:min-h-14 md:px-4 lg:text-lg';

const revokeDangerButtonClass =
    'min-h-12 w-full shrink-0 touch-manipulation rounded-2xl border-2 !border-danger/40 !bg-danger/10 px-8 text-base font-semibold !text-danger hover:!border-danger hover:!bg-danger/20 hover:!text-danger sm:w-auto';

const inviteForm = useForm({
    email: '',
});

function submitInvite(): void {
    if (props.familyInvitationStoreUrl === '') {
        return;
    }

    const emailError = validatePatientEmailField(inviteForm.email, {
        required: t('patient.family.emailRequired'),
        invalid: t('patient.family.emailInvalid'),
    });

    if (emailError !== null) {
        inviteForm.setError('email', emailError);

        return;
    }

    inviteForm.post(props.familyInvitationStoreUrl, {
        preserveScroll: true,
        onSuccess: () => {
            inviteForm.reset();
            inviteForm.clearErrors();
        },
    });
}

function revokeInvitation(invitation: PendingFamilyInvitation): void {
    router.delete(invitation.revoke_url, {
        preserveScroll: true,
    });
}

function acceptMedicationPlan(proposal: PendingMedicationPlanProposal): void {
    router.post(proposal.accept_url, {}, { preserveScroll: true });
}

function declineMedicationPlan(proposal: PendingMedicationPlanProposal): void {
    router.post(proposal.decline_url, {}, { preserveScroll: true });
}

function reviewMedicationPlan(proposal: PendingMedicationPlanProposal): void {
    router.visit(proposal.review_url);
}

function formatExpiry(iso: string): string {
    const d = new Date(iso);

    if (Number.isNaN(d.getTime())) {
        return '';
    }

    return new Intl.DateTimeFormat('nl-BE', {
        dateStyle: 'short',
        timeStyle: 'short',
    }).format(d);
}

</script>

<template>
    <Head>
        <title>{{ t('patient.family.title') }}</title>
    </Head>

    <PatientLayout>
        <PatientPageShell :title="t('patient.family.heading')">
            <section
                class="rounded-2xl border-2 border-border bg-surface p-6 shadow-sm sm:p-8"
                aria-labelledby="family-invite-heading"
            >
                <h2
                    id="family-invite-heading"
                    class="text-xl font-semibold text-text-heading"
                >
                    {{ t('patient.family.inviteHeading') }}
                </h2>

                <form
                    class="mt-6 flex flex-col gap-5"
                    @submit.prevent="submitInvite"
                >
                    <div>
                        <Label
                            for="family-invite-email"
                            :class="labelClass"
                        >
                            {{ t('patient.family.emailLabel') }}
                        </Label>
                        <Input
                            id="family-invite-email"
                            v-model="inviteForm.email"
                            type="email"
                            autocomplete="email"
                            :class="cn(fieldInputClass, inviteForm.errors.email ? 'border-danger ring-2 ring-danger/20' : null)"
                            :placeholder="t('patient.family.emailPlaceholder')"
                            :aria-invalid="Boolean(inviteForm.errors.email)"
                        />
                        <InputError
                            class="mt-2"
                            :message="inviteForm.errors.email"
                            variant="inline"
                        />
                    </div>

                    <Button
                        type="submit"
                        class="min-h-12 w-full touch-manipulation sm:w-auto"
                        :disabled="inviteForm.processing || props.familyInvitationStoreUrl === ''"
                    >
                        {{ t('patient.family.sendInvite') }}
                    </Button>
                </form>
            </section>

            <section
                id="family-pending-plans"
                class="scroll-mt-24 rounded-2xl border-2 border-border bg-surface p-6 shadow-sm sm:p-8"
                aria-labelledby="family-pending-plans-heading"
            >
                <h2
                    id="family-pending-plans-heading"
                    class="text-xl font-semibold text-text-heading"
                >
                    {{ t('patient.family.pendingMedicationPlansHeading') }}
                </h2>

                <p
                    v-if="props.pending_medication_plans.length === 0 && props.accepted_medication_plans.length === 0"
                    class="mt-6 text-sm text-text-muted"
                >
                    {{ t('patient.family.pendingMedicationPlansEmpty') }}
                </p>

                <div
                    v-if="props.accepted_medication_plans.length > 0"
                    class="mt-6"
                >
                    <h3 class="text-base font-semibold text-text-heading">
                        {{ t('patient.family.acceptedMedicationPlansHeading') }}
                    </h3>
                    <p class="mt-1 text-sm leading-relaxed text-text-muted">
                        {{ t('patient.family.acceptedMedicationPlansIntro') }}
                    </p>

                    <ul class="mt-4 flex flex-col gap-3">
                        <li
                            v-for="plan in props.accepted_medication_plans"
                            :key="plan.id"
                            class="rounded-2xl border-2 bg-surface px-4 py-4 shadow-sm border-success/55 dark:border-success/65 sm:px-5 sm:py-5"
                        >
                            <div class="flex flex-col gap-1">
                                <p class="text-base font-semibold text-text-heading">
                                    {{ plan.medication_name ?? t('family.medicationPlans.unnamed') }}
                                </p>
                                <p
                                    v-if="plan.family_member_name !== ''"
                                    class="text-sm text-text-muted"
                                >
                                    {{
                                        t('patient.family.acceptedMedicationPlansBy', {
                                            name: plan.family_member_name,
                                        })
                                    }}
                                </p>
                                <p
                                    v-if="plan.accepted_at !== null"
                                    class="text-sm text-text-muted"
                                >
                                    {{ t('patient.family.acceptedAt', { date: formatExpiry(plan.accepted_at) }) }}
                                </p>
                            </div>
                        </li>
                    </ul>
                </div>

                <ul
                    v-if="props.pending_medication_plans.length > 0"
                    class="mt-6 flex flex-col gap-4"
                >
                    <li
                        v-for="plan in props.pending_medication_plans"
                        :key="plan.id"
                        class="rounded-2xl border-2 border-border bg-surface px-4 py-4 sm:px-5 sm:py-5"
                    >
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0 space-y-1">
                                <p class="text-lg font-bold leading-snug text-text-heading">
                                    {{ plan.medication_name ?? t('family.medicationPlans.unnamed') }}
                                </p>
                                <p
                                    v-if="plan.family_member_name !== ''"
                                    class="text-sm leading-relaxed text-text-muted"
                                >
                                    {{
                                        t('patient.family.pendingMedicationPlansFrom', {
                                            name: plan.family_member_name,
                                        })
                                    }}
                                </p>
                                <p
                                    v-if="plan.expires_at !== null"
                                    class="text-sm text-text-muted"
                                >
                                    {{ t('patient.family.expiresAt', { date: formatExpiry(plan.expires_at) }) }}
                                </p>
                            </div>

                            <div class="flex min-w-0 w-full flex-col gap-2 sm:flex-row-reverse sm:gap-3">
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
                                    {{ t('patient.family.pendingMedicationPlansAccept') }}
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
                                    {{ t('patient.family.pendingMedicationPlansReview') }}
                                </button>
                                <button
                                    type="button"
                                    :class="
                                        cn(
                                            buttonVariants({
                                                variant: 'outline',
                                                size: 'lg',
                                            }),
                                            actionDangerOutlineButtonClass,
                                        )
                                    "
                                    @click="declineMedicationPlan(plan)"
                                >
                                    {{ t('patient.family.pendingMedicationPlansDecline') }}
                                </button>
                            </div>
                        </div>
                    </li>
                </ul>
            </section>

            <section
                class="rounded-2xl border-2 border-border bg-surface p-6 shadow-sm sm:p-8"
                aria-labelledby="family-pending-heading"
            >
                <h2
                    id="family-pending-heading"
                    class="text-xl font-semibold text-text-heading"
                >
                    {{ t('patient.family.pendingHeading') }}
                </h2>
                <p class="mt-2 text-sm leading-relaxed text-text-muted">
                    {{ t('patient.family.pendingOutgoingIntro') }}
                </p>
                <p
                    v-if="props.invitations.length === 0"
                    class="mt-6 text-sm text-text-muted"
                >
                    {{ t('patient.family.noPending') }}
                </p>
                <ul
                    v-else
                    class="mt-6 flex flex-col gap-3"
                >
                    <li
                        v-for="inv in props.invitations"
                        :key="inv.id"
                        class="flex flex-col gap-3 rounded-2xl border-2 border-border bg-surface px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5"
                    >
                        <div class="min-w-0">
                            <p class="truncate font-medium text-text">
                                {{ t('patient.family.pendingOutgoingItemLabel') }}
                            </p>
                            <p class="text-xs text-text-muted">
                                {{ t('patient.family.expiresAt', { date: formatExpiry(inv.expires_at) }) }}
                            </p>
                        </div>
                        <Button
                            type="button"
                            variant="outline"
                            size="lg"
                            :class="revokeDangerButtonClass"
                            @click="revokeInvitation(inv)"
                        >
                            {{ t('patient.family.revoke') }}
                        </Button>
                    </li>
                </ul>
            </section>
        </PatientPageShell>
    </PatientLayout>
</template>
