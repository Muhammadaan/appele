<template>
    <AppLayout>
        <div class="container mx-auto p-4">
            <h1 class="text-2xl font-bold mb-4">Create Question</h1>

            <form @submit.prevent="submit" class="space-y-4">

                <!-- CATEGORY -->
                <div>
                    <label class="block">Category</label>
                    <select v-model="form.category_id" class="border px-2 py-1 w-full">
                        <option value="">-</option>
                        <option v-for="category in categories" :key="category.id" :value="category.id">
                            {{ category.name }}
                        </option>
                    </select>
                </div>

                <!-- TEST TYPE -->
                <div>
                    <label class="block">Test Type</label>
                    <select v-model="form.test_type_id" class="border px-2 py-1 w-full">
                        <option value="">-</option>
                        <option v-for="testType in testTypes" :key="testType.id" :value="testType.id">
                            {{ testType.name }}
                        </option>
                    </select>
                </div>

                <!-- QUESTION -->
                <div>
                    <label class="block">Question Text</label>
                    <textarea v-model="form.question_text" class="border px-2 py-1 w-full"></textarea>
                </div>

                <!-- QUESTION TYPE -->
                <div>
                    <label class="block">Question Type</label>
                    <select v-model="form.question_type" class="border px-2 py-1 w-full">
                        <option value="ranking">Ranking</option>
                        <option value="single_choice">Single Choice</option>
                    </select>
                </div>

                <!-- ORDER -->
                <div>
                    <label class="block">Order Number</label>
                    <input v-model="form.order_number" type="number" class="border px-2 py-1 w-full">
                </div>

                <!-- RANKING -->
                <div v-if="form.question_type === 'ranking'">
                    <h2 class="text-lg font-semibold">Ranking Options</h2>

                    <div
                    
                        v-for="(option, index) in form.options"
                        :key="index"
                        class="space-y-2 border rounded p-3"
                    >
                        <div>
                            <label class="block">Element</label>
                            <input
                                :value="option.element_name"
                                class="border px-2 py-1 w-full bg-gray-100"
                                readonly
                            />
                        </div>

                        <div>
                            <label class="block">Option Text</label>
                            <input
                                v-model="option.option_text"
                                type="text"
                                class="border px-2 py-1 w-full"
                            />
                        </div>
                    </div>
                </div>

                <!-- SINGLE CHOICE -->
                <div v-if="form.question_type === 'single_choice'">
                    <h2 class="text-lg font-semibold">Single Choice Options</h2>

                    <div
                        v-for="(option, index) in form.options"
                        :key="index"
                        class="space-y-2 border rounded p-3"
                    >
                        <div>
                            <label class="block">Label</label>
                            <input
                                v-model="option.option_label"
                                readonly
                                class="border px-2 py-1 w-full bg-gray-100"
                            />
                        </div>

                        <div>
                            <label class="block">Option Text</label>
                            <input
                                v-model="option.option_text"
                                class="border px-2 py-1 w-full"
                            />
                        </div>

                        <div>
                            <label class="block">Score</label>
                            <input
                                v-model="option.score"
                                type="number"
                                class="border px-2 py-1 w-full"
                            />
                        </div>
                    </div>
                </div>

                <!-- ACTIVE -->
                <div>
                    <label class="block">
                        <input v-model="form.is_active" type="checkbox">
                        Is Active
                    </label>
                </div>

                <button
                    type="submit"
                    class="bg-blue-500 text-white px-4 py-2 rounded"
                >
                    Create
                </button>
            </form>

            <Link href="/questions" class="block mt-4">
                Back to List
            </Link>
        </div>
    </AppLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import { watch } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'

const props = defineProps({
    categories: Array,
    testTypes: Array,
    elements: Array,
})

/*
|--------------------------------
| Mapping element ranking (statis)
|--------------------------------
*/

const rankingElements = [
    { id: 1, name: 'Logam' },
    { id: 2, name: 'Air' },
    { id: 3, name: 'Kayu' },
    { id: 4, name: 'Api' },
    { id: 5, name: 'Tanah' },
]

/*
|--------------------------------
| Form
|--------------------------------
*/

const form = useForm({
    category_id: '',
    test_type_id: '',
    question_text: '',
    question_type: 'ranking',
    order_number: '',
    is_active: true,
    options: [],
})

/*
|--------------------------------
| Default Options
|--------------------------------
*/

const defaultOptions = (type) => {

    if (type === 'single_choice') {
        return [
            { option_label: 'A', option_text: '', score: '' },
            { option_label: 'B', option_text: '', score: '' },
            { option_label: 'C', option_text: '', score: '' },
        ]
    }

    return rankingElements.map(el => ({
        element_id: el.id,
        element_name: el.name,
        option_text: '',
        score: null,
    }))
}

form.options = defaultOptions(form.question_type)

/*
|--------------------------------
| Watch Question Type
|--------------------------------
*/

watch(
    () => form.question_type,
    (type) => {
        form.options = defaultOptions(type)
    }
)

/*
|--------------------------------
| Submit
|--------------------------------
*/

const submit = () => {
    form.post('/questions')
}
</script>