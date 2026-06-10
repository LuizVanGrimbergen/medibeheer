<script setup lang="ts">
import { Pencil, Trash2 } from 'lucide-vue-next';
import { IconActionButton } from '@/Components/ui/icon-action-button';
import {
    mobileShellPageCardToolbarButtonClass,
    mobileShellPageCardToolbarClass,
    mobileShellPageCardToolbarIconClass,
} from '@/lib/shell/mobileShellTypography';

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
        :class="mobileShellPageCardToolbarClass"
        role="toolbar"
        :aria-label="ariaLabel"
    >
        <IconActionButton
            v-if="showEdit"
            :class="mobileShellPageCardToolbarButtonClass"
            :ariaLabel="editAriaLabel"
            @click="emit('edit')"
        >
            <Pencil
                :class="mobileShellPageCardToolbarIconClass"
                aria-hidden="true"
            />
        </IconActionButton>
        <IconActionButton
            v-if="showDelete"
            :class="mobileShellPageCardToolbarButtonClass"
            tone="danger"
            :ariaLabel="deleteAriaLabel"
            @click="emit('delete')"
        >
            <Trash2
                :class="mobileShellPageCardToolbarIconClass"
                aria-hidden="true"
            />
        </IconActionButton>
    </div>
</template>
