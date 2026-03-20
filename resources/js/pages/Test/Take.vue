```vue
<template>
    <AppLayout>
        <div class="container mx-auto p-4">
            <h1 class="text-2xl font-bold mb-4">Kerjakan Test</h1>

            <form @submit.prevent="submit" class="space-y-6">

                <div
                    v-for="question in questions"
                    :key="question.id"
                    class="border rounded p-4"
                >
                    <h2 class="font-semibold mb-2">
                        {{ question.question_text }}
                    </h2>

                    <!-- RANKING QUESTION -->
                    <template v-if="question.question_type === 'ranking'">

                        <p class="text-sm text-gray-600 mb-3">
                            Drag pilihan dari paling sesuai → paling tidak sesuai
                        </p>

                        <draggable
                            v-model="ranking[question.id]"
                            item-key="id"
                            class="space-y-3"
                        >
                            <template #item="{ element, index }">
                                <div
                                    class="border rounded p-3 bg-white flex justify-between items-center cursor-move"
                                >
                                    <div>
                                        <div class="font-medium">
                                            {{ element.option_text }}
                                        </div>

                                        <div class="text-sm text-gray-500">
                                            Element:
                                            {{ element.element?.name ?? '-' }}
                                        </div>
                                    </div>

                                    <div class="text-sm font-semibold text-gray-600">
                                        Rank {{ index + 1 }}
                                    </div>
                                </div>
                            </template>
                        </draggable>

                    </template>

                    <!-- SINGLE CHOICE -->
                    <template v-else>

                        <p class="text-sm text-gray-600 mb-2">
                            Pilih jawaban yang paling cocok.
                        </p>

                        <div
                            v-for="option in question.options"
                            :key="option.id"
                            class="flex items-center gap-2"
                        >
                            <input
                                type="radio"
                                :name="`question_${question.id}`"
                                :value="option.id"
                                v-model="selectedOption[question.id]"
                            />

                            <span>
                                {{ option.option_label }} - {{ option.option_text }}
                            </span>
                        </div>

                    </template>
                </div>

                <button
                    type="submit"
                    class="bg-blue-500 text-white px-4 py-2 rounded"
                >
                    Selesai
                </button>

            </form>
        </div>
    </AppLayout>
</template>

<script setup>
import { reactive } from 'vue'
import { useForm } from '@inertiajs/vue3'
import draggable from 'vuedraggable'
import AppLayout from '@/layouts/AppLayout.vue'

const props = defineProps({
    session: Object,
    questions: Array,
})

/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

const ranking = reactive({})
const selectedOption = reactive({})

const form = useForm({
    answers: {},
})

/*
|--------------------------------------------------------------------------
| Initialize question state
|--------------------------------------------------------------------------
*/

props.questions.forEach((q) => {

    if (q.question_type === 'ranking') {

        // copy options so draggable can reorder safely
        ranking[q.id] = [...q.options]

    } else {

        selectedOption[q.id] = null

    }

})

/*
|--------------------------------------------------------------------------
| Submit
|--------------------------------------------------------------------------
*/

const submit = () => {

    const payload = {}

    props.questions.forEach((q) => {

        if (q.question_type === 'ranking') {

            const orderedOptions = ranking[q.id]

            const ranks = {}

            orderedOptions.forEach((option, index) => {

                ranks[option.id] = index + 1

            })

            payload[q.id] = { ranks }

        } else {

            payload[q.id] = {
                option_id: selectedOption[q.id]
            }

        }

    })

    form.answers = payload

    form.post(`/test/${props.session.id}/submit`)
}
</script>
```
