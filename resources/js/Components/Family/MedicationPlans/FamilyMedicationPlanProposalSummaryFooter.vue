<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { Button } from '@/Components/ui/button';
import { cn } from '@/lib/utils';

const props = defineProps<{
    publishUrl?: string;
    processing: boolean;
    canPublish: boolean;
    canAddAnother: boolean;
    standalone?: boolean;
}>();

const emit = defineEmits<{
    cancel: [];
    addAnother: [];
}>();

const { t } = useI18n();

const actionButtonClass = 'min-h-12 w-full touch-manipulation sm:w-auto';
</script>

<template>
    <div
        :class="
            cn(
                'flex min-w-0 flex-col gap-3',
                !props.standalone && 'border-t border-border pt-4',
            )
        "
    >
        <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap">
            <Button
                v-if="canAddAnother"
                type="button"
                variant="outline"
                size="lg"
                :class="actionButtonClass"
                :disabled="processing"
                @click="emit('addAnother')"
            >
                {{ t('family.medicationPlans.form.addAnotherMedication') }}
            </Button>

            <Button
                v-if="canPublish && props.publishUrl !== undefined"
                as-child
                size="lg"
                :class="actionButtonClass"
                :disabled="processing"
            >
                <Link :href="props.publishUrl">
                    {{ t('family.medicationPlans.form.share') }}
                </Link>
            </Button>
        </div>

        <Button
            type="button"
            variant="ghost"
            :class="cn(actionButtonClass, 'text-text-muted')"
            :disabled="processing"
            @click="emit('cancel')"
        >
            {{ t('family.medicationPlans.form.cancel') }}
        </Button>
    </div>
</template>
