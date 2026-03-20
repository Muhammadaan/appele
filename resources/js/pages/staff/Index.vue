<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import type { DataTablePageEvent, DataTableSortEvent } from 'primevue/datatable';
import { useToast } from 'primevue/usetoast';
import { useConfirm } from 'primevue/useconfirm';
import { index as staffIndex } from '@/routes/staff';

interface Staff {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    created_at: string;
    is_my_staff: boolean;
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
    staff: Staff[];
    pagination: Pagination;
    filters: Filters;
}

const props = defineProps<Props>();

const toast = useToast();
const confirm = useConfirm();

const searchValue = ref(props.filters?.search || '');
const loading = ref(false);

const sortField = ref(props.filters?.sort_field || 'name');
const sortOrder = ref(props.filters?.sort_order === 'desc' ? -1 : 1);

const perPageOptions = [
    { label: '5', value: 5 },
    { label: '10', value: 10 },
    { label: '25', value: 25 },
    { label: '50', value: 50 },
    { label: '100', value: 100 },
];

const selectedPerPage = ref(props.filters?.per_page || 10);

function handleSearch() {
    loading.value = true;
    router.get(
        staffIndex.url({
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
        staffIndex.url({
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
        staffIndex.url({
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
        staffIndex.url({
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

function confirmDelete(staffMember: Staff) {
    confirm.require({
        message: `Are you sure you want to remove "${staffMember.name}" from staff? This action cannot be undone.`,
        header: 'Remove Staff Member',
        icon: 'pi pi-exclamation-triangle',
        rejectProps: {
            label: 'Cancel',
            severity: 'secondary',
            outlined: true,
        },
        acceptProps: {
            label: 'Remove',
            severity: 'danger',
        },
        accept: () => {
            deleteStaff(staffMember);
        },
    });
}

function deleteStaff(staffMember: Staff) {
    router.delete(`/staff/${staffMember.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            toast.add({
                severity: 'success',
                summary: 'Success',
                detail: 'Staff member removed successfully!',
                life: 3000,
            });
        },
        onError: () => {
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: 'Failed to remove staff member!',
                life: 3000,
            });
        },
    });
}
</script>

<template>
    <Head title="Staff Management" />

    <AppLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2
                    class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200"
                >
                    Staff Management
                </h2>
                <Link
                    href="/staff/create"
                    class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-indigo-500 focus:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-indigo-700"
                >
                    Add New Staff
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div
                    class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800"
                >
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <!-- Search and Per Page -->
                        <div class="mb-4 flex items-center justify-between gap-4">
                            <div class="flex flex-1 items-center gap-2">
                                <InputText
                                    v-model="searchValue"
                                    placeholder="Search staff..."
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
                            :value="staff || []"
                            :loading="loading"
                            :paginator="true"
                            :rows="pagination?.per_page || 10"
                            :total-records="pagination?.total || 0"
                            :rows-per-page-options="[5, 10, 25, 50, 100]"
                            :sort-field="sortField"
                            :sort-order="sortOrder"
                            lazy
                            data-key="id"
                            table-style="min-width: 50rem"
                            show-gridlines
                            striped-rows
                            paginator-template="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                            current-page-report-template="Showing {first} to {last} of {totalRecords} entries"
                            @page="handlePage"
                            @sort="handleSort"
                            class="w-full"
                        >
                            <template #empty>
                                <div class="py-8 text-center text-gray-500">
                                    No staff members found.
                                </div>
                            </template>
                            <template #loading>
                                Loading staff data. Please wait...
                            </template>

                            <Column
                                field="id"
                                header="ID"
                                sortable
                                style="width: 80px"
                            >
                                <template #body="{ data }">
                                    <span class="font-medium">{{
                                        data.id
                                    }}</span>
                                </template>
                            </Column>

                            <Column field="name" header="Staff Member" sortable>
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
                                            <div class="font-medium">
                                                {{ data.name }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ data.email }}
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </Column>

                            <Column
                                field="is_my_staff"
                                header="Status"
                                sortable
                                style="width: 150px"
                            >
                                <template #body="{ data }">
                                    <span
                                        v-if="data.is_my_staff"
                                        class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900 dark:text-green-200"
                                    >
                                        My Staff
                                    </span>
                                    <span
                                        v-else
                                        class="text-sm text-gray-400"
                                    >
                                        Other Staff
                                    </span>
                                </template>
                            </Column>

                            <Column
                                field="created_at"
                                header="Joined"
                                sortable
                                style="width: 150px"
                            >
                                <template #body="{ data }">
                                    <span>{{
                                        new Date(data.created_at).toLocaleDateString()
                                    }}</span>
                                </template>
                            </Column>

                            <Column
                                header="Actions"
                                style="width: 180px"
                                :exportable="false"
                            >
                                <template #body="{ data }">
                                    <div class="flex items-center gap-2">
                                        <Link
                                            :href="`/staff/${data.id}/edit`"
                                            class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500"
                                        >
                                            <i class="pi pi-pencil mr-1"></i>
                                            Edit
                                        </Link>
                                        <Button
                                            icon="pi pi-trash"
                                            severity="danger"
                                            text
                                            rounded
                                            size="small"
                                            @click="confirmDelete(data)"
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

:deep(.p-paginator) {
    background-color: transparent;
    border: none;
}

:deep(.p-datatable-thead > tr > th) {
    background-color: #f9fafb;
}

:deep(.dark .p-datatable-thead > tr > th) {
    background-color: #1f2937;
}
</style>
