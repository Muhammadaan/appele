<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import type { DataTablePageEvent, DataTableSortEvent } from 'primevue/datatable';
import AppLayout from '@/layouts/AppLayout.vue';
import { index as testsIndex } from '@/routes/tests';

interface TestType {
    id: number;
    name: string;
}

interface Session {
    id: number;
    participant_name: string;
    test_type: {
        id: number;
        name: string;
    } | null;
    certificate_number: string;
    started_at: string;
    completed_at: string | null;
    user: {
        id: number;
        name: string;
    } | null;
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
    test_type_id: number | null;
    sort_field: string;
    sort_order: string;
    per_page: number;
}

interface Props {
    sessions: Session[];
    pagination: Pagination;
    filters: Filters;
    testTypes: TestType[];
}

const props = defineProps<Props>();

const searchValue = ref(props.filters?.search || '');
const selectedTestType = ref(props.filters?.test_type_id || null);
const loading = ref(false);

const sortField = ref(props.filters?.sort_field || 'started_at');
const sortOrder = ref(props.filters?.sort_order === 'asc' ? 1 : -1);

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
        testsIndex.url({
            query: {
                search: searchValue.value || null,
                test_type_id: selectedTestType.value || null,
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
        testsIndex.url({
            query: {
                search: searchValue.value || null,
                test_type_id: selectedTestType.value || null,
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
        testsIndex.url({
            query: {
                search: searchValue.value || null,
                test_type_id: selectedTestType.value || null,
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
        testsIndex.url({
            query: {
                search: searchValue.value || null,
                test_type_id: selectedTestType.value || null,
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

function formatDate(value: string) {
    return new Date(value).toLocaleString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function getStatusSeverity(status: string) {
    if (status === 'completed') return 'success';
    if (status === 'in_progress') return 'warning';
    return 'info';
}
</script>

<template>
    <Head title="My Tests" />

    <AppLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2
                    class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200"
                >
                    My Tests
                </h2>
                <Link
                    href="/test/start"
                    class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-indigo-500 focus:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-indigo-700"
                >
                    Start New Test
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div
                    class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800"
                >
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <!-- Search and Filters -->
                        <div class="mb-4 flex flex-wrap items-center justify-between gap-4">
                            <div class="flex flex-1 items-center gap-2">
                                <InputText
                                    v-model="searchValue"
                                    placeholder="Search tests..."
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
                                    >Test Type:</span
                                >
                                <select
                                    v-model="selectedTestType"
                                    class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                    @change="handleSearch"
                                >
                                    <option value="">All</option>
                                    <option
                                        v-for="testType in testTypes"
                                        :key="testType.id"
                                        :value="testType.id"
                                    >
                                        {{ testType.name }}
                                    </option>
                                </select>
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
                            :value="sessions || []"
                            :loading="loading"
                            :paginator="true"
                            :rows="pagination?.per_page || 10"
                            :total-records="pagination?.total || 0"
                            :rows-per-page-options="[5, 10, 25, 50, 100]"
                            :sort-field="sortField"
                            :sort-order="sortOrder"
                            lazy
                            data-key="id"
                            table-style="min-width: 60rem"
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
                                    Belum ada test yang diselesaikan. Silakan mulai test baru dari menu "Test".
                                </div>
                            </template>
                            <template #loading>
                                Loading test data. Please wait...
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

                            <Column
                                field="participant_name"
                                header="Participant"
                                sortable
                            >
                                <template #body="{ data }">
                                    <div>
                                        <div class="font-medium">
                                            {{ data.participant_name }}
                                        </div>
                                        <div
                                            v-if="data.user"
                                            class="text-sm text-gray-500"
                                        >
                                            By: {{ data.user.name }}
                                        </div>
                                    </div>
                                </template>
                            </Column>

                            <Column
                                field="test_type.name"
                                header="Test Type"
                                sortable
                                style="width: 200px"
                            >
                                <template #body="{ data }">
                                    <span>{{
                                        data.test_type?.name || 'N/A'
                                    }}</span>
                                </template>
                            </Column>

                            <Column
                                field="certificate_number"
                                header="Certificate"
                                sortable
                                style="width: 150px"
                            >
                                <template #body="{ data }">
                                    <span class="font-mono text-sm">{{
                                        data.certificate_number
                                    }}</span>
                                </template>
                            </Column>

                            <Column
                                field="started_at"
                                header="Started At"
                                sortable
                                style="width: 180px"
                            >
                                <template #body="{ data }">
                                    <span>{{ formatDate(data.started_at) }}</span>
                                </template>
                            </Column>

                            <Column
                                header="Actions"
                                style="width: 120px"
                                :exportable="false"
                            >
                                <template #body="{ data }">
                                    <Link
                                        :href="`/tests/${data.id}`"
                                        class="inline-flex items-center rounded-md bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-500"
                                    >
                                        <i class="pi pi-eye mr-1"></i>
                                        Detail
                                    </Link>
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
