<script setup lang="ts">
/* eslint-disable vue/no-mutating-props */
import { MapPin } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import type { AppointmentFormWithErrors } from '@/Components/Patient/Appointments/form/AppointmentFormTypes';
import AppointmentAddressManualFields from '@/Components/Patient/Appointments/steps/AppointmentAddressManualFields.vue';
import { Label } from '@/Components/ui/label';
import { useAppointmentAddressPlaceAutocomplete } from '@/composables/google-maps/useAppointmentAddressPlaceAutocomplete';
import { useAppointmentAddressLiveValidation } from '@/composables/patient/useAppointmentAddressLiveValidation';
import { isAppointmentAddressValidationRequired } from '@/lib/patient/appointments/appointmentAddressValidation';
import { isAppointmentAddressComplete } from '@/lib/patient/appointments/isAppointmentAddressComplete';
import { patientFormLabelClass } from '@/lib/patient/patientFormFieldClasses';

const { form, idPrefix } = defineProps<{
    form: AppointmentFormWithErrors;
    idPrefix: string;
}>();

const { t } = useI18n();
const addressSearchHostRef = ref<HTMLElement | null>(null);

const { isAvailable: isAddressSearchAvailable, placesVerifiedSnapshot } =
    useAppointmentAddressPlaceAutocomplete({
        form,
        hostRef: addressSearchHostRef,
        placeholder: t('patient.appointments.fields.addressSearchPlaceholder'),
    });

const showAddressFields = computed(
    () =>
        isAddressSearchAvailable.value &&
        isAppointmentAddressComplete(form),
);

const showManualFieldsOnly = computed(() => !isAddressSearchAvailable.value);

const isAddressRequired = computed(() =>
    isAppointmentAddressValidationRequired(form.needs_transport, form),
);

const shouldValidateAddressFields = computed(
    () =>
        isAddressRequired.value &&
        (showAddressFields.value || showManualFieldsOnly.value),
);

const { isVerifyingGeocode } = useAppointmentAddressLiveValidation({
    form,
    addressRequired: isAddressRequired,
    enabled: shouldValidateAddressFields,
    placesVerifiedSnapshot,
});

defineExpose({ isVerifyingGeocode });
</script>

<template>
    <div class="space-y-5 sm:space-y-7">
        <div class="space-y-1 sm:space-y-1.5">
            <p class="daily-checkin-step-title">
                {{ t('patient.appointments.steps.address.title') }}
            </p>
        </div>

        <fieldset class="space-y-5 border-0 p-0">
            <legend class="sr-only">
                {{ t('patient.appointments.fields.addressGroupLegend') }}
            </legend>
            <div class="space-y-5">
                <div v-if="isAddressSearchAvailable">
                    <Label
                        :for="`${idPrefix}-address-search`"
                        :class="patientFormLabelClass"
                    >
                        {{
                            isAddressRequired
                                ? t(
                                      'patient.appointments.fields.addressSearch',
                                  )
                                : t(
                                      'patient.appointments.fields.addressSearchOptional',
                                  )
                        }}
                        <span v-if="isAddressRequired" class="text-danger"
                            >*</span
                        >
                    </Label>
                    <div
                        :id="`${idPrefix}-address-search`"
                        class="patient-place-autocomplete-host relative w-full"
                    >
                        <MapPin
                            :size="20"
                            :stroke-width="2"
                            class="patient-place-autocomplete-leading-icon"
                            aria-hidden="true"
                        />
                        <div
                            ref="addressSearchHostRef"
                            class="w-full"
                        />
                    </div>

                    <div v-if="showAddressFields" class="mt-5">
                        <AppointmentAddressManualFields
                            :form="form"
                            :id-prefix="idPrefix"
                            :required="isAddressRequired"
                        />
                    </div>
                </div>
                <template v-else-if="showManualFieldsOnly">
                    <p
                        class="rounded-2xl border border-border/80 bg-surface px-4 py-3 text-sm text-text-muted"
                    >
                        {{
                            t(
                                'patient.appointments.fields.addressSearchUnavailable',
                            )
                        }}
                    </p>
                    <AppointmentAddressManualFields
                        :form="form"
                        :id-prefix="idPrefix"
                        :required="isAddressRequired"
                    />
                </template>
            </div>
        </fieldset>
    </div>
</template>
