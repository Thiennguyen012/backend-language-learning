<?php

namespace App\Models\TestType;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestType extends Model
{
    use HasFactory;

    protected $table = 'test_type';

    protected $fillable = [
        'test_type',
    ];
}
