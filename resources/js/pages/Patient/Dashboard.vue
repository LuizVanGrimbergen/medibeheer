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
import { Card, CardContent } from '@/Components/ui/card';
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
        today_checkin?: DailyCheckin | null;
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

const isTodayCheckinLoading = computed(() => props.today_checkin === undefined);

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

function maybeReloadWhenCalendarDayAdvanced(): void {
    if (localCalendarDateIso() === props.today_date) {
        return;
    }

    router.reload();
}

const documentVisibility = useDocumentVisibility();

watch(documentVisibility, (state) => {
    if (state !== 'visible') {
        return;
    }

    maybeReloadWhenCalendarDayAdvanced();

    router.reload({
        only: [
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
});

function onWindowPageShow(): void {
    if (document.visibilityState !== 'visible') {
        return;
    }

    maybeReloadWhenCalendarDayAdvanced();
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

const dailyCheckinMoodFlash = computed((): DailyMoodScoreValue | null => {
    const raw = page.props.flash?.daily_checkin_mood;

    if (raw === 'bad' || raw === 'ok' || raw === 'good') {
        return raw;
    }

    return null;
});

const dailyCheckinEncouragementFlash = computed((): string | null => {
    const raw = page.props.flash?.daily_checkin_encouragement;

    if (typeof raw !== 'string') {
        return null;
    }

    const trimmed = raw.trim();

    return trimmed === '' ? null : trimmed;
});
</script>

<template>
    <Head>
        <title>{{ t('patient.dashboard.title') }}</title>
    </Head>

    <PatientLayout>
        <PatientPageShell
            :title="t('patient.dashboard.heading')"
            class="min-h-full"
        >
            <DailyCheckinSuccessScreen
                :mood="dailyCheckinMoodFlash"
                :message="dailyCheckinEncouragementFlash"
            />

            <DailyCheckinCard
                v-if="!isTodayCheckinLoading && props.today_checkin === null"
                :today_date="props.today_date"
                :today_checkin="null"
            />

            <Card
                v-else-if="isTodayCheckinLoading"
                class="border-border/80 bg-surface text-text rounded-2xl border shadow-md shadow-black/[0.04] sm:rounded-3xl"
                aria-busy="true"
            >
                <CardContent class="p-0">
                    <div
                        class="bg-surface space-y-5 rounded-2xl px-4 py-4 sm:space-y-6 sm:rounded-3xl sm:px-5 sm:py-5 md:p-7 lg:p-8"
                    >
                        <div class="space-y-1 sm:space-y-1.5">
                            <p class="daily-checkin-mood-step-title">
                                {{ t('patient.dashboard.dailyCheckins.title') }}
                            </p>
                            <p class="daily-checkin-mood-step-description">
                                {{
                                    t(
                                        'patient.dashboard.dailyCheckins.description',
                                    )
                                }}
                            </p>
                        </div>

                        <div
                            class="mx-auto grid w-full max-w-xl grid-cols-3 gap-2.5 sm:gap-3 md:max-w-none md:flex md:w-auto md:items-center md:justify-center md:gap-12"
                            aria-hidden="true"
                        >
                            <div
                                v-for="index in 3"
                                :key="index"
                                class="bg-surface-2 min-h-28 animate-pulse rounded-2xl sm:min-h-32 md:min-h-36"
                            />
                        </div>
                    </div>
                </CardContent>
            </Card>

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
