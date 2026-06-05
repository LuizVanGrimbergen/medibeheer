<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { PageProps, SeoSharedProps } from '@/lib/types';

const props = defineProps<{
    title: string;
    description?: string;
    noIndex?: boolean;
}>();

const page = usePage<PageProps>();
const seo = computed(() => page.props.seo as SeoSharedProps);

const description = computed(
    () => props.description ?? seo.value.description ?? '',
);

const indexable = computed(
    () => props.noIndex !== true && seo.value.indexable === true,
);

const fullTitle = computed(() => `${props.title} - ${seo.value.siteName}`);

const canonicalUrl = computed(() => seo.value.canonicalUrl ?? '');

const ogImageUrl = computed(() => seo.value.ogImageUrl ?? '');

const ogLocale = computed(() => seo.value.locale ?? 'nl_BE');
</script>

<template>
    <Head>
        <title head-key="title">{{ props.title }}</title>
        <meta
            v-if="description !== ''"
            head-key="description"
            name="description"
            :content="description"
        />
        <meta
            v-if="indexable"
            head-key="robots"
            name="robots"
            content="index, follow"
        />
        <meta
            v-else
            head-key="robots"
            name="robots"
            content="noindex, nofollow"
        />
        <link
            v-if="indexable && canonicalUrl !== ''"
            head-key="canonical"
            rel="canonical"
            :href="canonicalUrl"
        />
        <meta head-key="og:type" property="og:type" content="website" />
        <meta head-key="og:locale" property="og:locale" :content="ogLocale" />
        <meta
            head-key="og:site_name"
            property="og:site_name"
            :content="seo.siteName"
        />
        <meta head-key="og:title" property="og:title" :content="fullTitle" />
        <meta
            v-if="description !== ''"
            head-key="og:description"
            property="og:description"
            :content="description"
        />
        <meta
            v-if="canonicalUrl !== ''"
            head-key="og:url"
            property="og:url"
            :content="canonicalUrl"
        />
        <meta
            v-if="ogImageUrl !== ''"
            head-key="og:image"
            property="og:image"
            :content="ogImageUrl"
        />
        <meta
            head-key="twitter:card"
            name="twitter:card"
            content="summary_large_image"
        />
        <meta
            head-key="twitter:title"
            name="twitter:title"
            :content="fullTitle"
        />
        <meta
            v-if="description !== ''"
            head-key="twitter:description"
            name="twitter:description"
            :content="description"
        />
        <meta
            v-if="ogImageUrl !== ''"
            head-key="twitter:image"
            name="twitter:image"
            :content="ogImageUrl"
        />
    </Head>
</template>
