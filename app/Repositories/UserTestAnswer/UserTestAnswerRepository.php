<?php

namespace App\Repositories\UserTestAnswer;

use App\Models\UserTestAnswer\UserTestAnswer;
use App\Repositories\Base\BaseRepository;

class UserTestAnswerRepository extends BaseRepository implements UserTestAnswerInterface
{
    public function model()
    {
        return UserTestAnswer::class;
    }
}
