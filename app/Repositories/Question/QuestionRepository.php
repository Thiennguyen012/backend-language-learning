<?php

namespace App\Repositories\Question;

use App\Models\Question\Question;
use App\Repositories\Base\BaseRepository;

class QuestionRepository extends BaseRepository implements QuestionInterface
{
    public function model()
    {
        return Question::class;
    }
}
