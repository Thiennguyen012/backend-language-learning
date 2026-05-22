<?php

namespace App\Repositories\FlashcardCollection;

use App\Models\FlashcardCollection\FlashcardCollection;
use App\Repositories\Base\BaseRepository;

class FlashcardCollectionRepository extends BaseRepository implements FlashcardCollectionInterface
{
    public function model()
    {
        return FlashcardCollection::class;
    }
}
