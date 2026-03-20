<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import type { DataTablePageEvent, DataTableSortEvent } from 'primevue/datatable';
import { useToast } from 'primevue/usetoast';
import { useConfirm } from 'primevue/useconfirm';
import { index as tokensIndex, store as tokensStore, destroy as tokensDestroy } from '@/routes/tokens';
import AppLayout from '@/layouts/AppLayout.vue';

interface User {
    id: number;
    name: string;
    email: string;
    tokens: number;
}

interface CurrentToken {
    id: number;
    name: string;
    email: string;
    tokens: number;
    created_at: string;
}

interface TokenHistory {
    id: number;
    user: {
        id: number;
        name: string;
        email: string;
    };
    amount: number;
    balance_after: number;
    type: string;
    notes?: string | null;
    creator?: {
        id: number;
        name: string;
    } | null;
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
    type: string | null;
    sort_field: string;
    sort_order: string;
    per_page: number;
}

interface Props {
    users: User[];
    currentTokens: CurrentToken[] | null;
    tokenHistories: TokenHistory[] | null;
    pagination: Pagination | null;
    historyFilters: Filters | null;
    isAdmin: boolean;
}

const props = defineProps<Props>();

const toast = useToast();
const confirm = useConfirm();

const form = ref({
    user_id: '',
    amount: 0,
    notes: '',
});

const value = ref('0');
const searchValue = ref(props.historyFilters?.search || '');
const typeFilter = ref(props.historyFilters?.type || '');
const loading = ref(false);
const showConfirmModal = ref(false);

const sortField = ref(props.historyFilters?.sort_field || 'created_at');
const sortOrder = ref(props.historyFilters?.sort_order === 'asc' ? 1 : -1);

const perPageOptions = [
    { label: '5', value: 5 },
    { label: '10', value: 10 },
    { label: '25', value: 25 },
    { label: '50', value: 50 },
    { label: '100', value: 100 },
];

const selectedPerPage = ref(props.historyFilters?.per_page || 10);

// Tab 1 - Current Tokens
const currentTokensSearch = ref('');
const currentTokensSortField = ref('name');
const currentTokensSortOrder = ref(1);

function handleSubmit() {
    // Show confirmation modal instead of submitting directly
    showConfirmModal.value = true;
}

function confirmAddTokens() {
    router.post(
        tokensStore.url(),
        {
            user_id: form.value.user_id,
            amount: form.value.amount,
            notes: form.value.notes,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                form.value.user_id = '';
                form.value.amount = 0;
                form.value.notes = '';
                showConfirmModal.value = false;
                toast.add({
                    severity: 'success',
                    summary: 'Success',
                    detail: 'Tokens added successfully!',
                    life: 3000,
                });
            },
            onError: () => {
                showConfirmModal.value = false;
            },
        },
    );
}

function handleHistorySearch() {
    loading.value = true;
    router.get(
        tokensIndex.url({
            query: {
                search: searchValue.value || null,
                type: typeFilter.value || null,
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

function handleHistoryPage(event: DataTablePageEvent) {
    loading.value = true;
    router.get(
        tokensIndex.url({
            query: {
                search: searchValue.value || null,
                type: typeFilter.value || null,
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

function handleHistorySort(event: DataTableSortEvent) {
    loading.value = true;
    sortField.value = event.sortField as string;
    sortOrder.value = event.sortOrder || 1;
    router.get(
        tokensIndex.url({
            query: {
                search: searchValue.value || null,
                type: typeFilter.value || null,
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

function handleHistoryPerPage() {
    loading.value = true;
    router.get(
        tokensIndex.url({
            query: {
                search: searchValue.value || null,
                type: typeFilter.value || null,
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

function confirmDelete(history: TokenHistory) {
    confirm.require({
        message: `Are you sure you want to delete this token history? This action cannot be undone.`,
        header: 'Delete Token History',
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
            deleteHistory(history);
        },
    });
}

function deleteHistory(history: TokenHistory) {
    router.delete(tokensDestroy.url({ tokenHistory: history.id }), {
        preserveScroll: true,
        onSuccess: () => {
            toast.add({
                severity: 'success',
                summary: 'Success',
                detail: 'Token history deleted successfully!',
                life: 3000,
            });
        },
        onError: () => {
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: 'Failed to delete token history!',
                life: 3000,
            });
        },
    });
}

function getTypeBadgeClass(type: string) {
    const classes: Record<string, string> = {
        purchase: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        refund: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        deduction: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
    };
    return classes[type] || 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200';
}

function handleCurrentTokensSearch() {
    // Client-side search for current tokens
}

function handleCurrentTokensSort(event: DataTableSortEvent) {
    currentTokensSortField.value = event.sortField as string;
    currentTokensSortOrder.value = event.sortOrder || 1;
}
</script>

<template>
    <Head title="Token Management" />

    <AppLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200"
            >
                Token Management
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div
                    class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800"
                >
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <!-- NOT ADMIN -->
                        <div
                            v-if="!isAdmin"
                            class="rounded border bg-yellow-50 p-4 text-sm text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200"
                        >
                            Anda tidak memiliki akses untuk mengelola token.
                        </div>

                        <div v-else>
                            <!-- ADD TOKEN FORM -->
                            <div
                                class="mb-6 rounded border bg-white p-4 dark:bg-gray-900"
                            >
                                <h2 class="mb-3 text-lg font-semibold">
                                    Add Tokens
                                </h2>

                                <form
                                    @submit.prevent="handleSubmit"
                                    class="flex flex-wrap gap-4 items-end"
                                >
                                    <!-- USER SELECT WITH SEARCH -->
                                    <div class="flex-1 min-w-[250px]">
                                        <label class="block text-sm font-medium mb-1">
                                            User
                                        </label>
                                        <Select
                                            v-model="form.user_id"
                                            :options="users"
                                            option-label="name"
                                            option-value="id"
                                            placeholder="Select user"
                                            class="w-full"
                                            :filter="true"
                                            filter-placeholder="Search user..."
                                            :filter-fields="['name', 'email']"
                                            :show-clear="true"
                                            required
                                        >
                                            <template #option="slotProps">
                                                <div class="flex items-center justify-between">
                                                    <span>{{ slotProps.option.name }}</span>
                                                    <span class="text-sm text-gray-500 dark:text-gray-400">
                                                        ({{ slotProps.option.tokens }} tokens)
                                                    </span>
                                                </div>
                                            </template>
                                            <template #value="slotProps">
                                                <div v-if="slotProps.value">
                                                    <span>{{
                                                        users.find((u) => u.id === slotProps.value)?.name
                                                    }}</span>
                                                </div>
                                                <span v-else>{{ slotProps.placeholder }}</span>
                                            </template>
                                        </Select>
                                    </div>

                                    <!-- AMOUNT WITH InputNumber -->
                                    <div class="w-40">
                                        <label class="block text-sm font-medium mb-1">
                                            Amount
                                        </label>
                                        <InputNumber
                                            v-model="form.amount"
                                            :min="1"
                                            :use-grouping="false"
                                            placeholder="0"
                                            class="w-full"
                                            input-class="w-full"
                                            required
                                        />
                                    </div>

                                    <!-- NOTES -->
                                    <div class="flex-1 min-w-[200px]">
                                        <label class="block text-sm font-medium mb-1">
                                            Notes (Optional)
                                        </label>
                                        <InputText
                                            v-model="form.notes"
                                            placeholder="e.g., Purchase on March 1"
                                            maxlength="500"
                                            class="w-full"
                                        />
                                    </div>

                                    <Button
                                        type="submit"
                                        icon="pi pi-plus"
                                        label="Add Tokens"
                                        :disabled="!form.user_id || form.amount < 1"
                                        :loading="form.processing"
                                    />
                                </form>
                            </div>

                            <!-- CONFIRMATION DIALOG -->
                            <Dialog
                                v-model:visible="showConfirmModal"
                                modal
                                header="Confirm Add Tokens"
                                :style="{ width: '30rem' }"
                                :closable="false"
                            >
                                <div class="flex items-start gap-4">
                                    <i
                                        class="pi pi-exclamation-triangle text-3xl text-yellow-500"
                                    ></i>
                                    <div>
                                        <p class="mb-2 text-sm text-gray-700 dark:text-gray-300">
                                            Are you sure you want to add tokens to
                                            <strong class="font-semibold">{{
                                                users.find((u) => u.id === form.user_id)?.name
                                            }}</strong
                                            >?
                                        </p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            <span class="font-semibold">Amount:</span>
                                            {{ form.amount }} tokens
                                        </p>
                                        <p
                                            v-if="form.notes"
                                            class="mt-1 text-sm text-gray-600 dark:text-gray-400"
                                        >
                                            <span class="font-semibold">Notes:</span>
                                            {{ form.notes }}
                                        </p>
                                    </div>
                                </div>

                                <template #footer>
                                    <Button
                                        label="Cancel"
                                        severity="secondary"
                                        @click="showConfirmModal = false"
                                        outlined
                                    />
                                    <Button
                                        label="Confirm"
                                        icon="pi pi-check"
                                        @click="confirmAddTokens"
                                        :loading="form.processing"
                                    />
                                </template>
                            </Dialog>

                            <!-- TABS -->
                            <Tabs v-model:value="value">
                                <TabList>
                                    <Tab value="0">Current Tokens</Tab>
                                    <Tab value="1">Token History</Tab>
                                </TabList>

                                <TabPanels>
                                    <!-- TAB 1: CURRENT TOKENS -->
                                    <TabPanel value="0">
                                        <div class="py-4">
                                            <h3 class="mb-4 text-lg font-medium">
                                                All Users Token Balance
                                            </h3>

                                            <!-- Search -->
                                            <div class="mb-4 flex items-center gap-2">
                                                <InputText
                                                    v-model="currentTokensSearch"
                                                    placeholder="Search users..."
                                                    class="w-full max-w-md"
                                                    @keyup.enter="handleCurrentTokensSearch"
                                                />
                                                <Button
                                                    icon="pi pi-search"
                                                    label="Search"
                                                    @click="handleCurrentTokensSearch"
                                                />
                                            </div>

                                            <!-- DataTable -->
                                            <DataTable
                                                :value="currentTokens || []"
                                                :sort-field="currentTokensSortField"
                                                :sort-order="currentTokensSortOrder"
                                                data-key="id"
                                                table-style="min-width: 50rem"
                                                show-gridlines
                                                striped-rows
                                                @sort="handleCurrentTokensSort"
                                                class="w-full"
                                            >
                                                <template #empty>
                                                    <div class="py-8 text-center text-gray-500">
                                                        No users found.
                                                    </div>
                                                </template>
                                                <template #loading>
                                                    Loading users data. Please wait...
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
                                                    field="name"
                                                    header="User"
                                                    sortable
                                                >
                                                    <template #body="{ data }">
                                                        <div>
                                                            <div class="font-medium">
                                                                {{ data.name }}
                                                            </div>
                                                            <div class="text-sm text-gray-500">
                                                                {{ data.email }}
                                                            </div>
                                                        </div>
                                                    </template>
                                                </Column>

                                                <Column
                                                    field="tokens"
                                                    header="Current Balance"
                                                    sortable
                                                    style="width: 150px"
                                                >
                                                    <template #body="{ data }">
                                                        <span
                                                            :class="[
                                                                'font-bold text-lg',
                                                                data.tokens > 0
                                                                    ? 'text-green-600'
                                                                    : 'text-gray-400',
                                                            ]"
                                                        >
                                                            {{ data.tokens }}
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
                                                            new Date(
                                                                data.created_at,
                                                            ).toLocaleDateString()
                                                        }}</span>
                                                    </template>
                                                </Column>
                                            </DataTable>
                                        </div>
                                    </TabPanel>

                                    <!-- TAB 2: TOKEN HISTORY -->
                                    <TabPanel value="1">
                                        <div class="py-4">
                                            <h3 class="mb-4 text-lg font-medium">
                                                Token Transaction History
                                            </h3>

                                            <!-- Search and Filters -->
                                            <div
                                                class="mb-4 flex flex-wrap items-center justify-between gap-4"
                                            >
                                                <div
                                                    class="flex flex-1 items-center gap-2"
                                                >
                                                    <InputText
                                                        v-model="searchValue"
                                                        placeholder="Search by user..."
                                                        class="w-full max-w-md"
                                                        @keyup.enter="handleHistorySearch"
                                                    />
                                                    <Button
                                                        icon="pi pi-search"
                                                        label="Search"
                                                        @click="handleHistorySearch"
                                                        :loading="loading"
                                                    />
                                                </div>

                                                <div class="flex items-center gap-2">
                                                    <span
                                                        class="text-sm text-gray-600 dark:text-gray-400"
                                                        >Type:</span
                                                    >
                                                    <select
                                                        v-model="typeFilter"
                                                        class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                                        @change="handleHistorySearch"
                                                    >
                                                        <option value="">All</option>
                                                        <option value="purchase">
                                                            Purchase
                                                        </option>
                                                        <option value="refund">
                                                            Refund
                                                        </option>
                                                        <option value="deduction">
                                                            Deduction
                                                        </option>
                                                    </select>
                                                </div>

                                                <div class="flex items-center gap-2">
                                                    <span
                                                        class="text-sm text-gray-600 dark:text-gray-400"
                                                        >Per Page:</span
                                                    >
                                                    <select
                                                        v-model="selectedPerPage"
                                                        class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                                        @change="handleHistoryPerPage"
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
                                                :value="tokenHistories || []"
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
                                                @page="handleHistoryPage"
                                                @sort="handleHistorySort"
                                                class="w-full"
                                            >
                                                <template #empty>
                                                    <div class="py-8 text-center text-gray-500">
                                                        No token history found.
                                                    </div>
                                                </template>
                                                <template #loading>
                                                    Loading token history data. Please wait...
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
                                                    field="user.name"
                                                    header="User"
                                                    sortable
                                                >
                                                    <template #body="{ data }">
                                                        <div>
                                                            <div class="font-medium">
                                                                {{ data.user.name }}
                                                            </div>
                                                            <div class="text-sm text-gray-500">
                                                                {{ data.user.email }}
                                                            </div>
                                                        </div>
                                                    </template>
                                                </Column>

                                                <Column
                                                    field="amount"
                                                    header="Amount"
                                                    sortable
                                                    style="width: 120px"
                                                >
                                                    <template #body="{ data }">
                                                        <span
                                                            :class="[
                                                                'font-semibold',
                                                                data.type === 'deduction'
                                                                    ? 'text-red-600'
                                                                    : 'text-green-600',
                                                            ]"
                                                        >
                                                            {{ data.type === 'deduction'
                                                                ? '-'
                                                                : '+' }}{{
                                                                data.amount
                                                            }}
                                                        </span>
                                                    </template>
                                                </Column>

                                                <Column
                                                    field="balance_after"
                                                    header="Balance After"
                                                    sortable
                                                    style="width: 120px"
                                                >
                                                    <template #body="{ data }">
                                                        <span class="font-medium">{{
                                                            data.balance_after
                                                        }}</span>
                                                    </template>
                                                </Column>

                                                <Column
                                                    field="type"
                                                    header="Type"
                                                    sortable
                                                    style="width: 120px"
                                                >
                                                    <template #body="{ data }">
                                                        <span
                                                            :class="[
                                                                'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize',
                                                                getTypeBadgeClass(data.type),
                                                            ]"
                                                        >
                                                            {{ data.type }}
                                                        </span>
                                                    </template>
                                                </Column>

                                                <Column
                                                    field="creator.name"
                                                    header="Created By"
                                                    sortable
                                                    style="width: 150px"
                                                >
                                                    <template #body="{ data }">
                                                        <span>{{
                                                            data.creator?.name || 'System'
                                                        }}</span>
                                                    </template>
                                                </Column>

                                                <Column
                                                    field="notes"
                                                    header="Notes"
                                                    sortable
                                                    style="width: 200px"
                                                >
                                                    <template #body="{ data }">
                                                        <span class="text-sm text-gray-600 dark:text-gray-400">{{
                                                            data.notes || '-'
                                                        }}</span>
                                                    </template>
                                                </Column>

                                                <Column
                                                    field="created_at"
                                                    header="Date"
                                                    sortable
                                                    style="width: 150px"
                                                >
                                                    <template #body="{ data }">
                                                        <span>{{
                                                            new Date(
                                                                data.created_at,
                                                            ).toLocaleDateString()
                                                        }}</span>
                                                    </template>
                                                </Column>

                                                <Column
                                                    header="Actions"
                                                    style="width: 80px"
                                                    :exportable="false"
                                                >
                                                    <template #body="{ data }">
                                                        <Button
                                                            icon="pi pi-trash"
                                                            severity="danger"
                                                            text
                                                            rounded
                                                            size="small"
                                                            @click="confirmDelete(data)"
                                                        />
                                                    </template>
                                                </Column>
                                            </DataTable>
                                        </div>
                                    </TabPanel>
                                </TabPanels>
                            </Tabs>
                        </div>
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

:deep(.p-tabs-tablist) {
    background-color: #f9fafb;
}

:deep(.dark .p-tabs-tablist) {
    background-color: #1f2937;
}
</style>
