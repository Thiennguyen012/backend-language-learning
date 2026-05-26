<?php

namespace App\Repositories\CollectionTest;

use App\Models\CollectionTest\CollectionTest;
use App\Repositories\Base\BaseRepository;

class CollectionTestRepository extends BaseRepository implements CollectionTestInterface
{
    public function model()
    {
        return CollectionTest::class;
    }
}
