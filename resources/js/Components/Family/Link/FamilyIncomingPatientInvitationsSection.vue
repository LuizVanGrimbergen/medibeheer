<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { UserPlus } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import FamilyOverviewCollapsibleSection from '@/Components/Family/Overview/FamilyOverviewCollapsibleSection.vue';
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
        return t('family.link.incomingInvitationsEmpty');
    }

    if (props.invitations.length === 1) {
        return t('family.link.incomingInvitationsCollapsedOne');
    }

    return t('family.link.incomingInvitationsCollapsedMany', {
        count: String(props.invitations.length),
    });
});

function acceptInvitation(invitation: IncomingCareTeamInvitation): void {
    router.post(invitation.accept_url, {}, { preserveScroll: true });
}
</script>

<template>
    <FamilyOverviewCollapsibleSection
        v-model:open="isOpen"
        :heading="t('family.link.incomingInvitationsHeading')"
        :toggle-label="t('family.link.incomingInvitationsToggle')"
        :collapsed-summary="collapsedSummary"
        collapsed-summary-class="line-clamp-2"
        icon-wrapper-class="bg-role-family/12 text-role-family"
        content-class="flex flex-col gap-4 border-t border-border px-4 pb-4 pt-4 md:gap-3 md:px-5 md:pb-5 md:pt-4"
    >
        <template #icon>
            <UserPlus :size="20" :stroke-width="1.75" />
        </template>

        <ul v-if="hasInvitations" class="flex flex-col gap-3">
            <li
                v-for="invitation in props.invitations"
                :key="invitation.public_id"
                class="border-border bg-surface-2/50 flex flex-col gap-3 rounded-xl border p-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="min-w-0">
                    <p class="text-text-heading text-base font-semibold">
                        {{
                            t('family.link.incomingInvitationsFrom', {
                                name: invitation.patient_name,
                            })
                        }}
                    </p>
                    <p class="text-text-muted mt-1 text-sm">
                        {{
                            t('family.link.incomingInvitationsExpires', {
                                date: formatCareTeamExpiry(
                                    invitation.expires_at,
                                ),
                            })
                        }}
                    </p>
                </div>

                <Button
                    type="button"
                    class="w-full shrink-0 sm:w-auto"
                    @click="acceptInvitation(invitation)"
                >
                    {{ t('family.link.incomingInvitationsAccept') }}
                </Button>
            </li>
        </ul>

        <p v-else class="text-text-muted text-sm leading-relaxed">
            {{ t('family.link.incomingInvitationsEmpty') }}
        </p>
    </FamilyOverviewCollapsibleSection>
</template>
