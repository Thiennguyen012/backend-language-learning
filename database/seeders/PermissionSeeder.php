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

        $permissions = array_merge($permissions, $this->generateActionPermissions('admin_dashboard', [
            'view' => 'View admin dashboard',
        ]));

        foreach ($permissions as $permissionName => $description) {
            Permission::updateOrCreate(
                ['permission_name' => $permissionName],
                ['descriptions' => $description]
            );
        }

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

}
