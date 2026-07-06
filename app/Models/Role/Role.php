<?php

namespace App\Models\Role;

use App\Models\Permission\Permission;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasFactory;
    const ROLE_SUPER_ADMIN = 'super_admin';
    const ROLE_BASIC_USER = 'basic_user';

    protected $fillable = ['role_name', 'descriptions'];

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_role', 'role_id', 'permission_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'role_user', 'role_id', 'user_id');
    }

    public static function getBasicUserRoleID(): ?int
    {
        return static::query()
            ->where('role_name', self::ROLE_BASIC_USER)
            ->value('id');
    }
}
