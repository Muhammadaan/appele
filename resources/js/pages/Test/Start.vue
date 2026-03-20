<template>
    <AppLayout>
        <div class="container mx-auto p-4">
            <h1 class="text-2xl font-bold mb-4">Mulai Test Kepribadian</h1>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label for="participant_name" class="block">Nama</label>
                    <input v-model="form.participant_name" id="participant_name" class="border px-2 py-1 w-full" />
                    <span v-if="form.errors.participant_name" class="text-red-500">{{ form.errors.participant_name }}</span>
                </div>

                <div>
                    <div class="text-sm text-gray-600">Sisa token Anda: <span class="font-semibold">{{ authUser?.tokens ?? 0 }}</span></div>
                </div>

                <div>
                    <label for="birth_date" class="block">Tanggal Lahir</label>
                    <input v-model="form.birth_date" id="birth_date" type="date" class="border px-2 py-1 w-full" />
                    <span v-if="form.errors.birth_date" class="text-red-500">{{ form.errors.birth_date }}</span>
                </div>

                <div>
                    <label for="test_type_id" class="block">Pilih Tipe Test</label>
                    <select v-model="form.test_type_id" id="test_type_id" class="border px-2 py-1 w-full">
                        <option value="">-</option>
                        <option v-for="type in testTypes" :key="type.id" :value="type.id">{{ type.name }}</option>
                    </select>
                    <span v-if="form.errors.test_type_id" class="text-red-500">{{ form.errors.test_type_id }}</span>
                </div>

                <template v-if="isAdmin">
                    <div>
                        <label for="user_id" class="block">Test Untuk</label>
                        <select v-model="form.user_id" id="user_id" class="border px-2 py-1 w-full">
                            <option value="">-</option>
                            <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }} ({{ user.email }})</option>
                        </select>
                        <span v-if="form.errors.user_id" class="text-red-500">{{ form.errors.user_id }}</span>
                    </div>
                </template>

                <button class="bg-blue-500 text-white px-4 py-2 rounded">Mulai</button>
            </form>
        </div>
    </AppLayout>
</template>

<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps({
    testTypes: Array,
    users: Array,
    isAdmin: Boolean,
});

const page = usePage();
const authUser = page.props.auth?.user;

const form = useForm({
    participant_name: '',
    birth_date: '',
    test_type_id: '',
});

const submit = () => {
    form.post('/test/start');
};
</script>
