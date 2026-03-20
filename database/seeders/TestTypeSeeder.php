<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TestType;

class TestTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TestType::create([
            'name' => 'Anak',
            'description' => 'Test for children',
        ]);

        TestType::create([
            'name' => 'Dewasa',
            'description' => 'Test for adults',
        ]);
    }
}
