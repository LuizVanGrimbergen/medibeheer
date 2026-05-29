<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { Users } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import FamilyOverviewCollapsibleSection from '@/Components/Family/Overview/FamilyOverviewCollapsibleSection.vue';
import type { FamilyDashboardProps, PageProps } from '@/lib/types';

type PageWithFamily = PageProps & { family?: FamilyDashboardProps };

const { t } = useI18n();
const page = usePage<PageWithFamily>();
const isOpen = ref(true);

const patients = computed(() => page.props.family?.patients ?? []);

const hasPatients = computed(() => patients.value.length > 0);

const collapsedSummary = computed((): string => {
    if (!hasPatients.value) {
        return t('family.link.patientsEmpty');
    }

    if (patients.value.length === 1) {
        return patients.value[0]?.name ?? '';
    }

    return t('family.overview.linkedPatientsCollapsedCount', {
        count: String(patients.value.length),
    });
});
</script>

<template>
    <FamilyOverviewCollapsibleSection
        v-model:open="isOpen"
        :heading="t('family.link.patientsHeading')"
        :toggle-label="t('family.link.patientsToggle')"
        :collapsed-summary="collapsedSummary"
        icon-wrapper-class="bg-role-family/12 text-role-family"
        content-class="border-t border-border px-4 pb-4 pt-4 md:px-5 md:pb-5 md:pt-4"
    >
        <template #icon>
            <Users class="size-5" />
        </template>

        <ul
            v-if="hasPatients"
            class="flex flex-col gap-2"
        >
            <li
                v-for="patient in patients"
                :key="patient.id"
                class="rounded-xl border border-border bg-surface-2/50 px-4 py-3 text-base font-semibold text-text-heading"
            >
                {{ patient.name }}
            </li>
        </ul>

        <p
            v-else
            class="text-sm leading-relaxed text-text-muted"
        >
            {{ t('family.link.patientsEmpty') }}
        </p>
    </FamilyOverviewCollapsibleSection>
</template>
