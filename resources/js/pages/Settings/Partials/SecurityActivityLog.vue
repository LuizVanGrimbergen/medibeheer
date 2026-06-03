<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import NumberedPagination from '@/Components/ui/pagination/NumberedPagination.vue';
import type {
    SecurityActivityEntry,
    SecurityActivityPaginator,
} from '@/lib/types';

const props = defineProps<{
    securityActivities: SecurityActivityPaginator;
}>();

const { t } = useI18n();

const descriptionLabel = (
    description: SecurityActivityEntry['description'],
): string => {
    const key = `profile.securityActivity.descriptions.${description}`;
    const translated = t(key);

    return translated === key ? description : translated;
};

const formatDateTime = (iso: string | null): string => {
    if (iso === null) {
        return '';
    }

    return new Intl.DateTimeFormat('nl-BE', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(iso));
};
</script>

<template>
    <div class="space-y-4">
        <div>
            <h2 class="text-primary text-lg font-semibold">
                {{ t('profile.securityActivity.title') }}
            </h2>
            <p class="text-text-muted mt-1 text-sm">
                {{ t('profile.securityActivity.description') }}
            </p>
        </div>

        <ul
            v-if="props.securityActivities.data.length > 0"
            class="divide-border border-border divide-y rounded-lg border"
        >
            <li
                v-for="entry in props.securityActivities.data"
                :key="entry.id"
                class="px-4 py-3"
            >
                <p class="text-primary font-medium">
                    {{ descriptionLabel(entry.description) }}
                </p>
                <p class="text-text-muted mt-1 text-sm">
                    {{ formatDateTime(entry.created_at) }}
                    <span v-if="entry.ip"> · {{ entry.ip }}</span>
                </p>
            </li>
        </ul>

        <p
            v-else
            class="border-border bg-surface-muted text-text-muted rounded-lg border px-4 py-6 text-center text-sm"
        >
            {{ t('profile.securityActivity.empty') }}
        </p>

        <NumberedPagination
            v-if="props.securityActivities.meta.last_page > 1"
            route-name="settings.edit"
            :meta="props.securityActivities.meta"
            :query="{ section: 'security-activity' }"
        />
    </div>
</template>
