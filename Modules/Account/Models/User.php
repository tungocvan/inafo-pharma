<?php

namespace Modules\Account\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Modules\Account\Models\CustomerProfile;
use Modules\Account\Models\EmployeeProfile;
use Modules\Account\Models\UserMeta;


class User extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    use HasRoles;

    protected $table = 'users';
    protected $guard_name = 'admin';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'avatar',
        'account_type',
        'password',
        'is_active',
        'last_login_at',
        'google_id',
        'google_token',
        'google_refresh_token',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'google_token',
        'google_refresh_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'is_active' => 'boolean',
        'password' => 'hashed',
    ];

    public function isSuperAdmin(): bool
    {
        return $this->roles()
            ->where('name', 'Super Admin')
            ->exists();
    }

    public function roleNamesText(): string
    {
        return $this->roles
            ->pluck('name')
            ->filter()
            ->implode(', ');
    }
    public function employeeProfile()
    {
        return $this->hasOne(EmployeeProfile::class, 'user_id');
    }

    public function customerProfile()
    {
        return $this->hasOne(CustomerProfile::class, 'user_id');
    }

    public function metas()
    {
        return $this->hasMany(UserMeta::class, 'user_id');
    }
}
