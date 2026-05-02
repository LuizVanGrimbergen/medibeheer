<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import DoctorLayout from '@/Layouts/DoctorLayout.vue';

defineProps<{
    patients: { public_id: string; name: string }[];
}>();

const { t } = useI18n();
</script>

<template>
    <Head>
        <title>{{ t('doctor.patients.title') }}</title>
    </Head>

    <DoctorLayout>
        <h1 class="text-2xl font-semibold text-text-heading">
            {{ t('doctor.patients.heading') }}
        </h1>

        <ul
            v-if="patients.length > 0"
            class="mt-6 divide-y divide-border rounded-xl border border-border bg-surface"
        >
            <li
                v-for="patient in patients"
                :key="patient.public_id"
                class="px-4 py-3 text-text-body first:rounded-t-xl last:rounded-b-xl"
            >
                {{ patient.name }}
            </li>
        </ul>

        <p
            v-else
            class="mt-6 text-text-muted"
        >
            {{ t('doctor.patients.empty') }}
        </p>
    </DoctorLayout>
</template>
