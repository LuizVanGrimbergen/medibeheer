<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import NumberedPagination from '@/Components/ui/pagination/NumberedPagination.vue';
import type { SecurityActivityEntry, SecurityActivityPaginator } from '@/lib/types';

const props = defineProps<{
    securityActivities: SecurityActivityPaginator;
}>();

const { t } = useI18n();

const descriptionLabel = (description: SecurityActivityEntry['description']): string => {
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
            <h2 class="text-lg font-semibold text-primary">
                {{ t('profile.securityActivity.title') }}
            </h2>
            <p class="mt-1 text-sm text-text-muted">
                {{ t('profile.securityActivity.description') }}
            </p>
        </div>

        <ul
            v-if="props.securityActivities.data.length > 0"
            class="divide-y divide-border rounded-lg border border-border"
        >
            <li
                v-for="entry in props.securityActivities.data"
                :key="entry.id"
                class="px-4 py-3"
            >
                <p class="font-medium text-primary">
                    {{ descriptionLabel(entry.description) }}
                </p>
                <p class="mt-1 text-sm text-text-muted">
                    {{ formatDateTime(entry.created_at) }}
                    <span v-if="entry.ip"> · {{ entry.ip }}</span>
                </p>
            </li>
        </ul>

        <p
            v-else
            class="rounded-lg border border-border bg-surface-muted px-4 py-6 text-center text-sm text-text-muted"
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
