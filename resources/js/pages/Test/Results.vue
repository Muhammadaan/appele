<template>
    <AppLayout>
        <div class="container mx-auto p-4">
            <h1 class="text-2xl font-bold mb-4">Hasil Test</h1>

            <div class="mb-6">
                <p class="text-gray-700">Jenis test: <span class="font-semibold">{{ session.test_type.name }}</span></p>
                <p class="text-gray-700">Nama: <span class="font-semibold">{{ session.participant_name }}</span></p>
                <p class="text-gray-700">Selesai: <span class="font-semibold">{{ formattedDate }}</span></p>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div class="border rounded p-4">
                    <h2 class="text-lg font-semibold mb-2">Skor</h2>
                    <p class="text-xl font-bold">{{ result.score }}</p>
                    <p class="text-sm text-gray-600">{{ scoreLabel }}</p>
                </div>

                <div class="border rounded p-4">
                    <h2 class="text-lg font-semibold mb-2">Informasi</h2>
                    <p class="text-sm text-gray-700">
                        <template v-if="session.test_type.name === 'EGM'">
                            Elemen dominan: <span class="font-semibold">{{ result.result_value }}</span>
                        </template>
                        <template v-else>
                            Hasil: <span class="font-semibold">{{ result.result_value }}</span> (Skor: {{ result.score }})
                        </template>
                    </p>
                </div>
            </div>

            <div class="mt-6">
                <h2 class="text-lg font-semibold mb-2">Rangkuman Jawaban</h2>
                <div class="space-y-4">
                    <div
                        v-for="(answer, index) in answers"
                        :key="index"
                        class="border rounded p-3"
                    >
                        <div class="font-medium">{{ answer.question.question_text }}</div>
                        <template v-if="session.test_type.name === 'EGM'">
                            <div class="text-sm text-gray-600">Poin untuk jawaban ini: {{ answer.score }}</div>
                            <div class="text-sm text-gray-600">Rank: {{ answer.rank }}</div>
                            <div class="text-sm text-gray-600">Pilihan: {{ answer.option.option_text }}</div>
                        </template>
                        <template v-else>
                            <div class="text-sm text-gray-600">
                                Dipilih: {{ answer.option.option_label }} - {{ answer.option.option_text }}
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <div class="mt-6">
                <inertia-link href="/">Kembali ke beranda</inertia-link>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';

const { session, results } = defineProps({
    session: Object,
    results: Array,
});

const result = computed(() => {
    return results?.[0] ?? {};
});

const answers = computed(() => {
    return session.test_answers ?? [];
});

const formattedDate = computed(() => {
    return new Date(session.updated_at).toLocaleString();
});

const scoreLabel = computed(() => {
    if (!result.value) return '';

    if (session.test_type.name === 'EGM') {
        if (result.value.score >= 20) return 'Kamu sangat tertarik dan berenergi dengan perubahan';
        if (result.value.score >= 12) return 'Kamu memiliki minat yang baik terhadap perubahan';
        return 'Kamu kurang tertarik dengan perubahan saat ini.';
    }

    const value = result.value.result_value || '';
    switch (value) {
        case 'PROFUNDA':
            return 'Skor ini menunjukkan pendekatan PROFUNDA (energi rendah).';
        case 'ESENSIAL':
            return 'Skor ini menunjukkan pendekatan ESENSIAL (energi sedang).';
        case 'SUPERFISIAL':
            return 'Skor ini menunjukkan pendekatan SUPERFISIAL (energi tinggi).';
        default:
            return '';
    }
});
</script>
