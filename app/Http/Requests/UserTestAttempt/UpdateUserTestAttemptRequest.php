<?php

namespace App\Http\Requests\UserTestAttempt;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Response;

class UpdateUserTestAttemptRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'user_id' => 'sometimes|required|integer|exists:users,id',
            'collection_test_id' => 'sometimes|required|integer|exists:collection_test,id',
            'status' => 'sometimes|nullable|integer',
            'correct_count' => 'sometimes|required|integer|min:0',
            'total_score' => 'sometimes|required|integer|min:0',
            'started_time' => 'sometimes|nullable|date',
            'finished_time' => 'sometimes|nullable|date',
            'total_time' => 'sometimes|nullable|date_format:H:i:s',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        $response = response()->json([
            'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
            'message' => __('validation.invalid'),
            'errors' => $validator->errors()
        ], Response::HTTP_UNPROCESSABLE_ENTITY);

        throw new \Illuminate\Validation\ValidationException($validator, $response);
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'user_id' => __('validation.attributes.user_test_attempt.user_id'),
            'collection_test_id' => __('validation.attributes.user_test_attempt.collection_test_id'),
            'status' => __('validation.attributes.user_test_attempt.status'),
            'correct_count' => __('validation.attributes.user_test_attempt.correct_count'),
            'total_score' => __('validation.attributes.user_test_attempt.total_score'),
            'started_time' => __('validation.attributes.user_test_attempt.started_time'),
            'finished_time' => __('validation.attributes.user_test_attempt.finished_time'),
            'total_time' => __('validation.attributes.user_test_attempt.total_time'),
        ];
    }
}
