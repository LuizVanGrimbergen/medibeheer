<script setup lang="ts">
import { Pencil } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useAppointmentDisplay } from '@/Components/Appointments/useAppointmentDisplay';
import type { AppointmentFormWithErrors } from '@/Components/Patient/Appointments/form/AppointmentFormTypes';
import { IconActionButton } from '@/Components/ui/icon-action-button';
import { formatAppointmentAddress } from '@/lib/appointments/formatAppointmentAddress';
import { isAppointmentAddressProvided } from '@/lib/patient/appointments/appointmentAddressValidation';
import type { AppointmentFormStepId } from '@/lib/patient/appointments/form-wizard/appointmentFormStepGuards';
import { patientFormLabelClass } from '@/lib/patient/patientFormFieldClasses';
import { cn } from '@/lib/utils';

const props = defineProps<{
    form: AppointmentFormWithErrors;
    idPrefix: string;
    transportFamilies: {
        id: number;
        name: string;
    }[];
    showTransportStep: boolean;
    goToWizardStep: (
        step: AppointmentFormStepId,
        focusElementIdSuffix?: string,
    ) => void;
}>();

const { t } = useI18n();
const { formatDateOnly, formatTimeOnly, doctorTypeLabel } =
    useAppointmentDisplay();

const summaryRowGroupClass =
    'flex flex-col gap-0.5 border-b border-border/60 py-3 last:border-b-0 sm:flex-row sm:items-baseline sm:justify-between sm:gap-4 sm:py-3.5';

const summaryDdClass =
    'flex min-w-0 flex-1 flex-row items-baseline justify-end gap-2';

const summaryLabelClass =
    'shrink-0 text-sm font-medium text-text-muted md:text-base';

const summaryValueClass =
    'min-w-0 flex-1 text-base font-semibold leading-snug text-text-heading sm:text-end md:text-lg';

const overviewSectionHeadingClass = cn(
    patientFormLabelClass,
    'mb-3 text-lg text-text-heading md:mb-4 md:text-xl',
);

const doctorTypeSummary = computed(() => {
    const value = props.form.doctor_type;

    if (value === '') {
        return '—';
    }

    return doctorTypeLabel(value);
});

const addressSummary = computed(() => {
    const formatted = formatAppointmentAddress(props.form).trim();

    return formatted.length > 0 ? formatted : '—';
});

const showAddressSection = computed(
    () =>
        props.form.needs_transport ||
        isAppointmentAddressProvided(props.form),
);

const scheduleDateSummary = computed(() => {
    const date = props.form.starts_at_date.trim();
    const time = props.form.starts_at_time.trim();

    if (date.length < 1) {
        return '—';
    }

    const iso = time.length > 0 ? `${date}T${time}:00` : `${date}T12:00:00`;

    return formatDateOnly(iso);
});

const scheduleTimeSummary = computed(() => {
    const date = props.form.starts_at_date.trim();
    const time = props.form.starts_at_time.trim();

    if (date.length < 1 || time.length < 1) {
        return '—';
    }

    return formatTimeOnly(`${date}T${time}:00`);
});

const transportSummary = computed(() => {
    if (!props.form.needs_transport) {
        return t('patient.appointments.transport.notNeeded');
    }

    const names = props.transportFamilies
        .filter((family) => props.form.transport_family_ids.includes(family.id))
        .map((family) => family.name);

    if (names.length < 1) {
        return '—';
    }

    return names.join(', ');
});

function summaryRowAria(fieldTranslationKey: string): string {
    return t('patient.appointments.overview.editRowAria', {
        field: t(`patient.appointments.fields.${fieldTranslationKey}`),
    });
}

function activateSummaryRow(
    step: AppointmentFormStepId,
    focusElementIdSuffix: string,
): void {
    if (props.form.processing) {
        return;
    }

    props.goToWizardStep(step, focusElementIdSuffix);
}
</script>

<template>
    <div class="space-y-6">
        <h2
            :id="`${idPrefix}-create-summary-title`"
            tabindex="-1"
            class="text-text-heading text-xl leading-tight font-bold md:text-2xl"
        >
            {{ t('patient.appointments.overview.title') }}
        </h2>

        <div>
            <p :class="overviewSectionHeadingClass">
                {{ t('patient.appointments.overview.sectionProvider') }}
            </p>
            <dl
                class="border-border/70 bg-surface rounded-2xl border px-4 py-1 md:px-5"
            >
                <div :class="summaryRowGroupClass">
                    <dt :class="summaryLabelClass">
                        {{ t('patient.appointments.fields.doctorType') }}
                    </dt>
                    <dd :class="summaryDdClass">
                        <span :class="summaryValueClass">{{
                            doctorTypeSummary
                        }}</span>
                        <IconActionButton
                            :ariaLabel="summaryRowAria('doctorType')"
                            :disabled="form.processing"
                            @click="activateSummaryRow('provider', 'doctor-type')"
                        >
                            <Pencil class="size-5" aria-hidden="true" />
                        </IconActionButton>
                    </dd>
                </div>
                <div :class="summaryRowGroupClass">
                    <dt :class="summaryLabelClass">
                        {{ t('patient.appointments.fields.providerName') }}
                    </dt>
                    <dd :class="summaryDdClass">
                        <span :class="summaryValueClass">{{
                            form.provider_name.trim() || '—'
                        }}</span>
                        <IconActionButton
                            :ariaLabel="summaryRowAria('providerName')"
                            :disabled="form.processing"
                            @click="
                                activateSummaryRow('provider', 'provider-name')
                            "
                        >
                            <Pencil class="size-5" aria-hidden="true" />
                        </IconActionButton>
                    </dd>
                </div>
            </dl>
        </div>

        <div v-if="showAddressSection">
            <p :class="overviewSectionHeadingClass">
                {{ t('patient.appointments.overview.sectionAddress') }}
            </p>
            <dl
                class="border-border/70 bg-surface rounded-2xl border px-4 py-1 md:px-5"
            >
                <div :class="summaryRowGroupClass">
                    <dt :class="summaryLabelClass">
                        {{ t('patient.appointments.fields.addressGroupLegend') }}
                    </dt>
                    <dd :class="summaryDdClass">
                        <span :class="summaryValueClass">{{
                            addressSummary
                        }}</span>
                        <IconActionButton
                            :ariaLabel="
                                summaryRowAria('addressGroupLegend')
                            "
                            :disabled="form.processing"
                            @click="activateSummaryRow('address', 'street')"
                        >
                            <Pencil class="size-5" aria-hidden="true" />
                        </IconActionButton>
                    </dd>
                </div>
            </dl>
        </div>

        <div>
            <p :class="overviewSectionHeadingClass">
                {{ t('patient.appointments.overview.sectionSchedule') }}
            </p>
            <dl
                class="border-border/70 bg-surface rounded-2xl border px-4 py-1 md:px-5"
            >
                <div :class="summaryRowGroupClass">
                    <dt :class="summaryLabelClass">
                        {{ t('patient.appointments.fields.startsAtDate') }}
                    </dt>
                    <dd :class="summaryDdClass">
                        <span :class="summaryValueClass">{{
                            scheduleDateSummary
                        }}</span>
                        <IconActionButton
                            :ariaLabel="summaryRowAria('startsAtDate')"
                            :disabled="form.processing"
                            @click="
                                activateSummaryRow('schedule', 'starts-at-date')
                            "
                        >
                            <Pencil class="size-5" aria-hidden="true" />
                        </IconActionButton>
                    </dd>
                </div>
                <div :class="summaryRowGroupClass">
                    <dt :class="summaryLabelClass">
                        {{ t('patient.appointments.fields.startsAtTime') }}
                    </dt>
                    <dd :class="summaryDdClass">
                        <span :class="summaryValueClass">{{
                            scheduleTimeSummary
                        }}</span>
                        <IconActionButton
                            :ariaLabel="summaryRowAria('startsAtTime')"
                            :disabled="form.processing"
                            @click="
                                activateSummaryRow('schedule', 'starts-at-time')
                            "
                        >
                            <Pencil class="size-5" aria-hidden="true" />
                        </IconActionButton>
                    </dd>
                </div>
            </dl>
        </div>

        <div v-if="showTransportStep">
            <p :class="overviewSectionHeadingClass">
                {{ t('patient.appointments.overview.sectionTransport') }}
            </p>
            <dl
                class="border-border/70 bg-surface rounded-2xl border px-4 py-1 md:px-5"
            >
                <div :class="summaryRowGroupClass">
                    <dt :class="summaryLabelClass">
                        {{ t('patient.appointments.fields.needsTransport') }}
                    </dt>
                    <dd :class="summaryDdClass">
                        <span :class="summaryValueClass">{{
                            transportSummary
                        }}</span>
                        <IconActionButton
                            :ariaLabel="summaryRowAria('needsTransport')"
                            :disabled="form.processing"
                            @click="
                                activateSummaryRow('transport', 'needs-transport')
                            "
                        >
                            <Pencil class="size-5" aria-hidden="true" />
                        </IconActionButton>
                    </dd>
                </div>
            </dl>
        </div>

        <div v-if="form.notes.trim().length > 0">
            <p :class="overviewSectionHeadingClass">
                {{ t('patient.appointments.overview.sectionNotes') }}
            </p>
            <dl
                class="border-border/70 bg-surface rounded-2xl border px-4 py-1 md:px-5"
            >
                <div :class="summaryRowGroupClass">
                    <dt :class="summaryLabelClass">
                        {{ t('patient.appointments.fields.notes') }}
                    </dt>
                    <dd :class="summaryDdClass">
                        <span
                            :class="
                                cn(
                                    summaryValueClass,
                                    'wrap-break-word whitespace-pre-wrap',
                                )
                            "
                        >
                            {{ form.notes.trim() }}
                        </span>
                        <IconActionButton
                            :ariaLabel="summaryRowAria('notes')"
                            :disabled="form.processing"
                            @click="activateSummaryRow('notes', 'notes')"
                        >
                            <Pencil class="size-5" aria-hidden="true" />
                        </IconActionButton>
                    </dd>
                </div>
            </dl>
        </div>
    </div>
</template>
