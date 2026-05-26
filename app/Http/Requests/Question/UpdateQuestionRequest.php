<?php

namespace App\Http\Requests\Question;

use App\Rules\QuestionDataRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Response;

class UpdateQuestionRequest extends FormRequest
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
            'question_type_id' => 'sometimes|nullable|integer|exists:question_type,id',
            'question_text' => 'sometimes|required|string|max:255',
            'question_data' => ['sometimes', 'required', 'array', new QuestionDataRule($this->input('question_type_id'))],
            'flashcard_reference_ids' => 'sometimes|nullable|array',
            'flashcard_reference_ids.*' => 'integer|exists:flashcard,id',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $data = $this->input('question_data');

        if (is_string($data)) {
            $decoded = json_decode($data, true);
            if (is_array($decoded)) {
                $this->merge(['question_data' => $decoded]);
            }
        }
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
            'question_type_id' => __('validation.attributes.question.question_type_id'),
            'question_text' => __('validation.attributes.question.question_text'),
            'question_data' => __('validation.attributes.question.question_data'),
            'flashcard_reference_ids' => __('validation.attributes.question.flashcard_reference_ids'),
        ];
    }
}
