<script setup lang="ts">
import { computed } from 'vue';
import { usePermission } from '@/composables/usePermission';

interface Props {
    permission?: string | string[];
    role?: string | string[];
    any?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    any: false,
});

const { hasPermission, hasRole } = usePermission();

const canShow = computed(() => {
    // If role is specified, check role
    if (props.role) {
        return Array.isArray(props.role)
            ? props.any
                ? props.role.some((r) => hasRole(r))
                : props.role.every((r) => hasRole(r))
            : hasRole(props.role);
    }

    // If permission is specified, check permission
    if (props.permission) {
        return Array.isArray(props.permission)
            ? props.any
                ? props.permission.some((p) => hasPermission(p))
                : props.permission.every((p) => hasPermission(p))
            : hasPermission(props.permission);
    }

    return true;
});
</script>

<template>
    <slot v-if="canShow" />
</template>
