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
import {
    mobileShellSectionHeadingClass,
} from '@/lib/shell/mobileShellTypography';
import type {
    LinkedCareTeamMember,
    PendingCareTeamInvitation,
} from '@/lib/types';

const props = withDefaults(
    defineProps<{
        linkedDoctors?: LinkedCareTeamMember[];
        doctorInvitations?: PendingCareTeamInvitation[];
        doctorInvitationStoreUrl?: string;
    }>(),
    {
        linkedDoctors: () => [],
        doctorInvitations: () => [],
        doctorInvitationStoreUrl: '',
    },
);

const { t } = useI18n();

const pendingDoctorInvitationsOpen = ref(false);
const linkedDoctorsOpen = ref(false);

const { form: doctorInviteForm, submitInvite: submitDoctorInvite } =
    usePatientFamilyCareTeamEmailInvite(
        () => props.doctorInvitationStoreUrl,
        () => ({
            required: t('patient.doctors.emailRequired'),
            invalid: t('patient.doctors.emailInvalid'),
        }),
    );

function revokeDoctorInvitation(invitation: PendingCareTeamInvitation): void {
    router.delete(invitation.revoke_url, {
        preserveScroll: true,
    });
}

function unlinkDoctor(doctor: LinkedCareTeamMember): void {
    router.delete(doctor.unlink_url, {
        preserveScroll: true,
    });
}
</script>

<template>
    <PatientFamilySectionCard
        id="doctors"
        scroll-margin
        aria-labelledby="family-doctors-heading"
    >
        <h2 id="family-doctors-heading" :class="mobileShellSectionHeadingClass">
            {{ t('patient.doctors.inviteHeading') }}
        </h2>

        <PatientFamilyCareTeamInviteForm
            input-id="doctor-invite-email"
            :email="doctorInviteForm.email"
            :email-error="doctorInviteForm.errors.email"
            :processing="doctorInviteForm.processing"
            :disabled="props.doctorInvitationStoreUrl === ''"
            :email-label="t('patient.doctors.emailLabel')"
            :email-placeholder="t('patient.doctors.emailPlaceholder')"
            :submit-label="t('patient.doctors.sendInvite')"
            @update:email="doctorInviteForm.email = $event"
            @submit="submitDoctorInvite"
        />

        <PatientFamilyCareTeamCollapsibleSection
            v-if="props.doctorInvitations.length > 0"
            v-model:open="pendingDoctorInvitationsOpen"
            labels-namespace="patient.doctors"
            :heading="t('patient.doctors.pendingHeading')"
            :count="props.doctorInvitations.length"
            :collapsed-one="t('patient.doctors.pendingCollapsedOne')"
            :collapsed-many="t('patient.doctors.pendingCollapsedMany')"
        >
            <PatientFamilyCareTeamRowItem
                v-for="inv in props.doctorInvitations"
                :key="inv.public_id"
                layout="collapsible"
                :title="inv.invited_email"
                :subtitle="
                    t('patient.doctors.pendingExpiresAt', {
                        date: formatCareTeamExpiry(inv.expires_at),
                    })
                "
                :action-label="t('patient.doctors.revoke')"
                :confirm-title="t('patient.doctors.revokeConfirmTitle')"
                :confirm-description="t('patient.doctors.revokeConfirmMessage')"
                :confirm-label="t('patient.doctors.revokeConfirmAction')"
                :cancel-label="t('patient.doctors.cancel')"
                @action="revokeDoctorInvitation(inv)"
            />
        </PatientFamilyCareTeamCollapsibleSection>

        <PatientFamilyCareTeamCollapsibleSection
            v-if="props.linkedDoctors.length > 0"
            v-model:open="linkedDoctorsOpen"
            labels-namespace="patient.doctors"
            :heading="t('patient.doctors.linkedHeading')"
            :count="props.linkedDoctors.length"
            :collapsed-one="t('patient.doctors.linkedCollapsedOne')"
            :collapsed-many="t('patient.doctors.linkedCollapsedMany')"
        >
            <PatientFamilyCareTeamRowItem
                v-for="doctor in props.linkedDoctors"
                :key="doctor.public_id"
                layout="collapsible"
                :title="doctor.name"
                :action-label="t('patient.doctors.unlink')"
                :confirm-title="t('patient.doctors.unlinkConfirmTitle')"
                :confirm-description="
                    t('patient.doctors.unlinkConfirmMessage', {
                        name: doctor.name,
                    })
                "
                :confirm-label="t('patient.doctors.unlinkConfirmAction')"
                :cancel-label="t('patient.doctors.cancel')"
                @action="unlinkDoctor(doctor)"
            />
        </PatientFamilyCareTeamCollapsibleSection>
    </PatientFamilySectionCard>
</template>
