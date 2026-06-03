<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Settings } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import { Button } from '@/Components/ui/button';

const { t } = useI18n();
</script>

<template>
    <div class="flex justify-end pb-1 md:hidden">
        <Button
            as-child
            variant="ghost"
            size="icon"
            class="text-primary hover:bg-primary/10 hover:text-primary hover:opacity-100"
            :aria-label="t('app.navigation.settings')"
        >
            <Link :href="route('settings.edit')">
                <Settings :size="18" />
            </Link>
        </Button>
    </div>
</template>
