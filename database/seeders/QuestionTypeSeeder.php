<?php

namespace Database\Seeders;

use App\Models\QuestionType\QuestionType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class QuestionTypeSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        QuestionType::query()->truncate();
        Schema::enableForeignKeyConstraints();

        $types = [
            [
                'question_type_name' => 'Multiple Choice',
                'description' => 'Chọn đáp án đúng',
                'keyword' => 'multiple_choice',
            ],
            [
                'question_type_name' => 'True/False',
                'description' => 'Chọn đúng sai',
                'keyword' => 'true_false',
            ],
            [
                'question_type_name' => 'Fill in the Blank',
                'description' => 'Điền từ vào chỗ trống',
                'keyword' => 'fill_in_blank',
            ],
            [
                'question_type_name' => 'Matching',
                'description' => 'Nối cặp tương ứng',
                'keyword' => 'matching',
            ],
        ];

        QuestionType::query()->upsert(
            $types,
            ['question_type_name'],
            ['description', 'keyword']
        );
    }
}
