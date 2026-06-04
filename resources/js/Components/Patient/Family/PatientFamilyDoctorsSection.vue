<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import PatientFamilyCareTeamCollapsibleSection from '@/Components/Patient/Family/PatientFamilyCareTeamCollapsibleSection.vue';
import PatientFamilyCareTeamRowItem from '@/Components/Patient/Family/PatientFamilyCareTeamRowItem.vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { InputError } from '@/Components/ui/input-error';
import { Label } from '@/Components/ui/label';
import { formatCareTeamExpiry } from '@/lib/patient/careTeam/formatCareTeamExpiry';
import type {
    LinkedCareTeamMember,
    PendingCareTeamInvitation,
} from '@/lib/types';
import { cn } from '@/lib/utils';
import { validatePatientEmailField } from '@/lib/validation/validatePatientEmailField';

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

const sectionHeadingClass =
    'text-xl font-bold leading-snug text-text-heading md:text-2xl';

const bodyTextClass = 'text-base leading-relaxed text-text-muted';

const labelClass =
    'mb-2 block text-base font-semibold leading-snug text-text-heading md:text-lg';

const fieldInputClass =
    'h-12 w-full rounded-2xl border-2 border-border bg-surface px-4 text-base leading-normal text-text placeholder:text-text-muted focus-visible:border-focus focus-visible:ring-2 focus-visible:ring-focus/25 md:h-14 md:text-lg';

const doctorInviteForm = useForm({
    email: '',
});

function submitDoctorInvite(): void {
    if (props.doctorInvitationStoreUrl === '') {
        return;
    }

    const emailError = validatePatientEmailField(doctorInviteForm.email, {
        required: t('patient.doctors.emailRequired'),
        invalid: t('patient.doctors.emailInvalid'),
    });

    if (emailError !== null) {
        doctorInviteForm.setError('email', emailError);

        return;
    }

    doctorInviteForm.post(props.doctorInvitationStoreUrl, {
        preserveScroll: true,
        onSuccess: () => {
            doctorInviteForm.reset();
            doctorInviteForm.clearErrors();
        },
    });
}

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
    <section
        id="doctors"
        class="border-border bg-surface scroll-mt-24 rounded-2xl border-2 p-6 shadow-sm sm:p-8"
        aria-labelledby="family-doctors-heading"
    >
        <h2 id="family-doctors-heading" :class="sectionHeadingClass">
            {{ t('patient.doctors.inviteHeading') }}
        </h2>
        <p class="mt-3" :class="bodyTextClass">
            {{ t('patient.doctors.inviteIntro') }}
        </p>

        <form
            class="mt-6 flex flex-col gap-5"
            @submit.prevent="submitDoctorInvite"
        >
            <div>
                <Label for="doctor-invite-email" :class="labelClass">
                    {{ t('patient.doctors.emailLabel') }}
                </Label>
                <Input
                    id="doctor-invite-email"
                    v-model="doctorInviteForm.email"
                    type="email"
                    autocomplete="email"
                    :class="
                        cn(
                            fieldInputClass,
                            doctorInviteForm.errors.email
                                ? 'border-danger ring-danger/20 ring-2'
                                : null,
                        )
                    "
                    :placeholder="t('patient.doctors.emailPlaceholder')"
                    :aria-invalid="Boolean(doctorInviteForm.errors.email)"
                />
                <InputError
                    class="mt-2"
                    :message="doctorInviteForm.errors.email"
                    variant="inline"
                />
            </div>

            <Button
                type="submit"
                class="min-h-12 w-full touch-manipulation text-base font-semibold sm:w-auto md:min-h-14 md:text-lg"
                :disabled="
                    doctorInviteForm.processing ||
                    props.doctorInvitationStoreUrl === ''
                "
            >
                {{ t('patient.doctors.sendInvite') }}
            </Button>
        </form>

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
    </section>
</template>
