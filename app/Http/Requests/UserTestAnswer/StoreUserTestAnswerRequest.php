<?php

namespace App\Http\Requests\UserTestAnswer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Response;

class StoreUserTestAnswerRequest extends FormRequest
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
            'user_test_attempt_id' => 'required|integer|exists:user_test_attempts,id',
            'question_id' => 'required|integer|exists:questions,id',
            'user_answer' => 'required|string',
            'is_correct' => 'required|integer|in:0,1',
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
            'user_test_attempt_id' => __('validation.attributes.user_test_answer.user_test_attempt_id'),
            'question_id' => __('validation.attributes.user_test_answer.question_id'),
            'user_answer' => __('validation.attributes.user_test_answer.user_answer'),
            'is_correct' => __('validation.attributes.user_test_answer.is_correct'),
        ];
    }
}
