<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import PatientFamilyCareTeamCollapsibleSection from '@/Components/Patient/Family/PatientFamilyCareTeamCollapsibleSection.vue';
import PatientFamilyCareTeamInviteForm from '@/Components/Patient/Family/PatientFamilyCareTeamInviteForm.vue';
import PatientFamilyCareTeamRowItem from '@/Components/Patient/Family/PatientFamilyCareTeamRowItem.vue';
import PatientFamilySectionCard from '@/Components/Patient/Family/PatientFamilySectionCard.vue';
import { usePatientFamilyCareTeamEmailInvite } from '@/composables/patient/usePatientFamilyCareTeamEmailInvite';
import { formatCareTeamExpiry } from '@/lib/patient/careTeam/formatCareTeamExpiry';
import { mobileShellSectionHeadingClass } from '@/lib/shell/mobileShellTypography';
import type {
    LinkedCareTeamMember,
    PendingCareTeamInvitation,
} from '@/lib/types';

const props = withDefaults(
    defineProps<{
        linkedFamilyMembers?: LinkedCareTeamMember[];
        familyInvitations?: PendingCareTeamInvitation[];
        familyInvitationStoreUrl?: string;
    }>(),
    {
        linkedFamilyMembers: () => [],
        familyInvitations: () => [],
        familyInvitationStoreUrl: '',
    },
);

const { t } = useI18n();

const pendingFamilyInvitationsOpen = ref(false);
const linkedFamilyMembersOpen = ref(false);

const { form: inviteForm, submitInvite } = usePatientFamilyCareTeamEmailInvite(
    () => props.familyInvitationStoreUrl,
    () => ({
        required: t('patient.family.emailRequired'),
        invalid: t('patient.family.emailInvalid'),
    }),
);

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
    <PatientFamilySectionCard aria-labelledby="family-invite-heading">
        <h2 id="family-invite-heading" :class="mobileShellSectionHeadingClass">
            {{ t('patient.family.inviteHeading') }}
        </h2>

        <PatientFamilyCareTeamInviteForm
            input-id="family-invite-email"
            :email="inviteForm.email"
            :email-error="inviteForm.errors.email"
            :processing="inviteForm.processing"
            :disabled="props.familyInvitationStoreUrl === ''"
            :email-label="t('patient.family.emailLabel')"
            :email-placeholder="t('patient.family.emailPlaceholder')"
            :submit-label="t('patient.family.sendInvite')"
            @update:email="inviteForm.email = $event"
            @submit="submitInvite"
        />

        <PatientFamilyCareTeamCollapsibleSection
            v-if="props.familyInvitations.length > 0"
            v-model:open="pendingFamilyInvitationsOpen"
            :heading="t('patient.family.pendingHeading')"
            :count="props.familyInvitations.length"
            :collapsed-one="t('patient.family.pendingCollapsedOne')"
            :collapsed-many="t('patient.family.pendingCollapsedMany')"
        >
            <PatientFamilyCareTeamRowItem
                v-for="inv in props.familyInvitations"
                :key="inv.public_id"
                layout="collapsible"
                :title="inv.invited_email"
                :subtitle="
                    t('patient.family.pendingOutgoingExpiresAt', {
                        date: formatCareTeamExpiry(inv.expires_at),
                    })
                "
                :action-label="t('patient.family.revoke')"
                :confirm-title="t('patient.family.revokeConfirmTitle')"
                :confirm-description="t('patient.family.revokeConfirmMessage')"
                :confirm-label="t('patient.family.revokeConfirmAction')"
                :cancel-label="t('patient.family.cancel')"
                @action="revokeInvitation(inv)"
            />
        </PatientFamilyCareTeamCollapsibleSection>

        <PatientFamilyCareTeamCollapsibleSection
            v-if="props.linkedFamilyMembers.length > 0"
            v-model:open="linkedFamilyMembersOpen"
            :heading="t('patient.family.linkedHeading')"
            :count="props.linkedFamilyMembers.length"
            :collapsed-one="t('patient.family.linkedCollapsedOne')"
            :collapsed-many="t('patient.family.linkedCollapsedMany')"
        >
            <PatientFamilyCareTeamRowItem
                v-for="member in props.linkedFamilyMembers"
                :key="member.public_id"
                layout="collapsible"
                :title="member.name"
                :action-label="t('patient.family.unlink')"
                :confirm-title="t('patient.family.unlinkConfirmTitle')"
                :confirm-description="
                    t('patient.family.unlinkConfirmMessage', {
                        name: member.name,
                    })
                "
                :confirm-label="t('patient.family.unlinkConfirmAction')"
                :cancel-label="t('patient.family.cancel')"
                @action="unlinkFamilyMember(member)"
            />
        </PatientFamilyCareTeamCollapsibleSection>
    </PatientFamilySectionCard>
</template>
