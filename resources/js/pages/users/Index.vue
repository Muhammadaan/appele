<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { usePermission } from '@/composables/usePermission';
import { ref, computed } from 'vue';
import type { DataTablePageEvent, DataTableSortEvent } from 'primevue/datatable';
import { useToast } from 'primevue/usetoast';
import { useConfirm } from 'primevue/useconfirm';
import { index as usersIndex, destroy as usersDestroy } from '@/routes/users';

interface Role {
    name: string;
    display_name: string;
}

interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    is_admin: boolean;
    roles: Role[];
    created_at: string;
}

interface Pagination {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
}

interface Filters {
    search: string | null;
    sort_field: string;
    sort_order: string;
    per_page: number;
}

interface Props {
    users: User[];
    pagination: Pagination;
    filters: Filters;
}

const props = defineProps<Props>();

const { isAdmin } = usePermission();
const toast = useToast();
const confirm = useConfirm();

const searchValue = ref(props.filters.search || '');
const loading = ref(false);

const sortField = ref(props.filters.sort_field || 'id');
const sortOrder = ref(props.filters.sort_order === 'desc' ? -1 : 1);

const perPageOptions = [
    { label: '5', value: 5 },
    { label: '10', value: 10 },
    { label: '25', value: 25 },
    { label: '50', value: 50 },
    { label: '100', value: 100 },
];

const selectedPerPage = ref(props.filters.per_page || 10);

function handleSearch() {
    loading.value = true;
    router.get(
        usersIndex.url({
            query: {
                search: searchValue.value || null,
                sort_field: sortField.value,
                sort_order: sortOrder.value === -1 ? 'desc' : 'asc',
                per_page: selectedPerPage.value,
            },
        }),
        {},
        {
            preserveState: true,
            onFinish: () => {
                loading.value = false;
            },
        },
    );
}

function handlePage(event: DataTablePageEvent) {
    loading.value = true;
    router.get(
        usersIndex.url({
            query: {
                search: searchValue.value || null,
                sort_field: sortField.value,
                sort_order: sortOrder.value === -1 ? 'desc' : 'asc',
                per_page: event.rows,
                page: event.page + 1,
            },
        }),
        {},
        {
            preserveState: true,
            onFinish: () => {
                loading.value = false;
            },
        },
    );
}

function handleSort(event: DataTableSortEvent) {
    loading.value = true;
    sortField.value = event.sortField as string;
    sortOrder.value = event.sortOrder || 1;
    router.get(
        usersIndex.url({
            query: {
                search: searchValue.value || null,
                sort_field: event.sortField,
                sort_order: event.sortOrder === -1 ? 'desc' : 'asc',
                per_page: selectedPerPage.value,
            },
        }),
        {},
        {
            preserveState: true,
            onFinish: () => {
                loading.value = false;
            },
        },
    );
}

function handlePerPage() {
    loading.value = true;
    router.get(
        usersIndex.url({
            query: {
                search: searchValue.value || null,
                sort_field: sortField.value,
                sort_order: sortOrder.value === -1 ? 'desc' : 'asc',
                per_page: selectedPerPage.value,
            },
        }),
        {},
        {
            preserveState: true,
            onFinish: () => {
                loading.value = false;
            },
        },
    );
}

function confirmDelete(user: User) {
    confirm.require({
        message: `Are you sure you want to delete user "${user.name}"? This action cannot be undone.`,
        header: 'Delete User',
        icon: 'pi pi-exclamation-triangle',
        rejectProps: {
            label: 'Cancel',
            severity: 'secondary',
            outlined: true,
        },
        acceptProps: {
            label: 'Delete',
            severity: 'danger',
        },
        accept: () => {
            deleteUser(user);
        },
    });
}

function deleteUser(user: User) {
    router.delete(usersDestroy.url({ user: user.id }), {
        preserveScroll: true,
        onSuccess: () => {
            toast.add({
                severity: 'success',
                summary: 'Success',
                detail: 'User deleted successfully!',
                life: 3000,
            });
        },
        onError: () => {
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: 'Failed to delete user!',
                life: 3000,
            });
        },
    });
}

function getRoleBadges(roles: Role[], isAdminUser: boolean) {
    const badges = [];
    if (isAdminUser) {
        badges.push({
            label: 'Admin',
            class: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
        });
    }
    roles.forEach((role) => {
        badges.push({
            label: role.display_name,
            class: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
        });
    });
    return badges;
}
</script>

<template>
    <Head title="User Management" />

    <AppLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2
                    class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200"
                >
                    User Management
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div
                    class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800"
                >
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <!-- Search and Actions -->
                        <div class="mb-4 flex items-center justify-between gap-4">
                            <div class="flex flex-1 items-center gap-2">
                                <InputText
                                    v-model="searchValue"
                                    placeholder="Search users..."
                                    class="w-full max-w-md"
                                    @keyup.enter="handleSearch"
                                />
                                <Button
                                    icon="pi pi-search"
                                    label="Search"
                                    @click="handleSearch"
                                    :loading="loading"
                                />
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-sm text-gray-600 dark:text-gray-400"
                                    >Per Page:</span
                                >
                                <select
                                    v-model="selectedPerPage"
                                    class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                    @change="handlePerPage"
                                >
                                    <option
                                        v-for="option in perPageOptions"
                                        :key="option.value"
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- DataTable -->
                        <DataTable
                            :value="users"
                            :loading="loading"
                            :paginator="true"
                            :rows="pagination.per_page"
                            :total-records="pagination.total"
                            :rows-per-page-options="[]"
                            :sort-field="sortField"
                            :sort-order="sortOrder"
                            lazy
                            data-key="id"
                            table-style="min-width: 50rem"
                            @page="handlePage"
                            @sort="handleSort"
                            class="p-datatable-striped p-datatable-gridlines"
                        >
                            <template #header>
                                <div
                                    class="flex items-center justify-between bg-gray-50 p-4 dark:bg-gray-900"
                                >
                                    <span class="text-sm text-gray-600 dark:text-gray-400">
                                        Showing {{ pagination.from || 0 }} to
                                        {{ pagination.to || 0 }} of
                                        {{ pagination.total }} entries
                                    </span>
                                </div>
                            </template>

                            <Column field="id" header="ID" sortable style="width: 80px">
                                <template #body="{ data }">
                                    <span class="font-medium">{{ data.id }}</span>
                                </template>
                            </Column>

                            <Column field="name" header="User" sortable>
                                <template #body="{ data }">
                                    <div class="flex items-center gap-3">
                                        <!-- Avatar -->
                                        <div
                                            v-if="data.avatar"
                                            class="flex h-10 w-10 flex-shrink-0 overflow-hidden rounded-full"
                                        >
                                            <img
                                                :src="data.avatar"
                                                :alt="data.name"
                                                class="h-full w-full object-cover"
                                            />
                                        </div>
                                        <div
                                            v-else
                                            class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-600 text-white font-medium"
                                        >
                                            {{ data.name.charAt(0).toUpperCase() }}
                                        </div>

                                        <!-- Info -->
                                        <div>
                                            <div class="font-medium">{{ data.name }}</div>
                                            <div class="text-sm text-gray-500">
                                                {{ data.email }}
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </Column>

                            <Column field="roles" header="Roles" style="width: 200px">
                                <template #body="{ data }">
                                    <div class="flex flex-wrap gap-1">
                                        <template
                                            v-for="badge in getRoleBadges(
                                                data.roles,
                                                data.is_admin,
                                            )"
                                            :key="badge.label"
                                        >
                                            <span
                                                :class="[
                                                    'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
                                                    badge.class,
                                                ]"
                                            >
                                                {{ badge.label }}
                                            </span>
                                        </template>
                                        <span
                                            v-if="data.roles.length === 0 && !data.is_admin"
                                            class="text-sm text-gray-400"
                                        >
                                            No roles
                                        </span>
                                    </div>
                                </template>
                            </Column>

                            <Column field="created_at" header="Created At" sortable>
                                <template #body="{ data }">
                                    <span>{{
                                        new Date(data.created_at).toLocaleDateString()
                                    }}</span>
                                </template>
                            </Column>

                            <Column header="Actions" style="width: 100px">
                                <template #body="{ data }">
                                    <div class="flex items-center gap-2">
                                        <Button
                                            icon="pi pi-trash"
                                            severity="danger"
                                            text
                                            rounded
                                            size="small"
                                            @click="confirmDelete(data)"
                                            :disabled="!isAdmin"
                                        />
                                    </div>
                                </template>
                            </Column>
                        </DataTable>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
:deep(.p-datatable-header) {
    background-color: transparent;
    border: none;
}

:deep(.p-datatable-thead > tr > th) {
    background-color: #f9fafb;
}

:deep(.dark .p-datatable-thead > tr > th) {
    background-color: #1f2937;
}

:deep(.p-paginator) {
    background-color: transparent;
    border: none;
    justify-content: flex-end;
}
</style>
