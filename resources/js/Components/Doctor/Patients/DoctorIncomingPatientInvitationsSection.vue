<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { UserPlus } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import DoctorCollapsibleSection from '@/Components/Doctor/Patients/DoctorCollapsibleSection.vue';
import { Button } from '@/Components/ui/button';
import { formatCareTeamExpiry } from '@/lib/patient/careTeam/formatCareTeamExpiry';
import type { IncomingCareTeamInvitation } from '@/lib/types';

const props = defineProps<{
    invitations: IncomingCareTeamInvitation[];
}>();

const { t } = useI18n();
const isOpen = ref(false);

const hasInvitations = computed(() => props.invitations.length > 0);

const collapsedSummary = computed((): string => {
    if (!hasInvitations.value) {
        return t('doctor.patients.incomingEmpty');
    }

    if (props.invitations.length === 1) {
        return t('doctor.patients.incomingCollapsedOne');
    }

    return t('doctor.patients.incomingCollapsedMany', {
        count: String(props.invitations.length),
    });
});

function acceptInvitation(invitation: IncomingCareTeamInvitation): void {
    router.post(invitation.accept_url, {}, { preserveScroll: true });
}
</script>

<template>
    <DoctorCollapsibleSection
        v-model:open="isOpen"
        :heading="t('doctor.patients.incomingHeading')"
        :toggle-label="t('doctor.patients.incomingToggle')"
        :collapsed-summary="collapsedSummary"
        icon-wrapper-class="bg-primary/12 text-primary"
    >
        <template #icon>
            <UserPlus class="size-5" />
        </template>

        <p class="text-sm leading-relaxed text-text-muted">
            {{ t('doctor.patients.incomingIntro') }}
        </p>

        <ul
            v-if="hasInvitations"
            class="flex flex-col gap-3"
        >
            <li
                v-for="invitation in props.invitations"
                :key="invitation.public_id"
                class="flex flex-col gap-3 rounded-xl border border-border bg-surface-2/50 p-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="min-w-0">
                    <p class="text-base font-semibold text-text-heading">
                        {{ t('doctor.patients.incomingFrom', { name: invitation.patient_name }) }}
                    </p>
                    <p class="mt-1 text-sm text-text-muted">
                        {{ t('doctor.patients.incomingExpires', { date: formatCareTeamExpiry(invitation.expires_at) }) }}
                    </p>
                </div>

                <Button
                    type="button"
                    class="w-full shrink-0 sm:w-auto"
                    @click="acceptInvitation(invitation)"
                >
                    {{ t('doctor.patients.incomingAccept') }}
                </Button>
            </li>
        </ul>

        <p
            v-else
            class="text-sm leading-relaxed text-text-muted"
        >
            {{ t('doctor.patients.incomingEmpty') }}
        </p>
    </DoctorCollapsibleSection>
</template>
