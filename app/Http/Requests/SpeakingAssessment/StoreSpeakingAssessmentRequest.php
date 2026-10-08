<?php

namespace App\Http\Requests\SpeakingAssessment;

use Illuminate\Foundation\Http\FormRequest;

class StoreSpeakingAssessmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reference_text' => ['required', 'string', 'max:3000'],
            'dialect' => ['required', 'string', 'in:en-us,en-gb,fr-fr,fr-ca,es-es,es-mx'],
            'idempotency_key' => ['required', 'uuid'],
            // Android's AAC-in-MP4 recordings are commonly identified by PHP as
            // audio/mp4 or application/octet-stream, so Laravel's content-based
            // `mimes` rule rejects valid .m4a uploads. Validate the uploaded file,
            // its allowed client extension, and size instead.
            'audio' => ['required', 'file', 'extensions:wav,mp3,m4a,webm,ogg,aiff', 'max:10240'],
        ];
    }
}
