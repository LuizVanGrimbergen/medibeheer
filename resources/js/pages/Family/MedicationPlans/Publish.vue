<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import FamilyPageShell from '@/Components/Family/FamilyPageShell.vue';
import { buttonVariants } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { InputError } from '@/Components/ui/input-error';
import { Label } from '@/Components/ui/label';
import FamilyLayout from '@/Layouts/FamilyLayout.vue';
import {
    patientFormFieldInputClass,
    patientFormFieldInvalidClass,
    patientFormLabelClass,
} from '@/lib/patient/patientFormFieldClasses';
import type { FamilyDashboardProps, PageProps } from '@/lib/types';
import { validatePatientEmailField } from '@/lib/validation/validatePatientEmailField';
import { cn } from '@/lib/utils';

type FamilyMedicationPlansPageProps = PageProps & {
    family?: FamilyDashboardProps;
};

const props = defineProps<{
    proposal_id: number;
    medication_name: string | null;
    cancel_url: string;
}>();

const { t } = useI18n();
const page = usePage<FamilyMedicationPlansPageProps>();

const form = useForm({
    patient_email: '',
});

const footerPrimaryButtonClass =
    'min-h-12 min-w-0 w-full touch-manipulation gap-2.5 rounded-2xl px-3 text-base font-semibold md:min-h-14 md:flex-1 md:px-4 lg:text-lg';

const footerOutlineButtonClass =
    'min-h-12 min-w-0 w-full touch-manipulation rounded-2xl px-3 text-base font-semibold md:min-h-14 md:flex-1 md:px-4 lg:text-lg';

function submit(): void {
    form.clearErrors('patient_email');

    const clientError = validatePatientEmailField(form.patient_email, {
        required: t('family.medicationPlans.publishPage.emailRequired'),
        invalid: t('family.medicationPlans.publishPage.emailInvalid'),
    });

    if (clientError !== null) {
        form.setError('patient_email', clientError);

        return;
    }

    form.transform((data) => ({
        ...data,
        patient_email: data.patient_email.trim().toLowerCase(),
    }));

    form.post(route('family.medication-plans.publish.store', props.proposal_id), {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head>
        <title>{{ t('family.medicationPlans.publishPage.title') }}</title>
    </Head>

    <FamilyLayout>
        <FamilyPageShell
            :title="t('family.medicationPlans.publishPage.title')"
            :family="page.props.family"
            :show-active-patient="page.props.family?.has_linked_patient ?? false"
        >
            <Card class="rounded-2xl border-border bg-surface shadow-sm">
                <CardHeader class="space-y-1.5 border-b border-border px-5 py-4 sm:px-6">
                    <CardTitle class="text-xl font-bold text-text-heading">
                        {{ t('family.medicationPlans.publishPage.title') }}
                    </CardTitle>
                    <p
                        v-if="props.medication_name !== null"
                        class="text-base font-semibold text-text-heading"
                    >
                        {{ props.medication_name }}
                    </p>
                    <p class="text-sm leading-relaxed text-text-muted">
                        {{ t('family.medicationPlans.publishPage.description') }}
                    </p>
                </CardHeader>
                <CardContent class="px-5 py-6 sm:px-6">
                    <form
                        class="flex min-w-0 flex-col gap-6"
                        novalidate
                        @submit.prevent="submit"
                    >
                        <div class="flex flex-col gap-2">
                            <Label
                                for="medication-plan-patient-email"
                                :class="patientFormLabelClass"
                            >
                                {{ t('family.medicationPlans.publishPage.emailLabel') }}
                            </Label>
                            <Input
                                id="medication-plan-patient-email"
                                v-model="form.patient_email"
                                type="email"
                                autocomplete="email"
                                inputmode="email"
                                :class="
                                    cn(
                                        patientFormFieldInputClass,
                                        form.errors.patient_email
                                            ? patientFormFieldInvalidClass
                                            : null,
                                    )
                                "
                                :placeholder="t('family.medicationPlans.publishPage.emailPlaceholder')"
                                :aria-invalid="Boolean(form.errors.patient_email)"
                            />
                            <InputError
                                class="mt-1"
                                :message="form.errors.patient_email"
                            />
                        </div>

                        <div class="flex min-w-0 w-full flex-col gap-2 md:flex-row-reverse md:gap-3">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                :class="
                                    cn(
                                        buttonVariants({
                                            variant: 'default',
                                            size: 'lg',
                                        }),
                                        footerPrimaryButtonClass,
                                    )
                                "
                            >
                                {{ t('family.medicationPlans.publishPage.submit') }}
                            </button>
                            <Link
                                :href="props.cancel_url"
                                :class="
                                    cn(
                                        buttonVariants({
                                            variant: 'outline',
                                            size: 'lg',
                                        }),
                                        footerOutlineButtonClass,
                                    )
                                "
                            >
                                {{ t('family.medicationPlans.publishPage.cancel') }}
                            </Link>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </FamilyPageShell>
    </FamilyLayout>
</template>
