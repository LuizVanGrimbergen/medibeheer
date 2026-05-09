<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import FamilyWellbeingScreen from '@/Components/Family/Wellbeing/FamilyWellbeingScreen.vue';
import FamilyLayout from '@/Layouts/FamilyLayout.vue';
import type { FamilyWellbeingScreenProps } from '@/lib/family/wellbeing/familyWellbeingScreenProps';
import type { FamilyDashboardProps } from '@/lib/types';

const props = defineProps<
    FamilyWellbeingScreenProps & {
        family: FamilyDashboardProps;
    }
>();

const { t } = useI18n();
</script>

<template>
    <Head>
        <title>{{ t('family.wellbeing.title') }}</title>
    </Head>

    <FamilyLayout>
        <FamilyWellbeingScreen v-bind="props" />
    </FamilyLayout>
</template>
