<?php

namespace App\Repositories\UserTestAttempt;

use App\Models\UserTestAttempt\UserTestAttempt;
use App\Repositories\Base\BaseRepository;

class UserTestAttemptRepository extends BaseRepository implements UserTestAttemptInterface
{
    public function model()
    {
        return UserTestAttempt::class;
    }
}
