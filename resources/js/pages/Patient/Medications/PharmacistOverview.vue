<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import PatientPageShell from '@/Components/Patient/PatientPageShell.vue';
import PatientLayout from '@/Layouts/PatientLayout.vue';

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
        <PatientPageShell :title="t('patient.medications.pharmacistOverview.title')">
            <div class="space-y-3">
                <h1 class="text-2xl font-bold leading-tight text-text-heading md:text-3xl">
                    {{ t('patient.medications.pharmacistOverview.title') }}
                </h1>
                <p class="text-base leading-relaxed text-text-muted md:text-lg">
                    {{ t('patient.medications.pharmacistOverview.description') }}
                </p>
            </div>

            <ul
                class="flex min-w-0 w-full flex-col gap-5"
                :aria-label="t('patient.medications.pharmacistOverview.title')"
            >
                <li
                    v-for="(medicationName, index) in props.medication_names"
                    :key="`${medicationName}-${index}`"
                    class="min-w-0"
                >
                    <Card
                        class="min-w-0 w-full rounded-3xl border border-border bg-surface text-text shadow-md shadow-black/[0.04]"
                    >
                        <CardContent class="p-6 sm:p-7">
                            <p class="text-lg font-bold leading-snug text-text-heading sm:text-xl">
                                {{ medicationName }}
                            </p>
                        </CardContent>
                    </Card>
                </li>
            </ul>

            <div class="border-t border-border pt-4">
                <Button
                    as-child
                    size="lg"
                    class="min-h-14 w-full touch-manipulation gap-2.5 px-6 font-body text-lg font-bold"
                >
                    <Link :href="route('patient.medications')">
                        {{ t('patient.medications.pharmacistOverview.back') }}
                    </Link>
                </Button>
            </div>
        </PatientPageShell>
    </PatientLayout>
</template>
