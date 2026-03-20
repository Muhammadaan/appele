<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            TestCategorySeeder::class,
            ElementSeeder::class,
            TestTypeSeeder::class,
            EgmAdultQuestionSeeder::class,
        ]);

        // Create default admin user
        $admin = User::factory()->create([
            'name' => 'Administrator',
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);
        $admin->assignRole('admin');

        // Create sample owner
        $owner = User::factory()->create([
            'name' => 'Product Owner',
            'email' => 'owner@example.com',
        ]);
        $owner->assignRole('owner');

        // Create sample staff
        $staff = User::factory()->create([
            'name' => 'Staff Member',
            'email' => 'staff@example.com',
        ]);
        $staff->assignRole('staff', $owner);
    }
}
