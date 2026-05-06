<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { InputError } from '@/Components/ui/input-error';
import { Label } from '@/Components/ui/label';
import PatientLayout from '@/Layouts/PatientLayout.vue';
import type { PendingFamilyInvitation } from '@/lib/types';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        invitations?: PendingFamilyInvitation[];
        familyInvitationStoreUrl?: string;
    }>(),
    {
        invitations: () => [],
        familyInvitationStoreUrl: '',
    },
);

const { t } = useI18n();

const labelClass =
    'mb-2 block text-base font-semibold leading-snug text-text-heading';

const fieldInputClass =
    'h-12 w-full rounded-2xl border-2 border-border bg-surface px-4 text-base leading-normal text-text placeholder:text-text-muted focus-visible:border-focus focus-visible:ring-2 focus-visible:ring-focus/25';

const inviteForm = useForm({
    email: '',
});

function submitInvite(): void {
    if (props.familyInvitationStoreUrl === '') {
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

function formatExpiry(iso: string): string {
    const d = new Date(iso);

    if (Number.isNaN(d.getTime())) {
        return '';
    }

    return d.toLocaleString(undefined, {
        dateStyle: 'short',
        timeStyle: 'short',
    });
}
</script>

<template>
    <Head>
        <title>{{ t('patient.family.title') }}</title>
    </Head>

    <PatientLayout>
        <div class="mx-auto flex w-full max-w-2xl flex-col gap-8">
            <div>
                <h1 class="text-2xl font-semibold text-text-heading">
                    {{ t('patient.family.heading') }}
                </h1>
            </div>

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
                <p class="mt-2 text-sm leading-relaxed text-text-muted">
                    {{ t('patient.family.inviteDescription') }}
                </p>

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
                class="flex flex-col gap-3"
                aria-labelledby="family-pending-heading"
            >
                <h2
                    id="family-pending-heading"
                    class="text-lg font-semibold text-text-heading"
                >
                    {{ t('patient.family.pendingHeading') }}
                </h2>
                <p
                    v-if="props.invitations.length === 0"
                    class="text-sm text-text-muted"
                >
                    {{ t('patient.family.noPending') }}
                </p>
                <ul
                    v-else
                    class="flex flex-col gap-2"
                >
                    <li
                        v-for="inv in props.invitations"
                        :key="inv.id"
                        class="flex flex-col gap-2 rounded-xl border border-border bg-surface px-4 py-3 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div class="min-w-0">
                            <p class="truncate font-medium text-text">
                                {{ inv.invited_email }}
                            </p>
                            <p class="text-xs text-text-muted">
                                {{ t('patient.family.expiresAt', { date: formatExpiry(inv.expires_at) }) }}
                            </p>
                        </div>
                        <Button
                            type="button"
                            variant="outline"
                            class="shrink-0"
                            @click="revokeInvitation(inv)"
                        >
                            {{ t('patient.family.revoke') }}
                        </Button>
                    </li>
                </ul>
            </section>
        </div>
    </PatientLayout>
</template>
