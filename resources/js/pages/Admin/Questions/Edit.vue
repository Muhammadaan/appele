<template>
    <AppLayout>
        <div class="container mx-auto p-4">
            <h1 class="text-2xl font-bold mb-4">Edit Question</h1>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label for="category_id" class="block">Category</label>
                    <select v-model="form.category_id" id="category_id" class="border px-2 py-1 w-full">
                        <option value="">-</option>
                        <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                    </select>
                    <span v-if="form.errors.category_id" class="text-red-500">{{ form.errors.category_id }}</span>
                </div>

                <div>
                    <label for="test_type_id" class="block">Test Type</label>
                    <select v-model="form.test_type_id" id="test_type_id" class="border px-2 py-1 w-full">
                        <option value="">-</option>
                        <option v-for="testType in testTypes" :key="testType.id" :value="testType.id">{{ testType.name }}</option>
                    </select>
                    <span v-if="form.errors.test_type_id" class="text-red-500">{{ form.errors.test_type_id }}</span>
                </div>

                <div>
                    <label for="question_text" class="block">Question Text</label>
                    <textarea v-model="form.question_text" id="question_text" class="border px-2 py-1 w-full"></textarea>
                    <span v-if="form.errors.question_text" class="text-red-500">{{ form.errors.question_text }}</span>
                </div>

                <div>
                    <label for="question_type" class="block">Question Type</label>
                    <select v-model="form.question_type" id="question_type" class="border px-2 py-1 w-full">
                        <option value="ranking">Ranking</option>
                        <option value="single_choice">Single Choice</option>
                    </select>
                    <span v-if="form.errors.question_type" class="text-red-500">{{ form.errors.question_type }}</span>
                </div>

                <div>
                    <label for="order_number" class="block">Order Number</label>
                    <input v-model="form.order_number" type="number" id="order_number" class="border px-2 py-1 w-full">
                    <span v-if="form.errors.order_number" class="text-red-500">{{ form.errors.order_number }}</span>
                </div>

                <div v-if="form.question_type === 'ranking'">
                    <h2 class="text-lg font-semibold">Ranking Options (5)</h2>
                    <div v-for="(option, index) in form.options" :key="index" class="space-y-2 border rounded p-3">
                        <div>
                            <label class="block">Option Text</label>
                            <input v-model="option.option_text" type="text" class="border px-2 py-1 w-full" />
                        </div>
                        <div>
                            <label class="block">Element</label>
                            <select v-model="option.element_id" class="border px-2 py-1 w-full">
                                <option value="">Select Element</option>
                                <option v-for="element in elements" :key="element.id" :value="element.id">{{ element.name }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div v-if="form.question_type === 'single_choice'">
                    <h2 class="text-lg font-semibold">Single Choice Options (A/B/C)</h2>
                    <div v-for="(option, index) in form.options" :key="index" class="space-y-2 border rounded p-3">
                        <div>
                            <label class="block">Option Label</label>
                            <input v-model="option.option_label" type="text" class="border px-2 py-1 w-full" readonly />
                        </div>

                        <div>
                            <label class="block">Option Text</label>
                            <input v-model="option.option_text" type="text" class="border px-2 py-1 w-full" />
                        </div>

                        <div>
                            <label class="block">Score</label>
                            <input v-model="option.score" type="number" class="border px-2 py-1 w-full" />
                        </div>
                    </div>
                </div>

                <div>
                    <label for="is_active" class="block">
                        <input v-model="form.is_active" type="checkbox" id="is_active"> Is Active
                    </label>
                    <span v-if="form.errors.is_active" class="text-red-500">{{ form.errors.is_active }}</span>
                </div>

                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Update</button>
            </form>

            <Link href="/questions" class="block mt-4">Back to List</Link>
        </div>
    </AppLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps({
    question: Object,
    categories: Array,
    testTypes: Array,
    elements: Array,
    options: Array,
});

const form = useForm({
    category_id: props.question.category_id,
    test_type_id: props.question.test_type_id,
    question_text: props.question.question_text,
    question_type: props.question.question_type,
    order_number: props.question.order_number,
    is_active: props.question.is_active,
    options: props.options.map((o) => ({
        option_label: o.option_label,
        option_text: o.option_text,
        element_id: o.element_id,
        score: o.score,
    })),
});

const defaultOptions = (type) => {
    if (type === 'single_choice') {
        return [
            { option_label: 'A', option_text: '', score: '' },
            { option_label: 'B', option_text: '', score: '' },
            { option_label: 'C', option_text: '', score: '' },
        ];
    }

    return Array.from({ length: 5 }, () => ({ option_label: '', option_text: '', element_id: '', score: null }));
};

watch(
    () => form.question_type,
    (type) => {
        if (!form.options?.length) {
            form.options = defaultOptions(type);
        }
    },
);

const submit = () => {
    form.put(`/questions/${props.question.id}`);
};
</script>
