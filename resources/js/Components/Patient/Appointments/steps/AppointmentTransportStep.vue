<script setup lang="ts">
/* eslint-disable vue/no-mutating-props */
import { useI18n } from 'vue-i18n';
import type { AppointmentFormWithErrors } from '@/Components/Patient/Appointments/form/AppointmentFormTypes';
import { Checkbox } from '@/Components/ui/checkbox';
import { InputError } from '@/Components/ui/input-error';
import { Label } from '@/Components/ui/label';
import { Switch } from '@/Components/ui/switch';

const { form, idPrefix, transportFamilies } = defineProps<{
    form: AppointmentFormWithErrors;
    idPrefix: string;
    transportFamilies: {
        id: number;
        name: string;
    }[];
}>();

const { t } = useI18n();

function transportFamilyChecked(id: number): boolean {
    return form.transport_family_ids.includes(id);
}

function setTransportFamilyChecked(id: number, on: boolean): void {
    const next = on
        ? [...form.transport_family_ids, id]
        : form.transport_family_ids.filter((value: number) => value !== id);

    form.transport_family_ids = Array.from(new Set(next));
}
</script>

<template>
    <div class="space-y-5 sm:space-y-7">
        <div class="space-y-1 sm:space-y-1.5">
            <p class="daily-checkin-step-title">
                {{ t('patient.appointments.steps.transport.title') }}
            </p>
            <p class="daily-checkin-step-description">
                {{ t('patient.appointments.steps.transport.description') }}
            </p>
        </div>

        <div
            class="border-border bg-surface space-y-3 rounded-2xl border-2 p-4"
        >
            <div class="flex items-center justify-between gap-4">
                <div class="min-w-0">
                    <p
                        class="text-text-heading text-lg leading-snug font-semibold"
                    >
                        {{ t('patient.appointments.fields.needsTransport') }}
                    </p>
                    <p class="text-text-muted mt-1 text-sm leading-relaxed">
                        {{ t('patient.appointments.fields.transportNotes') }}
                    </p>
                </div>
                <Switch
                    :id="`${idPrefix}-needs-transport`"
                    v-model="form.needs_transport"
                    size="lg"
                    :disabled="form.processing"
                />
            </div>

            <div v-if="form.needs_transport">
                <div class="mt-4 space-y-2">
                    <p class="text-text-heading text-sm font-semibold">
                        {{ t('patient.appointments.fields.transportNotify') }}
                    </p>
                    <div class="space-y-2">
                        <div
                            v-for="fam in transportFamilies"
                            :key="fam.id"
                            class="border-border/70 bg-surface text-text hover:bg-surface-hover focus-within:ring-focus/25 flex cursor-pointer items-center gap-4 rounded-2xl border-2 px-4 py-3 text-base transition-colors focus-within:ring-2"
                            @click="
                                setTransportFamilyChecked(
                                    fam.id,
                                    !transportFamilyChecked(fam.id),
                                )
                            "
                        >
                            <Checkbox
                                :id="`${idPrefix}-transport-family-${fam.id}`"
                                :model-value="transportFamilyChecked(fam.id)"
                                :disabled="form.processing"
                                class="size-6"
                                @click.stop
                                @update:model-value="
                                    (value) =>
                                        setTransportFamilyChecked(
                                            fam.id,
                                            value === true,
                                        )
                                "
                            />
                            <Label
                                :for="`${idPrefix}-transport-family-${fam.id}`"
                                class="text-text min-w-0 cursor-pointer text-base leading-snug font-medium wrap-break-word"
                            >
                                {{ fam.name }}
                            </Label>
                        </div>
                    </div>
                    <InputError
                        :id="`${idPrefix}-transport-family-ids-error`"
                        :message="form.errors.transport_family_ids"
                    />
                </div>
            </div>
        </div>
    </div>
</template>
