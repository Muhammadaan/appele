<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Element;

class ElementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Element::create([
            'name' => 'Logam',
            'description' => 'Metal element',
            'color' => 'Silver',
        ]);

        Element::create([
            'name' => 'Tanah',
            'description' => 'Earth element',
            'color' => 'Brown',
        ]);

        Element::create([
            'name' => 'Air',
            'description' => 'Air element',
            'color' => 'Blue',
        ]);

        Element::create([
            'name' => 'Api',
            'description' => 'Fire element',
            'color' => 'Red',
        ]);

        Element::create([
            'name' => 'Kayu',
            'description' => 'Wood element',
            'color' => 'Green',
        ]);
    }
}
