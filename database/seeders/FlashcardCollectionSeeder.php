<?php

namespace Database\Seeders;

use App\Models\Flashcard\Flashcard;
use App\Models\FlashcardCollection\FlashcardCollection;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FlashcardCollectionSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('flashcard_flashcard_collection')->truncate();
        FlashcardCollection::query()->truncate();
        Schema::enableForeignKeyConstraints();

        $collections = [
            [
                'collection_name' => 'Basic Vocabulary',
                'description' => 'Common words for daily use',
            ],
            [
                'collection_name' => 'Food and Drink',
                'description' => 'Vocabulary about food and drinks',
            ],
        ];

        FlashcardCollection::query()->upsert(
            $collections,
            ['collection_name'],
            ['description']
        );

        $foodWords = [
            'apple',
            'banana',
            'orange',
            'grape',
            'lemon',
            'mango',
            'pineapple',
            'strawberry',
            'watermelon',
            'peach',
            'pear',
            'cherry',
            'plum',
            'kiwi',
            'coconut',
            'avocado',
            'carrot',
            'potato',
            'tomato',
            'onion',
            'garlic',
            'cabbage',
            'lettuce',
            'cucumber',
            'pepper',
            'rice',
            'bread',
            'milk',
            'cheese',
            'egg',
            'chicken',
            'beef',
            'pork',
            'fish',
            'shrimp',
            'salt',
            'sugar',
            'coffee',
            'tea',
            'water',
        ];

        $basicWords = [
            'book',
            'pen',
            'phone',
            'computer',
            'table',
            'chair',
            'house',
            'school',
            'teacher',
            'student',
        ];

        $foodIds = Flashcard::query()
            ->whereIn('original_word', $foodWords)
            ->pluck('id')
            ->all();

        $basicIds = Flashcard::query()
            ->whereIn('original_word', $basicWords)
            ->pluck('id')
            ->all();

        $foodCollection = FlashcardCollection::query()
            ->where('collection_name', 'Food and Drink')
            ->first();
        if ($foodCollection) {
            $foodCollection->flashcards()->sync($foodIds);
        }

        $basicCollection = FlashcardCollection::query()
            ->where('collection_name', 'Basic Vocabulary')
            ->first();
        if ($basicCollection) {
            $basicCollection->flashcards()->sync($basicIds);
        }
    }
}
