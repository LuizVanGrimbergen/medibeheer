<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import ActivePatientBadge from '@/Components/Family/ActivePatientBadge.vue';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { InputError } from '@/Components/ui/input-error';
import { Label } from '@/Components/ui/label';
import FamilyLayout from '@/Layouts/FamilyLayout.vue';
import type { FamilyDashboardProps } from '@/lib/types';

const props = defineProps<{
    family: FamilyDashboardProps;
}>();

const { t } = useI18n();

const acceptForm = useForm({
    code: '',
});

function switchPatient(switchUrl: string): void {
    router.post(switchUrl, {}, { preserveScroll: true });
}

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
    <Head>
        <title>{{ t('family.overview.title') }}</title>
    </Head>

    <FamilyLayout>
        <div class="flex flex-col gap-8">
            <div>
                <h1 class="text-2xl font-semibold text-text-heading">
                    {{ t('family.overview.heading') }}
                </h1>
            </div>

            <Card
                v-if="props.family.patients.length > 0"
                class="border-border"
            >
                <CardHeader>
                    <CardTitle>{{ t('family.overview.linkedPatientsHeading') }}</CardTitle>
                    <CardDescription v-if="props.family.active_patient_id !== null">
                        <ActivePatientBadge
                            :family="props.family"
                            variant="inline"
                        />
                    </CardDescription>
                </CardHeader>
                <CardContent class="flex flex-col gap-2 sm:flex-row sm:flex-wrap">
                    <Button
                        v-for="p in props.family.patients"
                        :key="p.id"
                        type="button"
                        :variant="p.is_active ? 'default' : 'outline'"
                        class="w-full justify-start truncate sm:w-auto sm:justify-center"
                        @click="switchPatient(p.switch_url)"
                    >
                        {{ p.name }}
                    </Button>
                </CardContent>
            </Card>

            <Card
                class="border-border"
            >
                <CardHeader>
                    <CardTitle>{{ t('family.invitation.acceptHeading') }}</CardTitle>
                    <CardDescription>
                        {{ t('family.invitation.acceptDescription') }}
                    </CardDescription>
                </CardHeader>
                <CardContent class="flex max-w-md flex-col gap-4">
                    <div class="flex flex-col gap-2">
                        <Label for="family-invite-code">{{ t('family.invitation.codeLabel') }}</Label>
                        <Input
                            id="family-invite-code"
                            v-model="acceptForm.code"
                            type="text"
                            autocomplete="one-time-code"
                            autocapitalize="characters"
                            spellcheck="false"
                            :placeholder="t('family.invitation.codePlaceholder')"
                        />
                        <InputError :message="acceptForm.errors.code" />
                    </div>
                    <Button
                        type="button"
                        class="w-full sm:w-fit"
                        :disabled="acceptForm.processing"
                        @click="submitAccept"
                    >
                        {{ t('family.invitation.submit') }}
                    </Button>
                </CardContent>
            </Card>
        </div>
    </FamilyLayout>
</template>
