<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import PatientLayout from '@/Layouts/PatientLayout.vue';

const { t } = useI18n();
</script>

<template>
    <Head>
        <title>{{ t('patient.dashboard.title') }}</title>
    </Head>

    <PatientLayout>
        <h1 class="text-2xl font-semibold text-text-heading">
            {{ t('patient.dashboard.heading') }}
        </h1>
    </PatientLayout>
</template>
