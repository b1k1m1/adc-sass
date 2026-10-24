<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class TenantUser extends Authenticatable
{
    use HasFactory, Notifiable;

    /*
    |--------------------------------------------------------------------------
    | Central SaaS Authentication Database
    |--------------------------------------------------------------------------
    */

    protected $connection = 'master';

    protected $table = 'tenant_users';

    /*
    |--------------------------------------------------------------------------
    | Mass Assignment
    |--------------------------------------------------------------------------
    */

    protected $guarded = [];

    /*
    |--------------------------------------------------------------------------
    | Hidden Fields
    |--------------------------------------------------------------------------
    */

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */

    protected function casts(): array
    {
        return [
            'email_verified_at'  => 'datetime',
            'password'           => 'hashed',
            'last_login_time'    => 'datetime',
            'current_login_time' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Compatibility: Check Role
    |--------------------------------------------------------------------------
    |
    | The authenticated user lives in adc_master.
    | Roles still live inside the currently selected tenant database.
    |
    */

    public function hasRole(string|array $roles): bool
    {
        $roles = (array) $roles;

        if (empty($this->role)) {
            return false;
        }

        return DB::connection('mysql')
            ->table('roles')
            ->where('id', $this->role)
            ->whereIn('name', $roles)
            ->exists();
    }

    /*
    |--------------------------------------------------------------------------
    | Compatibility: Check Any Role
    |--------------------------------------------------------------------------
    */

    public function hasAnyRole(array|string ...$roles): bool
    {
        /*
         * Allow:
         *
         * hasAnyRole(['admin', 'superadmin'])
         *
         * and:
         *
         * hasAnyRole('admin', 'superadmin')
         */

        if (
            count($roles) === 1 &&
            is_array($roles[0])
        ) {
            $roles = $roles[0];
        }

        return $this->hasRole($roles);
    }

    /*
    |--------------------------------------------------------------------------
    | Existing Permission Helpers
    |--------------------------------------------------------------------------
    |
    | These continue reading the currently selected tenant database.
    |
    */

    public static function getPermissionGroups()
    {
        return DB::connection('mysql')
            ->table('permissions')
            ->select('group_name')
            ->groupBy('group_name')
            ->get();
    }

    public static function getpermissionByGroupName($group_name)
    {
        return DB::connection('mysql')
            ->table('permissions')
            ->select('name', 'id')
            ->where('group_name', $group_name)
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | Convenience Organization Relationship
    |--------------------------------------------------------------------------
    */

    public function organization()
    {
        return $this->belongsTo(
            Organization::class,
            'organization_id',
            'id'
        );
    }
}
