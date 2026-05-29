<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { InputError } from '@/Components/ui/input-error';
import { Label } from '@/Components/ui/label';
import PatientFamilyCareTeamCollapsibleSection from '@/Components/Patient/Family/PatientFamilyCareTeamCollapsibleSection.vue';
import PatientFamilyCareTeamRowItem from '@/Components/Patient/Family/PatientFamilyCareTeamRowItem.vue';
import type { LinkedCareTeamMember, PendingCareTeamInvitation } from '@/lib/types';
import { formatCareTeamExpiry } from '@/lib/patient/careTeam/formatCareTeamExpiry';
import { cn } from '@/lib/utils';
import { validatePatientEmailField } from '@/lib/validation/validatePatientEmailField';

const props = withDefaults(
    defineProps<{
        linked_family_members?: LinkedCareTeamMember[];
        family_invitations?: PendingCareTeamInvitation[];
        family_invitation_store_url?: string;
    }>(),
    {
        linked_family_members: () => [],
        family_invitations: () => [],
        family_invitation_store_url: '',
    },
);

const { t } = useI18n();

const pendingFamilyInvitationsOpen = ref(false);
const linkedFamilyMembersOpen = ref(false);

const sectionHeadingClass = 'text-xl font-bold leading-snug text-text-heading md:text-2xl';

const bodyTextClass = 'text-base leading-relaxed text-text-muted';

const labelClass =
    'mb-2 block text-base font-semibold leading-snug text-text-heading md:text-lg';

const fieldInputClass =
    'h-12 w-full rounded-2xl border-2 border-border bg-surface px-4 text-base leading-normal text-text placeholder:text-text-muted focus-visible:border-focus focus-visible:ring-2 focus-visible:ring-focus/25 md:h-14 md:text-lg';

const inviteForm = useForm({
    email: '',
});

function submitInvite(): void {
    if (props.family_invitation_store_url === '') {
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

    inviteForm.post(props.family_invitation_store_url, {
        preserveScroll: true,
        onSuccess: () => {
            inviteForm.reset();
            inviteForm.clearErrors();
        },
    });
}

function revokeInvitation(invitation: PendingCareTeamInvitation): void {
    router.delete(invitation.revoke_url, {
        preserveScroll: true,
    });
}

function unlinkFamilyMember(member: LinkedCareTeamMember): void {
    router.delete(member.unlink_url, {
        preserveScroll: true,
    });
}
</script>

<template>
    <section
        class="rounded-2xl border-2 border-border bg-surface p-6 shadow-sm sm:p-8"
        aria-labelledby="family-invite-heading"
    >
        <h2
            id="family-invite-heading"
            :class="sectionHeadingClass"
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
                class="min-h-12 w-full touch-manipulation text-base font-semibold md:min-h-14 md:text-lg sm:w-auto"
                :disabled="inviteForm.processing || props.family_invitation_store_url === ''"
            >
                {{ t('patient.family.sendInvite') }}
            </Button>
        </form>

        <PatientFamilyCareTeamCollapsibleSection
            v-if="props.family_invitations.length > 0"
            v-model:open="pendingFamilyInvitationsOpen"
            class="mt-8"
            :heading="t('patient.family.pendingHeading')"
            :toggle-label="t('patient.family.pendingToggle')"
            :count="props.family_invitations.length"
            :collapsed-one="t('patient.family.pendingCollapsedOne')"
            :collapsed-many="t('patient.family.pendingCollapsedMany')"
        >
            <PatientFamilyCareTeamRowItem
                v-for="inv in props.family_invitations"
                :key="inv.public_id"
                :title="t('patient.family.pendingOutgoingItemLabel')"
                :subtitle="t('patient.family.expiresAt', { date: formatCareTeamExpiry(inv.expires_at) })"
                :action-label="t('patient.family.revoke')"
                :confirm-title="t('patient.family.revokeConfirmTitle')"
                :confirm-description="t('patient.family.revokeConfirmMessage')"
                :confirm-label="t('patient.family.revokeConfirmAction')"
                :cancel-label="t('patient.family.cancel')"
                @action="revokeInvitation(inv)"
            />
        </PatientFamilyCareTeamCollapsibleSection>

        <PatientFamilyCareTeamCollapsibleSection
            v-if="props.linked_family_members.length > 0"
            v-model:open="linkedFamilyMembersOpen"
            class="mt-8"
            :heading="t('patient.family.linkedHeading')"
            :toggle-label="t('patient.family.linkedToggle')"
            :count="props.linked_family_members.length"
            :collapsed-one="t('patient.family.linkedCollapsedOne')"
            :collapsed-many="t('patient.family.linkedCollapsedMany')"
        >
            <PatientFamilyCareTeamRowItem
                v-for="member in props.linked_family_members"
                :key="member.public_id"
                :title="member.name"
                :action-label="t('patient.family.unlink')"
                :confirm-title="t('patient.family.unlinkConfirmTitle')"
                :confirm-description="
                    t('patient.family.unlinkConfirmMessage', { name: member.name })
                "
                :confirm-label="t('patient.family.unlinkConfirmAction')"
                :cancel-label="t('patient.family.cancel')"
                @action="unlinkFamilyMember(member)"
            />
        </PatientFamilyCareTeamCollapsibleSection>
    </section>
</template>
