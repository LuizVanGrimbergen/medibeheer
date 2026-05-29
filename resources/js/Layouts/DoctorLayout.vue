<script setup lang="ts">
import DoctorMobileFooterNav from '@/Components/Doctor/DoctorMobileFooterNav.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const shellPaddingX = 'px-4 sm:px-6 md:px-6';
</script>

<template>
    <AuthenticatedLayout>
        <div class="flex min-h-0 min-w-0 flex-1 flex-col overflow-hidden">
            <div
                class="h-0 min-h-0 min-w-0 flex-1 overflow-x-hidden overflow-y-auto overscroll-y-contain"
            >
                <div
                    :class="`relative mx-auto w-full max-w-7xl pt-6 pb-4 ${shellPaddingX}`"
                >
                    <slot />
                </div>
            </div>

            <DoctorMobileFooterNav />
        </div>
    </AuthenticatedLayout>
</template>
