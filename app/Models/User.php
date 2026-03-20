<?php

namespace App\Models;

use App\Models\Permission;
use App\Models\RoleUser;
use App\Models\TokenHistory;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'tokens',
    ];

    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'is_admin' => 'boolean',
            'tokens' => 'integer',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public function tokenHistories(): HasMany
    {
        return $this->hasMany(TokenHistory::class);
    }

    public function roleUser(): HasMany
    {
        return $this->hasMany(RoleUser::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id')
            ->withPivot('parent_id')
            ->withTimestamps();
    }

    public function staff(): HasMany
    {
        return $this->hasMany(RoleUser::class, 'parent_id')->whereHas('role', function ($query) {
            $query->where('name', 'staff');
        });
    }

    public function owner(): HasMany
    {
        return $this->hasMany(RoleUser::class, 'parent_id')->whereHas('role', function ($query) {
            $query->where('name', 'owner');
        });
    }

    public function assignRole(string $roleName, ?User $parent = null): self
    {
        $role = Role::firstOrCreate(
            ['name' => $roleName],
            ['display_name' => ucfirst($roleName)]
        );

        RoleUser::create([
            'user_id' => $this->id,
            'role_id' => $role->id,
            'parent_id' => $parent?->id,
        ]);

        return $this;
    }

    public function hasRole(string|array $roleNames): bool
    {
        $roleNames = is_array($roleNames) ? $roleNames : [$roleNames];

        return $this->roles()->whereIn('name', $roleNames)->exists();
    }

    public function hasPermission(string $permissionName): bool
    {
        foreach ($this->roles as $role) {
            if ($role->hasPermission($permissionName)) {
                return true;
            }
        }

        return false;
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin') || $this->is_admin;
    }

    public function isOwner(): bool
    {
        return $this->hasRole('owner');
    }

    public function isStaff(): bool
    {
        return $this->hasRole('staff');
    }

    public function getPermissionsAttribute(): array
    {
        $permissions = [];

        foreach ($this->roles as $role) {
            foreach ($role->permissions as $permission) {
                $permissions[] = $permission->name;
            }
        }

        return array_unique($permissions);
    }
}