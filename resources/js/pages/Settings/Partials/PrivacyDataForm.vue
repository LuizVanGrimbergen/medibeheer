<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Download } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import { Button } from '@/Components/ui/button';
import type { PageProps } from '@/lib/types';

const { t } = useI18n();
const page = usePage<PageProps>();
</script>

<template>
    <section class="space-y-6">
        <div>
            <h2 class="text-lg font-semibold text-text">
                {{ t('privacy.settings.legalLinksTitle') }}
            </h2>
            <ul class="mt-3 space-y-2 text-base">
                <li>
                    <a
                        :href="page.props.legal.privacyUrl"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="font-semibold text-primary hover:opacity-80"
                    >
                        {{ t('privacy.settings.privacyLink') }}
                    </a>
                </li>
                <li>
                    <a
                        :href="page.props.legal.cookiesUrl"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="font-semibold text-primary hover:opacity-80"
                    >
                        {{ t('privacy.settings.cookiesLink') }}
                    </a>
                </li>
            </ul>
        </div>

        <div>
            <h2 class="text-lg font-semibold text-text">
                {{ t('privacy.settings.exportTitle') }}
            </h2>
            <p class="mt-1 text-sm text-text-muted">
                {{ t('privacy.settings.exportDescription') }}
            </p>
            <Button
                as-child
                class="mt-4"
            >
                <a :href="route('settings.export')">
                    <Download class="mr-2 h-4 w-4" />
                    {{ t('privacy.settings.exportAction') }}
                </a>
            </Button>
        </div>

        <div class="border-t border-border pt-6">
            <p class="text-sm text-text-muted">
                {{ t('profile.delete.description') }}
            </p>
            <Button
                as-child
                variant="outline"
                class="mt-4 border-danger text-danger hover:bg-danger/10"
            >
                <Link :href="route('settings.edit', { section: 'delete' })">
                    {{ t('profile.delete.action') }}
                </Link>
            </Button>
        </div>
    </section>
</template>
