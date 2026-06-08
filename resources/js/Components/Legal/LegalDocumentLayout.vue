<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import { onMounted, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import SeoHead from '@/Components/Seo/SeoHead.vue';

const props = withDefaults(
    defineProps<{
        titleKey: string;
        metaTitleKey: string;
        metaDescriptionKey: string;
        documentVersion: string;
        versionLabelKey?: string;
        documentLocale: string;
        localeRouteName: string;
    }>(),
    {
        versionLabelKey: 'privacy.versionLabel',
    },
);

const { t, locale } = useI18n();

const syncLocale = (value: string): void => {
    if (value === 'en' || value === 'nl') {
        locale.value = value;
    }
};

onMounted(() => {
    syncLocale(props.documentLocale);
});

watch(
    () => props.documentLocale,
    (value) => {
        syncLocale(value);
    },
);

const localeHref = (lang: string): string =>
    `${route(props.localeRouteName)}?lang=${lang}`;
</script>

<template>
    <SeoHead
        :title="t(props.metaTitleKey)"
        :description="t(props.metaDescriptionKey)"
    />

    <div
        class="bg-bg min-h-dvh pt-[env(safe-area-inset-top)] pb-[env(safe-area-inset-bottom)]"
    >
        <div class="mx-auto max-w-2xl px-4 py-8 sm:px-6 sm:py-10">
            <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
                <Link
                    :href="route('login')"
                    class="text-primary inline-flex items-center gap-2 text-sm font-semibold hover:opacity-80"
                >
                    <ArrowLeft :size="18" />
                    <span>{{ t('privacy.back') }}</span>
                </Link>

                <div
                    class="text-text-muted flex items-center gap-2 text-sm"
                    :aria-label="t('privacy.localeSwitcherLabel')"
                >
                    <Link
                        :href="localeHref('nl')"
                        class="font-semibold hover:opacity-80"
                        :class="
                            documentLocale === 'nl'
                                ? 'text-primary'
                                : 'text-text-muted'
                        "
                    >
                        {{ t('privacy.localeNl') }}
                    </Link>
                    <span aria-hidden="true">|</span>
                    <Link
                        :href="localeHref('en')"
                        class="font-semibold hover:opacity-80"
                        :class="
                            documentLocale === 'en'
                                ? 'text-primary'
                                : 'text-text-muted'
                        "
                    >
                        {{ t('privacy.localeEn') }}
                    </Link>
                </div>
            </div>

            <h1 class="text-text text-3xl font-bold">
                {{ t(props.titleKey) }}
            </h1>
            <p class="text-text-muted mt-2 text-sm">
                {{
                    t(props.versionLabelKey, {
                        version: props.documentVersion,
                    })
                }}
            </p>

            <div
                class="text-text mt-8 max-w-none space-y-8 text-base leading-relaxed"
            >
                <slot />
            </div>
        </div>
    </div>
</template>
