import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

interface Permission {
    id: number;
    name: string;
    display_name: string;
    group: string;
}

interface Role {
    id: number;
    name: string;
    display_name: string;
    description: string | null;
    permissions: Permission[];
}

interface RoleUser {
    id: number;
    user_id: number;
    role_id: number;
    parent_id: number | null;
    role: Role;
}

export function usePermission() {
    const page = usePage();
    const auth = computed(() => page.props.auth as any);
    const user = computed(() => auth.value?.user as any);
    const roles = computed(() => user.value?.roles as Role[] | undefined);
    const permissions = computed(() => user.value?.permissions as string[] | undefined);

    const hasPermission = (permissionName: string): boolean => {
        if (!permissions.value) return false;
        return permissions.value.includes(permissionName);
    };

    const hasRole = (roleName: string | string[]): boolean => {
        if (!roles.value) return false;
        const roleNames = Array.isArray(roleName) ? roleName : [roleName];
        return roles.value.some((role) => roleNames.includes(role.name));
    };

    const hasAnyPermission = (permissionNames: string[]): boolean => {
        return permissionNames.some((permission) => hasPermission(permission));
    };

    const hasAllPermissions = (permissionNames: string[]): boolean => {
        return permissionNames.every((permission) => hasPermission(permission));
    };

    const isAdmin = (): boolean => {
        return hasRole('admin') || user.value?.is_admin === true;
    };

    const isOwner = (): boolean => {
        return hasRole('owner');
    };

    const isStaff = (): boolean => {
        return hasRole('staff');
    };

    const canManageStaff = (): boolean => {
        return hasPermission('manage-staff') || isAdmin();
    };

    const canViewReports = (): boolean => {
        return hasPermission('view-tests') || isAdmin();
    };

    const canManageSettings = (): boolean => {
        return hasPermission('manage-tokens') || isAdmin();
    };

    const getPermissionsByGroup = (group: string): Permission[] => {
        if (!roles.value) return [];
        
        const allPermissions = roles.value.flatMap((role) => role.permissions || []);
        return allPermissions.filter((permission) => permission.group === group);
    };

    const getAllPermissions = (): Permission[] => {
        if (!roles.value) return [];
        return roles.value.flatMap((role) => role.permissions || []);
    };

    return {
        // Core permission checks
        hasPermission,
        hasRole,
        hasAnyPermission,
        hasAllPermissions,
        
        // Role checks
        isAdmin,
        isOwner,
        isStaff,
        
        // Convenience methods
        canManageStaff,
        canViewReports,
        canManageSettings,
        
        // Get permissions
        getPermissionsByGroup,
        getAllPermissions,
        
        // Raw data
        user,
        roles,
        permissions,
    };
}
