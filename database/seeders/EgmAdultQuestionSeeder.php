<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Question;
use App\Models\Option;

class EgmAdultQuestionSeeder extends Seeder
{
    public function run(): void
    {

        $questions = [

            [
                'question' => 'SAAT BANGUN PAGI - Apa yang biasanya langsung muncul di pikiranmu begitu membuka mata?',
                'options' => [
                    [1,'Langsung ingat jadwal/to-do list hari ini. Harus on-time!'],
                    [2,'Melakukan rutinitas yang sama persis (minum air, merapikan selimut).'],
                    [3,'Masih ingin guling-guling dulu menikmati kasur yang empuk.'],
                    [4,'Langsung cek HP! Lihat notifikasi WA atau Instagram.'],
                    [5,'Punya ide baru atau semangat baru. Hari ini mau ngapain ya yang seru?'],
                ]
            ],

            [
                'question' => 'GAYA BELAJAR / BEKERJA - Bagaimana caramu menyelesaikan tugas yang menumpuk?',
                'options' => [
                    [1,'Fokus total. Matikan HP dan kerjakan satu per satu sampai tuntas.'],
                    [2,'Dicicil pelan-pelan tapi pasti. Sedikit demi sedikit lama-lama menjadi bukit.'],
                    [3,'Sambil dengar musik atau nyemil supaya suasana nyaman.'],
                    [4,'Semangat di awal tapi cepat bosan di tengah jalan.'],
                    [5,'Suka loncat-loncat. Kerjakan A sebentar lalu pindah ke B.'],
                ]
            ],

        ];

        foreach ($questions as $index => $q) {

            $question = Question::create([
                'category_id' => 1,
                'test_type_id' => 1,
                'question_text' => $q['question'],
                'question_type' => 'ranking',
                'order_number' => $index + 1,
                'is_active' => true
            ]);

            foreach ($q['options'] as $opt) {

                Option::create([
                    'question_id' => $question->id,
                    'option_label' => null,
                    'option_text' => $opt[1],
                    'element_id' => $opt[0],
                    'score' => null
                ]);

            }

        }

    }
}