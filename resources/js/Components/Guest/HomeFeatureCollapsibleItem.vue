<script setup lang="ts">
import type { Component } from 'vue';
import { useI18n } from 'vue-i18n';
import PatientListCardDetailsToggle from '@/Components/Patient/PatientListCardDetailsToggle.vue';
import { Card, CardContent } from '@/Components/ui/card';
import { Collapsible, CollapsibleContent } from '@/Components/ui/collapsible';
import { Label } from '@/Components/ui/label';
import { cn } from '@/lib/utils';

const open = defineModel<boolean>('open', { default: false });

const props = defineProps<{
    icon: Component;
    title: string;
    summary: string;
    points: string[];
}>();

const { t } = useI18n();

const detailListClass = cn(
    'list-outside list-disc space-y-2.5 pl-5',
    'text-text-muted text-base leading-relaxed',
);
</script>

<template>
    <Card
        class="bg-surface text-text border-border/80 w-full rounded-3xl border shadow-md shadow-black/[0.04]"
    >
        <CardContent class="p-5 sm:p-6">
            <Collapsible v-model:open="open" :unmount-on-hide="false">
                <div class="flex items-start gap-4">
                    <div
                        class="bg-primary/10 text-primary flex size-12 shrink-0 items-center justify-center rounded-xl"
                        aria-hidden="true"
                    >
                        <component
                            :is="props.icon"
                            class="size-6"
                            stroke-width="2"
                        />
                    </div>
                    <div class="min-w-0 flex-1 space-y-1.5">
                        <p
                            class="text-text-heading text-lg leading-snug font-bold"
                        >
                            {{ props.title }}
                        </p>
                        <p
                            v-if="!open"
                            class="text-text text-base leading-snug font-medium"
                        >
                            {{ props.summary }}
                        </p>
                    </div>
                </div>

                <PatientListCardDetailsToggle
                    v-if="!open"
                    mode="expand"
                    :label="t('home.cardExpandHint')"
                    :ariaLabel="
                        t('home.showDetails', { title: props.title })
                    "
                    wrapper-class="mt-5 border-border/70 border-t pt-5"
                />

                <CollapsibleContent>
                    <div class="border-border/70 mt-5 space-y-3 border-t pt-5">
                        <Label
                            class="text-text-heading text-base leading-tight font-semibold"
                        >
                            {{ t('home.featureDetailLabel') }}
                        </Label>
                        <ul :class="detailListClass">
                            <li
                                v-for="(point, index) in props.points"
                                :key="index"
                            >
                                {{ point }}
                            </li>
                        </ul>

                        <PatientListCardDetailsToggle
                            mode="collapse"
                            wrapper-class="border-0 pt-5"
                            :label="t('home.cardCollapseHint')"
                            :ariaLabel="
                                t('home.hideDetails', { title: props.title })
                            "
                        />
                    </div>
                </CollapsibleContent>
            </Collapsible>
        </CardContent>
    </Card>
</template>
