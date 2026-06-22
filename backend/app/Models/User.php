<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\RolePermission;
use App\Notifications\Auth\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'name',
        'email',
        'badge_number',
        'contact_number',
        'position',
        'office_unit',
        'role_id',
        'is_active',
        'email_verified_at',
        'last_login_at',
        'accepted_at',
        'rejected_at',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
        'accepted_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    /**
     * Mutator for hashing the password on save
     *
     * @param string $value
     * @return void
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

      /**
     * Send the password reset notification.
     *
     * @param string $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function createdProjects()
    {
        return $this->hasMany(Project::class, 'created_by');
    }

    public function createdContracts()
    {
        return $this->hasMany(Contract::class, 'created_by');
    }

    public function reviewedAccessRequests()
    {
        return $this->hasMany(AccessRequest::class, 'reviewed_by');
    }

    public function canModule(string $module, string $ability): bool
    {
        if ($this->role?->name === 'System Administrator') {
            return true;
        }

        if (!$this->role_id) {
            return false;
        }

        $permission = $this->modulePermissionRecord($module);

        if (!$permission) {
            return false;
        }

        return match ($ability) {
            'view' => (bool) $permission->can_view,
            'create' => (bool) $permission->can_create,
            'edit' => (bool) $permission->can_edit,
            'delete' => (bool) $permission->can_delete,
            'approve' => (bool) $permission->can_approve,
            'export' => (bool) $permission->can_export,
            default => false,
        };
    }

    public function modulePermissionsMap(): array
    {
        if (!$this->role_id) {
            return [];
        }

        return RolePermission::query()
            ->where('role_id', $this->role_id)
            ->get()
            ->mapWithKeys(fn (RolePermission $permission) => [
                $permission->module => [
                    'can_view' => (bool) $permission->can_view,
                    'can_create' => (bool) $permission->can_create,
                    'can_edit' => (bool) $permission->can_edit,
                    'can_delete' => (bool) $permission->can_delete,
                    'can_approve' => (bool) $permission->can_approve,
                    'can_export' => (bool) $permission->can_export,
                ],
            ])
            ->all();
    }

    private function modulePermissionRecord(string $module): ?RolePermission
    {
        if (!$this->relationLoaded('role')) {
            $this->load('role.permissions');
        } elseif ($this->role && !$this->role->relationLoaded('permissions')) {
            $this->role->load('permissions');
        }

        return $this->role?->permissions?->firstWhere('module', $module);
    }
}
