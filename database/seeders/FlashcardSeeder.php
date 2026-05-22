<?php

namespace Database\Seeders;

use App\Models\Flashcard\Flashcard;
use App\Models\WordType\WordType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class FlashcardSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        Flashcard::query()->truncate();
        Schema::enableForeignKeyConstraints();

        $nounId = WordType::query()
            ->where('type_name', 'noun')
            ->value('id');

        $flashcards = [
            ['original_word' => 'apple', 'translated_word' => 'quả táo', 'word_type_id' => $nounId],
            ['original_word' => 'banana', 'translated_word' => 'quả chuối', 'word_type_id' => $nounId],
            ['original_word' => 'orange', 'translated_word' => 'quả cam', 'word_type_id' => $nounId],
            ['original_word' => 'grape', 'translated_word' => 'quả nho', 'word_type_id' => $nounId],
            ['original_word' => 'lemon', 'translated_word' => 'quả chanh', 'word_type_id' => $nounId],
            ['original_word' => 'mango', 'translated_word' => 'quả xoài', 'word_type_id' => $nounId],
            ['original_word' => 'pineapple', 'translated_word' => 'quả dứa', 'word_type_id' => $nounId],
            ['original_word' => 'strawberry', 'translated_word' => 'dâu tây', 'word_type_id' => $nounId],
            ['original_word' => 'watermelon', 'translated_word' => 'dưa hấu', 'word_type_id' => $nounId],
            ['original_word' => 'peach', 'translated_word' => 'quả đào', 'word_type_id' => $nounId],
            ['original_word' => 'pear', 'translated_word' => 'quả lê', 'word_type_id' => $nounId],
            ['original_word' => 'cherry', 'translated_word' => 'quả anh đào', 'word_type_id' => $nounId],
            ['original_word' => 'plum', 'translated_word' => 'quả mận', 'word_type_id' => $nounId],
            ['original_word' => 'kiwi', 'translated_word' => 'quả kiwi', 'word_type_id' => $nounId],
            ['original_word' => 'coconut', 'translated_word' => 'dừa', 'word_type_id' => $nounId],
            ['original_word' => 'avocado', 'translated_word' => 'bơ', 'word_type_id' => $nounId],
            ['original_word' => 'carrot', 'translated_word' => 'cà rốt', 'word_type_id' => $nounId],
            ['original_word' => 'potato', 'translated_word' => 'khoai tây', 'word_type_id' => $nounId],
            ['original_word' => 'tomato', 'translated_word' => 'cà chua', 'word_type_id' => $nounId],
            ['original_word' => 'onion', 'translated_word' => 'hành tây', 'word_type_id' => $nounId],
            ['original_word' => 'garlic', 'translated_word' => 'tỏi', 'word_type_id' => $nounId],
            ['original_word' => 'cabbage', 'translated_word' => 'bắp cải', 'word_type_id' => $nounId],
            ['original_word' => 'lettuce', 'translated_word' => 'xà lách', 'word_type_id' => $nounId],
            ['original_word' => 'cucumber', 'translated_word' => 'dưa leo', 'word_type_id' => $nounId],
            ['original_word' => 'pepper', 'translated_word' => 'ớt chuông', 'word_type_id' => $nounId],
            ['original_word' => 'rice', 'translated_word' => 'gạo', 'word_type_id' => $nounId],
            ['original_word' => 'bread', 'translated_word' => 'bánh mì', 'word_type_id' => $nounId],
            ['original_word' => 'milk', 'translated_word' => 'sữa', 'word_type_id' => $nounId],
            ['original_word' => 'cheese', 'translated_word' => 'phô mai', 'word_type_id' => $nounId],
            ['original_word' => 'egg', 'translated_word' => 'trứng', 'word_type_id' => $nounId],
            ['original_word' => 'chicken', 'translated_word' => 'gà', 'word_type_id' => $nounId],
            ['original_word' => 'beef', 'translated_word' => 'thịt bò', 'word_type_id' => $nounId],
            ['original_word' => 'pork', 'translated_word' => 'thịt lợn', 'word_type_id' => $nounId],
            ['original_word' => 'fish', 'translated_word' => 'cá', 'word_type_id' => $nounId],
            ['original_word' => 'shrimp', 'translated_word' => 'tôm', 'word_type_id' => $nounId],
            ['original_word' => 'salt', 'translated_word' => 'muối', 'word_type_id' => $nounId],
            ['original_word' => 'sugar', 'translated_word' => 'đường', 'word_type_id' => $nounId],
            ['original_word' => 'coffee', 'translated_word' => 'cà phê', 'word_type_id' => $nounId],
            ['original_word' => 'tea', 'translated_word' => 'trà', 'word_type_id' => $nounId],
            ['original_word' => 'water', 'translated_word' => 'nước', 'word_type_id' => $nounId],
            ['original_word' => 'book', 'translated_word' => 'sách', 'word_type_id' => $nounId],
            ['original_word' => 'pen', 'translated_word' => 'bút', 'word_type_id' => $nounId],
            ['original_word' => 'phone', 'translated_word' => 'điện thoại', 'word_type_id' => $nounId],
            ['original_word' => 'computer', 'translated_word' => 'máy tính', 'word_type_id' => $nounId],
            ['original_word' => 'table', 'translated_word' => 'bàn', 'word_type_id' => $nounId],
            ['original_word' => 'chair', 'translated_word' => 'ghế', 'word_type_id' => $nounId],
            ['original_word' => 'house', 'translated_word' => 'nhà', 'word_type_id' => $nounId],
            ['original_word' => 'school', 'translated_word' => 'trường học', 'word_type_id' => $nounId],
            ['original_word' => 'teacher', 'translated_word' => 'giáo viên', 'word_type_id' => $nounId],
            ['original_word' => 'student', 'translated_word' => 'học sinh', 'word_type_id' => $nounId],
        ];

        Flashcard::query()->upsert(
            $flashcards,
            ['original_word', 'translated_word'],
            ['original_word', 'translated_word']
        );
    }
}
