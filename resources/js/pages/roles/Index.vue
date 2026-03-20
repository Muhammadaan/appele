<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

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
    users_count: number;
}

interface User {
    id: number;
    name: string;
    email: string;
}

interface Props {
    roles: Role[];
    permissions: Permission[];
    users: User[];
}

const props = defineProps<Props>();

// Modal states
const showRoleModal = ref(false);
const showPermissionModal = ref(false);
const showAssignUserModal = ref(false);
const showPermissionsModal = ref(false);
const editingRole = ref<Role | null>(null);
const editingPermission = ref<Permission | null>(null);
const selectedRole = ref<Role | null>(null);

// Forms
const roleForm = useForm({
    name: '',
    display_name: '',
    description: '',
});

const permissionForm = useForm({
    name: '',
    display_name: '',
    group: '',
});

const assignUserForm = useForm({
    user_id: 0,
    role_name: '',
    parent_id: null as number | null,
});

const permissionsForm = useForm({
    permission_ids: [] as number[],
});

// Computed
const owners = computed(() => {
    return props.users.filter(u => {
        const role = props.roles.find(r => r.name === 'owner');
        return role; // Simplified - in real app check user roles
    });
});

const permissionsByGroup = computed(() => {
    const groups: Record<string, Permission[]> = {};
    props.permissions.forEach(p => {
        if (!groups[p.group]) {
            groups[p.group] = [];
        }
        groups[p.group].push(p);
    });
    return groups;
});

// Role functions
const openCreateRole = () => {
    editingRole.value = null;
    roleForm.reset();
    showRoleModal.value = true;
};

const openEditRole = (role: Role) => {
    editingRole.value = role;
    roleForm.name = role.name;
    roleForm.display_name = role.display_name;
    roleForm.description = role.description || '';
    showRoleModal.value = true;
};

const submitRole = () => {
    if (editingRole.value) {
        roleForm.put(`/roles/${editingRole.value.id}`, {
            onSuccess: () => closeRoleModal(),
        });
    } else {
        roleForm.post('/roles', {
            onSuccess: () => closeRoleModal(),
        });
    }
};

const closeRoleModal = () => {
    showRoleModal.value = false;
    editingRole.value = null;
    roleForm.reset();
};

const deleteRole = (role: Role) => {
    if (!confirm(`Delete role "${role.display_name}"?`)) return;
    
    useForm({}).delete(`/roles/${role.id}`, {
        preserveScroll: true,
    });
};

// Permission functions
const openCreatePermission = () => {
    editingPermission.value = null;
    permissionForm.reset();
    showPermissionModal.value = true;
};

const openEditPermission = (permission: Permission) => {
    editingPermission.value = permission;
    permissionForm.name = permission.name;
    permissionForm.display_name = permission.display_name;
    permissionForm.group = permission.group;
    showPermissionModal.value = true;
};

const submitPermission = () => {
    if (editingPermission.value) {
        permissionForm.put(`/permissions/${editingPermission.value.id}`, {
            onSuccess: () => closePermissionModal(),
        });
    } else {
        permissionForm.post('/permissions', {
            onSuccess: () => closePermissionModal(),
        });
    }
};

const closePermissionModal = () => {
    showPermissionModal.value = false;
    editingPermission.value = null;
    permissionForm.reset();
};

const deletePermission = (permission: Permission) => {
    if (!confirm(`Delete permission "${permission.display_name}"?`)) return;
    
    useForm({}).delete(`/permissions/${permission.id}`, {
        preserveScroll: true,
    });
};

// Assign user to role
const openAssignUser = () => {
    assignUserForm.reset();
    showAssignUserModal.value = true;
};

const submitAssignUser = () => {
    assignUserForm.post('/roles/assign-to-user', {
        onSuccess: () => {
            showAssignUserModal.value = false;
            assignUserForm.reset();
        },
    });
};

// Manage role permissions
const openPermissionsManager = (role: Role) => {
    selectedRole.value = role;
    permissionsForm.permission_ids = role.permissions.map(p => p.id);
    showPermissionsModal.value = true;
};

const submitPermissions = () => {
    if (!selectedRole.value) return;
    
    permissionsForm.post(`/roles/${selectedRole.value.id}/permissions`, {
        onSuccess: () => {
            showPermissionsModal.value = false;
            selectedRole.value = null;
        },
    });
};

const getRoleBadgeClass = (roleName: string) => {
    const classes: Record<string, string> = {
        admin: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
        owner: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
        staff: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
    };
    return classes[roleName] || 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200';
};

const isDefaultRole = (roleName: string) => {
    return ['admin', 'owner', 'staff'].includes(roleName);
};

const getPermissionsByGroup = (group: string) => {
    return props.permissions.filter(p => p.group === group);
};
</script>

<template>
    <Head title="Roles & Permissions" />

    <AppLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Roles & Permissions
                </h2>
                <div class="flex gap-2">
                    <button
                        @click="openCreateRole"
                        class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-xs font-semibold text-white hover:bg-indigo-500"
                    >
                        New Role
                    </button>
                    <button
                        @click="openCreatePermission"
                        class="inline-flex items-center rounded-md border border-transparent bg-green-600 px-4 py-2 text-xs font-semibold text-white hover:bg-green-500"
                    >
                        New Permission
                    </button>
                    <button
                        @click="openAssignUser"
                        class="inline-flex items-center rounded-md border border-transparent bg-purple-600 px-4 py-2 text-xs font-semibold text-white hover:bg-purple-500"
                    >
                        Assign Role to User
                    </button>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Flash Messages -->
                <div v-if="$page.props.flash?.success" class="mb-4 rounded-md bg-green-50 p-4 dark:bg-green-900">
                    <p class="text-sm text-green-800 dark:text-green-200">{{ $page.props.flash.success }}</p>
                </div>
                <div v-if="$page.props.flash?.error" class="mb-4 rounded-md bg-red-50 p-4 dark:bg-red-900">
                    <p class="text-sm text-red-800 dark:text-red-200">{{ $page.props.flash.error }}</p>
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <!-- Roles List -->
                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <h3 class="mb-4 text-lg font-medium">Roles</h3>
                            
                            <div v-if="roles.length === 0" class="text-center py-8 text-gray-500">
                                No roles found.
                            </div>

                            <div v-else class="space-y-3">
                                <div
                                    v-for="role in roles"
                                    :key="role.id"
                                    class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900"
                                >
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2">
                                                <h4 class="font-medium">{{ role.display_name }}</h4>
                                                <span :class="getRoleBadgeClass(role.name)" class="rounded-full px-2 py-0.5 text-xs">
                                                    {{ role.name }}
                                                </span>
                                                <span v-if="isDefaultRole(role.name)" class="rounded-full bg-gray-200 px-2 py-0.5 text-xs text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                                    Default
                                                </span>
                                            </div>
                                            <p v-if="role.description" class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                                {{ role.description }}
                                            </p>
                                            <div class="mt-2 flex flex-wrap gap-1">
                                                <span
                                                    v-for="perm in role.permissions"
                                                    :key="perm.id"
                                                    class="rounded bg-blue-100 px-2 py-0.5 text-xs text-blue-800 dark:bg-blue-900 dark:text-blue-200"
                                                >
                                                    {{ perm.display_name }}
                                                </span>
                                            </div>
                                            <p class="mt-2 text-xs text-gray-500">
                                                {{ role.users_count }} user(s)
                                            </p>
                                        </div>
                                        <div class="flex gap-1">
                                            <button
                                                @click="openPermissionsManager(role)"
                                                class="rounded p-1 text-green-600 hover:bg-green-100 dark:hover:bg-green-900"
                                                title="Manage Permissions"
                                            >
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                            </button>
                                            <button
                                                @click="openEditRole(role)"
                                                class="rounded p-1 text-blue-600 hover:bg-blue-100 dark:hover:bg-blue-900"
                                                title="Edit"
                                            >
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </button>
                                            <button
                                                v-if="!isDefaultRole(role.name)"
                                                @click="deleteRole(role)"
                                                class="rounded p-1 text-red-600 hover:bg-red-100 dark:hover:bg-red-900"
                                                title="Delete"
                                            >
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Permissions List -->
                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <h3 class="mb-4 text-lg font-medium">Permissions</h3>
                            
                            <div v-if="permissions.length === 0" class="text-center py-8 text-gray-500">
                                No permissions found.
                            </div>

                            <div v-else class="space-y-4">
                                <div v-for="(perms, group) in permissionsByGroup" :key="group">
                                    <h4 class="mb-2 capitalize text-sm font-medium text-gray-600 dark:text-gray-400">
                                        {{ group }}
                                    </h4>
                                    <div class="space-y-2">
                                        <div
                                            v-for="perm in perms"
                                            :key="perm.id"
                                            class="flex items-center justify-between rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-900"
                                        >
                                            <div>
                                                <p class="font-medium">{{ perm.display_name }}</p>
                                                <p class="text-xs text-gray-500">{{ perm.name }}</p>
                                            </div>
                                            <div class="flex gap-1">
                                                <button
                                                    @click="openEditPermission(perm)"
                                                    class="rounded p-1 text-blue-600 hover:bg-blue-100 dark:hover:bg-blue-900"
                                                >
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                </button>
                                                <button
                                                    v-if="!['view-dashboard', 'view-questions', 'manage-questions', 'view-tests', 'manage-tests', 'start-test', 'manage-staff', 'view-staff', 'manage-users', 'view-users', 'manage-tokens', 'view-tokens'].includes(perm.name)"
                                                    @click="deletePermission(perm)"
                                                    class="rounded p-1 text-red-600 hover:bg-red-100 dark:hover:bg-red-900"
                                                >
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Role Modal -->
                <div v-if="showRoleModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
                    <div class="w-full max-w-md rounded-lg bg-white p-6 dark:bg-gray-800">
                        <h3 class="mb-4 text-lg font-medium">{{ editingRole ? 'Edit Role' : 'Create Role' }}</h3>
                        <form @submit.prevent="submitRole">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                                    <input v-model="roleForm.name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" required />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Display Name</label>
                                    <input v-model="roleForm.display_name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" required />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                                    <textarea v-model="roleForm.description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"></textarea>
                                </div>
                            </div>
                            <div class="mt-6 flex justify-end gap-2">
                                <button type="button" @click="closeRoleModal" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                                    Cancel
                                </button>
                                <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">
                                    {{ editingRole ? 'Update' : 'Create' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Permission Modal -->
                <div v-if="showPermissionModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
                    <div class="w-full max-w-md rounded-lg bg-white p-6 dark:bg-gray-800">
                        <h3 class="mb-4 text-lg font-medium">{{ editingPermission ? 'Edit Permission' : 'Create Permission' }}</h3>
                        <form @submit.prevent="submitPermission">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                                    <input v-model="permissionForm.name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" required />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Display Name</label>
                                    <input v-model="permissionForm.display_name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" required />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Group</label>
                                    <input v-model="permissionForm.group" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" required />
                                </div>
                            </div>
                            <div class="mt-6 flex justify-end gap-2">
                                <button type="button" @click="closePermissionModal" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                                    Cancel
                                </button>
                                <button type="submit" class="rounded-md bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-500">
                                    {{ editingPermission ? 'Update' : 'Create' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Assign User Modal -->
                <div v-if="showAssignUserModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
                    <div class="w-full max-w-md rounded-lg bg-white p-6 dark:bg-gray-800">
                        <h3 class="mb-4 text-lg font-medium">Assign Role to User</h3>
                        <form @submit.prevent="submitAssignUser">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">User</label>
                                    <select v-model="assignUserForm.user_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" required>
                                        <option value="0">Select a user</option>
                                        <option v-for="user in users" :key="user.id" :value="user.id">
                                            {{ user.name }} ({{ user.email }})
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Role</label>
                                    <select v-model="assignUserForm.role_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" required>
                                        <option value="">Select a role</option>
                                        <option v-for="role in roles" :key="role.id" :value="role.name">
                                            {{ role.display_name }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="mt-6 flex justify-end gap-2">
                                <button type="button" @click="showAssignUserModal = false" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                                    Cancel
                                </button>
                                <button type="submit" class="rounded-md bg-purple-600 px-4 py-2 text-sm font-medium text-white hover:bg-purple-500">
                                    Assign
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Permissions Manager Modal -->
                <div v-if="showPermissionsModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
                    <div class="w-full max-w-2xl rounded-lg bg-white p-6 dark:bg-gray-800">
                        <h3 class="mb-4 text-lg font-medium">Manage Permissions for {{ selectedRole?.display_name }}</h3>
                        <form @submit.prevent="submitPermissions">
                            <div class="max-h-[60vh] space-y-4 overflow-y-auto pr-2">
                                <!-- Dashboard -->
                                <div>
                                    <h4 class="mb-2 flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                        </svg>
                                        Dashboard
                                    </h4>
                                    <div class="space-y-2 rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-900">
                                        <label v-for="perm in getPermissionsByGroup('dashboard')" :key="perm.id" class="flex cursor-pointer items-center gap-2">
                                            <input
                                                type="checkbox"
                                                :value="perm.id"
                                                v-model="permissionsForm.permission_ids"
                                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900"
                                            />
                                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ perm.display_name }}</span>
                                        </label>
                                    </div>
                                </div>

                                <!-- Questions -->
                                <div>
                                    <h4 class="mb-2 flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Questions
                                    </h4>
                                    <div class="space-y-2 rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-900">
                                        <label v-for="perm in getPermissionsByGroup('questions')" :key="perm.id" class="flex cursor-pointer items-center gap-2">
                                            <input
                                                type="checkbox"
                                                :value="perm.id"
                                                v-model="permissionsForm.permission_ids"
                                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900"
                                            />
                                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ perm.display_name }}</span>
                                        </label>
                                    </div>
                                </div>

                                <!-- Tests -->
                                <div>
                                    <h4 class="mb-2 flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                        </svg>
                                        Tests
                                    </h4>
                                    <div class="space-y-2 rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-900">
                                        <label v-for="perm in getPermissionsByGroup('tests')" :key="perm.id" class="flex cursor-pointer items-center gap-2">
                                            <input
                                                type="checkbox"
                                                :value="perm.id"
                                                v-model="permissionsForm.permission_ids"
                                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900"
                                            />
                                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ perm.display_name }}</span>
                                        </label>
                                    </div>
                                </div>

                                <!-- Staff -->
                                <div>
                                    <h4 class="mb-2 flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                        </svg>
                                        Staff
                                    </h4>
                                    <div class="space-y-2 rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-900">
                                        <label v-for="perm in getPermissionsByGroup('staff')" :key="perm.id" class="flex cursor-pointer items-center gap-2">
                                            <input
                                                type="checkbox"
                                                :value="perm.id"
                                                v-model="permissionsForm.permission_ids"
                                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900"
                                            />
                                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ perm.display_name }}</span>
                                        </label>
                                    </div>
                                </div>

                                <!-- Users -->
                                <div>
                                    <h4 class="mb-2 flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                        </svg>
                                        Users
                                    </h4>
                                    <div class="space-y-2 rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-900">
                                        <label v-for="perm in getPermissionsByGroup('users')" :key="perm.id" class="flex cursor-pointer items-center gap-2">
                                            <input
                                                type="checkbox"
                                                :value="perm.id"
                                                v-model="permissionsForm.permission_ids"
                                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900"
                                            />
                                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ perm.display_name }}</span>
                                        </label>
                                    </div>
                                </div>

                                <!-- Tokens -->
                                <div>
                                    <h4 class="mb-2 flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Tokens
                                    </h4>
                                    <div class="space-y-2 rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-900">
                                        <label v-for="perm in getPermissionsByGroup('tokens')" :key="perm.id" class="flex cursor-pointer items-center gap-2">
                                            <input
                                                type="checkbox"
                                                :value="perm.id"
                                                v-model="permissionsForm.permission_ids"
                                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900"
                                            />
                                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ perm.display_name }}</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-6 flex justify-end gap-2">
                                <button type="button" @click="showPermissionsModal = false" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                                    Cancel
                                </button>
                                <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">
                                    Save Permissions
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
