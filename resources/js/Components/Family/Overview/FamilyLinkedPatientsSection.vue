<script setup lang="ts">
import { Check, Users } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import CollapsibleSectionCard from '@/Components/ui/collapsible-section/CollapsibleSectionCard.vue';
import { useFamilyActivePatientSwitch } from '@/composables/family/useFamilyActivePatientSwitch';
import type { FamilyDashboardProps } from '@/lib/types';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        family: FamilyDashboardProps;
        headingKey?: string;
        toggleKey?: string;
    }>(),
    {
        headingKey: 'family.overview.linkedPatientsHeading',
        toggleKey: 'family.overview.linkedPatientsToggle',
    },
);

const { t } = useI18n();
const { switchToPatient } = useFamilyActivePatientSwitch();

const patients = computed(() => props.family.patients);

const hasPatients = computed(() => patients.value.length > 0);

const isOpen = ref(false);
const isSwitching = ref(false);

const COLLAPSE_ANIMATION_MS = 300;

function collapseAnimationMs(): number {
    if (
        typeof globalThis.window !== 'undefined' &&
        globalThis.window.matchMedia('(prefers-reduced-motion: reduce)').matches
    ) {
        return 0;
    }

    return COLLAPSE_ANIMATION_MS;
}

const activePatient = computed(() =>
    patients.value.find((patient) => patient.is_active),
);

const collapsedSummary = computed(() => {
    if (activePatient.value !== undefined) {
        return t('family.overview.linkedPatientsCollapsedActive', {
            name: activePatient.value.name,
        });
    }

    return t('family.overview.linkedPatientsCollapsedCount');
});

function selectPatient(switchUrl: string, isActive: boolean): void {
    if (isActive || isSwitching.value) {
        return;
    }

    isSwitching.value = true;
    isOpen.value = false;

    globalThis.setTimeout(() => {
        switchToPatient(switchUrl, isActive);
        isSwitching.value = false;
    }, collapseAnimationMs());
}
</script>

<template>
    <CollapsibleSectionCard
        v-if="hasPatients"
        v-model:open="isOpen"
        :heading="t(props.headingKey)"
        :toggle-label="t(props.toggleKey)"
        :collapsed-summary="collapsedSummary"
        icon-wrapper-class="bg-role-family/12 text-role-family"
    >
        <template #icon>
            <Users :size="20" :stroke-width="1.75" />
        </template>

        <ul class="flex flex-col gap-1">
            <li v-for="patient in patients" :key="patient.id">
                <button
                    type="button"
                    class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left transition"
                    :class="
                        cn(
                            patient.is_active
                                ? 'bg-primary/6 text-primary font-semibold'
                                : 'text-text-heading hover:bg-surface-2',
                        )
                    "
                    :disabled="patient.is_active || isSwitching"
                    :aria-label="
                        patient.is_active
                            ? t('family.overview.linkedPatientActiveAria', {
                                  name: patient.name,
                              })
                            : t('family.overview.linkedPatientSwitchAria', {
                                  name: patient.name,
                              })
                    "
                    @click="
                        selectPatient(patient.switch_url, patient.is_active)
                    "
                >
                    <span class="min-w-0 flex-1 truncate text-base">
                        {{ patient.name }}
                    </span>
                    <Check
                        v-if="patient.is_active"
                        class="size-4 shrink-0 stroke-[2.25]"
                        aria-hidden="true"
                    />
                </button>
            </li>
        </ul>
    </CollapsibleSectionCard>
</template>
