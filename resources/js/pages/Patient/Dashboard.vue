<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { useDocumentVisibility } from '@vueuse/core';
import { computed, onMounted, onUnmounted, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import DailyCheckinCard from '@/Components/Patient/DailyCheckins/form/DailyCheckinCard.vue';
import DailyCheckinSuccessScreen from '@/Components/Patient/DailyCheckins/form/DailyCheckinSuccessScreen.vue';
import PatientMedicationReminderPrompt from '@/Components/Patient/Medications/PatientMedicationReminderPrompt.vue';
import TodayMedicationIntakesSection from '@/Components/Patient/Medications/TodayMedicationIntakesSection.vue';
import PatientLayout from '@/Layouts/PatientLayout.vue';
import type {
    DailyCheckin,
    DailyMoodScoreValue,
    PageProps,
    TodayMedicationIntakeSlot,
} from '@/lib/types';

const { t } = useI18n();
const page = usePage<PageProps>();

const props = defineProps<{
    today_date: string;
    today_checkin: DailyCheckin | null;
    today_medication_intakes: TodayMedicationIntakeSlot[];
    pending_push_medication_mark: string | null;
}>();

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
        only: ['today_medication_intakes', 'pending_push_medication_mark'],
        onSuccess: (reloadedPage) => {
            redirectToPushSuccessIfPending(
                reloadedPage.props.pending_push_medication_mark as string | null | undefined,
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

let pushMarkPollTimer: ReturnType<typeof globalThis.setInterval> | null = null;
let pushMarkBroadcastChannel: BroadcastChannel | null = null;

onMounted(() => {
    window.addEventListener('pageshow', onWindowPageShow);

    redirectToPushSuccessIfPending(props.pending_push_medication_mark);

    if ('BroadcastChannel' in globalThis) {
        pushMarkBroadcastChannel = new BroadcastChannel('medibeheer-medication-push-mark');
        pushMarkBroadcastChannel.onmessage = () => {
            router.visit(PUSH_MARK_SUCCESS_ROUTE, { replace: true });
        };
    }

    if (props.pending_push_medication_mark !== null) {
        return;
    }

    let attempts = 0;

    pushMarkPollTimer = globalThis.setInterval(() => {
        attempts += 1;

        if (attempts > 20) {
            if (pushMarkPollTimer !== null) {
                globalThis.clearInterval(pushMarkPollTimer);
            }

            return;
        }

        router.reload({
            only: ['pending_push_medication_mark'],
            onSuccess: (reloadedPage) => {
                const name = reloadedPage.props.pending_push_medication_mark as string | null;

                if (name === null) {
                    return;
                }

                if (pushMarkPollTimer !== null) {
                    globalThis.clearInterval(pushMarkPollTimer);
                }

                redirectToPushSuccessIfPending(name);
            },
        });
    }, 1000);
});

onUnmounted(() => {
    window.removeEventListener('pageshow', onWindowPageShow);

    if (pushMarkPollTimer !== null) {
        globalThis.clearInterval(pushMarkPollTimer);
    }

    pushMarkBroadcastChannel?.close();
});

const dailyCheckinMoodFlash = computed((): DailyMoodScoreValue | null => {
    const raw = page.props.flash?.daily_checkin_mood;

    if (raw === 'bad' || raw === 'ok' || raw === 'good') {
        return raw;
    }

    return null;
});
</script>

<template>
    <Head>
        <title>{{ t('patient.dashboard.title') }}</title>
    </Head>

    <PatientLayout>
        <DailyCheckinSuccessScreen :mood="dailyCheckinMoodFlash" />

        <div class="flex flex-col gap-4 sm:gap-8">
            <DailyCheckinCard
                :today_date="props.today_date"
                :today_checkin="props.today_checkin"
            />

            <PatientMedicationReminderPrompt />

            <TodayMedicationIntakesSection :slots="props.today_medication_intakes" />
        </div>
    </PatientLayout>
</template>
