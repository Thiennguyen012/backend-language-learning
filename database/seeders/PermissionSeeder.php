<?php

namespace Database\Seeders;

use App\Models\Permission\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modules = [
            'user' => 'users',
            'role' => 'roles',
            'permission' => 'permissions',
            'flashcard' => 'flashcards',
            'flashcard_collection' => 'flashcard collections',
            'collection_test' => 'collection tests',
            'user_test_attempt' => 'user test attempts',
            'user_test_answer' => 'user test answers',
            'test_type' => 'test types',
            'question' => 'questions',
        ];

        $permissions = [];
        foreach ($modules as $module => $label) {
            $permissions = array_merge($permissions, $this->generateCrudPermissions($module, $label));
        }

        $permissions = array_merge($permissions, $this->generateActionPermissions('attempt', [
            'do' => 'Start, answer and submit attempts',
            'view' => 'View attempt details and questions',
            'history' => 'View own attempt history',
        ]));

        foreach ($permissions as $permissionName => $description) {
            Permission::updateOrCreate(
                ['permission_name' => $permissionName],
                ['descriptions' => $description]
            );
        }

        $this->replaceDeprecatedAttemptPermissions();
    }

    private function generateCrudPermissions(string $module, string $label): array
    {
        return [
            "{$module}.view" => "View {$label}",
            "{$module}.create" => "Create {$label}",
            "{$module}.update" => "Update {$label}",
            "{$module}.delete" => "Delete {$label}",
        ];
    }

    private function generateActionPermissions(string $module, array $actions): array
    {
        $permissions = [];

        foreach ($actions as $action => $description) {
            $permissions["{$module}.{$action}"] = $description;
        }

        return $permissions;
    }

    private function replaceDeprecatedAttemptPermissions(): void
    {
        $deprecatedPermissions = Permission::query()
            ->with('roles:id')
            ->whereIn('permission_name', [
                'attempt.start',
                'attempt.answer',
                'attempt.submit',
            ])
            ->get();

        $roleIds = $deprecatedPermissions
            ->flatMap(fn (Permission $permission) => $permission->roles->pluck('id'))
            ->unique()
            ->values()
            ->all();

        $attemptPermission = Permission::query()
            ->where('permission_name', 'attempt.do')
            ->firstOrFail();

        if (!empty($roleIds)) {
            $attemptPermission->roles()->syncWithoutDetaching($roleIds);
        }

        foreach ($deprecatedPermissions as $permission) {
            $permission->delete();
        }
    }
}
