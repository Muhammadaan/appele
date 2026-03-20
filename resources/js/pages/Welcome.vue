<script setup lang="ts">
import { Link, Head } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { login, register } from '@/routes';

defineProps<{
    canRegister: boolean;
}>();
</script>

<template>
    <Head title="Welcome" />

    <div class="flex min-h-screen flex-col items-center justify-center gap-6 bg-background p-6 md:p-10">
        <div class="flex flex-col items-center gap-4">
            <div class="flex h-16 w-16 items-center justify-center rounded-md">
                <AppLogoIcon class="size-16 fill-current text-[var(--foreground)] dark:text-white" />
            </div>
            <h1 class="text-3xl font-bold">Welcome</h1>
            <p class="text-center text-sm text-muted-foreground">
                This is the welcome page. Please login to continue.
            </p>
        </div>

        <div class="flex gap-4">
            <Link
                :href="login().url"
                class="inline-flex items-center justify-center rounded-md px-4 py-2 text-sm font-medium bg-primary text-primary-foreground hover:bg-primary/90"
            >
                Login
            </Link>

            <Link
                v-if="canRegister"
                :href="register().url"
                class="inline-flex items-center justify-center rounded-md px-4 py-2 text-sm font-medium border border-input bg-background hover:bg-accent"
            >
                Register
            </Link>
        </div>
    </div>
</template>
