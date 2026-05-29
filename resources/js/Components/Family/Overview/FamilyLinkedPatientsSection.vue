<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Check, Users } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import FamilyOverviewCollapsibleSection from '@/Components/Family/Overview/FamilyOverviewCollapsibleSection.vue';
import { Badge } from '@/Components/ui/badge';
import type { FamilyDashboardProps } from '@/lib/types';

const props = defineProps<{
    family: FamilyDashboardProps;
    lowStockPatientIds?: number[];
}>();

const { t } = useI18n();

const patients = computed(() => props.family.patients);

const hasPatients = computed(() => patients.value.length > 0);

const hasMultiplePatients = computed(() => patients.value.length > 1);

const isOpen = ref(hasMultiplePatients.value);

const activePatient = computed(() => patients.value.find((patient) => patient.is_active) ?? null);

const lowStockPatientIdSet = computed(
    () => new Set(props.lowStockPatientIds ?? []),
);

const collapsedSummary = computed((): string => {
    if (!hasMultiplePatients.value) {
        return activePatient.value?.name ?? '';
    }

    if (activePatient.value === null) {
        return t('family.overview.linkedPatientsCollapsedCount', {
            count: String(patients.value.length),
        });
    }

    return t('family.overview.linkedPatientsCollapsedActive', {
        count: String(patients.value.length),
        name: activePatient.value.name,
    });
});

function patientInitial(name: string): string {
    const trimmed = name.trim();

    if (trimmed === '') {
        return '?';
    }

    return trimmed.charAt(0).toUpperCase();
}

function switchPatient(switchUrl: string, isActive: boolean): void {
    if (isActive) {
        return;
    }

    router.post(switchUrl, {}, { preserveScroll: true });
}

function patientHasLowStock(patientId: number): boolean {
    return lowStockPatientIdSet.value.has(patientId);
}
</script>

<template>
    <FamilyOverviewCollapsibleSection
        v-if="hasPatients && hasMultiplePatients"
        v-model:open="isOpen"
        :heading="t('family.overview.linkedPatientsHeading')"
        :toggle-label="t('family.overview.linkedPatientsToggle')"
        :collapsed-summary="collapsedSummary"
        icon-wrapper-class="bg-role-family/12 text-role-family"
        content-class="border-t border-border px-4 pb-4 pt-4 md:px-5 md:pb-5 md:pt-4"
    >
        <template #icon>
            <Users class="size-5" />
        </template>

        <ul
            class="flex flex-col gap-2 md:grid md:grid-cols-2 md:gap-3 xl:grid-cols-3"
        >
            <li
                v-for="patient in patients"
                :key="patient.id"
            >
                <button
                    type="button"
                    class="flex h-full w-full items-center gap-3 rounded-2xl border px-4 py-3 text-left transition md:px-4 md:py-3"
                    :class="
                        patient.is_active
                            ? 'border-primary/50 bg-primary/6 shadow-sm'
                            : 'border-border bg-surface-2/50 hover:border-primary/30 hover:bg-surface-2'
                    "
                    :disabled="patient.is_active"
                    :aria-label="
                        patient.is_active
                            ? t('family.overview.linkedPatientActiveAria', { name: patient.name })
                            : t('family.overview.linkedPatientSwitchAria', { name: patient.name })
                    "
                    @click="switchPatient(patient.switch_url, patient.is_active)"
                >
                    <div
                        class="flex size-11 shrink-0 items-center justify-center rounded-full text-base font-bold md:size-10"
                        :class="
                            patient.is_active
                                ? 'bg-primary text-white'
                                : 'bg-role-family/12 text-role-family'
                        "
                        aria-hidden="true"
                    >
                        {{ patientInitial(patient.name) }}
                    </div>

                    <div class="min-w-0 flex-1">
                        <p
                            class="truncate text-base font-semibold text-text-heading md:text-base"
                        >
                            {{ patient.name }}
                        </p>
                        <div class="mt-1 flex flex-wrap items-center gap-2">
                            <Badge
                                v-if="patient.is_active"
                                variant="secondary"
                                class="border-primary/20 bg-primary/10 text-primary"
                            >
                                {{ t('family.overview.activePatientBadge') }}
                            </Badge>
                            <Badge
                                v-if="patientHasLowStock(patient.id)"
                                variant="secondary"
                                class="border-danger/25 bg-danger/10 text-danger"
                            >
                                {{ t('patient.inventory.lowStockBadge') }}
                            </Badge>
                        </div>
                    </div>

                    <Check
                        v-if="patient.is_active"
                        class="size-5 shrink-0 text-primary md:size-5"
                        aria-hidden="true"
                    />
                </button>
            </li>
        </ul>
    </FamilyOverviewCollapsibleSection>

    <div
        v-else-if="hasPatients && activePatient !== null"
        class="flex items-center gap-3 rounded-2xl border border-primary/50 bg-primary/6 px-4 py-4 shadow-sm md:px-5 md:py-4"
    >
        <div
            class="flex size-11 shrink-0 items-center justify-center rounded-full bg-primary text-base font-bold text-white md:size-10"
            aria-hidden="true"
        >
            {{ patientInitial(activePatient.name) }}
        </div>

        <div class="min-w-0 flex-1">
            <p class="text-xs font-semibold uppercase tracking-wide text-text-muted">
                {{ t('family.overview.linkedPatientsHeading') }}
            </p>
            <p class="mt-1 truncate text-lg font-semibold text-text-heading md:text-base">
                {{ activePatient.name }}
            </p>
            <Badge
                v-if="patientHasLowStock(activePatient.id)"
                variant="secondary"
                class="mt-2 border-danger/25 bg-danger/10 text-danger"
            >
                {{ t('patient.inventory.lowStockBadge') }}
            </Badge>
        </div>

        <Check
            class="size-5 shrink-0 text-primary"
            aria-hidden="true"
        />
    </div>
</template>
