<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::middleware(['guest'])->group(function () {
    Route::inertia('/', 'Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ])->name('home');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::redirect('/', '/dashboard');
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::resource('questions', App\Http\Controllers\QuestionController::class);

    // Staff Management Routes (requires manage-staff permission)
    Route::middleware(['permission:manage-staff'])->group(function () {
        Route::resource('staff', App\Http\Controllers\StaffController::class)
            ->except(['show']);
    });

    // User Management Routes (admin only)
    Route::middleware(['role:admin'])->group(function () {
        Route::get('users', [App\Http\Controllers\UserController::class, 'index'])->name('users.index');
        Route::delete('users/{user}', [App\Http\Controllers\UserController::class, 'destroy'])->name('users.destroy');

        // Role & Permission Management
        Route::get('roles', [App\Http\Controllers\RoleController::class, 'index'])->name('roles.index');
        Route::post('roles', [App\Http\Controllers\RoleController::class, 'store'])->name('roles.store');
        Route::put('roles/{role}', [App\Http\Controllers\RoleController::class, 'update'])->name('roles.update');
        Route::delete('roles/{role}', [App\Http\Controllers\RoleController::class, 'destroy'])->name('roles.destroy');
        Route::post('roles/{role}/permissions', [App\Http\Controllers\RoleController::class, 'assignPermissions'])->name('roles.permissions');
        Route::post('roles/assign-to-user', [App\Http\Controllers\RoleController::class, 'assignToUser'])->name('roles.assign-user');
        Route::delete('roles/remove-from-user', [App\Http\Controllers\RoleController::class, 'removeFromUser'])->name('roles.remove-user');

        // Permission Management
        Route::post('permissions', [App\Http\Controllers\PermissionController::class, 'store'])->name('permissions.store');
        Route::put('permissions/{permission}', [App\Http\Controllers\PermissionController::class, 'update'])->name('permissions.update');
        Route::delete('permissions/{permission}', [App\Http\Controllers\PermissionController::class, 'destroy'])->name('permissions.destroy');
    });
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Questions - requires view-questions permission
    Route::middleware(['permission:view-questions'])->group(function () {
        Route::resource('questions', App\Http\Controllers\QuestionController::class);
    });

    // Tests - requires view-tests permission
    Route::middleware(['permission:view-tests'])->group(function () {
        Route::get('test/start', [App\Http\Controllers\PersonalityTestController::class, 'start'])->name('test.start');
        Route::post('test/start', [App\Http\Controllers\PersonalityTestController::class, 'storeStart'])->name('test.start.store');
        Route::get('test/{session}/take', [App\Http\Controllers\PersonalityTestController::class, 'take'])->name('test.take');
        Route::post('test/{session}/submit', [App\Http\Controllers\PersonalityTestController::class, 'submit'])->name('test.submit');
        Route::get('test/{session}/results', [App\Http\Controllers\PersonalityTestController::class, 'results'])->name('test.results');

        Route::get('tests', [App\Http\Controllers\PersonalityTestController::class, 'index'])->name('tests.index');
        Route::get('tests/{session}', [App\Http\Controllers\PersonalityTestController::class, 'show'])->name('tests.show');
    });

    // Tokens - requires view-tokens permission
    Route::middleware(['permission:view-tokens'])->group(function () {
        Route::get('tokens', [App\Http\Controllers\TokenController::class, 'index'])->name('tokens.index');
        Route::post('tokens', [App\Http\Controllers\TokenController::class, 'store'])->name('tokens.store');
        Route::delete('tokens/{tokenHistory}', [App\Http\Controllers\TokenController::class, 'destroy'])->name('tokens.destroy');
    });
});

require __DIR__.'/settings.php';
