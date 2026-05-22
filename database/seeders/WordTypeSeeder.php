<?php

namespace Database\Seeders;

use App\Models\WordType\WordType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WordTypeSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['type_name' => 'noun'],
            ['type_name' => 'verb'],
            ['type_name' => 'adjective'],
            ['type_name' => 'adverb'],
            ['type_name' => 'pronoun'],
            ['type_name' => 'preposition'],
            ['type_name' => 'conjunction'],
            ['type_name' => 'interjection'],
        ];

        WordType::query()->upsert($types, ['type_name'], ['type_name']);
    }
}
