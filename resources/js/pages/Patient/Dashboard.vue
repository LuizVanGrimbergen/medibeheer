<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { useDocumentVisibility } from '@vueuse/core';
import { computed, onMounted, onUnmounted, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import DailyCheckinCard from '@/Components/Patient/DailyCheckins/form/DailyCheckinCard.vue';
import DailyCheckinSuccessScreen from '@/Components/Patient/DailyCheckins/form/DailyCheckinSuccessScreen.vue';
import PatientMedicationOnboardingShortcuts from '@/Components/Patient/Medications/PatientMedicationOnboardingShortcuts.vue';
import PatientMedicationReminderPrompt from '@/Components/Patient/Medications/PatientMedicationReminderPrompt.vue';
import TodayMedicationIntakesSection from '@/Components/Patient/Medications/TodayMedicationIntakesSection.vue';
import PatientPageShell from '@/Components/Patient/PatientPageShell.vue';
import PatientLayout from '@/Layouts/PatientLayout.vue';
import type {
    DailyCheckin,
    DailyMoodScoreValue,
    PageProps,
    TodayMedicationIntakeSlot,
} from '@/lib/types';

const { t } = useI18n();
const page = usePage<PageProps>();

const props = withDefaults(
    defineProps<{
        today_date: string;
        today_checkin: DailyCheckin | null;
        today_medication_intakes?: TodayMedicationIntakeSlot[];
        pending_push_medication_mark: string | null;
        has_medications?: boolean;
        can_create_medication?: boolean;
    }>(),
    {
        has_medications: false,
        can_create_medication: false,
    },
);

const showMedicationOnboardingShortcuts = computed(
    () => !props.has_medications,
);

const dailyCheckinMoodFlash = computed((): DailyMoodScoreValue | null => {
    const raw = page.props.flash?.daily_checkin_mood;

    if (raw === 'bad' || raw === 'ok' || raw === 'good') {
        return raw;
    }

    return null;
});

const showDailyCheckinCard = computed(
    () =>
        props.today_checkin === null &&
        dailyCheckinMoodFlash.value === null,
);

const dailyCheckinEncouragementFlash = computed((): string | null => {
    const raw = page.props.flash?.daily_checkin_encouragement;

    if (typeof raw !== 'string') {
        return null;
    }

    const trimmed = raw.trim();

    return trimmed === '' ? null : trimmed;
});

const PUSH_MARK_SUCCESS_ROUTE = route('patient.medication-push-mark.success');

function redirectToPushSuccessIfPending(name: string | null | undefined): void {
    if (typeof name !== 'string' || name === '') {
        return;
    }

    router.visit(PUSH_MARK_SUCCESS_ROUTE, { replace: true });
}

function localCalendarDateIso(): string {
    const now = new Date();

    const y = now.getFullYear();
    const m = String(now.getMonth() + 1).padStart(2, '0');
    const d = String(now.getDate()).padStart(2, '0');

    return `${y}-${m}-${d}`;
}

function hasCalendarDayAdvanced(): boolean {
    return localCalendarDateIso() !== props.today_date;
}

function reloadDashboardState(): void {
    if (hasCalendarDayAdvanced()) {
        router.reload();

        return;
    }

    router.reload({
        only: [
            'today_date',
            'today_checkin',
            'today_medication_intakes',
            'pending_push_medication_mark',
        ],
        onSuccess: (reloadedPage) => {
            redirectToPushSuccessIfPending(
                reloadedPage.props.pending_push_medication_mark as
                    | string
                    | null
                    | undefined,
            );
        },
    });
}

const documentVisibility = useDocumentVisibility();

watch(documentVisibility, (state) => {
    if (state !== 'visible') {
        return;
    }

    reloadDashboardState();
});

function onWindowPageShow(): void {
    if (document.visibilityState !== 'visible') {
        return;
    }

    reloadDashboardState();
}

let pushMarkBroadcastChannel: BroadcastChannel | null = null;

onMounted(() => {
    window.addEventListener('pageshow', onWindowPageShow);

    redirectToPushSuccessIfPending(props.pending_push_medication_mark);

    if ('BroadcastChannel' in globalThis) {
        pushMarkBroadcastChannel = new BroadcastChannel(
            'medibeheer-medication-push-mark',
        );
        pushMarkBroadcastChannel.onmessage = () => {
            router.visit(PUSH_MARK_SUCCESS_ROUTE, { replace: true });
        };
    }
});

onUnmounted(() => {
    window.removeEventListener('pageshow', onWindowPageShow);

    pushMarkBroadcastChannel?.close();
});
</script>

<template>
    <Head>
        <title>{{ t('patient.dashboard.title') }}</title>
        <meta
            name="description"
            :content="t('patient.dashboard.metaDescription')"
        />
    </Head>

    <PatientLayout>
        <PatientPageShell :title="t('patient.dashboard.heading')">
            <DailyCheckinSuccessScreen
                :mood="dailyCheckinMoodFlash"
                :message="dailyCheckinEncouragementFlash"
            />

            <DailyCheckinCard
                v-if="showDailyCheckinCard"
                :today_date="props.today_date"
                :today_checkin="null"
            />

            <PatientMedicationOnboardingShortcuts
                v-if="showMedicationOnboardingShortcuts"
                :can-create-medication="props.can_create_medication"
            />

            <PatientMedicationReminderPrompt />

            <TodayMedicationIntakesSection
                class="mt-auto"
                :slots="props.today_medication_intakes"
            />
        </PatientPageShell>
    </PatientLayout>
</template>
