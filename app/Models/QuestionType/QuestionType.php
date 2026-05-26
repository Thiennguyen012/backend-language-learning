<?php

namespace App\Models\QuestionType;

use App\Models\Question\Question;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionType extends Model
{
    use HasFactory;

    protected $table = 'question_type';

    protected $fillable = [
        'question_type_name',
        'description',
        'keyword',
    ];

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class, 'question_type_id');
    }
}
