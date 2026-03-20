<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Roles
        $adminRole = Role::firstOrCreate([
            'name' => 'admin',
        ], [
            'display_name' => 'Administrator',
            'description' => 'Full system access with ability to manage all users and staff',
        ]);

        $ownerRole = Role::firstOrCreate([
            'name' => 'owner',
        ], [
            'display_name' => 'Owner',
            'description' => 'Product owner who can manage their own staff',
        ]);

        $staffRole = Role::firstOrCreate([
            'name' => 'staff',
        ], [
            'display_name' => 'Staff',
            'description' => 'Staff member with limited access',
        ]);

        // Create Permissions
        $permissions = [
            // Dashboard
            ['name' => 'view-dashboard', 'display_name' => 'View Dashboard', 'group' => 'dashboard'],

            // Questions
            ['name' => 'view-questions', 'display_name' => 'View Questions', 'group' => 'questions'],
            ['name' => 'manage-questions', 'display_name' => 'Manage Questions', 'group' => 'questions'],

            // Tests
            ['name' => 'view-tests', 'display_name' => 'View Tests', 'group' => 'tests'],
            ['name' => 'manage-tests', 'display_name' => 'Manage Tests', 'group' => 'tests'],
            ['name' => 'start-test', 'display_name' => 'Start Test', 'group' => 'tests'],

            // Staff Management
            ['name' => 'manage-staff', 'display_name' => 'Manage Staff', 'group' => 'staff'],
            ['name' => 'view-staff', 'display_name' => 'View Staff', 'group' => 'staff'],

            // User Management
            ['name' => 'manage-users', 'display_name' => 'Manage Users', 'group' => 'users'],
            ['name' => 'view-users', 'display_name' => 'View Users', 'group' => 'users'],

            // Tokens
            ['name' => 'manage-tokens', 'display_name' => 'Manage Tokens', 'group' => 'tokens'],
            ['name' => 'view-tokens', 'display_name' => 'View Tokens', 'group' => 'tokens'],
        ];

        foreach ($permissions as $permissionData) {
            Permission::firstOrCreate(
                ['name' => $permissionData['name']],
                [
                    'display_name' => $permissionData['display_name'],
                    'group' => $permissionData['group'],
                ]
            );
        }

        // Assign Permissions to Roles
        // Admin - Full Access
        $adminPermissions = Permission::all()->pluck('id');
        $adminRole->permissions()->sync($adminPermissions);

        // Owner - Can manage their own staff, view tests and questions
        $ownerPermissions = Permission::whereIn('name', [
            'view-dashboard',
            'view-questions',
            'manage-questions',
            'view-tests',
            'manage-tests',
            'start-test',
            'manage-staff',
            'view-staff',
            'view-tokens',
        ])->pluck('id');
        $ownerRole->permissions()->sync($ownerPermissions);

        // Staff - Basic access only
        $staffPermissions = Permission::whereIn('name', [
            'view-dashboard',
            'view-questions',
            'view-tests',
            'start-test',
        ])->pluck('id');
        $staffRole->permissions()->sync($staffPermissions);

        // Assign admin role to existing admin users (if any)
        User::where('is_admin', true)->each(function ($user) use ($adminRole) {
            $user->assignRole('admin');
        });
    }
}
