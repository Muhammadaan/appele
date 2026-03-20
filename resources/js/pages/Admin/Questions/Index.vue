<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import type { DataTablePageEvent, DataTableSortEvent } from 'primevue/datatable';
import { useToast } from 'primevue/usetoast';
import { useConfirm } from 'primevue/useconfirm';
import AppLayout from '@/layouts/AppLayout.vue';
import { index as questionsIndex } from '@/routes/questions';

interface Category {
    id: number;
    name: string;
}

interface TestType {
    id: number;
    name: string;
}

interface Question {
    id: number;
    category: {
        id: number;
        name: string;
    } | null;
    test_type: {
        id: number;
        name: string;
    } | null;
    question_text: string;
    question_type: string;
    order_number: number;
    is_active: boolean;
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
    category_id: number | null;
    test_type_id: number | null;
    question_type: string | null;
    sort_field: string;
    sort_order: string;
    per_page: number;
}

interface Props {
    questions: Question[];
    pagination: Pagination;
    filters: Filters;
    categories: Category[];
    testTypes: TestType[];
}

const props = defineProps<Props>();

const toast = useToast();
const confirm = useConfirm();

const searchValue = ref(props.filters?.search || '');
const selectedCategory = ref(props.filters?.category_id || null);
const selectedTestType = ref(props.filters?.test_type_id || null);
const selectedQuestionType = ref(props.filters?.question_type || '');
const loading = ref(false);

const sortField = ref(props.filters?.sort_field || 'order_number');
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
        questionsIndex.url({
            query: {
                search: searchValue.value || null,
                category_id: selectedCategory.value || null,
                test_type_id: selectedTestType.value || null,
                question_type: selectedQuestionType.value || null,
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
        questionsIndex.url({
            query: {
                search: searchValue.value || null,
                category_id: selectedCategory.value || null,
                test_type_id: selectedTestType.value || null,
                question_type: selectedQuestionType.value || null,
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
        questionsIndex.url({
            query: {
                search: searchValue.value || null,
                category_id: selectedCategory.value || null,
                test_type_id: selectedTestType.value || null,
                question_type: selectedQuestionType.value || null,
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
        questionsIndex.url({
            query: {
                search: searchValue.value || null,
                category_id: selectedCategory.value || null,
                test_type_id: selectedTestType.value || null,
                question_type: selectedQuestionType.value || null,
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

function confirmDelete(question: Question) {
    confirm.require({
        message: `Are you sure you want to delete this question? This action cannot be undone.`,
        header: 'Delete Question',
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
            deleteQuestion(question);
        },
    });
}

function deleteQuestion(question: Question) {
    router.delete(`/questions/${question.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            toast.add({
                severity: 'success',
                summary: 'Success',
                detail: 'Question deleted successfully!',
                life: 3000,
            });
        },
        onError: () => {
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: 'Failed to delete question!',
                life: 3000,
            });
        },
    });
}

function getTypeBadgeClass(type: string) {
    return type === 'ranking'
        ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200'
        : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200';
}
</script>

<template>
    <Head title="Questions Management" />

    <AppLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2
                    class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200"
                >
                    Questions Management
                </h2>
                <Link
                    href="/questions/create"
                    class="inline-flex items-center rounded bg-indigo-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-indigo-500"
                >
                    <i class="pi pi-plus mr-1"></i>
                    Create Question
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
                        <div
                            class="mb-4 flex flex-wrap items-center justify-between gap-4"
                        >
                            <div class="flex flex-1 items-center gap-2">
                                <InputText
                                    v-model="searchValue"
                                    placeholder="Search questions..."
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
                                    >Category:</span
                                >
                                <select
                                    v-model="selectedCategory"
                                    class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                    @change="handleSearch"
                                >
                                    <option value="">All</option>
                                    <option
                                        v-for="cat in categories"
                                        :key="cat.id"
                                        :value="cat.id"
                                    >
                                        {{ cat.name }}
                                    </option>
                                </select>
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
                                        v-for="type in testTypes"
                                        :key="type.id"
                                        :value="type.id"
                                    >
                                        {{ type.name }}
                                    </option>
                                </select>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-sm text-gray-600 dark:text-gray-400"
                                    >Type:</span
                                >
                                <select
                                    v-model="selectedQuestionType"
                                    class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                    @change="handleSearch"
                                >
                                    <option value="">All</option>
                                    <option value="single_choice">
                                        Single Choice
                                    </option>
                                    <option value="ranking">Ranking</option>
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
                            :value="questions || []"
                            :loading="loading"
                            :paginator="true"
                            :rows="pagination?.per_page || 10"
                            :total-records="pagination?.total || 0"
                            :rows-per-page-options="[5, 10, 25, 50, 100]"
                            :sort-field="sortField"
                            :sort-order="sortOrder"
                            lazy
                            data-key="id"
                            table-style="min-width: 70rem"
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
                                    No questions found.
                                </div>
                            </template>
                            <template #loading>
                                Loading questions data. Please wait...
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
                                field="category.name"
                                header="Category"
                                sortable
                                style="width: 150px"
                            >
                                <template #body="{ data }">
                                    <span>{{
                                        data.category?.name || '—'
                                    }}</span>
                                </template>
                            </Column>

                            <Column
                                field="test_type.name"
                                header="Test Type"
                                sortable
                                style="width: 150px"
                            >
                                <template #body="{ data }">
                                    <span>{{
                                        data.test_type?.name || '—'
                                    }}</span>
                                </template>
                            </Column>

                            <Column
                                field="question_text"
                                header="Question"
                                sortable
                            >
                                <template #body="{ data }">
                                    <div class="max-w-md truncate">
                                        {{ data.question_text }}
                                    </div>
                                </template>
                            </Column>

                            <Column
                                field="question_type"
                                header="Type"
                                sortable
                                style="width: 120px"
                            >
                                <template #body="{ data }">
                                    <span
                                        :class="[
                                            'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize',
                                            getTypeBadgeClass(data.question_type),
                                        ]"
                                    >
                                        {{ data.question_type }}
                                    </span>
                                </template>
                            </Column>

                            <Column
                                field="order_number"
                                header="Order"
                                sortable
                                style="width: 80px"
                            >
                                <template #body="{ data }">
                                    <span class="font-medium">{{
                                        data.order_number
                                    }}</span>
                                </template>
                            </Column>

                            <Column
                                field="is_active"
                                header="Active"
                                sortable
                                style="width: 100px"
                            >
                                <template #body="{ data }">
                                    <span
                                        :class="[
                                            'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
                                            data.is_active
                                                ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                                : 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
                                        ]"
                                    >
                                        {{ data.is_active ? 'Yes' : 'No' }}
                                    </span>
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
                                            :href="`/questions/${data.id}/edit`"
                                            class="inline-flex items-center rounded-md bg-yellow-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-yellow-400"
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
