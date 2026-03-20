<template>
    <AppLayout>
        <div class="container mx-auto p-4">
            <h1 class="text-3xl font-bold">Dashboard</h1>
            <p class="mt-4 text-gray-600">HAlo Edit A</p>

            <div class="mt-6 rounded-lg border p-4">
                <div class="text-sm text-gray-600">Sisa Token:</div>
                <div class="text-2xl font-semibold">{{ tokens }}</div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

const page = usePage();
const tokens = computed(() => page.props.auth?.user?.tokens ?? 0);
</script>
