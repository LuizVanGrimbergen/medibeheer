<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { Button } from '@/Components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog';
import { PasswordConfirmField } from '@/Components/ui/password-confirm-field';

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
            <h2 class="text-text text-lg font-medium">
                {{ t('profile.delete.title') }}
            </h2>

            <p class="text-text-muted mt-1 text-sm">
                {{ t('profile.delete.description') }}
            </p>
        </header>

        <Button variant="destructive" @click="confirmUserDeletion">
            {{ t('profile.delete.action') }}
        </Button>

        <Dialog
            :open="confirmingUserDeletion"
            @update:open="(open) => !open && closeModal()"
        >
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>
                        {{ t('profile.delete.modalTitle') }}
                    </DialogTitle>

                    <DialogDescription>
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

                <DialogFooter
                    class="mt-4 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end"
                >
                    <Button variant="outline" @click="closeModal">
                        {{ t('profile.delete.cancel') }}
                    </Button>

                    <Button
                        variant="destructive"
                        :disabled="form.processing"
                        @click="deleteUser"
                    >
                        {{ t('profile.delete.confirmDelete') }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </section>
</template>
