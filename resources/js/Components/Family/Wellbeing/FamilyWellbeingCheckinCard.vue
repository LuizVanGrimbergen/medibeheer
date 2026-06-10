<script setup lang="ts">
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import CollapsibleSectionCard from '@/Components/ui/collapsible-section/CollapsibleSectionCard.vue';
import FamilyWellbeingCheckinDetailsContent from '@/Components/Family/Wellbeing/FamilyWellbeingCheckinDetailsContent.vue';
import { MoodIconBadge } from '@/Components/ui/mood-icon';
import type { DailyCheckin } from '@/lib/types';

const props = withDefaults(
    defineProps<{
        checkin: DailyCheckin;
        density?: 'default' | 'compact';
    }>(),
    {
        density: 'default',
    },
);

const { t, locale } = useI18n();

const isOpen = ref(false);

const formattedDate = computed((): string => {
    const [y, m, d] = props.checkin.checkin_date.split('-').map(Number);

    if (!y || !m || !d) {
        return props.checkin.checkin_date;
    }

    const date = new Date(y, m - 1, d);

    const formatted = new Intl.DateTimeFormat(
        locale.value === 'nl' ? 'nl-NL' : undefined,
        {
            weekday: 'long',
            day: 'numeric',
            month: 'long',
            year: 'numeric',
        },
    ).format(date);

    return formatted.charAt(0).toUpperCase() + formatted.slice(1);
});

const hasSymptoms = computed(
    (): boolean =>
        props.checkin.symptoms !== null && props.checkin.symptoms.length > 0,
);

const hasNote = computed((): boolean => {
    const note = props.checkin.note?.trim();

    return note !== undefined && note.length > 0;
});

const hasDetails = computed((): boolean => hasSymptoms.value || hasNote.value);

const useCollapsible = computed(
    (): boolean => props.density === 'default' && hasDetails.value,
);

const patientName = computed((): string | null => {
    const name = props.checkin.patient_name?.trim();

    if (name === undefined || name.length < 1) {
        return null;
    }

    return name;
});

const cardHeading = computed(
    (): string => patientName.value ?? formattedDate.value,
);

const collapsedSummary = computed((): string => {
    const parts: string[] = [];

    if (hasSymptoms.value) {
        const count = props.checkin.symptoms?.length ?? 0;

        parts.push(
            count === 1
                ? t('family.wellbeing.collapsedSummarySymptomsOne')
                : t('family.wellbeing.collapsedSummarySymptomsMany', {
                      count: String(count),
                  }),
        );
    }

    if (hasNote.value) {
        parts.push(t('family.wellbeing.collapsedSummaryNote'));
    }

    return parts.join(' · ');
});
</script>

<template>
    <CollapsibleSectionCard
        v-if="useCollapsible"
        v-model:open="isOpen"
        :heading="cardHeading"
        :subheading="patientName !== null ? formattedDate : undefined"
        :toggle-label="t('family.wellbeing.checkinToggle')"
        :collapsed-summary="collapsedSummary"
        icon-wrapper-class="!size-12 !rounded-2xl bg-transparent p-0 sm:!size-14"
        content-class="border-t border-border px-4 pb-4 pt-4 md:px-5 md:pb-5 md:pt-4"
    >
        <template #icon>
            <MoodIconBadge :mood="checkin.mood_score" />
        </template>

        <FamilyWellbeingCheckinDetailsContent :checkin="checkin" />
    </CollapsibleSectionCard>

    <div
        v-else
        class="border-border bg-surface w-full min-w-0 rounded-2xl border shadow-sm"
    >
        <div class="flex items-start gap-3 px-4 py-4 md:px-5 md:py-3.5">
            <MoodIconBadge :mood="checkin.mood_score" />
            <div class="min-w-0 flex-1 space-y-4">
                <p class="text-text-heading text-lg font-semibold md:text-base">
                    {{ cardHeading }}
                </p>
                <p
                    v-if="patientName !== null"
                    class="text-text-muted mt-0.5 text-sm"
                >
                    {{ formattedDate }}
                </p>

                <FamilyWellbeingCheckinDetailsContent
                    v-if="hasDetails"
                    :checkin="checkin"
                />
            </div>
        </div>
    </div>
</template>
