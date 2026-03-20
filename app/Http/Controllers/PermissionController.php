<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PermissionController extends Controller
{
    /**
     * Store a new permission.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:permissions,name'],
            'display_name' => ['required', 'string', 'max:255'],
            'group' => ['required', 'string', 'max:255'],
        ]);

        Permission::create($validated);

        return back()->with('success', 'Permission created successfully');
    }

    /**
     * Update an existing permission.
     */
    public function update(Request $request, Permission $permission): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:permissions,name,' . $permission->id],
            'display_name' => ['required', 'string', 'max:255'],
            'group' => ['required', 'string', 'max:255'],
        ]);

        $permission->update($validated);

        return back()->with('success', 'Permission updated successfully');
    }

    /**
     * Remove a permission.
     */
    public function destroy(Request $request, Permission $permission): RedirectResponse
    {
        // Prevent deleting default permissions
        $defaultPermissions = ['view-dashboard', 'view-questions', 'manage-questions', 'view-tests', 'manage-tests', 'start-test', 'manage-staff', 'view-staff', 'manage-users', 'view-users', 'manage-tokens', 'view-tokens'];

        if (in_array($permission->name, $defaultPermissions)) {
            return back()->with('error', 'Cannot delete default permissions');
        }

        $permission->delete();

        return back()->with('success', 'Permission deleted successfully');
    }
}
