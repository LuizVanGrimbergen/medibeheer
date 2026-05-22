<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Link2 } from 'lucide-vue-next';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import FamilyOverviewCollapsibleSection from '@/Components/Family/Overview/FamilyOverviewCollapsibleSection.vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { InputError } from '@/Components/ui/input-error';
import { Label } from '@/Components/ui/label';

const { t } = useI18n();

const isOpen = ref(false);

const acceptForm = useForm({
    code: '',
});

function submitAccept(): void {
    acceptForm.post(route('family.invitations.accept'), {
        preserveScroll: true,
        onSuccess: () => {
            acceptForm.reset();
            acceptForm.clearErrors();
        },
    });
}
</script>

<template>
    <FamilyOverviewCollapsibleSection
        v-model:open="isOpen"
        :heading="t('family.invitation.acceptHeading')"
        :toggle-label="t('family.invitation.acceptToggle')"
        :collapsed-summary="t('family.invitation.acceptCollapsedSummary')"
        collapsed-summary-class="line-clamp-2"
        icon-wrapper-class="bg-primary/12 text-primary"
        content-class="flex max-w-md flex-col gap-4 border-t border-border px-4 pb-4 pt-4 md:gap-3 md:px-5 md:pb-5 md:pt-4"
    >
        <template #icon>
            <Link2 class="size-5" />
        </template>

        <form
            class="flex flex-col gap-4 md:gap-3"
            novalidate
            @submit.prevent="submitAccept"
        >
            <div class="flex flex-col gap-2">
                <Label
                    for="family-invite-code"
                    class="text-sm font-semibold text-text-heading"
                >
                    {{ t('family.invitation.codeLabel') }}
                </Label>
                <p class="text-sm leading-snug text-text-muted">
                    {{ t('family.invitation.acceptCollapsedSummary') }}
                </p>
                <Input
                    id="family-invite-code"
                    v-model="acceptForm.code"
                    type="text"
                    autocomplete="one-time-code"
                    autocapitalize="characters"
                    spellcheck="false"
                    :placeholder="t('family.invitation.codePlaceholder')"
                    class="h-auto w-full rounded-xl border-border bg-surface px-4 py-3 font-mono text-base tracking-wide text-text uppercase placeholder:font-sans placeholder:normal-case placeholder:tracking-normal placeholder:text-text-muted focus-visible:ring-focus/20"
                />
                <InputError :message="acceptForm.errors.code" />
            </div>

            <Button
                type="submit"
                class="w-full md:w-fit"
                :disabled="acceptForm.processing"
            >
                {{ t('family.invitation.submit') }}
            </Button>
        </form>
    </FamilyOverviewCollapsibleSection>
</template>
