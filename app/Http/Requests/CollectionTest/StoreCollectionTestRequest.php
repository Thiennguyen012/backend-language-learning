<?php

namespace App\Http\Requests\CollectionTest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Response;

class StoreCollectionTestRequest extends FormRequest
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
            'test_type_id' => 'nullable|integer|exists:test_type,id',
            'collection_id' => 'nullable|integer|exists:flashcard_collection,id',
            'test_name' => 'required|string|max:255',
            'total_questions' => 'nullable|integer|min:0',
            'status' => 'nullable|integer',
            'started_at' => 'nullable|date',
            'finished_at' => 'nullable|date',
            'question_ids' => 'nullable|array',
            'question_ids.*' => 'integer|exists:questions,id',
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
            'test_type_id' => __('validation.attributes.collection_test.test_type_id'),
            'collection_id' => __('validation.attributes.collection_test.collection_id'),
            'test_name' => __('validation.attributes.collection_test.test_name'),
            'total_questions' => __('validation.attributes.collection_test.total_questions'),
            'status' => __('validation.attributes.collection_test.status'),
            'started_at' => __('validation.attributes.collection_test.started_at'),
            'finished_at' => __('validation.attributes.collection_test.finished_at'),
            'question_ids' => __('validation.attributes.collection_test.question_ids'),
        ];
    }
}
