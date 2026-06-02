<script setup lang="ts">
import { Pencil, Trash2 } from 'lucide-vue-next';
import { IconActionButton } from '@/Components/ui/icon-action-button';
import {
    patientPageCardToolbarButtonClass,
    patientPageCardToolbarClass,
    patientPageCardToolbarIconClass,
} from '@/lib/patient/patientPageTypography';

defineProps<{
    ariaLabel: string;
    showEdit: boolean;
    showDelete: boolean;
    editAriaLabel: string;
    deleteAriaLabel: string;
}>();

const emit = defineEmits<{
    edit: [];
    delete: [];
}>();
</script>

<template>
    <div
        :class="patientPageCardToolbarClass"
        role="toolbar"
        :aria-label="ariaLabel"
    >
        <IconActionButton
            v-if="showEdit"
            :class="patientPageCardToolbarButtonClass"
            :ariaLabel="editAriaLabel"
            @click="emit('edit')"
        >
            <Pencil
                :class="patientPageCardToolbarIconClass"
                aria-hidden="true"
            />
        </IconActionButton>
        <IconActionButton
            v-if="showDelete"
            :class="patientPageCardToolbarButtonClass"
            tone="danger"
            :ariaLabel="deleteAriaLabel"
            @click="emit('delete')"
        >
            <Trash2
                :class="patientPageCardToolbarIconClass"
                aria-hidden="true"
            />
        </IconActionButton>
    </div>
</template>
