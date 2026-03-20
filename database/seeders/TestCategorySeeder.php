<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TestCategory;

class TestCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TestCategory::create([
            'name' => 'EGM Personality',
            'description' => 'Personality assessment category',
        ]);

        TestCategory::create([
            'name' => 'Energy Flow',
            'description' => 'Energy flow assessment category',
        ]);
    }
}
