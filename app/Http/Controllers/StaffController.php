<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StaffController extends Controller
{
    /**
     * Display a listing of staff members.
     * Only accessible by users with 'manage-staff' permission or admin.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        // Admin can see all staff, owners can only see their own staff
        $query = User::whereHas('roles', function ($q) {
            $q->where('name', 'staff');
        });

        if (! $user->isAdmin()) {
            $query->whereHas('roleUser', function ($q) use ($user) {
                $q->where('parent_id', $user->id);
            });
        }

        // Search
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortField = $request->get('sort_field', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortField, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 10);
        $staff = $query->paginate($perPage);

        $staffData = $staff->map(function ($staffMember) use ($user) {
            return [
                'id' => $staffMember->id,
                'name' => $staffMember->name,
                'email' => $staffMember->email,
                'avatar' => $staffMember->avatar,
                'created_at' => $staffMember->created_at,
                'is_my_staff' => $staffMember->roleUser->first()?->parent_id === $user->id,
            ];
        });

        return Inertia::render('staff/Index', [
            'staff' => $staffData,
            'pagination' => [
                'current_page' => $staff->currentPage(),
                'last_page' => $staff->lastPage(),
                'per_page' => $staff->perPage(),
                'total' => $staff->total(),
                'from' => $staff->firstItem(),
                'to' => $staff->lastItem(),
            ],
            'filters' => [
                'search' => $request->get('search'),
                'sort_field' => $sortField,
                'sort_order' => $sortOrder,
                'per_page' => $perPage,
            ],
        ]);
    }

    /**
     * Show the form for creating a new staff member.
     */
    public function create(): Response
    {
        return Inertia::render('staff/Create');
    }

    /**
     * Store a newly created staff member.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $user = $request->user();

        // Create the new user
        $staff = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
        ]);

        // Assign staff role with the current user as parent (owner)
        $staff->assignRole('staff', $user);

        return redirect()->route('staff.index')
            ->with('success', 'Staff member created successfully.');
    }

    /**
     * Show the form for editing the specified staff member.
     */
    public function edit(Request $request, User $staff): Response
    {
        $user = $request->user();

        // Check if user can edit this staff member
        if (! $user->isAdmin() && $staff->roleUser->first()?->parent_id !== $user->id) {
            abort(403, 'You can only edit your own staff members.');
        }

        return Inertia::render('staff/Edit', [
            'staff' => [
                'id' => $staff->id,
                'name' => $staff->name,
                'email' => $staff->email,
            ],
        ]);
    }

    /**
     * Update the specified staff member.
     */
    public function update(Request $request, User $staff): RedirectResponse
    {
        $user = $request->user();

        // Check if user can edit this staff member
        if (! $user->isAdmin() && $staff->roleUser->first()?->parent_id !== $user->id) {
            abort(403, 'You can only update your own staff members.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $staff->id],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $staff->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if (! empty($validated['password'])) {
            $staff->update([
                'password' => bcrypt($validated['password']),
            ]);
        }

        return redirect()->route('staff.index')
            ->with('success', 'Staff member updated successfully.');
    }

    /**
     * Remove the specified staff member.
     */
    public function destroy(Request $request, User $staff): RedirectResponse
    {
        $user = $request->user();

        // Check if user can delete this staff member
        if (! $user->isAdmin() && $staff->roleUser->first()?->parent_id !== $user->id) {
            abort(403, 'You can only delete your own staff members.');
        }

        // Remove the role assignment
        $staff->roleUser()->delete();

        // Optionally delete the user account
        $staff->delete();

        return redirect()->route('staff.index')
            ->with('success', 'Staff member removed successfully.');
    }
}
