<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import type { BreadcrumbItem } from '@/types';
import Toast from 'primevue/toast';
import ConfirmDialog from 'primevue/confirmdialog';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

defineSlots<{
    default(): any;
    header?(): any;
}>();
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar />
        <AppContent variant="sidebar" class="overflow-x-hidden">
            <AppSidebarHeader :breadcrumbs="breadcrumbs">
                <template #header>
                    <slot name="header" />
                </template>
            </AppSidebarHeader>
            <slot />
        </AppContent>
    </AppShell>
    <Toast />
    <ConfirmDialog />
</template>
