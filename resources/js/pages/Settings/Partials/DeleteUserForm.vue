<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { Button } from '@/Components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog';
import { PasswordConfirmField } from '@/Components/ui/password-confirm-field';
import {
    mobileShellConfirmDialogContentClass,
    mobileShellFormWizardFooterCancelButtonClass,
    mobileShellFormWizardFooterPrimaryButtonClass,
    mobileShellDialogOverlayAboveAppChromeClass,
    mobileShellWizardFooterClass,
} from '@/lib/shell/mobileShellDialogLayout';
import { cn } from '@/lib/utils';

const confirmingUserDeletion = ref(false);
const { t } = useI18n();

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;
};

const deleteUser = () => {
    form.delete(route('settings.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => {
            nextTick(() => {
                const passwordField = document.getElementById(
                    'delete-account-password',
                );
                passwordField?.focus();
            });
        },
        onFinish: () => {
            form.reset();
        },
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;

    form.clearErrors();
    form.reset();
};
</script>

<template>
    <section class="space-y-6">
        <header>
            <h2 class="text-primary text-lg font-semibold">
                {{ t('profile.delete.title') }}
            </h2>

            <p class="text-text-muted mt-1 text-sm">
                {{ t('profile.delete.description') }}
            </p>
        </header>

        <Button
            type="button"
            variant="secondary"
            size="lg"
            :class="mobileShellFormWizardFooterCancelButtonClass"
            @click="confirmUserDeletion"
        >
            {{ t('profile.delete.action') }}
        </Button>

        <Dialog
            :open="confirmingUserDeletion"
            @update:open="(open) => !open && closeModal()"
        >
            <DialogContent
                :class="
                    cn(
                        mobileShellConfirmDialogContentClass,
                        'flex min-h-0 flex-col',
                    )
                "
                :overlay-class="
                    mobileShellDialogOverlayAboveAppChromeClass('md')
                "
            >
                <DialogHeader class="shrink-0 text-left">
                    <DialogTitle
                        class="text-text-heading text-xl font-bold md:text-2xl"
                    >
                        {{ t('profile.delete.modalTitle') }}
                    </DialogTitle>

                    <DialogDescription
                        class="text-text-muted text-base leading-relaxed"
                    >
                        {{ t('profile.delete.modalDescription') }}
                    </DialogDescription>
                </DialogHeader>

                <PasswordConfirmField
                    input-id="delete-account-password"
                    v-model="form.password"
                    :label="t('profile.delete.passwordLabel')"
                    :placeholder="t('profile.delete.passwordPlaceholder')"
                    :error="form.errors.password"
                    @enter="deleteUser"
                />

                <div
                    :class="[
                        'flex w-full min-w-0 flex-col gap-2 md:flex-row md:gap-3',
                        mobileShellWizardFooterClass,
                        'mt-auto shrink-0',
                    ]"
                >
                    <Button
                        type="button"
                        variant="default"
                        size="lg"
                        :class="mobileShellFormWizardFooterPrimaryButtonClass"
                        :disabled="form.processing"
                        @click="closeModal"
                    >
                        {{ t('profile.delete.cancel') }}
                    </Button>

                    <Button
                        type="button"
                        variant="secondary"
                        size="lg"
                        :class="mobileShellFormWizardFooterCancelButtonClass"
                        :disabled="form.processing"
                        @click="deleteUser"
                    >
                        {{ t('profile.delete.confirmDelete') }}
                    </Button>
                </div>
            </DialogContent>
        </Dialog>
    </section>
</template>
