<?php

namespace App\Http\Requests\SpeakingAssessment;

use Illuminate\Foundation\Http\FormRequest;

class SubmitSpeakingLevelAnswerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'audio' => ['required', 'file', 'extensions:wav,mp3,m4a,webm,ogg,aiff', 'max:20480'],
        ];
    }
}
