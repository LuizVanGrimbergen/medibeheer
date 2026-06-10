<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import FamilyWellbeingScreen from '@/Components/Family/Wellbeing/FamilyWellbeingScreen.vue';
import FamilyLayout from '@/Layouts/FamilyLayout.vue';
import type { FamilyWellbeingScreenProps } from '@/lib/family/wellbeing/familyWellbeingScreenProps';
import type { FamilyDashboardProps, PageProps } from '@/lib/types';

type PageWithFamily = PageProps & { family?: FamilyDashboardProps };

const props = defineProps<Omit<FamilyWellbeingScreenProps, 'family'>>();

const { t } = useI18n();
const page = usePage<PageWithFamily>();

const family = computed(() => page.props.family);
</script>

<template>
    <Head>
        <title>{{ t('family.wellbeing.title') }}</title>
    </Head>

    <FamilyLayout>
        <FamilyWellbeingScreen v-bind="props" :family="family" />
    </FamilyLayout>
</template>
