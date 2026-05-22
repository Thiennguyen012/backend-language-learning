<?php

namespace App\Repositories\Flashcard;

use App\Models\Flashcard\Flashcard;
use App\Repositories\Base\BaseRepository;

class FlashcardRepository extends BaseRepository implements FlashcardInterface
{
    public function model()
    {
        return Flashcard::class;
    }
}
