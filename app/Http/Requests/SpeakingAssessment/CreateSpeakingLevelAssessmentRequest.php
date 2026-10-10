<?php

namespace App\Http\Requests\SpeakingAssessment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateSpeakingLevelAssessmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'topic' => ['required', 'string', Rule::in(array_keys(config('speaking_level.topics', [])))],
        ];
    }
}
