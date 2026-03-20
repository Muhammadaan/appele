<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RoleController extends Controller
{
    /**
     * Display a listing of roles with permissions.
     * Only accessible by admin.
     */
    public function index(Request $request): Response
    {
        $roles = Role::with('permissions')->get()->map(function ($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'display_name' => $role->display_name,
                'description' => $role->description,
                'permissions' => $role->permissions->map(fn ($permission) => [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'display_name' => $permission->display_name,
                    'group' => $permission->group,
                ]),
                'users_count' => $role->users()->count(),
            ];
        });

        $permissions = Permission::orderBy('group')->orderBy('name')->get()->map(fn ($permission) => [
            'id' => $permission->id,
            'name' => $permission->name,
            'display_name' => $permission->display_name,
            'group' => $permission->group,
        ]);

        $users = User::all()->map(fn ($user) => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ]);

        return Inertia::render('roles/Index', [
            'roles' => $roles,
            'permissions' => $permissions,
            'users' => $users,
        ]);
    }

    /**
     * Store a new role.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'display_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        Role::create($validated);

        return back()->with('success', 'Role created successfully');
    }

    /**
     * Update an existing role.
     */
    public function update(Request $request, Role $role): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name,' . $role->id],
            'display_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $role->update($validated);

        return back()->with('success', 'Role updated successfully');
    }

    /**
     * Remove a role.
     */
    public function destroy(Request $request, Role $role): RedirectResponse
    {
        // Prevent deleting default roles
        if (in_array($role->name, ['admin', 'owner', 'staff'])) {
            return back()->with('error', 'Cannot delete default roles');
        }

        $role->delete();

        return back()->with('success', 'Role deleted successfully');
    }

    /**
     * Assign permissions to a role.
     */
    public function assignPermissions(Request $request, Role $role): RedirectResponse
    {
        $validated = $request->validate([
            'permission_ids' => ['required', 'array'],
            'permission_ids.*' => ['exists:permissions,id'],
        ]);

        $role->permissions()->sync($validated['permission_ids']);

        return back()->with('success', 'Permissions updated successfully');
    }

    /**
     * Assign a role to a user.
     */
    public function assignToUser(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'role_name' => ['required', 'exists:roles,name'],
            'parent_id' => ['nullable', 'exists:users,id'],
        ]);

        $user = User::findOrFail($validated['user_id']);
        $parent = $validated['parent_id'] ? User::find($validated['parent_id']) : null;

        // Check if user already has this role
        $existingRole = $user->roleUser()->where('role_id', function ($query) use ($validated) {
            $query->select('id')->from('roles')->where('name', $validated['role_name']);
        })->first();

        if ($existingRole) {
            return back()->with('error', 'User already has this role');
        }

        $user->assignRole($validated['role_name'], $parent);

        return back()->with('success', 'Role assigned successfully');
    }

    /**
     * Remove a role from a user.
     */
    public function removeFromUser(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'role_id' => ['required', 'exists:roles,id'],
        ]);

        $user = User::findOrFail($validated['user_id']);

        // Remove the role assignment
        $user->roleUser()->where('role_id', $validated['role_id'])->delete();

        return back()->with('success', 'Role removed successfully');
    }
}
