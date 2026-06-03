<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import PatientPageShell from '@/Components/Patient/PatientPageShell.vue';
import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import PatientLayout from '@/Layouts/PatientLayout.vue';
import {
    patientPageIntroClass,
    patientPageTitleClass,
} from '@/lib/patient/patientPageTypography';

const props = withDefaults(
    defineProps<{
        medication_names?: string[];
    }>(),
    {
        medication_names: () => [],
    },
);

const { t } = useI18n();
</script>

<template>
    <Head>
        <title>{{ t('patient.medications.pharmacistOverview.title') }}</title>
    </Head>

    <PatientLayout>
        <PatientPageShell
            :title="t('patient.medications.pharmacistOverview.title')"
        >
            <div class="space-y-3">
                <h1 :class="patientPageTitleClass">
                    {{ t('patient.medications.pharmacistOverview.title') }}
                </h1>
                <p :class="patientPageIntroClass">
                    {{
                        t('patient.medications.pharmacistOverview.description')
                    }}
                </p>
            </div>

            <ul
                class="flex w-full min-w-0 flex-col gap-5"
                :aria-label="t('patient.medications.pharmacistOverview.title')"
            >
                <li
                    v-for="(medicationName, index) in props.medication_names"
                    :key="`${medicationName}-${index}`"
                    class="min-w-0"
                >
                    <Card
                        class="border-border bg-surface text-text w-full min-w-0 rounded-3xl border shadow-md shadow-black/[0.04]"
                    >
                        <CardContent class="p-6 sm:p-7">
                            <p
                                class="text-text-heading text-lg leading-snug font-bold sm:text-xl"
                            >
                                {{ medicationName }}
                            </p>
                        </CardContent>
                    </Card>
                </li>
            </ul>

            <div class="border-border border-t pt-4">
                <Button
                    as-child
                    size="lg"
                    class="font-body min-h-14 w-full touch-manipulation gap-2.5 px-6 text-lg font-bold"
                >
                    <Link :href="route('patient.medications')">
                        {{ t('patient.medications.pharmacistOverview.done') }}
                    </Link>
                </Button>
            </div>
        </PatientPageShell>
    </PatientLayout>
</template>
