<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    Calendar,
    ClipboardCheck,
    FileText,
    HeartHandshake,
    ListChecks,
    Package,
    Pill,
    Stethoscope,
    Users,
} from 'lucide-vue-next';
import type { Component } from 'vue';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import HomeFeatureCollapsibleItem from '@/Components/Guest/HomeFeatureCollapsibleItem.vue';
import SeoHead from '@/Components/Seo/SeoHead.vue';
import { AuthPageContainer } from '@/Components/ui/auth-page';
import { Button } from '@/Components/ui/button';
import {
    homeFeatureKeys,
    homeFeaturePoints
    
} from '@/lib/guest/homeFeatures';
import type {HomeFeatureKey} from '@/lib/guest/homeFeatures';

const { t } = useI18n();

const featureIcons: Record<HomeFeatureKey, Component> = {
    medication: Pill,
    prescriptions: FileText,
    intakes: ClipboardCheck,
    inventory: Package,
    appointments: Calendar,
    medicationPlans: ListChecks,
    family: HeartHandshake,
    checkins: Stethoscope,
    roles: Users,
};

const features = homeFeatureKeys.map((key) => ({
    key,
    icon: featureIcons[key],
    points: homeFeaturePoints(key),
}));

const openByKey = ref<Record<HomeFeatureKey, boolean>>(
    Object.fromEntries(
        homeFeatureKeys.map((key) => [key, false]),
    ) as Record<HomeFeatureKey, boolean>,
);
</script>

<template>
    <SeoHead
        :title="t('home.metaTitle')"
        :description="t('home.metaDescription')"
    />

    <AuthPageContainer
        title-key="home.welcomeTitle"
        subtitle-key="home.welcomeSubtitle"
        subtitle-tone="muted"
        append-app-name
    >
        <div class="mb-8 flex flex-col gap-3 sm:flex-row sm:justify-center">
            <Button as-child size="lg" class="w-full sm:flex-1">
                <Link :href="route('login')">
                    {{ t('home.ctaLogin') }}
                </Link>
            </Button>
            <Button
                as-child
                variant="outline"
                size="lg"
                class="border-border bg-surface text-text hover:bg-surface-hover w-full sm:flex-1"
            >
                <Link :href="route('register')">
                    {{ t('home.ctaRegister') }}
                </Link>
            </Button>
        </div>

        <section :aria-label="t('home.featuresHeading')" class="mb-2 space-y-4">
            <div class="text-center">
                <h2 class="text-text-heading text-xl font-semibold">
                    {{ t('home.featuresHeading') }}
                </h2>
                <p class="text-text-muted mt-2 text-base leading-relaxed">
                    {{ t('home.featuresIntro') }}
                </p>
            </div>

            <ul class="flex flex-col gap-4">
                <li v-for="feature in features" :key="feature.key">
                    <HomeFeatureCollapsibleItem
                        v-model:open="openByKey[feature.key]"
                        :icon="feature.icon"
                        :title="t(`home.features.${feature.key}.title`)"
                        :summary="t(`home.features.${feature.key}.summary`)"
                        :points="feature.points"
                    />
                </li>
            </ul>
        </section>

        <footer
            class="text-text-muted mt-10 flex flex-wrap items-center justify-center gap-x-6 gap-y-2 text-sm"
        >
            <Link
                :href="route('legal.privacy')"
                class="text-primary font-semibold hover:opacity-80"
            >
                {{ t('home.legalPrivacy') }}
            </Link>
            <Link
                :href="route('legal.cookies')"
                class="text-primary font-semibold hover:opacity-80"
            >
                {{ t('home.legalCookies') }}
            </Link>
        </footer>
    </AuthPageContainer>
</template>
