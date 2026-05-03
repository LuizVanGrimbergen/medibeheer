<script setup lang="ts">
/* eslint-disable vue/no-mutating-props -- Inertia useForm() proxy; parent owns mutable form state. */
import { useI18n } from 'vue-i18n';
import { Input } from '@/Components/ui/input';
import { InputError } from '@/Components/ui/input-error';
import { Label } from '@/Components/ui/label';
import { cn } from '@/lib/utils';
import { resolveAppointmentDoctorTypeLabel } from '@/Components/Patient/Appointments/useAppointmentDisplay';
import type { AppointmentFormWithErrors } from '@/Components/Patient/Appointments/appointmentFormTypes';
import type { AppointmentDoctorType } from '@/lib/types';

defineProps<{
    form: AppointmentFormWithErrors;
    idPrefix: string;
    doctorTypeValues: AppointmentDoctorType[];
    showDoctorTypePlaceholder: boolean;
}>();

const { t, te } = useI18n();

const labelClass =
    'mb-2 block text-lg font-semibold leading-snug text-text-heading';

const fieldInputClass =
    'h-auto min-h-14 w-full rounded-2xl border-2 border-border bg-surface px-4 py-3.5 text-lg leading-normal text-text placeholder:text-text-muted focus-visible:border-focus focus-visible:ring-2 focus-visible:ring-focus/25';

const nativeDateTimeInputClass = cn(
    fieldInputClass,
    'native-picker-input block min-h-16 py-4 text-xl pr-14 touch-manipulation',
);

const selectBaseClass = `${fieldInputClass} appearance-none bg-[length:1.5rem] bg-[right_1rem_center] bg-no-repeat pr-14 touch-manipulation`;

const fieldInvalidClass =
    'border-danger ring-2 ring-danger/25 focus-visible:border-danger focus-visible:ring-danger/30';

const selectChevronStyle = {
    backgroundImage: `url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%23667d94'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2.5' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E")`,
};

function doctorTypeLabel(type: AppointmentDoctorType): string {
    return resolveAppointmentDoctorTypeLabel(t, te, type);
}
</script>

<template>
    <div class="space-y-6">
        <div>
            <Label
                :for="`${idPrefix}-doctor-type`"
                :class="labelClass"
            >
                {{ t('patient.appointments.fields.doctorType') }}
            </Label>
            <select
                :id="`${idPrefix}-doctor-type`"
                v-model="form.doctor_type"
                required
                :class="
                    cn(
                        selectBaseClass,
                        form.errors.doctor_type ? fieldInvalidClass : null,
                    )
                "
                :style="selectChevronStyle"
                :aria-invalid="Boolean(form.errors.doctor_type)"
                :aria-describedby="
                    form.errors.doctor_type
                        ? `${idPrefix}-doctor-type-error`
                        : undefined
                "
            >
                <option
                    v-if="showDoctorTypePlaceholder"
                    disabled
                    value=""
                >
                    {{
                        t('patient.appointments.fields.doctorTypePlaceholder')
                    }}
                </option>
                <option
                    v-for="opt in doctorTypeValues"
                    :key="opt"
                    :value="opt"
                >
                    {{ doctorTypeLabel(opt) }}
                </option>
            </select>
            <InputError
                :id="`${idPrefix}-doctor-type-error`"
                :message="form.errors.doctor_type"
            />
        </div>

        <div>
            <Label
                :for="`${idPrefix}-provider-name`"
                :class="labelClass"
            >
                {{ t('patient.appointments.fields.providerName') }}
            </Label>
            <Input
                :id="`${idPrefix}-provider-name`"
                v-model="form.provider_name"
                type="text"
                required
                autocomplete="organization"
                :class="
                    cn(
                        fieldInputClass,
                        form.errors.provider_name ? fieldInvalidClass : null,
                    )
                "
                :placeholder="
                    t('patient.appointments.fields.providerNamePlaceholder')
                "
                :aria-invalid="Boolean(form.errors.provider_name)"
                :aria-describedby="
                    form.errors.provider_name
                        ? `${idPrefix}-provider-name-error`
                        : undefined
                "
            />
            <InputError
                :id="`${idPrefix}-provider-name-error`"
                :message="form.errors.provider_name"
            />
        </div>

        <div>
            <Label
                :for="`${idPrefix}-address`"
                :class="labelClass"
            >
                {{ t('patient.appointments.fields.address') }}
            </Label>
            <textarea
                :id="`${idPrefix}-address`"
                v-model="form.address"
                required
                rows="4"
                :class="
                    cn(
                        fieldInputClass,
                        form.errors.address ? fieldInvalidClass : null,
                    )
                "
                :placeholder="
                    t('patient.appointments.fields.addressPlaceholder')
                "
                :aria-invalid="Boolean(form.errors.address)"
                :aria-describedby="
                    form.errors.address
                        ? `${idPrefix}-address-error`
                        : undefined
                "
            />
            <InputError
                :id="`${idPrefix}-address-error`"
                :message="form.errors.address"
            />
        </div>

        <fieldset class="space-y-5 border-0 p-0">
            <legend
                class="mb-1 block text-xl font-bold leading-snug text-text-heading"
            >
                {{ t('patient.appointments.fields.startsAtGroupLegend') }}
            </legend>
            <p
                :id="`${idPrefix}-starts-at-hint`"
                class="text-lg leading-relaxed text-text-muted"
            >
                {{ t('patient.appointments.fields.startsAtHint') }}
            </p>
            <div class="space-y-5">
                <div>
                    <Label
                        :for="`${idPrefix}-starts-at-date`"
                        class="mb-2 block text-xl font-semibold leading-snug text-text-heading"
                    >
                        {{ t('patient.appointments.fields.startsAtDate') }}
                    </Label>
                    <input
                        :id="`${idPrefix}-starts-at-date`"
                        v-model="form.starts_at_date"
                        type="date"
                        required
                        autocomplete="off"
                        :class="
                            cn(
                                nativeDateTimeInputClass,
                                form.errors.starts_at
                                    ? fieldInvalidClass
                                    : null,
                            )
                        "
                        :aria-invalid="Boolean(form.errors.starts_at)"
                        :aria-describedby="
                            form.errors.starts_at
                                ? `${idPrefix}-starts-at-hint ${idPrefix}-starts-at-error`
                                : `${idPrefix}-starts-at-hint`
                        "
                    />
                </div>
                <div>
                    <Label
                        :for="`${idPrefix}-starts-at-time`"
                        class="mb-2 block text-xl font-semibold leading-snug text-text-heading"
                    >
                        {{ t('patient.appointments.fields.startsAtTime') }}
                    </Label>
                    <input
                        :id="`${idPrefix}-starts-at-time`"
                        v-model="form.starts_at_time"
                        type="time"
                        step="60"
                        required
                        autocomplete="off"
                        :class="
                            cn(
                                nativeDateTimeInputClass,
                                form.errors.starts_at
                                    ? fieldInvalidClass
                                    : null,
                            )
                        "
                        :aria-invalid="Boolean(form.errors.starts_at)"
                        :aria-describedby="
                            form.errors.starts_at
                                ? `${idPrefix}-starts-at-hint ${idPrefix}-starts-at-error`
                                : `${idPrefix}-starts-at-hint`
                        "
                    />
                </div>
            </div>
            <InputError
                :id="`${idPrefix}-starts-at-error`"
                :message="form.errors.starts_at"
            />
        </fieldset>

        <div>
            <Label
                :for="`${idPrefix}-notes`"
                :class="labelClass"
            >
                {{ t('patient.appointments.fields.notes') }}
            </Label>
            <textarea
                :id="`${idPrefix}-notes`"
                v-model="form.notes"
                rows="4"
                :class="
                    cn(
                        fieldInputClass,
                        form.errors.notes ? fieldInvalidClass : null,
                    )
                "
                :placeholder="
                    t('patient.appointments.fields.notesPlaceholder')
                "
                :aria-invalid="Boolean(form.errors.notes)"
                :aria-describedby="
                    form.errors.notes ? `${idPrefix}-notes-error` : undefined
                "
            />
            <InputError
                :id="`${idPrefix}-notes-error`"
                :message="form.errors.notes"
            />
        </div>
    </div>
</template>
