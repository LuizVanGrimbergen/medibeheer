<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { Button } from '@/Components/ui/button';

const { t } = useI18n();
const page = usePage();

function pathOnly(urlOrPath: string): string {
    const raw = urlOrPath.startsWith('http')
        ? new URL(urlOrPath).pathname
        : (urlOrPath.split('?')[0] ?? '');

    if (raw.length > 1 && raw.endsWith('/')) {
        return raw.slice(0, -1);
    }

    return raw;
}

const showOnHomePage = computed(() => {
    const pathname = pathOnly(page.url);

    return [
        pathOnly(route('patient.dashboard') as string),
        pathOnly(route('family.overview') as string),
    ].includes(pathname);
});
</script>

<template>
    <div v-if="showOnHomePage" class="flex justify-end pb-1 md:hidden">
        <Button
            as-child
            variant="ghost"
            class="text-primary hover:bg-primary/10 hover:text-primary h-auto px-2 py-1 text-sm font-semibold hover:opacity-100"
        >
            <Link :href="route('settings.edit')">
                {{ t('app.navigation.settings') }}
            </Link>
        </Button>
    </div>
</template>
