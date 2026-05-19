<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import { Button } from '@/Components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog';

const props = defineProps<{
    open: boolean;
    medicationName: string;
    processing?: boolean;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    confirm: [];
    cancel: [];
}>();

const { t } = useI18n();
</script>

<template>
    <Dialog
        :open="props.open"
        @update:open="emit('update:open', $event)"
    >
        <DialogContent @pointer-down-outside="emit('cancel')" @escape-key-down="emit('cancel')">
            <DialogHeader>
                <DialogTitle>
                    {{ t('patient.medications.deleteConfirm.title') }}
                </DialogTitle>
                <DialogDescription>
                    {{
                        t('patient.medications.deleteConfirm.description', {
                            name: props.medicationName,
                        })
                    }}
                </DialogDescription>
            </DialogHeader>

            <DialogFooter class="mt-4 gap-3">
                <Button
                    variant="outline"
                    size="lg"
                    :disabled="props.processing"
                    @click="emit('cancel')"
                >
                    {{ t('patient.medications.deleteConfirm.cancel') }}
                </Button>
                <Button
                    variant="destructive"
                    size="lg"
                    :disabled="props.processing"
                    @click="emit('confirm')"
                >
                    {{ t('patient.medications.deleteConfirm.confirm') }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
