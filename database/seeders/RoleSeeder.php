<?php

namespace Database\Seeders;

use App\Models\Permission\Permission;
use App\Models\Role\Role;
use Illuminate\Database\Seeder;
use RuntimeException;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissionNames = [
            'flashcard.view',
            'flashcard_collection.view',
            'collection_test.view',
            'attempt.do',
            'attempt.view',
            'attempt.history',
        ];

        $permissions = Permission::query()
            ->whereIn('permission_name', $permissionNames)
            ->get();

        $missingPermissions = array_diff($permissionNames, $permissions->pluck('permission_name')->all());

        if (!empty($missingPermissions)) {
            throw new RuntimeException('Missing permissions: ' . implode(', ', $missingPermissions));
        }

        $role = Role::query()->updateOrCreate(
            ['role_name' => 'basic_user'],
            ['descriptions' => 'Basic application user']
        );

        $role->permissions()->sync($permissions->pluck('id')->all());
    }
}
