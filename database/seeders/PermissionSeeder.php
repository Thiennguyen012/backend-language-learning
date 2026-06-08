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
}
