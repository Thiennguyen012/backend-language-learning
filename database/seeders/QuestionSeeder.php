<?php

namespace Database\Seeders;

use App\Models\Flashcard\Flashcard;
use App\Models\Question\Question;
use App\Models\QuestionType\QuestionType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class QuestionSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        Question::query()->truncate();
        Schema::enableForeignKeyConstraints();

        $typeIds = QuestionType::query()->pluck('id', 'keyword');
        $flashcards = Flashcard::query()->select('id', 'original_word', 'translated_word')->get();

        if ($flashcards->count() < 6 || $typeIds->isEmpty()) {
            return;
        }

        $now = now();
        $questions = [];

        if (isset($typeIds['multiple_choice'])) {
            $questions = array_merge(
                $questions,
                $this->buildMultipleChoice($flashcards, $typeIds['multiple_choice'], $now)
            );
        }

        if (isset($typeIds['true_false'])) {
            $questions = array_merge(
                $questions,
                $this->buildTrueFalse($flashcards, $typeIds['true_false'], $now)
            );
        }

        if (isset($typeIds['fill_in_blank'])) {
            $questions = array_merge(
                $questions,
                $this->buildFillInBlank($flashcards, $typeIds['fill_in_blank'], $now)
            );
        }

        if (isset($typeIds['matching'])) {
            $questions = array_merge(
                $questions,
                $this->buildMatching($flashcards, $typeIds['matching'], $now)
            );
        }

        if (!empty($questions)) {
            Question::query()->insert($questions);
        }
    }

    private function buildMultipleChoice($flashcards, int $typeId, $now): array
    {
        $templates = [
            'Từ "%s" nghĩa là gì?',
            'Từ "%s" được dịch là gì?',
            'Chọn nghĩa đúng của "%s".',
            'Trong bài học này, "%s" là gì?',
            'Từ "%s" nên hiểu thế nào?',
            'Chọn đáp án đúng cho "%s".',
            'Trong ngữ cảnh đời thường, "%s" là gì?',
            'Từ "%s" tương ứng với từ nào?',
            'Nghĩa chính xác của "%s" là gì?',
            'Từ "%s" trong bảng từ vựng này là gì?',
        ];

        $items = $flashcards->values();
        $questions = [];

        for ($i = 0; $i < 10; $i++) {
            $card = $items[$i % $items->count()];
            $distractors = $items->where('id', '!=', $card->id)->shuffle()->take(3);
            $options = $distractors->pluck('translated_word')
                ->push($card->translated_word)
                ->shuffle()
                ->values()
                ->all();

            $referenceIds = $distractors->pluck('id')->push($card->id)->values()->all();

            $questions[] = [
                'question_type_id' => $typeId,
                'question_text' => sprintf($templates[$i], $card->original_word),
                'question_data' => json_encode([
                    'options' => $options,
                    'correct' => $card->translated_word,
                ], JSON_UNESCAPED_UNICODE),
                'flashcard_reference_ids' => json_encode($referenceIds, JSON_UNESCAPED_UNICODE),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        return $questions;
    }

    private function buildTrueFalse($flashcards, int $typeId, $now): array
    {
        $templates = [
            'Từ "%s" nghĩa là "%s".',
            'Dịch nghĩa của "%s" là "%s".',
            'Trong bài học, "%s" được hiểu là "%s".',
            'Khái niệm "%s" tương ứng với "%s".',
            'Đúng hay sai: "%s" là "%s".',
            'Xác nhận: "%s" dịch là "%s".',
            'Đúng hay sai: "%s" nghĩa là "%s".',
            'Ở mức độ khó, "%s" vẫn có nghĩa "%s".',
            'Trong giao tiếp, "%s" tương ứng "%s".',
            'Từ "%s" có nghĩa chính là "%s".',
        ];

        $items = $flashcards->values();
        $questions = [];

        for ($i = 0; $i < 10; $i++) {
            $card = $items[$i % $items->count()];
            $isTrue = $i % 2 === 0;

            $wrongCard = $items->where('id', '!=', $card->id)->shuffle()->first();
            $meaning = $isTrue ? $card->translated_word : $wrongCard->translated_word;
            $referenceIds = $isTrue ? [$card->id] : [$card->id, $wrongCard->id];

            $questions[] = [
                'question_type_id' => $typeId,
                'question_text' => sprintf($templates[$i], $card->original_word, $meaning),
                'question_data' => json_encode([
                    'correct' => $isTrue,
                ], JSON_UNESCAPED_UNICODE),
                'flashcard_reference_ids' => json_encode($referenceIds, JSON_UNESCAPED_UNICODE),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        return $questions;
    }

    private function buildFillInBlank($flashcards, int $typeId, $now): array
    {
        $templates = [
            'Điền từ còn thiếu: "%s" nghĩa là "____".',
            'Điền đúng từ: "%s" tương ứng "____".',
            'Hoàn thành câu: "%s" dịch là "____".',
            'Chọn từ phù hợp: "%s" = "____".',
            'Điền từ thích hợp cho "%s": "____".',
            'Điền nghĩa của "%s": "____".',
            'Hãy viết nghĩa tiếng Việt của "%s": "____".',
            'Ở mức độ khó, hãy điền nghĩa của "%s": "____".',
            'Hoàn thiện: "%s" là "____".',
            'Điền từ còn thiếu cho "%s": "____".',
        ];

        $items = $flashcards->values();
        $questions = [];

        for ($i = 0; $i < 10; $i++) {
            $card = $items[$i % $items->count()];

            $questions[] = [
                'question_type_id' => $typeId,
                'question_text' => sprintf($templates[$i], $card->original_word),
                'question_data' => json_encode([
                    'answer' => $card->translated_word,
                ], JSON_UNESCAPED_UNICODE),
                'flashcard_reference_ids' => json_encode([$card->id], JSON_UNESCAPED_UNICODE),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        return $questions;
    }

    private function buildMatching($flashcards, int $typeId, $now): array
    {
        $templates = [
            'Nối đúng cặp Anh - Việt.',
            'Nối từ tiếng Anh với nghĩa tiếng Việt.',
            'Ghép cặp tương ứng.',
            'Nối đúng cặp từ vựng.',
            'Ở mức độ khó hơn, hãy nối đúng cặp chính xác.',
            'Chọn cặp phù hợp nhất.',
            'Nối cặp theo nghĩa đúng.',
            'Hoàn thành bài nối cặp.',
            'Ghép đúng cặp từ - nghĩa.',
            'Nối từ vựng ở mức nâng cao.',
        ];

        $items = $flashcards->values();
        $questions = [];

        for ($i = 0; $i < 10; $i++) {
            $pairsItems = $items->shuffle()->take(2);
            $pairs = $pairsItems->map(function ($card) {
                return [
                    'left' => $card->original_word,
                    'right' => $card->translated_word,
                ];
            })->values()->all();

            $questions[] = [
                'question_type_id' => $typeId,
                'question_text' => $templates[$i],
                'question_data' => json_encode([
                    'pairs' => $pairs,
                ], JSON_UNESCAPED_UNICODE),
                'flashcard_reference_ids' => json_encode($pairsItems->pluck('id')->values()->all(), JSON_UNESCAPED_UNICODE),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        return $questions;
    }
}
