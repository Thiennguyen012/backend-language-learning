<?php

namespace App\Repositories\TestType;

use App\Models\TestType\TestType;
use App\Repositories\Base\BaseRepository;

class TestTypeRepository extends BaseRepository implements TestTypeInterface
{
    public function model()
    {
        return TestType::class;
    }
}
